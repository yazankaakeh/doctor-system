<?php

namespace Modules\Blog\Repository\Category;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Modules\Blog\Models\BlogCategory;

interface CategoryInterface
{
    public function index(): LengthAwarePaginator;

    public function store(Request $request): BlogCategory;

    public function find(int $id): BlogCategory;

    public function update(int $id, Request $request): BlogCategory;

    public function destroy(int $id): void;
}
