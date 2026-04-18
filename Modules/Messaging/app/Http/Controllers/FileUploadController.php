<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Handle temporary file upload for messaging.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');

        if (! $file || ! $file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file upload',
            ], 422);
        }

        try {
            // Capture file info before moving
            $originalName = $file->getClientOriginalName();
            $originalExtension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Generate unique filename
            $filename = Str::uuid().'.'.$originalExtension;
            $relativePath = 'messaging-temp/'.$filename;

            // Get the destination path
            $destinationPath = storage_path('app/public/messaging-temp');

            // Ensure directory exists
            if (! is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the file directly (works better on Windows)
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
     * Remove temporary file.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Only allow removing files in messaging-temp directory
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
