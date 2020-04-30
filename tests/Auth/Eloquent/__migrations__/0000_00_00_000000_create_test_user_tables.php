<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestUserTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Only nullable for tests so we can test passwords get encrypted.
            $table->boolean('super')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('role_id');
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('group_id');
        });
    }
}
