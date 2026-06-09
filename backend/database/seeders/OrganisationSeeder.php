<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobTitle;
use App\Models\Service;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'DG' => ['name' => 'Direction Générale', 'services' => ['Cabinet', 'Audit Interne']],
            'DRH' => ['name' => 'Ressources Humaines', 'services' => ['Administration du Personnel', 'Paie', 'Formation', 'Recrutement']],
            'DFC' => ['name' => 'Finances et Comptabilité', 'services' => ['Comptabilité', 'Trésorerie', 'Budget']],
            'DSI' => ['name' => "Systèmes d'Information", 'services' => ['Développement', 'Infrastructure', 'Support']],
            'DTL' => ['name' => 'Technique et Logistique', 'services' => ['Maintenance', 'Logistique', 'Approvisionnement']],
        ];

        foreach ($structure as $code => $info) {
            $dept = Department::firstOrCreate(['code' => $code], ['name' => $info['name']]);

            foreach ($info['services'] as $i => $serviceName) {
                Service::firstOrCreate(
                    ['code' => $code.'-S'.($i + 1)],
                    ['department_id' => $dept->id, 'name' => $serviceName]
                );
            }
        }

        $jobTitles = [
            ['DIR', 'Directeur', 'Direction'],
            ['CHEF', 'Chef de Service', 'Encadrement'],
            ['CADRE', 'Cadre', 'Cadre'],
            ['AGENT', 'Agent', 'Exécution'],
            ['COMPTA', 'Comptable', 'Cadre'],
            ['DEV', 'Développeur', 'Cadre'],
            ['RH', 'Gestionnaire RH', 'Cadre'],
            ['ASSIST', 'Assistant Administratif', 'Exécution'],
        ];

        foreach ($jobTitles as [$code, $name, $category]) {
            JobTitle::firstOrCreate(['code' => $code], ['name' => $name, 'category' => $category]);
        }
    }
}
