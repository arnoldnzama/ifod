<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\EmployeeMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class EmployeeController extends Controller
{
    #[OA\Get(
        path: '/api/employees',
        tags: ['Employees'],
        summary: 'Liste paginée des employés',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'department_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Employee::query()->with(['department', 'service', 'jobTitle']);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('postnom', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->integer('service_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $query->orderBy($request->string('sort_by', 'created_at')->toString(),
            $request->string('sort_dir', 'desc')->toString());

        return EmployeeResource::collection(
            $query->paginate($request->integer('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/employees',
        tags: ['Employees'],
        summary: 'Créer un employé',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Créé')]
    )]
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['matricule'] = $data['matricule'] ?? $this->generateMatricule();
        $data['status'] = $data['status'] ?? 'active';
        $data['contract_type'] = $data['contract_type'] ?? 'CDI';

        $employee = DB::transaction(function () use ($data, $request) {
            $employee = Employee::create($data);

            EmployeeMovement::create([
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
                'type' => 'created',
                'description' => "Création de l'employé {$employee->full_name}.",
            ]);

            return $employee;
        });

        return (new EmployeeResource($employee->load(['department', 'service', 'jobTitle'])))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/employees/{employee}',
        tags: ['Employees'],
        summary: 'Détail d\'un employé',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'employee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function show(Employee $employee): EmployeeResource
    {
        return new EmployeeResource(
            $employee->load(['department', 'service', 'jobTitle', 'manager'])
        );
    }

    #[OA\Put(
        path: '/api/employees/{employee}',
        tags: ['Employees'],
        summary: 'Mettre à jour un employé',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'employee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $data = $request->validated();
        $original = $employee->only(array_keys($data));

        DB::transaction(function () use ($employee, $data, $original, $request) {
            $employee->update($data);

            $changes = [];
            foreach ($data as $key => $value) {
                if (($original[$key] ?? null) != $value) {
                    $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
                }
            }

            if (! empty($changes)) {
                EmployeeMovement::create([
                    'employee_id' => $employee->id,
                    'user_id' => $request->user()->id,
                    'type' => 'updated',
                    'description' => "Mise à jour du dossier de {$employee->full_name}.",
                    'changes' => $changes,
                ]);
            }
        });

        return new EmployeeResource($employee->load(['department', 'service', 'jobTitle']));
    }

    #[OA\Delete(
        path: '/api/employees/{employee}',
        tags: ['Employees'],
        summary: 'Supprimer (soft delete) un employé',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'employee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Supprimé')]
    )]
    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        EmployeeMovement::create([
            'employee_id' => $employee->id,
            'user_id' => $request->user()->id,
            'type' => 'deleted',
            'description' => "Suppression de l'employé {$employee->full_name}.",
        ]);

        $employee->delete();

        return response()->json(['message' => 'Employé supprimé.']);
    }

    #[OA\Post(
        path: '/api/employees/{employee}/archive',
        tags: ['Employees'],
        summary: 'Archiver un employé',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'employee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Archivé')]
    )]
    public function archive(Request $request, Employee $employee): EmployeeResource
    {
        $employee->update(['status' => 'archived']);

        EmployeeMovement::create([
            'employee_id' => $employee->id,
            'user_id' => $request->user()->id,
            'type' => 'archived',
            'description' => "Archivage de l'employé {$employee->full_name}.",
        ]);

        return new EmployeeResource($employee);
    }

    #[OA\Get(
        path: '/api/employees/{employee}/movements',
        tags: ['Employees'],
        summary: 'Historique des mouvements d\'un employé',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'employee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function movements(Employee $employee): JsonResponse
    {
        return response()->json([
            'data' => $employee->movements()->with('user:id,name')->latest()->get(),
        ]);
    }

    protected function generateMatricule(): string
    {
        $year = now()->format('Y');
        $last = Employee::withTrashed()
            ->where('matricule', 'like', "IFOD-{$year}-%")
            ->orderByDesc('matricule')
            ->value('matricule');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('IFOD-%s-%04d', $year, $next);
    }
}
