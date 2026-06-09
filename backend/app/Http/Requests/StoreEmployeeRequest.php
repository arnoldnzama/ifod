<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'matricule' => ['nullable', 'string', 'max:50', 'unique:employees,matricule'],
            'nom' => ['required', 'string', 'max:100'],
            'postnom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'sexe' => ['required', 'in:M,F,Autre'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:150'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'etat_civil' => ['nullable', 'in:celibataire,marie,divorce,veuf'],
            'email' => ['nullable', 'email', 'max:150', 'unique:employees,email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'adresse' => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'job_title_id' => ['nullable', 'exists:job_titles,id'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'hire_date' => ['nullable', 'date'],
            'contract_type' => ['nullable', 'in:CDI,CDD,stage,consultant'],
            'contract_end_date' => ['nullable', 'date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,suspended,on_leave,on_mission,archived'],
        ];
    }
}
