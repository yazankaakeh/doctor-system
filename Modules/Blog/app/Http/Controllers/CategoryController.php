<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Blog\Http\Requests\CategoryRequest;
use Modules\Blog\Repository\Category\CategoryInterface;

class CategoryController extends Controller
{
    public function __construct(public CategoryInterface $categories) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->categories->index();

        return view('blog::category.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blog::category.create');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = $this->categories->find($id);

        return view('blog::category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        $this->categories->update($id, $request);

        return redirect()->route('doctor.categories.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->categories->destroy($id);

        return redirect()->route('doctor.categories.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $this->categories->store($request);

        return redirect()->route('doctor.categories.index')->with('success', trans('core::core.env.save'));
    }
}
