<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuillUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'], // 4MB
        ]);

        // Store to /storage/app/public/quill
        $path = $request->file('image')->store('quill', 'public');

        // Public URL (make sure storage:link is set)
        $url = Storage::disk('public')->url($path);

        return response()->json(['url' => $url]);
    }
}
