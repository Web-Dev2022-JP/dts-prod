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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requested_document_id'); // Foreign key
            $table->string('trk_id')->nullable();//tracking no
            $table->string('forwarded_to');//id of departments forwarded
            $table->string('current_location');
            $table->string('notes')->nullable();
            $table->string('notes_user')->nullable();
            $table->string('status');
            $table->boolean('scanned')->default(false);//indicators for that the documents is scan by user. so able to forward
            $table->timestamps();
            $table->string('time_range')->nullable()->default(now());
            $table->string('destination')->nullable();
             // Define a foreign key constraint
             $table->foreign('requested_document_id')
             ->references('id')
             ->on('requested_documents')
             ->onDelete('cascade'); // You can adjust the onDelete behavior as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
