<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateLogsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create(
            'logs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('slug'); // Classe Gateway que pertence
                $table->string('description')->nullable();
                $table->integer('status');
                $table->timestamps();
            }
        );

        Schema::create(
            'audits', function (Blueprint $table) {
                $table->increments('id');
                $table->string('route')->nullable();
                $table->string('business')->nullable();
                $table->string('user')->nullable();
                $table->longText('data')->nullable();
                $table->timestamps();
            }
        );
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
