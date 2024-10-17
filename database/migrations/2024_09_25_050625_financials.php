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
        Schema::create('financial_statements', function (Blueprint $table) {
            $table->id('soa_id');                 // Primary key
            $table->string('LRN');                 // Column for student LRN
            $table->string('filename');            // Column for the file name
            $table->timestamp('date_uploaded')->useCurrent(); // Column for the upload date
            $table->timestamps();                  // Columns for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statements');
    }
};
