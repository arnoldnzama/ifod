<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeMovement;
use App\Models\JobTitle;
use App\Models\Service;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::with('services')->get();
        $jobTitles = JobTitle::all();

        if ($departments->isEmpty() || $jobTitles->isEmpty()) {
            return;
        }

        Employee::factory()->count(48)->make()->each(function (Employee $employee) use ($departments, $jobTitles) {
            $dept = $departments->random();
            $service = $dept->services->isNotEmpty() ? $dept->services->random() : null;

            $employee->department_id = $dept->id;
            $employee->service_id = $service?->id;
            $employee->job_title_id = $jobTitles->random()->id;
            $employee->save();

            EmployeeMovement::create([
                'employee_id' => $employee->id,
                'type' => 'created',
                'description' => "Import initial de l'employé {$employee->full_name}.",
            ]);
        });

        // Quelques cas déterministes pour alimenter les alertes du dashboard.
        $dept = $departments->first();

        Employee::factory()->create([
            'matricule' => 'IFOD-'.now()->format('Y').'-0001',
            'nom' => 'KABASELE', 'postnom' => 'TSHILOMBO', 'prenom' => 'Jean',
            'department_id' => $dept->id,
            'service_id' => $dept->services->first()?->id,
            'job_title_id' => $jobTitles->first()->id,
            'contract_type' => 'CDD',
            'contract_end_date' => now()->addDays(25)->toDateString(),
            'date_naissance' => now()->subYears(34)->toDateString(),
            'status' => 'active',
        ]);
    }
}
