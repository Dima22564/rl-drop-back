<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToNotificationsTableCanClose extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('notifications', function (Blueprint $table) {
      $table->boolean('can_close')
        ->after('user_id')
        ->default(1);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('notifications', function (Blueprint $table) {
      $table->dropColumn('can_close');
    });
  }
}
