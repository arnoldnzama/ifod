<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Catalogue des permissions par domaine fonctionnel (modules du cahier des charges).
     *
     * @var array<string, list<string>>
     */
    protected array $permissions = [
        'dashboard' => ['dashboard.view'],
        'employees' => ['employees.view', 'employees.create', 'employees.update', 'employees.delete'],
        'organisation' => ['organisation.manage'],
        'documents' => ['documents.view', 'documents.manage'],
        'payroll' => ['payroll.view', 'payroll.manage'],
        'attendance' => ['attendance.view', 'attendance.manage'],
        'leaves' => ['leaves.view', 'leaves.request', 'leaves.approve'],
        'missions' => ['missions.view', 'missions.manage', 'missions.approve'],
        'recruitment' => ['recruitment.view', 'recruitment.manage'],
        'training' => ['training.view', 'training.manage'],
        'performance' => ['performance.view', 'performance.manage'],
        'reporting' => ['reporting.view'],
        'audit' => ['audit.view'],
        'settings' => ['settings.manage'],
    ];

    /**
     * @var array<string, list<string>>
     */
    protected array $roles = [
        'Super Administrateur' => ['*'],
        'Administrateur RH' => [
            'dashboard.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'organisation.manage', 'documents.view', 'documents.manage',
            'attendance.view', 'attendance.manage', 'leaves.view', 'leaves.approve',
            'missions.view', 'missions.manage', 'missions.approve',
            'recruitment.view', 'recruitment.manage', 'training.view', 'training.manage',
            'performance.view', 'performance.manage', 'reporting.view',
        ],
        'Responsable Paie' => ['dashboard.view', 'employees.view', 'payroll.view', 'payroll.manage', 'reporting.view'],
        'Responsable Formation' => ['dashboard.view', 'employees.view', 'training.view', 'training.manage', 'reporting.view'],
        'Responsable Recrutement' => ['dashboard.view', 'employees.view', 'recruitment.view', 'recruitment.manage', 'reporting.view'],
        'Manager' => ['dashboard.view', 'employees.view', 'leaves.view', 'leaves.approve', 'missions.view', 'missions.approve', 'performance.view'],
        'Employé' => ['leaves.request', 'documents.view'],
        'Auditeur' => ['dashboard.view', 'employees.view', 'payroll.view', 'reporting.view', 'audit.view'],
    ];

    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        $all = collect($this->permissions)->flatten()->all();

        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        foreach ($this->roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            $role->syncPermissions($perms === ['*'] ? $all : $perms);
        }
    }
}
