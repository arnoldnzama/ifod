<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Un compte de test par profil utilisateur (cahier des charges).
        $accounts = [
            ['Super Administrateur', 'superadmin@ifod.local', 'Super Administrateur'],
            ['Administrateur RH', 'rh@ifod.local', 'Administrateur RH'],
            ['Responsable Paie', 'paie@ifod.local', 'Responsable Paie'],
            ['Responsable Formation', 'formation@ifod.local', 'Responsable Formation'],
            ['Responsable Recrutement', 'recrutement@ifod.local', 'Responsable Recrutement'],
            ['Manager', 'manager@ifod.local', 'Manager'],
            ['Employé', 'employe@ifod.local', 'Employé'],
            ['Auditeur', 'auditeur@ifod.local', 'Auditeur'],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'locale' => 'fr',
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
