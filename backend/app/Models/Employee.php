<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule', 'nom', 'postnom', 'prenom', 'sexe', 'date_naissance',
        'lieu_naissance', 'nationalite', 'etat_civil', 'email', 'telephone',
        'adresse', 'photo_path', 'signature_path', 'biometric_ref',
        'department_id', 'service_id', 'job_title_id', 'manager_id',
        'hire_date', 'contract_type', 'contract_end_date', 'base_salary',
        'status', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'hire_date' => 'date',
            'contract_end_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom} {$this->postnom}");
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(EmployeeMovement::class);
    }
}
