<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Denormalized daily snapshot per advisor for delta/trend analysis
        Schema::create('pipeline_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');

            $table->integer('leads_total')->default(0);
            $table->integer('leads_new')->default(0);
            $table->integer('leads_contacted')->default(0);
            $table->integer('leads_qualified')->default(0);
            $table->integer('leads_in_progress')->default(0);
            $table->integer('leads_approved')->default(0);
            $table->integer('leads_closed_won')->default(0);
            $table->integer('leads_closed_lost')->default(0);

            $table->decimal('total_ev', 12, 2)->default(0);        // Σ EV všech leadů
            $table->decimal('warm_rate', 5, 4)->nullable();         // qualified+ / total
            $table->decimal('conversion_rate', 5, 4)->nullable();   // closed_won / total

            $table->unique(['advisor_id', 'snapshot_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_snapshots');
    }
};
