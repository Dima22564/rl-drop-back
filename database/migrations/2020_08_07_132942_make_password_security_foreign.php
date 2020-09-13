<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePasswordSecurityForeign extends Migration
{
  /**
   * Run the migrations.>make(
   *
   * @return void
   */
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->foreignId('password_security')->nullable()->constrained('password_securities');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function ($table) {
      $table->dropForeign('users_password_security_foreign');
    });
  }
}
