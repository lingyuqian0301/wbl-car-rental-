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
        Schema::table('vehicledocument', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicledocument', 'document_type')) {
                $table->string('document_type', 50)->nullable()->after('fileURL');
            }
            if (!Schema::hasColumn('vehicledocument', 'leasing_document_url')) {
                $table->string('leasing_document_url', 255)->nullable()->after('document_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicledocument', function (Blueprint $table) {
            if (Schema::hasColumn('vehicledocument', 'document_type')) {
                $table->dropColumn('document_type');
            }
            if (Schema::hasColumn('vehicledocument', 'leasing_document_url')) {
                $table->dropColumn('leasing_document_url');
            }
        });
    }
};
