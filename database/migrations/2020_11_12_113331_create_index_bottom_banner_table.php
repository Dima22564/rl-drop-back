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
      $table->string('name')->nullable();
      $table->string('title')->nullable();
      $table->text('text_ru')->nullable();
      $table->text('text_en')->nullable();
      $table->integer('case_id')->nullable();
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
    Schema::dropIfExists('index_bottom_banner');
  }
}
