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
        Schema::table('id_image_uploads', function (Blueprint $table) {
            $table->boolean('email_sent')->default(false)->after('status');
            $table->timestamp('email_sent_at')->nullable()->after('email_sent');
            $table->string('order_id')->nullable()->after('email_sent_at');
            $table->string('order_number')->nullable()->after('order_id');
            $table->json('email_metadata')->nullable()->after('order_number');
            
            $table->index(['email_sent', 'created_at']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('id_image_uploads', function (Blueprint $table) {
            $table->dropIndex(['email_sent', 'created_at']);
            $table->dropIndex(['order_id']);
            
            $table->dropColumn([
                'email_sent',
                'email_sent_at', 
                'order_id',
                'order_number',
                'email_metadata'
            ]);
        });
    }
};