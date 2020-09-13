<?php


namespace App\Repository\Interfaces;


interface StatsRepositoryInterface
{
  public function getFilteredItemsByPlatform($isCraft);

  public function getOpenedChestsGroupedByPlatforms();

  public function getSoldItemsGroupedByPlatforms();

  public function getSoldItemsGroupedByMonth();
}
