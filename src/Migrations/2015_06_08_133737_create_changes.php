<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'changes', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');

                $table->string('model');
                $table->string('key');
                $table->string('action');
                $table->string('title')->nullable();
                $table->longText('changed')->nullable();

                $table->longText('meta')->nullable();
                $table->boolean('deleted')->nullable();
                $table->timestamps();

                $table->index(['model', 'key']);
                $table->index(['key', 'model']);
                $table->index(['action', 'created_at']);
                $table->index(['created_at', 'action']);
                $table->index(['deleted', 'created_at']);

                $table->integer('changeable_id')->nullable();
                $table->string('changeable_type', 255)->nullable();
                // $table->foreign('admin_id')->references('id')->on('admins')->onUpdate('cascade')->onDelete('cascade');
                // $table->integer('admin_id')->unsigned();
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
        Schema::drop('changes');
    }
}
