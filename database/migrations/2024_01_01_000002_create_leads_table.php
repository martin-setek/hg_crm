<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('region')->nullable(); // Praha, Jihomoravský, ...
            $table->string('source')->nullable(); // PPC, SMS, organic
            $table->string('type')->default('HG'); // HG = HypoGO, SMS, Exter

            // Pipeline status
            $table->string('status')->default('new');
            // new → contacted → qualified → in_progress → approved → closed_won → closed_lost

            // Mortgage data for EV model
            $table->decimal('loan_amount', 12, 2)->nullable();      // Kč
            $table->integer('loan_maturity_years')->nullable();
            $table->decimal('property_value', 12, 2)->nullable();   // Kč
            $table->decimal('interest_rate', 5, 3)->nullable();     // %
            $table->decimal('commission_pct', 5, 4)->default(0.006); // 0.6%

            // EV calculation
            $table->decimal('ev_value', 10, 2)->nullable()->comment('commission_pct × loan_amount × probability');
            $table->decimal('probability', 5, 4)->nullable();       // 0.00–1.00

            // Dates
            $table->date('assigned_at')->nullable();
            $table->date('contacted_at')->nullable();
            $table->date('qualified_at')->nullable();
            $table->date('approved_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->date('disbursed_at')->nullable();               // skutečné čerpání

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('advisor_id');
            $table->index('region');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
