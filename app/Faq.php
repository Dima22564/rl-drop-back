<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
  protected $table = 'faqs';

  protected $fillable = [
    'text_ru',
    'text_en',
    'title_ru',
    'title_en',
    'category_ru',
    'category_en'
  ];
}
