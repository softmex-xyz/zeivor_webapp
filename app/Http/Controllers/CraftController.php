<?php

namespace App\Http\Controllers;

use App\Services\CraftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CraftController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CraftService::getAllCrafts());
    }

    public function show(int|string $id): JsonResponse
    {
        $craft = CraftService::getCraftById($id);
        abort_if($craft === null, 404);

        return response()->json($craft);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'map_color' => ['nullable', 'string', 'max:9'],
            'active' => ['boolean'],
        ]);

        return response()->json(CraftService::createCraft($data));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $craft = CraftService::getCraftById($id);
        abort_if($craft === null, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('crafts', 'name')->ignore($craft)],
            'description' => ['nullable', 'string'],
            'map_color' => ['nullable', 'string', 'max:9'],
            'active' => ['boolean'],
        ]);

        $craft = CraftService::updateCraft($craft->getKey(), $data);
        abort_if($craft === null, 404);

        return response()->json($craft);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $craft = CraftService::deleteCraft($id);
        abort_if($craft === null, 404);

        return response()->json($craft);
    }
}
