<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexTopBannerTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('index_top_banner', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('title')->nullable();
      $table->string('case_category')->nullable();
      $table->string('end_date')->nullable();
      $table->string('start_date')->nullable();
      $table->text('image')->nullable();
      $table->text('mobile_image')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('index_top_banner');
  }
}
