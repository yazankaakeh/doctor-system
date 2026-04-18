<?php

namespace Modules\Doctor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Doctor\Http\Requests\UploadFileRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UploadFileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(UploadFileRequest $request)
    {
        $model = $request->model::find($request->model_id);

        foreach ($request->file('files', []) as $uploadedFile) {
            $model
                ->addMedia($uploadedFile)
                ->withCustomProperties([
                    'uploaded_by' => optional(auth()->user())->id,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                ])
                ->toMediaCollection('attachments');
        }

        return redirect()->back()->with('success', trans('customer.card.savedSuccessfully'));
    }

    public function delete(Request $request, $id)
    {
        $media = Media::findOrFail($id);
        $media->delete();

        return redirect()->back()->with('success', 'File deleted successfully');
    }
}
