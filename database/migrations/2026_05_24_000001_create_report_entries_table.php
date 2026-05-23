<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();
            $table->string('section', 64);
            $table->string('metric_key', 128);
            $table->string('label');
            $table->decimal('value_num', 15, 2)->nullable();
            $table->text('value_text')->nullable();
            $table->json('row_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['section', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_entries');
    }
};
