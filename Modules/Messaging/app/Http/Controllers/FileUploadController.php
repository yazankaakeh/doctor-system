<?php

/**
 * -----------------------------------------------------------------------------
 * FileUploadController
 * -----------------------------------------------------------------------------
 *
 * Handles TEMPORARY uploads used by the message composer. When an agent
 * picks a file to attach, the browser POSTs it here; the controller stores
 * the file inside `storage/app/public/messaging-temp/<uuid>.<ext>` and
 * returns a JSON descriptor (path, URL, size, mime) that the composer keeps
 * in its state until the message itself is actually sent.
 *
 * Once the message is persisted, the file gets promoted to a permanent
 * Attachment row by the sender service; files left behind are swept by a
 * scheduled cleanup job.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Upload a file to the temporary messaging bucket.
     *
     * Returns JSON on success/failure; the front-end composer uses the
     * returned path/URL to display a preview before the message is sent.
     */
    public function upload(Request $request)
    {
        // 10 MB max — matches WhatsApp's document size limit so we never
        // accept something that couldn't be forwarded through the driver.
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        if (! $file || ! $file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file upload',
            ], 422);
        }

        try {
            // Snapshot meta BEFORE move() — UploadedFile is invalidated afterwards.
            $originalName = $file->getClientOriginalName();
            $originalExtension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Randomised filename prevents guessable URLs and collisions.
            $filename = Str::uuid().'.'.$originalExtension;
            $relativePath = 'messaging-temp/'.$filename;

            // Resolve target directory under storage/app/public.
            $destinationPath = storage_path('app/public/messaging-temp');

            // Ensure the directory exists (first upload after install).
            if (! is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Using move() instead of Storage::put() works more reliably on
            // Windows/XAMPP — avoids locked file handles.
            $file->move($destinationPath, $filename);

            $fullPath = $destinationPath.DIRECTORY_SEPARATOR.$filename;

            return response()->json([
                'success' => true,
                'path' => $relativePath,
                'url' => asset('storage/'.$relativePath),
                'name' => $originalName,
                'size' => file_exists($fullPath) ? filesize($fullPath) : $size,
                'mime_type' => $mimeType,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a previously-uploaded temporary file (fired when the user
     * changes their mind and deletes an attachment before sending).
     *
     * Important: we restrict the accepted path to `messaging-temp/*` so
     * that this endpoint can't be used to delete arbitrary files on the
     * public disk.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Path traversal guard — only accept files in the temp folder.
        if (! str_starts_with($path, 'messaging-temp/')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid path',
            ], 422);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['success' => true]);
    }
}
