<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actingAsRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $token = auth('api')->login($user);
        $this->withHeader('Authorization', "Bearer {$token}");

        return $user;
    }

    public function test_admin_rh_can_create_employee_with_auto_matricule(): void
    {
        $this->actingAsRole('Administrateur RH');
        $dept = Department::create(['code' => 'D1', 'name' => 'Dept 1']);

        $response = $this->postJson('/api/employees', [
            'nom' => 'KABILA',
            'prenom' => 'Joseph',
            'sexe' => 'M',
            'department_id' => $dept->id,
            'base_salary' => 750000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.full_name', 'Joseph KABILA')
            ->assertJsonPath('data.status', 'active');

        $this->assertStringStartsWith('IFOD-', $response->json('data.matricule'));
        $this->assertDatabaseHas('employee_movements', ['type' => 'created']);
    }

    public function test_employee_role_cannot_list_employees(): void
    {
        $this->actingAsRole('Employé');

        $this->getJson('/api/employees')->assertStatus(403);
    }

    public function test_update_records_movement_history(): void
    {
        $this->actingAsRole('Administrateur RH');
        $employee = Employee::factory()->create(['base_salary' => 500000]);

        $this->putJson("/api/employees/{$employee->id}", ['base_salary' => 900000])
            ->assertOk()
            ->assertJsonPath('data.base_salary', 900000);

        $this->assertDatabaseHas('employee_movements', [
            'employee_id' => $employee->id,
            'type' => 'updated',
        ]);
    }

    public function test_archive_sets_status_archived(): void
    {
        $this->actingAsRole('Administrateur RH');
        $employee = Employee::factory()->create(['status' => 'active']);

        $this->postJson("/api/employees/{$employee->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');
    }

    public function test_validation_errors_on_missing_required_fields(): void
    {
        $this->actingAsRole('Administrateur RH');

        $this->postJson('/api/employees', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nom', 'prenom', 'sexe']);
    }
}
