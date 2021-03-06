<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChestItemWinTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('chest_item_win', function (Blueprint $table) {
      $table->id();
      $table->foreignId('chest_id')
        ->references('id')
        ->on('chests')
        ->onDelete('cascade');
      $table->foreignId('item_id')
        ->references('id')
        ->on('items')
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
    Schema::dropIfExists('chest_item_win');
  }
}
