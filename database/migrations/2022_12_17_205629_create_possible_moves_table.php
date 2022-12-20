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
        Schema::create('possible_moves', function (Blueprint $table) {
            $table->id();
            $table->string('fen');
            $table->boolean('is_white')->default(1);
            $table->string('move_from');
            $table->string('move_to');
            $table->string('notation');
            $table->integer('probability')->default(100);
            $table->foreignIdFor(\App\Models\User::class);
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
        Schema::dropIfExists('possible_moves');
    }
};
