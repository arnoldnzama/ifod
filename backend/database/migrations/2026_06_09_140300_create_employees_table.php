<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();

            // Identity
            $table->string('nom');
            $table->string('postnom')->nullable();
            $table->string('prenom');
            $table->enum('sexe', ['M', 'F', 'Autre'])->default('M');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('nationalite')->nullable();
            $table->enum('etat_civil', ['celibataire', 'marie', 'divorce', 'veuf'])->nullable();

            // Contact
            $table->string('email')->nullable()->unique();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();

            // Media
            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('biometric_ref')->nullable();

            // Organisation
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            // Contract / status
            $table->date('hire_date')->nullable();
            $table->enum('contract_type', ['CDI', 'CDD', 'stage', 'consultant'])->default('CDI');
            $table->date('contract_end_date')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'on_leave', 'on_mission', 'archived'])->default('active');

            // Account link (self-service portal)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'service_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
