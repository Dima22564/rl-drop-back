<?php


namespace App\Repository;

use App\Repository\Interfaces\StatsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class StatsRepository implements StatsRepositoryInterface
{
  public function getFilteredItemsByPlatform($isCraft)
  {
    return DB::table('user_item')
      ->where('is_craft', $isCraft)
      ->get()
      ->groupBy('platform')
      ->map(function ($row, $key) {
        $sum = array_sum(array_column($row->toArray(), 'price'));
        return [
          'quantity' => $row->count(),
          'totalSum' => $sum
        ];
      })->toArray();
  }

  public function getOpenedChestsGroupedByPlatforms()
  {
    return DB::table('user_chest')
      ->get()
      ->groupBy(function ($items) {
        return Carbon::parse($items->date)->format('Y-m-d');
      });
  }

  public function getSoldItemsGroupedByMonth()
  {
    return DB::table('user_item')
      ->where('sold', 1)
      ->get()
      ->groupBy(function ($items) {
        return Carbon::parse($items->date)->format('Y-m-d');
      });
  }

  public function getSoldItemsGroupedByPlatforms()
  {
    return DB::table('user_item')
      ->where('sold', 1)
      ->get()
      ->groupBy('platform')
      ->map(function ($row, $key) {
        return $row->count();
      })->toArray();
  }
}
