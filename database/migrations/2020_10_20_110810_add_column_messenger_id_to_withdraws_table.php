<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMessengerIdToWithdrawsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('withdraws', function (Blueprint $table) {
      $table->foreignId('messenger_id')
        ->after('user_id')
        ->references('id')
        ->on('users')
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
    Schema::table('withdraws', function (Blueprint $table) {
      $table->dropColumn('messenger_id');
    });
  }
}
