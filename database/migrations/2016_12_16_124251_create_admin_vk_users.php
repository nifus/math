<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminVkUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_vk_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login');
            $table->string('pass');
            $table->string('vk_id');
            $table->string('scope');
            $table->string('captcha_url')->nullable();
            $table->string('captcha_sid')->nullable();
            $table->smallInteger('sort')->dedault(0);
            $table->enum('status',['active','captcha','blocked'])->default('active');
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
        Schema::drop('admin_vk_users');
    }
}
