<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexBottomBannerTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('index_bottom_banner', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('title_ru');
      $table->string('title_en');
      $table->text('text_ru');
      $table->text('text_en');
      $table->integer('case_id');
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
    Schema::dropIfExists('index_bottom_banner');
  }
}
