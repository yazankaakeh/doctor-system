<?php

namespace Modules\Blog\Repository\Tag;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Blog\Http\Requests\TagRequest;
use Modules\Blog\Models\BlogPostTags;

class TagRepository implements TagInterface
{
    public function index(): LengthAwarePaginator
    {
        return BlogPostTags::query()->paginate(Pagination::PAG->value);
    }

    public function store(TagRequest $request): BlogPostTags
    {
        $validated = $request->validated();

        $tag = new BlogPostTags;
        $tag->name = $validated['name'];
        $tag->save();

        return $tag;
    }

    public function update(int $id, TagRequest $request): BlogPostTags
    {
        $validated = $request->validated();
        $tag = $this->find($id);

        $tag->name = $validated['name'];
        $tag->save();

        return $tag;
    }

    public function find(int $id): BlogPostTags
    {
        return BlogPostTags::query()->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $tag = $this->find($id);
        $tag->delete();
    }

    public function getTagOptions(): array
    {
        return BlogPostTags::all()->mapWithKeys(function ($tag) {
            $name = $tag->getTranslation('name', app()->getLocale());

            return [$tag->id => $name ?: 'Tag '.$tag->id];
        })->toArray();
    }
}
