<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $sexe = $this->faker->randomElement(['M', 'F']);

        return [
            'matricule' => 'IFOD-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'nom' => strtoupper($this->faker->lastName()),
            'postnom' => strtoupper($this->faker->lastName()),
            'prenom' => $this->faker->firstName($sexe === 'M' ? 'male' : 'female'),
            'sexe' => $sexe,
            'date_naissance' => $this->faker->dateTimeBetween('-58 years', '-22 years')->format('Y-m-d'),
            'lieu_naissance' => $this->faker->city(),
            'nationalite' => 'Congolaise',
            'etat_civil' => $this->faker->randomElement(['celibataire', 'marie', 'divorce', 'veuf']),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => '+243'.$this->faker->numerify('#########'),
            'adresse' => $this->faker->address(),
            'hire_date' => $this->faker->dateTimeBetween('-8 years', 'now')->format('Y-m-d'),
            'contract_type' => $this->faker->randomElement(['CDI', 'CDI', 'CDD', 'stage', 'consultant']),
            'base_salary' => $this->faker->numberBetween(450, 4500) * 1000,
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'active', 'on_leave', 'on_mission', 'suspended']),
        ];
    }
}
