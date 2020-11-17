<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitedToChestsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('chests', function (Blueprint $table) {
      $table->boolean('is_limited')->default(0);
      $table->integer('max_open')->nullable()->default(0);
      $table->integer('current_open')->nullable();
      $table->text('background_image')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('chests', function (Blueprint $table) {
      $table->dropColumn('is_limited');
      $table->dropColumn('max_open');
      $table->dropColumn('current_open');
      $table->dropColumn('background_image');
    });
  }
}
