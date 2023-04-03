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
        Schema::create('attempt_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Attempt::class);
            $table->integer('move_number');
            $table->string('notation');
            $table->boolean('correct');
            $table->string('correct_move')->nullable();
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
        Schema::dropIfExists('attempt_moves');
    }
};
