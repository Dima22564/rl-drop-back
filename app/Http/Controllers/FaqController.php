<?php

namespace App\Http\Controllers;

use App\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;

class FaqController extends Controller
{
  public function index()
  {
    $faqs = Faq::all();
    return $this->sendResponse($faqs, 'Ok', 200);
  }

  public function store(Request $request)
  {
    $faq = Faq::create($request->all());

    return $this->sendResponse($faq, 'Ok', 200);
  }
}
