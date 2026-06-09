<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ServiceController extends Controller
{
    #[OA\Get(
        path: '/api/services',
        tags: ['Organisation'],
        summary: 'Liste des services',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'department_id', in: 'query', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()->with('department:id,name')->withCount('employees');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        return response()->json(['data' => $query->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:20', 'unique:services,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => Service::create($data)], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $request->validate([
            'department_id' => ['sometimes', 'exists:departments,id'],
            'code' => ['sometimes', 'string', 'max:20', 'unique:services,code,'.$service->id],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $service->update($data);

        return response()->json(['data' => $service]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Service supprimé.']);
    }
}
