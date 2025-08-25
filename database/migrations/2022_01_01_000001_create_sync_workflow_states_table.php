<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('laravel-sync-workflow.table_name', 'sync_workflow_states'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('workflow');
            $table->longText('instance')->nullable()->comment('The workflow instance');
            $table->longText('activities')->nullable()->comment('Serialized activities and results');
            $table->longText('result')->nullable()->comment('Serialized result workflow result');
            $table->longText('errors')->nullable()->comment('Serialized errors');
            $table->boolean('was_success')->nullable()->comment('Indicates the workflow execution was successful');
            $table->unsignedInteger('attempts')->default(0)->comment('The number of times the workflow was attempted');
            $table->dateTime('first_started_at', 3)->nullable()->comment('The first time that the workflow was started');
            $table->dateTime('started_at', 3)->nullable();
            $table->dateTime('finished_at', 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('laravel-sync-workflow.table_name', 'sync_workflow_states'));
    }
};
