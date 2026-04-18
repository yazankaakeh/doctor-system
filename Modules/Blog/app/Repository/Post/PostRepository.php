<?php

namespace Modules\Blog\Repository\Post;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Blog\Models\BlogPost;
use Modules\Seo\Models\SeoMeta;

class PostRepository implements PostInterface
{
    public function index(): LengthAwarePaginator
    {
        return BlogPost::query()->paginate(Pagination::PAG->value);
    }

    public function store(Request $request): BlogPost
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $post = new BlogPost;
            $post->title = $validated['title'];
            $post->description = $validated['description'] ?? [];
            $post->type = $validated['type'] ?? null;
            $post->save();

            // related posts
            if (! empty($validated['relatedPosts']) && is_array($validated['relatedPosts'])) {
                $post->relatedPosts()->sync($validated['relatedPosts']);
            }

            // tags
            if (! empty($validated['tags']) && is_array($validated['tags'])) {
                $post->tags()->sync($validated['tags']);
            }

            if ($request->hasFile('image')) {
                $post->addMediaFromRequest('image')->toMediaCollection('img');
            }

            $this->saveSeo($post, $validated);

            return $post;
        });
    }

    private function saveSeo(BlogPost $post, array $validated): void
    {
        $metaTitle = $validated['meta_title'] ?? null;
        $metaDescription = $validated['meta_description'] ?? null;
        if ($metaTitle || $metaDescription) {
            $seo = $post->seo ?: new SeoMeta;
            if ($metaTitle) {
                $seo->title = $metaTitle;
            }
            if ($metaDescription) {
                $seo->meta_description = $metaDescription;
            }
            $post->seo()->save($seo);
        }
    }

    public function update(int $id, Request $request): BlogPost
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($id, $request, $validated) {
            $post = $this->find($id);
            $post->title = $validated['title'];
            $post->description = $validated['description'] ?? [];
            if (isset($validated['type'])) {
                $post->type = $validated['type'];
            }
            $post->save();

            if (array_key_exists('relatedPosts', $validated)) {
                $post->relatedPosts()->sync($validated['relatedPosts'] ?? []);
            }

            if (array_key_exists('tags', $validated)) {
                $post->tags()->sync($validated['tags'] ?? []);
            }

            if ($request->hasFile('image')) {
                $post->addMediaFromRequest('image')->toMediaCollection('img');
            }

            $this->saveSeo($post, $validated);

            return $post;
        });
    }

    public function find(int $id): BlogPost
    {
        return BlogPost::query()->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $post = $this->find($id);
        $post->delete();
    }

    public function getRelatedOptions($id = null): array
    {
        return BlogPost::query()
            ->select(['id', 'title'])
            ->when($id, function ($query) use ($id) {
                return $query->whereNot('id', $id);
            })
            ->get()
            ->mapWithKeys(function (BlogPost $p) {
                $title = $p->getTranslation('title', app()->getLocale());

                return [$p->id => is_string($title) ? $title : (json_encode($title) ?: 'Post '.$p->id)];
            })->toArray();
    }
}
