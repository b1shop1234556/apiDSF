<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_sender');   // Column for sender ID or name
            $table->string('message_reciever'); // Column for receiver ID or name
            $table->text('message');   
            $table->timestamp('message_date')->useCurrent();          // Column for the message text
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
