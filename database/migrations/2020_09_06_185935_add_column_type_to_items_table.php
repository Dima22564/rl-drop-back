<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeToItemsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('items', function (Blueprint $table) {
      $table->foreignId('type_id')
        ->after('image')
        ->nullable()
        ->references('id')
        ->on('items_types')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('items', function (Blueprint $table) {
      $table->dropForeign('items_type_id_foreign');
    });
  }
}
