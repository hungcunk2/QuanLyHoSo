<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->string('scope_khoa_hoc', 64)->default('')->after('section');
            $table->string('scope_lop', 64)->default('')->after('scope_khoa_hoc');
        });

        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropUnique('report_entries_section_metric_key_unique');
            $table->unique(
                ['section', 'metric_key', 'scope_khoa_hoc', 'scope_lop'],
                'report_entries_scope_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropUnique('report_entries_scope_unique');
            $table->unique(['section', 'metric_key'], 'report_entries_section_metric_key_unique');
            $table->dropColumn(['scope_khoa_hoc', 'scope_lop']);
        });
    }
};
