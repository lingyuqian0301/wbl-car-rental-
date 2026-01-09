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
            if (!Schema::hasColumn('ownercar', 'ic_img')) {
                $table->string('ic_img', 255)->nullable()->after('ic_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ownercar', function (Blueprint $table) {
            if (Schema::hasColumn('ownercar', 'ic_img')) {
                $table->dropColumn('ic_img');
            }
        });
    }
};
