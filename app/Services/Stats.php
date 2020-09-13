<?php


namespace App\Services;

use App\Repository\StatsRepository;
use App\Repository\Interfaces\StatsRepositoryInterface;


class Stats
{
  private $stats;

  public function __construct(StatsRepository $stats)
  {
    $this->stats = $stats;
  }

  public function getIndexData($isCraft)
  {
    $items = $this->stats->getFilteredItemsByPlatform($isCraft);

    $chests = $this->stats->getOpenedChestsGroupedByPlatforms();

    return [
      'stats' => $items,
      'chests' => $chests
    ];
  }

  public function getFilteredItemsForCraft($isCraft)
  {
    return $this->stats->getFilteredItemsByPlatform($isCraft);

  }

  public function getSoldItemsGroupedByMonth()
  {
    $items = $this->stats->getSoldItemsGroupedByMonth();
    return $items;
  }

  public function getSoldItemsGroupedByPlatforms()
  {
    $items = $this->stats->getSoldItemsGroupedByPlatforms();
    $keys = [];
    $soldItems = [];
    foreach ($items as $key => $value) {
      $data = $this->buildData($items, $key);
      array_push($soldItems, $data);
      array_push($keys, $key);
    };
    return [
      'keys' => $keys,
      'data' => $soldItems
    ];
  }

  public function buildResponseForStats($data)
  {
    $keys = [];
    $chestsByPlatforms = [];
    $chestsPrice = [];
    foreach ($data as $key => $value) {
      array_push($keys, $key);
      $data1 = [
        'value' => $value['quantity'],
        'name' => $key
      ];
      $data2 = [
        'value' => $value['totalSum'],
        'name' => $key
      ];
      array_push($chestsByPlatforms, $data1);
      array_push($chestsPrice, $data2);
    }
  }

  public function buildData($data, $key, $field = null)
  {
    return [
      'value' => $field === null ? $data[$key] : $data[$field],
      'name' => $key
    ];
  }
}
