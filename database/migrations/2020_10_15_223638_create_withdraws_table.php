<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('withdraws', function (Blueprint $table) {
      $table->id('withdraw_id');
      $table->string('status')
        ->nullable();
      $table->foreignId('item_id')
        ->references('id')
        ->on('items')
        ->onDelete('cascade');
      $table->foreignId('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');
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
    Schema::dropIfExists('withdraws');
  }
}
