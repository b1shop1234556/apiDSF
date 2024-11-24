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
        Schema::create('students', function (Blueprint $table) {
            $table->string('LRN')->primary(); 
            $table->string('lname', 255); 
            $table->string('fname', 255);
            $table->string('mname', 255)->nullable(); 
            $table->string('suffix', 25)->nullable();
            $table->date('bdate'); 
            $table->string('bplace', 255); 
            $table->string('gender', 255); 
            $table->string('religion', 255)->nullable(); 
            $table->text('address', 255); 
            $table->text('contact_no', 255); 
            $table->text('student_pic', 255); 
            $table->string('email', 255)->unique(); 
            $table->string('password', 255); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
