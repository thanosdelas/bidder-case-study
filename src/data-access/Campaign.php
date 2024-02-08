<?php

namespace DataAccess;

require_once SOURCE.'/Helpers.php';
require_once SOURCE.'/data-access/json/Campaign.php';
require_once SOURCE.'/data-access/database/Campaign.php';

interface CampainDataAccess{
  public function getByTargetedCountries($countryCode);
}

/**
 * Campaigns data access factory.
 * Create an instance of where to retriece campaigns in $this->campaignDataAccess.
 * Instances must implement CampainDataAccess interface and can fetch data from
 * JSON files, Database, or an API call.
 */
class Campaign{
  private $campaigns;
  private $campaignDataAccess;
  private static $Helpers;

  public function __construct(){
    self::$Helpers = new \Helpers();
    $this->campaignDataAccess = new \DataAccess\JSON\Campaign(self::$Helpers);
    // $this->campaignDataAccess = new \DataAccess\Database\Campaign();
  }

  /**
   * @param string $countryCode
   * @return [] $result with errors or campaigns found.
   */
  public function findCampaignsByTargetCountry($countryCode){
    $result = [];

    $findCampaigns = $this->campaignDataAccess->getByTargetedCountries($countryCode);

    if(array_key_exists('errors', $findCampaigns)){
      $result['errors'] = $findCampaigns['errors'];
      return $result;
    }

    $this->campaigns = $findCampaigns['data'];
    $result['data'] = $this->campaigns;
    return $result;
  }

  /**
   * Find campaign with max price from $this->campaigns.
   * This function assumes that a reasonable ammount of campaigns was returned from findCampaignsByTargetCountry().
   * In a real world scenario with a lot of data, probably a database query should handle max price query.
   *
   * Covers the case where more than one campaigns have the same price.
   * May be redundant if price is unique, or if we do not care what campaign is matched.
   * To return only one campaign, disable second iteration and return $maxPriceIndex
   *
   * @return [] $result containing campaigns with highest price from $this->campaigns;
   */
  public function findCampaignWithHighestPrice(){
    $result = [];

    $index = -1;
    $maxPriceIndex = -1;
    $maxPriceIndexes = [];
    $foundMaxPriceIndexes = [];
    $maxPrice = -1;

    //
    // Find $index with highest price
    // Collect $maxPriceIndexes for second iteration
    //
    foreach ($this->campaigns as $campaign){

      ++$index;

      // Set on first iteration
      if($maxPrice === -1){
        $maxPriceIndex = $index;
        $maxPriceIndexes[] = $index;
        $maxPrice = $campaign['price'];
      }
      else if($campaign['price'] >= $maxPrice){
        $maxPriceIndex = $index;
        $maxPriceIndexes[] = $index;
        $maxPrice = $campaign['price'];
      }
    }

    // Match maxPriceIndexes with $maxPrice
    $foundMaxPriceIndexes = [];
    foreach ($maxPriceIndexes as $index) {
      if($this->campaigns[$index]['price'] === $maxPrice){
        $foundMaxPriceIndexes[] = $index;
      }
    }

    // Debug
    // return [
    //  'maxPriceIndexes' => $maxPriceIndexes,
    //  'maxPriceIndex' => $maxPriceIndex,
    //  'maxPrice' => $maxPrice,
    //  'foundMaxPriceIndexes' => $foundMaxPriceIndexes
    // ];

    foreach ($foundMaxPriceIndexes as $i) {
      $result[] = $this->campaigns[$i];
    }

    return $result;
  }
}
