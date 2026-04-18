<?php

namespace Modules\CMS\Actions\Page;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CMS\Models\Page;
use Modules\CMS\Repository\Page\PageInterface;

class UpdateHomePageContentAction
{
    public function __construct(
        private readonly PageInterface $pageRepository
    ) {}

    public function handle(Request $request): Page
    {
        $homePage = $this->pageRepository->getHomePage();

        if (! $homePage) {
            throw new \Exception('Home page not found.');
        }

        return DB::transaction(function () use ($homePage, $request) {
            // Get current meta_data
            $metaData = $homePage->meta_data ?? [];

            // Update Hero section
            $metaData['hero'] = [
                'title' => $request->input('hero_title', []),
                'subtitle' => $request->input('hero_subtitle', []),
                'description' => $request->input('hero_description', []),
                'cta_text' => $request->input('hero_cta_text', []),
                'cta_url' => $request->input('hero_cta_url', $metaData['hero']['cta_url'] ?? ''),
                'image' => $metaData['hero']['image'] ?? '',
            ];

            // Handle hero image upload
            if ($request->hasFile('hero_image')) {
                $heroImage = $request->file('hero_image');
                $heroImageName = 'hero_'.time().'.'.$heroImage->getClientOriginalExtension();
                $heroImage->move(public_path('assets/img/landing'), $heroImageName);
                $metaData['hero']['image'] = 'assets/img/landing/'.$heroImageName;
            } elseif ($request->filled('hero_image_path')) {
                $metaData['hero']['image'] = $request->input('hero_image_path');
            }

            // Update Features section
            $metaData['features'] = array_merge($metaData['features'] ?? [], [
                'badge' => $request->input('features_badge', []),
                'title' => $request->input('features_title', []),
                'description' => $request->input('features_description', []),
            ]);

            // Update CTA section
            $metaData['cta'] = [
                'title' => $request->input('cta_title', []),
                'subtitle' => $request->input('cta_subtitle', []),
                'button_text' => $request->input('cta_button_text', []),
                'button_url' => $request->input('cta_button_url', $metaData['cta']['button_url'] ?? ''),
            ];

            // Update Contact section
            $metaData['contact'] = [
                'badge' => $request->input('contact_badge', []),
                'title' => $request->input('contact_title', []),
                'description' => $request->input('contact_description', []),
                'email' => $request->input('contact_email', $metaData['contact']['email'] ?? ''),
                'phone' => $request->input('contact_phone', $metaData['contact']['phone'] ?? ''),
            ];

            // Keep other sections intact (team, faq, etc.)
            $homePage->meta_data = $metaData;
            $homePage->save();

            return $homePage;
        });
    }
}
