<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnWithdrawStatusToUserItemTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('user_item', function (Blueprint $table) {
      $table->string('withdraw_status')
        ->after('craft_fail')
        ->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('user_item', function (Blueprint $table) {
      $table->dropColumn('withdraw_status');
    });
  }
}
