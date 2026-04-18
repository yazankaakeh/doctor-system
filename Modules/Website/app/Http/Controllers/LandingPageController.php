<?php

namespace Modules\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\CMS\Actions\Page\GetHomePageAction;
use Modules\CMS\Repository\Panel\PanelInterface;

class LandingPageController extends Controller
{
    public function home(GetHomePageAction $action, PanelInterface $panelRepository): View|\Illuminate\Foundation\Application|Factory|Application
    {
        // Fetch the landing page from CMS using Action
        $landingPage = $action->handleOrFail();

        // Get current locale
        $locale = app()->getLocale();

        // Extract meta_data sections (for backward compatibility)
        $sections = $landingPage->meta_data ?? [];

        // Fetch active panels with their items for the landing page
        $panels = $panelRepository->getActiveByPage($landingPage->id);

        // Transform panels into a structured format for the view
        $panelsData = $panels->map(function ($panel) {
            return [
                'id' => $panel->id,
                'type' => $panel->type->value,
                'title' => $panel->getTranslations('title'),
                'settings' => $panel->settings ?? [],
                'is_active' => $panel->is_active,
                'order' => $panel->order,
                'media' => [
                    'panel_image' => $panel->getFirstMediaUrl('panel_image') ?: null,
                    'panel_gallery' => $panel->getMedia('panel_gallery')->map(fn ($m) => $m->getUrl())->toArray(),
                ],
                'items' => $panel->activeItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type->value,
                        'title' => $item->getTranslations('title'),
                        'content' => $item->getTranslations('content'),
                        'data' => $item->data ?? [],
                        'is_active' => $item->is_active,
                        'order' => $item->order,
                        'media' => [
                            'item_image' => $item->getFirstMediaUrl('item_image') ?: null,
                            'item_gallery' => $item->getMedia('item_gallery')->map(fn ($m) => $m->getUrl())->toArray(),
                        ],
                    ];
                })->toArray(),
            ];
        });

        return view('website::newLanding.home', compact('landingPage', 'sections', 'locale', 'panelsData'));
    }

    public function privacy(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('website::newLanding.privacy');
    }

    public function hiHelloInfo(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('website::landing.hiHelloInfo');
    }

    public function hiHelloCreate(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('website::landing.hiHelloInfoCreate');
    }

    public function comingSoon(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('website::landing.soon');
    }

    public function hiHelloBlog(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('website::landing.blog');
    }
}
