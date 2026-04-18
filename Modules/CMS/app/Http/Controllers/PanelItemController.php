<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\CMS\Http\Requests\PanelItemRequest;
use Modules\CMS\Repository\PanelItem\PanelItemInterface;

class PanelItemController extends Controller
{
    public function __construct(public PanelItemInterface $panelItems) {}

    /**
     * Store a new panel item
     */
    public function store(PanelItemRequest $request): JsonResponse
    {
        try {
            $item = $this->panelItems->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Panel item created successfully',
                'item' => [
                    'id' => $item->id,
                    'panel_id' => $item->panel_id,
                    'type' => $item->type->value,
                    'type_label' => $item->type->label(),
                    'title' => $item->title,
                    'is_active' => $item->is_active,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing panel item
     */
    public function update(PanelItemRequest $request, int $id): JsonResponse
    {
        try {
            $item = $this->panelItems->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Panel item updated successfully',
                'item' => [
                    'id' => $item->id,
                    'panel_id' => $item->panel_id,
                    'type' => $item->type->value,
                    'type_label' => $item->type->label(),
                    'title' => $item->title,
                    'is_active' => $item->is_active,
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
     * Delete a panel item
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->panelItems->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Panel item deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific panel item
     */
    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->panelItems->find($id);

            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $item->id,
                    'panel_id' => $item->panel_id,
                    'type' => $item->type->value,
                    'type_label' => $item->type->label(),
                    'title' => $item->title,
                    'content' => $item->content,
                    'link' => $item->link,
                    'image' => $item->image,
                    'is_active' => $item->is_active,
                    'order' => $item->order,
                    'settings' => $item->settings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
