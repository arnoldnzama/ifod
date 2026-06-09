<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('employee')->id ?? null;

        return [
            'matricule' => ['sometimes', 'string', 'max:50', Rule::unique('employees', 'matricule')->ignore($id)],
            'nom' => ['sometimes', 'string', 'max:100'],
            'postnom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['sometimes', 'string', 'max:100'],
            'sexe' => ['sometimes', 'in:M,F,Autre'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:150'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'etat_civil' => ['nullable', 'in:celibataire,marie,divorce,veuf'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($id)],
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
