<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if role column exists and update it
        if (Schema::hasColumn('users', 'role')) {
            // For MySQL/MariaDB
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer', 'admin', 'staff') DEFAULT 'customer'");
            } else {
                // For other databases, drop and recreate
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('role');
                });
                Schema::table('users', function (Blueprint $table) {
                    $table->enum('role', ['customer', 'admin', 'staff'])->default('customer')->after('email');
                });
            }
        } else {
            // If role column doesn't exist, create it
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['customer', 'admin', 'staff'])->default('customer')->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values if needed
        if (Schema::hasColumn('users', 'role')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer', 'admin') DEFAULT 'customer'");
            }
        }
    }
};











