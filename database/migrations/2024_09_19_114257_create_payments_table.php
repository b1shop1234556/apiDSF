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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id'); // Primary key for payments table
            $table->string('LRN'); // This must match the type in students
            // $table->foreign('LRN')->references('LRN')->on('students')->onDelete('cascade');
            $table->string('OR_number', 255);
            $table->decimal('amount_paid', 10, 2); // Use decimal for monetary values
            $table->string('proof_payment', 255);
            $table->date('date_of_payment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('payments', function (Blueprint $table) {
        //     $table->dropForeign(['LRN']); 
        // });
        Schema::dropIfExists('payments');
    }
};
