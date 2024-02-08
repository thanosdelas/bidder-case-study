<?php

require_once 'config.php';
require_once SOURCE.'/Helpers.php';

class BidderResponseWithBidCest{
  public function _before(ApiTester $apiTester){

  }

  public function requestBidWithABidResponse(ApiTester $apiTester){
  	$Helpers = new \Helpers();

  	$json = file_get_contents(SOURCE.'/specifications/bidder_case_study/test-case-1-input.json');
		$json = json_decode($Helpers::removeUTF8BOM($json), true);

    $apiTester->haveHttpHeader('Content-Type', 'application/json');
    $apiTester->sendPOST('/bid', $json);
    $apiTester->canSeeResponseCodeIs(200);
    $apiTester->seeResponseIsJson();

    $expectedOutput = file_get_contents(SOURCE.'/specifications/bidder_case_study/test_case_1_expected_output.json');
    $expectedOutput = json_encode(json_decode($Helpers::removeUTF8BOM($expectedOutput), true));

    $apiTester->seeResponseContains($expectedOutput);
  }

  public function requestBidWithOutABidResponse(ApiTester $apiTester){

  	$Helpers = new \Helpers();

  	$json = file_get_contents(SOURCE.'/specifications/bidder_case_study/test_case_2_input.json');
		$json = json_decode($Helpers::removeUTF8BOM($json), true);

    $apiTester->haveHttpHeader('Content-Type', 'application/json');
    $apiTester->sendPOST('/bid', $json);
    $apiTester->canSeeResponseCodeIs(204);

    //
    // If response code is 204 then seeResponseIsJson() fails
    // because header 'Content-Type: application/json;charset=utf-8' is missing.
    // The same happens in postman too.
    //
    // Find out if this is the desired behaviour.
    //
    // $apiTester->seeResponseIsJson();

    $expectedOutput = file_get_contents(SOURCE.'/specifications/bidder_case_study/test_case_1_expected_output.json');
    $expectedOutput = json_encode(json_decode($Helpers::removeUTF8BOM($expectedOutput), true));
  }
}
