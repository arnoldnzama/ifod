<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function tokenForRole(string $role): string
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return auth('api')->login($user);
    }

    public function test_dashboard_returns_kpis_and_charts(): void
    {
        Employee::factory()->count(5)->create(['status' => 'active', 'base_salary' => 100000]);
        Employee::factory()->count(2)->create(['status' => 'on_mission', 'base_salary' => 200000]);

        $token = $this->tokenForRole('Super Administrateur');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'kpis' => ['total_employees', 'active', 'on_mission', 'masse_salariale'],
                'alerts' => ['contracts_expiring', 'birthdays_this_month'],
                'charts' => ['headcount_by_department', 'salary_evolution'],
            ])
            ->assertJsonPath('kpis.total_employees', 7)
            ->assertJsonPath('kpis.active', 5)
            ->assertJsonPath('kpis.on_mission', 2);
    }

    public function test_dashboard_forbidden_without_permission(): void
    {
        $token = $this->tokenForRole('Employé');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard')
            ->assertStatus(403);
    }
}
