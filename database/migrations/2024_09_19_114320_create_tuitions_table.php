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
        Schema::create('tuiton_fees', function (Blueprint $table) {
            $table->id('fee_id');
            $table->string('grade_level');
            $table->string('tuition');
            $table->string('general');
            $table->bigInteger('esc')->change();
            $table->string('subsidy');
            $table->string('req_Downpayment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuiton_fees');
    }
};
