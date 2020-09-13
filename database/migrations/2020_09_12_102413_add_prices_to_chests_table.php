<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPricesToChestsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('chests', function (Blueprint $table) {
      $table->float('pc_price')->nullable();
      $table->float('xbox_price')->nullable();
      $table->float('ps4_price')->nullable();
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
      $table->dropColumn('pc_price');
      $table->dropColumn('xbox_price');
      $table->dropColumn('ps4_price');
    });
  }
}
