<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard',
        tags: ['Dashboard'],
        summary: 'Statistiques du tableau de bord exécutif RH',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(): JsonResponse
    {
        $byStatus = Employee::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $byStatus->sum();

        $masseSalariale = (float) Employee::where('status', '!=', 'archived')->sum('base_salary');

        return response()->json([
            'kpis' => [
                'total_employees' => $total,
                'active' => (int) ($byStatus['active'] ?? 0),
                'suspended' => (int) ($byStatus['suspended'] ?? 0),
                'on_leave' => (int) ($byStatus['on_leave'] ?? 0),
                'on_mission' => (int) ($byStatus['on_mission'] ?? 0),
                'archived' => (int) ($byStatus['archived'] ?? 0),
                'masse_salariale' => $masseSalariale,
            ],
            'alerts' => [
                'contracts_expiring' => $this->contractsExpiring(),
                'birthdays_this_month' => $this->birthdaysThisMonth(),
                // Les modules suivants (congés/recrutement) alimenteront ces compteurs.
                'pending_leaves' => 0,
                'open_recruitments' => 0,
            ],
            'charts' => [
                'headcount_by_department' => $this->headcountByDepartment(),
                'salary_evolution' => $this->salaryEvolution(),
                'gender_distribution' => $this->genderDistribution(),
                'contract_type_distribution' => $this->contractTypeDistribution(),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function contractsExpiring(): array
    {
        return Employee::whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->orderBy('contract_end_date')
            ->get(['id', 'matricule', 'nom', 'prenom', 'contract_end_date'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'matricule' => $e->matricule,
                'name' => $e->full_name,
                'contract_end_date' => $e->contract_end_date->toDateString(),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function birthdaysThisMonth(): array
    {
        return Employee::whereNotNull('date_naissance')
            ->whereMonth('date_naissance', now()->month)
            ->get(['id', 'matricule', 'nom', 'prenom', 'date_naissance'])
            ->sortBy(fn ($e) => (int) $e->date_naissance->format('d'))
            ->values()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'day' => (int) $e->date_naissance->format('d'),
            ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function headcountByDepartment(): array
    {
        return Department::withCount(['employees' => fn ($q) => $q->where('status', '!=', 'archived')])
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => ['label' => $d->name, 'value' => $d->employees_count])
            ->all();
    }

    /**
     * Masse salariale cumulée par mois (sur 6 mois) à partir des dates d'embauche.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function salaryEvolution(): array
    {
        $points = [];
        for ($i = 5; $i >= 0; $i--) {
            $end = now()->startOfMonth()->subMonths($i)->endOfMonth();
            $value = (float) Employee::where('status', '!=', 'archived')
                ->where(function ($q) use ($end) {
                    $q->whereNull('hire_date')->orWhere('hire_date', '<=', $end);
                })
                ->sum('base_salary');
            $points[] = ['label' => $end->locale('fr')->isoFormat('MMM YY'), 'value' => $value];
        }

        return $points;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function genderDistribution(): array
    {
        return Employee::select('sexe', DB::raw('count(*) as total'))
            ->groupBy('sexe')
            ->get()
            ->map(fn ($r) => ['label' => $r->sexe, 'value' => (int) $r->total])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function contractTypeDistribution(): array
    {
        return Employee::select('contract_type', DB::raw('count(*) as total'))
            ->groupBy('contract_type')
            ->get()
            ->map(fn ($r) => ['label' => $r->contract_type, 'value' => (int) $r->total])
            ->all();
    }
}
