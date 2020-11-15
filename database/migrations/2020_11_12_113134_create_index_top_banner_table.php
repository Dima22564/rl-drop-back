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
      $table->string('name');
      $table->string('title_ru');
      $table->string('title_en');
      $table->string('case_category');
      $table->string('end_date');
      $table->string('start_date');
      $table->text('image');
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
