<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vk_user');
            $table->text('post')->nullable();
            $table->integer('post_id')->nullable();
            $table->smallInteger('count_results')->default(0);
            $table->text('normalize')->nullable();
            $table->text('attachments')->nullable();
            $table->enum('is_answered',['0','1'])->default('0');
            $table->enum('type',['wall','message','comment'])->default('wall');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('posts');
    }
}
