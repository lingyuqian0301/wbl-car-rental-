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
        Schema::table('users', function (Blueprint $table) {
            $table->string('matric_number')->nullable()->after('email');
            $table->string('identification_card')->nullable()->after('matric_number');
            $table->string('college')->nullable()->after('identification_card');
            $table->string('faculty')->nullable()->after('college');
            $table->string('program')->nullable()->after('faculty');
            $table->text('address')->nullable()->after('program');
            $table->string('city')->nullable()->after('address');
            $table->string('region')->nullable()->after('city');
            $table->string('postcode')->nullable()->after('region');
            $table->string('state')->nullable()->after('postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'matric_number',
                'identification_card',
                'college',
                'faculty',
                'program',
                'address',
                'city',
                'region',
                'postcode',
                'state'
            ]);
        });
    }
};

