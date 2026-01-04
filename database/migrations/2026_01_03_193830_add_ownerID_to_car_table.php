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
                $table->unsignedBigInteger('ownerID')->nullable()->after('vehicleID');
                $table->foreign('ownerID')->references('ownerID')->on('ownercar')->onDelete('set null');
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

