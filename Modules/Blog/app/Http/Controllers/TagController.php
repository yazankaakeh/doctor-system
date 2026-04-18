<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Blog\Http\Requests\TagRequest;
use Modules\Blog\Repository\Tag\TagInterface;

class TagController extends Controller
{
    public function __construct(public TagInterface $tags) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->tags->index();

        return view('blog::tags.index', compact('data'));
    }

    /**
     * Store a newly created tag via AJAX.
     */
    public function storeAjax(TagRequest $request): JsonResponse
    {
        $tag = $this->tags->store($request);

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->getTranslation('name', app()->getLocale()),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $request): RedirectResponse
    {
        $this->tags->store($request);

        return redirect()->route('doctor.tags.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tag = $this->tags->find($id);

        return view('blog::tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagRequest $request, $id): RedirectResponse
    {
        $this->tags->update($id, $request);

        return redirect()->route('doctor.tags.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $this->tags->destroy($id);

        return redirect()->route('doctor.tags.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Get all tags for select options.
     */
    public function getOptions(): JsonResponse
    {
        $tags = $this->tags->getTagOptions();

        return response()->json([
            'success' => true,
            'tags' => $tags,
        ]);
    }
}
