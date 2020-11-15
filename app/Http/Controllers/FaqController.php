<?php

namespace App\Http\Controllers;

use App\Faq;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\API\BaseController as Controller;
use Illuminate\Support\Facades\Redis;

class FaqController extends Controller
{
  public function index()
  {
//    if (Auth::user()->isAdmin()) {
//      $faqs = Faq::all();
//    } else {
//    $faqs = Cache::remember('faqs', 24 * 60 * 60, function () {
//      return Faq::all();
//    });
//    }
    $locale = app()->getLocale();
    if (auth()->user() ? auth()->user()->hasAccess([Role::ADMIN_ROLE]): false) {
      $faqs = Cache::remember('faqsAdmin',  24 * 60 * 60, function () {
        return Faq::all();
      });
    } else {
      $faqs = Cache::remember('faqs' . $locale, 24 * 60 * 60, function () use ($locale) {
        return Faq::select(['text_' . $locale, 'title_' . $locale, 'category_' . $locale])
          ->get();
      });
    }

    $options = $faqs
      ->groupBy('category_' . app()->getLocale())
      ->toArray();
    return $this->sendResponse([
      'faqs' => $faqs,
      'options' => array_keys($options)
    ], 'Ok', 200);
  }

  public function store(Request $request)
  {
    Cache::forget('faqsAdmin');
    Cache::forget('faqs_ru');
    Cache::forget('faqs_en');
    $faq = Faq::create($request->all());

    return $this->sendResponse($faq, 'Ok', 200);
  }
}
