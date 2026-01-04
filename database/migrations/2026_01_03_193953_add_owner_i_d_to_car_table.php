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
        Schema::table('car', function (Blueprint $table) {
            if (!Schema::hasColumn('car', 'ownerID')) {
                // Use integer to match int(11) in ownercar table
                $table->integer('ownerID')->nullable()->after('vehicleID');
                // Note: Foreign key constraint added without explicit constraint name to avoid issues
                // You can add the foreign key manually if needed: ALTER TABLE car ADD FOREIGN KEY (ownerID) REFERENCES ownercar(ownerID) ON DELETE SET NULL;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            if (Schema::hasColumn('car', 'ownerID')) {
                $table->dropForeign(['ownerID']);
                $table->dropColumn('ownerID');
            }
        });
    }
};
