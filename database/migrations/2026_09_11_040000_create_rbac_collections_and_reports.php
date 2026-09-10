<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        Schema::create('prompt_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->char('invite_token_hash', 64)->nullable()->unique();
            $table->string('invite_role', 20)->default('viewer');
            $table->timestamp('invite_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('collection_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prompt_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('viewer');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->unique(['prompt_collection_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        Schema::table('prompts', function (Blueprint $table): void {
            $table->foreignId('collection_id')->nullable()->after('user_id')
                ->constrained('prompt_collections')->nullOnDelete();
            $table->index(['collection_id', 'updated_at']);
        });

        Schema::create('prompt_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prompt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 50);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['prompt_id', 'reported_by']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_reports');
        Schema::table('prompts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('collection_id');
        });
        Schema::dropIfExists('collection_members');
        Schema::dropIfExists('prompt_collections');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
