<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResetTokensTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('reset_tokens', function (Blueprint $table) {
      $table->id();
      $table->string('email');
      $table->string('token');
      $table->string('type');
      $table->dateTime('created_at');
      $table->dateTime('expires_at');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('reset_tokens');
  }
}
