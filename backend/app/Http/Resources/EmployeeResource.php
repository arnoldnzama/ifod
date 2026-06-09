<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'postnom' => $this->postnom,
            'prenom' => $this->prenom,
            'full_name' => $this->full_name,
            'sexe' => $this->sexe,
            'date_naissance' => optional($this->date_naissance)->toDateString(),
            'lieu_naissance' => $this->lieu_naissance,
            'nationalite' => $this->nationalite,
            'etat_civil' => $this->etat_civil,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'photo_path' => $this->photo_path,
            'signature_path' => $this->signature_path,
            'department_id' => $this->department_id,
            'service_id' => $this->service_id,
            'job_title_id' => $this->job_title_id,
            'manager_id' => $this->manager_id,
            'hire_date' => optional($this->hire_date)->toDateString(),
            'contract_type' => $this->contract_type,
            'contract_end_date' => optional($this->contract_end_date)->toDateString(),
            'base_salary' => (float) $this->base_salary,
            'status' => $this->status,
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'service' => $this->whenLoaded('service', fn () => [
                'id' => $this->service->id,
                'name' => $this->service->name,
            ]),
            'job_title' => $this->whenLoaded('jobTitle', fn () => [
                'id' => $this->jobTitle->id,
                'name' => $this->jobTitle->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
