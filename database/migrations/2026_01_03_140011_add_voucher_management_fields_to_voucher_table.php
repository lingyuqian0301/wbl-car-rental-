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
        Schema::table('voucher', function (Blueprint $table) {
            if (!Schema::hasColumn('voucher', 'voucher_code')) {
                $table->string('voucher_code', 50)->unique()->after('voucherID');
            }
            if (!Schema::hasColumn('voucher', 'voucher_name')) {
                $table->string('voucher_name', 255)->nullable()->after('voucher_code');
            }
            if (!Schema::hasColumn('voucher', 'description')) {
                $table->text('description')->nullable()->after('voucher_name');
            }
            if (!Schema::hasColumn('voucher', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('voucher', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('discount_value');
            }
            if (!Schema::hasColumn('voucher', 'num_valid')) {
                $table->integer('num_valid')->default(0)->comment('Total number of valid vouchers')->after('expiry_date');
            }
            if (!Schema::hasColumn('voucher', 'num_applied')) {
                $table->integer('num_applied')->default(0)->comment('Number of applied vouchers')->after('num_valid');
            }
            if (!Schema::hasColumn('voucher', 'restrictions')) {
                $table->text('restrictions')->nullable()->after('num_applied');
            }
            if (!Schema::hasColumn('voucher', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('restrictions');
            }
            if (!Schema::hasColumn('voucher', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher', function (Blueprint $table) {
            $columns = ['voucher_code', 'voucher_name', 'description', 'discount_value', 
                       'expiry_date', 'num_valid', 'num_applied', 'restrictions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('voucher', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
