<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('id_image_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('shop_domain');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->string('status')->default('uploaded'); // uploaded, verified, rejected
            $table->json('metadata')->nullable(); // For storing additional data
            $table->timestamps();
            
            $table->index(['shop_domain', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('id_image_uploads');
    }
};