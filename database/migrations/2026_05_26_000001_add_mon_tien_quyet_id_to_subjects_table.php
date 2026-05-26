<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'mon_tien_quyet_id')) {
                $table->foreignId('mon_tien_quyet_id')
                    ->nullable()
                    ->after('ten_mon_hoc')
                    ->constrained('subjects')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'mon_tien_quyet_id')) {
                $table->dropConstrainedForeignId('mon_tien_quyet_id');
            }
        });
    }
};
