<?php

namespace DataAccess\JSON;

use DataAccess\CampainDataAccess;

/**
 * Retrieve campaigns through JSON.
 * According to case study requirements, we should create a mock service
 * to emulate a request to an API.
 *
 * Consumers should retrieve available campaigns for the Blue Banana bidder.
 * Url: http://campaigns.pa.org/campaigns
 *
 * Currently json file is loaded in contructor.
 * Maybe you should create a sevice to emulate an API request.
 */
class Campaign implements CampainDataAccess{
  private static $Helpers;
  private $jsonData;

  public function __construct(\Helpers $Helpers){
    self::$Helpers = $Helpers;

    // The provided files: `test_case_1_campaign_api_mock.json` and `test_case_2_campaign_api_mock.json` are identical.
    // Maybe one of them was supposed to contain data to fail.
    $jsonFile = SOURCE.'/specifications/bidder_case_study/test_case_1_campaign_api_mock.json';

    if(file_exists($jsonFile)){
      $jsonRaw = file_get_contents($jsonFile);

      $json = json_decode($jsonRaw, true);

      // In case json_decode fails try one more removing BOM.
      if(!$Helpers::validateJSON($json)){
        $json = json_decode($Helpers::removeUTF8BOM($jsonRaw), true);
      }

      $this->jsonData = $json;
    }
  }

  public function getByTargetedCountries($countryCode){
    $result = [];
    $result['data'] = [];

    if(!is_array($this->jsonData)){
      $result['errors'][] = ['error' => 'Could not retrieve data.'];
      return $result;
    }

    $collectCampaigns = [];

    foreach ($this->jsonData as $campaign) {
      if(array_key_exists('targetedCountries', $campaign)) {
        foreach ($campaign['targetedCountries'] as $country) {
          if($country === $countryCode){
            $collectCampaigns[] = $campaign;
          }
        }
      }
    }

    $result['data'] = $collectCampaigns;

    return $result;
  }
}
