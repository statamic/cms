<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StatamicAuthTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('super')->default(false);
            $table->string('avatar')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('password')->nullable()->change();
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
