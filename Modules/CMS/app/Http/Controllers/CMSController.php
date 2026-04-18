<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CMS\Actions\Page\UpdateHomePageContentAction;
use Modules\CMS\Enums\PageStatusEnum;
use Modules\CMS\Enums\PageTemplateEnum;
use Modules\CMS\Http\Requests\HomePageRequest;
use Modules\CMS\Http\Requests\PageRequest;
use Modules\CMS\Repository\Page\PageInterface;

class CMSController extends Controller
{
    public function __construct(public PageInterface $pages) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->pages->index();

        return view('cms::pages.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentOptions = $this->pages->getParentOptions();
        $statusOptions = PageStatusEnum::getAllEnumValuesKeysLabel();
        $templateOptions = PageTemplateEnum::getAllEnumValuesKeysLabel();

        return view('cms::pages.create', compact('parentOptions', 'statusOptions', 'templateOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PageRequest $request)
    {
        $page = $this->pages->store($request);

        return redirect()->route('cms.edit', $page->id)->with('success', trans('core::core.env.save'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $page = $this->pages->find($id);

        return view('cms::pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $page = $this->pages->find($id);
        $parentOptions = $this->pages->getParentOptions();
        $statusOptions = PageStatusEnum::getAllEnumValuesKeysLabel();
        $templateOptions = PageTemplateEnum::getAllEnumValuesKeysLabel();

        return view('cms::pages.edit', compact('page', 'parentOptions', 'statusOptions', 'templateOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PageRequest $request, $id)
    {
        $this->pages->update($id, $request);

        return redirect()->route('cms.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->pages->destroy($id);

        return redirect()->route('cms.index')->with('success', trans('core::core.env.save'));
    }

    /**
     * Show the form for editing home page.
     */
    public function editHome()
    {
        $page = $this->pages->getHomePage();

        if (! $page) {
            return redirect()->route('cms.index')
                ->with('error', 'Home page not found. Please run the seeder first.');
        }

        return view('cms::pages.edit-home', compact('page'));
    }

    /**
     * Update the home page content.
     */
    public function updateHome(HomePageRequest $request, UpdateHomePageContentAction $action)
    {
        try {
            $action->handle($request);

            return redirect()->route('cms.home.edit')
                ->with('success', trans('core::core.env.save'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating home page: '.$e->getMessage())
                ->withInput();
        }
    }
}
