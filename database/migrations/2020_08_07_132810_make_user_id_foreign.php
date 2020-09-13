<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUserIdForeign extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('password_securities', function (Blueprint $table) {
      $table->foreignId('user_id')->nullable()->constrained('users');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('password_securities', function ($table) {
      $table->dropForeign('password_securities_user_id_foreign');
    });
  }
}
