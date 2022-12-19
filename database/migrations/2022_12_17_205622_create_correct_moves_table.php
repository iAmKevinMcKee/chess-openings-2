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
        Schema::create('correct_moves', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_white')->default(1);
            $table->string('from_fen')->nullable();
            $table->string('to_fen');
            $table->json('move');
            $table->text('message')->nullable();
            $table->foreignIdFor(\App\Models\Opening::class)->nullable();
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
        Schema::dropIfExists('correct_moves');
    }
};
