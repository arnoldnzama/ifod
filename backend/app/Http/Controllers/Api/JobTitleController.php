<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobTitle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class JobTitleController extends Controller
{
    #[OA\Get(
        path: '/api/job-titles',
        tags: ['Organisation'],
        summary: 'Liste des fonctions',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => JobTitle::withCount('employees')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:job_titles,code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json(['data' => JobTitle::create($data)], 201);
    }

    public function update(Request $request, JobTitle $jobTitle): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:20', 'unique:job_titles,code,'.$jobTitle->id],
            'name' => ['sometimes', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $jobTitle->update($data);

        return response()->json(['data' => $jobTitle]);
    }

    public function destroy(JobTitle $jobTitle): JsonResponse
    {
        $jobTitle->delete();

        return response()->json(['message' => 'Fonction supprimée.']);
    }
}
