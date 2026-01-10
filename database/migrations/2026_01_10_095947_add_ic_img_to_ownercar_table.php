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
        if (Schema::hasTable('ownercar')) {
            if (!Schema::hasColumn('ownercar', 'ic_img')) {
                Schema::table('ownercar', function (Blueprint $table) {
                    $table->string('ic_img', 500)->nullable()->after('license_img');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ownercar')) {
            if (Schema::hasColumn('ownercar', 'ic_img')) {
                Schema::table('ownercar', function (Blueprint $table) {
                    $table->dropColumn('ic_img');
                });
            }
        }
    }
};
