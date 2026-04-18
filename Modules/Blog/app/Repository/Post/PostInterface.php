<?php

namespace Modules\Blog\Repository\Post;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Modules\Blog\Models\BlogPost;

interface PostInterface
{
    public function index(): LengthAwarePaginator;

    public function store(Request $request): BlogPost;

    public function find(int $id): BlogPost;

    public function update(int $id, Request $request): BlogPost;

    public function destroy(int $id): void;

    /** @return array<int,string> id => localized title */
    public function getRelatedOptions($id = null): array;
}
