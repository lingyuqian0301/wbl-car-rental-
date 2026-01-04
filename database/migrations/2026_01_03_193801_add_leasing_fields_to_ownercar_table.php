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
        Schema::table('ownercar', function (Blueprint $table) {
            if (!Schema::hasColumn('ownercar', 'leasing_due_date')) {
                $table->date('leasing_due_date')->nullable()->after('registration_date');
            }
            if (!Schema::hasColumn('ownercar', 'leasing_price')) {
                $table->decimal('leasing_price', 10, 2)->nullable()->after('leasing_due_date');
            }
            if (!Schema::hasColumn('ownercar', 'isActive')) {
                $table->boolean('isActive')->default(true)->after('leasing_price');
            }
            if (!Schema::hasColumn('ownercar', 'leasing_end_month')) {
                $table->integer('leasing_end_month')->nullable()->after('isActive');
            }
            if (!Schema::hasColumn('ownercar', 'leasing_end_year')) {
                $table->integer('leasing_end_year')->nullable()->after('leasing_end_month');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ownercar', function (Blueprint $table) {
            if (Schema::hasColumn('ownercar', 'leasing_due_date')) {
                $table->dropColumn('leasing_due_date');
            }
            if (Schema::hasColumn('ownercar', 'leasing_price')) {
                $table->dropColumn('leasing_price');
            }
            if (Schema::hasColumn('ownercar', 'isActive')) {
                $table->dropColumn('isActive');
            }
            if (Schema::hasColumn('ownercar', 'leasing_end_month')) {
                $table->dropColumn('leasing_end_month');
            }
            if (Schema::hasColumn('ownercar', 'leasing_end_year')) {
                $table->dropColumn('leasing_end_year');
            }
        });
    }
};
