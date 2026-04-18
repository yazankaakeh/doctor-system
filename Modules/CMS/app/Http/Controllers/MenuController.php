<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CMS\Http\Requests\MenuRequest;
use Modules\CMS\Models\Page;
use Modules\CMS\Repository\Menu\MenuInterface;

class MenuController extends Controller
{
    public function __construct(public MenuInterface $menus) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->menus->index();

        return view('cms::menus.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pages = Page::published()->get()->mapWithKeys(function ($page) {
            $title = $page->getTranslation('title', app()->getLocale());

            return [$page->id => $title];
        })->toArray();

        return view('cms::menus.create', compact('pages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuRequest $request)
    {
        $this->menus->store($request);

        return redirect()->route('menus.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $menu = $this->menus->find($id);

        return view('cms::menus.show', compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $menu = $this->menus->find($id);

        $pages = Page::published()->get()->mapWithKeys(function ($page) {
            $title = $page->getTranslation('title', app()->getLocale());

            return [$page->id => $title];
        })->toArray();

        return view('cms::menus.edit', compact('menu', 'pages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuRequest $request, $id)
    {
        $this->menus->update($id, $request);

        return redirect()->route('menus.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->menus->destroy($id);

        return redirect()->route('menus.index')->with('success', trans('core::core.env.save'));
    }
}
