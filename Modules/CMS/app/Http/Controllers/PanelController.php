<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CMS\Actions\Panel\CreatePanelAction;
use Modules\CMS\Actions\Panel\DeletePanelAction;
use Modules\CMS\Actions\Panel\DuplicatePanelAction;
use Modules\CMS\Actions\Panel\GetPanelsForPageAction;
use Modules\CMS\Actions\Panel\ReorderPanelsAction;
use Modules\CMS\Actions\Panel\TogglePanelStatusAction;
use Modules\CMS\Actions\Panel\UpdatePanelAction;
use Modules\CMS\Http\Requests\PanelRequest;
use Modules\CMS\Repository\Panel\PanelInterface;

class PanelController extends Controller
{
    public function __construct(public PanelInterface $panels) {}

    /**
     * Get all panels for a specific page
     */
    public function getPanelsForPage(int $pageId, GetPanelsForPageAction $action): JsonResponse
    {
        try {
            $panels = $action->handle($pageId);

            return response()->json([
                'success' => true,
                'panels' => $panels->map(function ($panel) {
                    return [
                        'id' => $panel->id,
                        'type' => $panel->type->value,
                        'type_label' => $panel->type->label(),
                        'icon' => $panel->type->icon(),
                        'title' => $panel->title,
                        'is_active' => $panel->is_active,
                        'order' => $panel->order,
                        'can_have_items' => $panel->type->hasItems(),
                        'items' => $panel->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'type' => $item->type->value,
                                'type_label' => $item->type->label(),
                                'title' => $item->title,
                                'content' => $item->content,
                                'is_active' => $item->is_active,
                                'order' => $item->order,
                            ];
                        }),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific panel
     */
    public function show(int $id): JsonResponse
    {
        try {
            $panel = $this->panels->find($id);

            return response()->json([
                'success' => true,
                'panel' => [
                    'id' => $panel->id,
                    'page_id' => $panel->page_id,
                    'type' => $panel->type->value,
                    'type_label' => $panel->type->label(),
                    'icon' => $panel->type->icon(),
                    'title' => $panel->title,
                    'slug' => $panel->slug,
                    'is_active' => $panel->is_active,
                    'order' => $panel->order,
                    'settings' => $panel->settings,
                    'items' => $panel->items,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new panel
     */
    public function store(PanelRequest $request, CreatePanelAction $action): JsonResponse
    {
        try {
            $panel = $action->handle($request);

            return response()->json([
                'success' => true,
                'message' => 'Panel created successfully',
                'panel' => [
                    'id' => $panel->id,
                    'type' => $panel->type->value,
                    'type_label' => $panel->type->label(),
                    'title' => $panel->title,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing panel
     */
    public function update(PanelRequest $request, int $id, UpdatePanelAction $action): JsonResponse
    {
        try {
            $panel = $action->handle($id, $request);

            return response()->json([
                'success' => true,
                'message' => 'Panel updated successfully',
                'panel' => [
                    'id' => $panel->id,
                    'type' => $panel->type->value,
                    'type_label' => $panel->type->label(),
                    'title' => $panel->title,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a panel
     */
    public function destroy(int $id, DeletePanelAction $action): JsonResponse
    {
        try {
            $action->handle($id);

            return response()->json([
                'success' => true,
                'message' => 'Panel deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle panel active status
     */
    public function toggle(int $id, TogglePanelStatusAction $action): JsonResponse
    {
        try {
            $panel = $action->handle($id);

            return response()->json([
                'success' => true,
                'message' => 'Panel status toggled successfully',
                'is_active' => $panel->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a panel
     */
    public function duplicate(int $id, DuplicatePanelAction $action): JsonResponse
    {
        try {
            $newPanel = $action->handle($id);

            return response()->json([
                'success' => true,
                'message' => 'Panel duplicated successfully',
                'panel' => [
                    'id' => $newPanel->id,
                    'title' => $newPanel->title,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder panels
     */
    public function reorder(Request $request, ReorderPanelsAction $action): JsonResponse
    {
        try {
            $request->validate([
                'panel_ids' => 'required|array',
                'panel_ids.*' => 'required|integer|exists:cms_panels,id',
            ]);

            $action->handle($request->panel_ids);

            return response()->json([
                'success' => true,
                'message' => 'Panels reordered successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
