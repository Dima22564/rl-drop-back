<?php

namespace App\Http\Controllers\Admin;

use App\IndexTopBanner;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as Controller;

class IndexTopBannerController extends Controller
{
  public function update(Request $request)
  {
    $bannerF = IndexTopBanner::where('name', 'top-index-banner')->first();
    if ($bannerF) {
      $banner = $bannerF;
    } else {
      $banner = new IndexTopBanner();
    }
    $banner->name = 'top-index-banner';
    $banner->title = $request->get('title');
    $banner->case_category = $request->get('caseCategory');
    $banner->end_date = $request->get('endDate');
    $banner->start_date = $request->get('startDate');
    if ($request->file('image')) {
      $img = [
        'name' => 'top-index-banner',
        'image' => $request->file('image')
      ];
      $banner->saveImage($img);
    }
    if ($request->file('mobileImage')) {
      $img = [
        'name' => 'top-index-banner',
        'image' => $request->file('image')
      ];
      $banner->saveMobileImage($img);
    }
    $banner->save();

    return $this->sendResponse([], 'Ok', 200);
  }

  public function index()
  {
    $banner = IndexTopBanner::where('name', 'top-index-banner')->first();
    return $this->sendResponse([
      'banner' => $banner
    ], 'Ok', 200);
  }
}
