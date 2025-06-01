<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Usuários
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student');
        });

        // Categorias de atividades
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Projetos
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Atividades submetidas pelos alunos
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Aluno
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('pendente'); // pendente, aprovada, rejeitada
            $table->timestamps();
        });

        // Feedbacks dados por avaliadores
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade')->nullable();
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade')->nullable(); // Avaliador
            $table->text('comments')->nullable();
            $table->integer('validated_hours')->nullable(); // 1 a 10
            $table->timestamp('created_at')->useCurrent();
        });

        // Avaliações formais (aprovado/rejeitado)
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade')->nullable();
            $table->string('decision'); // aprovado, rejeitado, revisão
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
        });

        // Log de progresso do aluno
        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
            $table->string('status_before')->nullable();
            $table->string('status_after');
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_logs');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
    }
};
