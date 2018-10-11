<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbkAdzones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbk_adzones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tbk_adzone_id')->index();
            $table->string('tbk_site_id')->index();
            $table->unsignedInteger('user_id')->default(0)->index();
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
        Schema::dropIfExists('tbk_adzones');
    }
}
