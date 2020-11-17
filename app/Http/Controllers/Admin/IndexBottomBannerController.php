<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\API\BaseController as Controller;
use App\IndexBottomBanner;
use Illuminate\Http\Request;

class IndexBottomBannerController extends Controller
{
  public function update(Request $request)
  {
    $bannerF = IndexBottomBanner::where('name', 'bottom-index-banner')->first();
    if ($bannerF) {
      $banner = $bannerF;
    } else {
      $banner = new IndexBottomBanner();
    }
    $banner->name = 'bottom-index-banner';
    $banner->title = $request->get('title');
    $banner->case_id = $request->get('caseId');
    $banner->text_ru = $request->get('textRu');
    $banner->text_en = $request->get('textEn');
    if ($request->file('image')) {
      $img = [
        'name' => 'bottom-index-banner',
        'image' => $request->file('image')
      ];
      $banner->saveImage($img);
    }
    if ($request->file('mobileImage')) {
      $img = [
        'name' => 'bottom-index-banner',
        'image' => $request->file('image')
      ];
      $banner->saveMobileImage($img);
    }
    $banner->save();

    return $this->sendResponse([], 'Ok', 200);
  }

  public function index()
  {
    $banner = IndexBottomBanner::where('name', 'bottom-index-banner')->first();
    return $this->sendResponse([
      'banner' => $banner
    ], 'Ok', 200);
  }
}
