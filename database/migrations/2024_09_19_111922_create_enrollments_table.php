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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id('enrol_id'); // BIGINT UNSIGNED with auto increment
            $table->string('LRN', 255); // VARCHAR(255)
            $table->date('regapproval_date'); // DATE
            $table->date('payment_approval'); // TINYINT(1)
            $table->integer('grade_level'); // INT
            $table->string('contact_no', 15); // VARCHAR(15)
            $table->string('guardian_name', 100); // VARCHAR(100)
            $table->string('last_attended', 100); // VARCHAR(100)
            $table->enum('public_private', ['Public', 'Private']); // ENUM
            $table->date('date_register'); // DATE
            $table->string('strand', 50)->nullable(); // VARCHAR(50) NULL
            $table->string('school_year', 10); // VARCHAR(10)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
