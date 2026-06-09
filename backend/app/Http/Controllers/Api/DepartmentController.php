<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DepartmentController extends Controller
{
    #[OA\Get(
        path: '/api/departments',
        tags: ['Organisation'],
        summary: 'Liste des départements',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Department::withCount(['employees', 'services'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => Department::create($data)], 201);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json(['data' => $department->load('services')]);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:20', 'unique:departments,code,'.$department->id],
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $department->update($data);

        return response()->json(['data' => $department]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(['message' => 'Département supprimé.']);
    }
}
