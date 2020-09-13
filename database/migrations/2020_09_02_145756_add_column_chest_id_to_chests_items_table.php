<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnChestIdToChestsItemsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('chests_items', function (Blueprint $table) {
      $table->foreignId('chest_id')->references('id')->on('chests')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('chests_items', function (Blueprint $table) {
      $table->dropForeign('chests_items_chest_id_foreign');
    });
  }
}
