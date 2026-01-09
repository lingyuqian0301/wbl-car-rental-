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
        if (!Schema::hasTable('leasing_expenses')) {
            Schema::create('leasing_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('expense_type')->default('leasing'); // leasing, other
                $table->string('description');
                $table->decimal('amount', 10, 2);
                $table->date('expense_date');
                $table->string('vehicle_id')->nullable(); // Can link to vehicle if needed
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leasing_expenses');
    }
};











