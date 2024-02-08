<?php

namespace Controllers;

require_once SOURCE.'/Helpers.php';
require_once SOURCE.'/services/JSONSchemaValidator.php';
require_once SOURCE.'/data-access/Bidder.php';
require_once SOURCE.'/data-access/Campaign.php';
require_once SOURCE.'/use-cases/Bidder.php';

/**
 * Bidder API Controller
 *
 * Ask Bidder To Submit A Bid
 * -- The bidder receives bid requests from an ad exchange and it responds back either with a bid or without one.
 * -- The bid request from the ad exchange contains info that is needed by the bidder to perform its operation.
 *
 * Endpoints:
 *   Method: POST
 *   Headers:
 *     Content-Type:application/json
 *   Response Code: 200 (Bid Response With A Bid)
 *   Response Code: 204 (Bid Response Without A Bid)
 */
class Bidder extends AppController{
  private static $Helpers;
  private static $methodMapper;

  public function __construct(){
    parent::__construct();

    self::$Helpers = new \Helpers();

    // Map HTTP request methods to this class methods
    self::$methodMapper = [
      // 'GET'=>'bidRequest',
      'POST'=>'bidRequest',
      // 'PUT'=>'update',
      // 'DELETE'=>'delete'
    ];
  }

  /**
   * HTTP request methods dispatcher.
   * @param \Slim\Http\Request $request
   * @param \Slim\Http\Response $response
   * @param [] $args
   * @return $this->methodDispatcher()
   */
  public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args){
    $this->request = $request;
    $this->response = $response;

    return $this->methodDispatcher($args);
  }

  /**
   * @param [] $args
   * @return $this->{method} or $this->invalidRequest()
   */
  public function methodDispatcher($args = []){
    //
    // Validate against self::$methodMapper
    //
    if(!array_key_exists($this->request->getMethod(), self::$methodMapper)){

      return $this->invalidRequest([
        'error' => 'Method not suppoted.'
      ]);
    }

    //
    // Validate against this class methods
    //
    if(!method_exists($this, self::$methodMapper[$this->request->getMethod()])){

      return $this->invalidRequest([
        'error' => 'This action is not suuported.'
      ]);
    }

    return $this->{self::$methodMapper[$this->request->getMethod()]}($args);
  }

  /**
   * Request method [GET]
   */
  public function get($args = []){

    $this->responseData['data'] = [];
    return $this->renderResponse('json');
  }

  /**
   * Parse Bid Request [POST], find campaigns and generate a response.
   * Responsibilities:
   * -- Parse and validate request headers and body with JSON schema.
   * -- Initiate Bidder use cases and pass data (request body) for further validation and match a campaign.
   * -- Generate a reponse with bid or without one.
   * @param $args
   * @return $this->renderResponse('json')
   */
  public function bidRequest($args = []){
    //
    // Validate request. Return on error with invalidRequest.
    //
    if(!$this->isJSONRequest()){
      return $this->invalidRequest([
        'error' => 'Request header Content-Type must be application/json.'
      ]);
    }

    //
    // Validate json schema.
    // Could have been wrapped in a function.
    // Return on error with invalidRequest().
    //
    $schemaFile = SOURCE.'/specifications/json-schema/bid-request-body-schema.json';
    $schemaValidator = new \Services\JSONSchemaValidator((string)$this->request->getBody(), $schemaFile, self::$Helpers);

    $result = $schemaValidator->validate();
    if(array_key_exists('errors', $result)){

      $this->responseData['message'] = 'Failed to parse request.';
      $this->responseData['data'] = $result;
      return $this->invalidRequest($result['errors']);
    }

    $bidderData = $schemaValidator->getArray();

    //
    // Initalize dependencies to inject into Bidder Use Cases
    //
    $bidderDataAccess = new \DataAccess\Bidder();
    $campaignsDataAccess = new \DataAccess\Campaign();
    $bidderUseCases = new \UseCases\Bidder($bidderDataAccess, $campaignsDataAccess);

    $result = $bidderUseCases->matchCampaigns($bidderData);

    if(array_key_exists('errors', $result)){
      return $this->invalidRequest($result['errors']);
    }

    //
    // Contruct bid response
    //
    $bidderId = $result['bidderId'];
    $campaign = $result['data'];

    return $this->bidResponse($bidderId, $bidderData, $campaign);
  }

  /**
   * Bid Response
   * Dispatch response with or without bid according to campaign[] length.
   * @param string $bidderId
   * @param [] $bidderData
   * @param [] $campaign
   *
   * @return $this->renderResponse | $this->invalidResponse
   */
  private function bidResponse($bidderId, $bidderData, $campaign){
    //
    // Response with a bid
    //
    if(count($campaign) > 0){
      $dataToSend = self::constructResponseWithBid($bidderId, $campaign);
    }

    //
    // Response without a bid
    //
    if(count($campaign) === 0){
      // Find out if is valid to send body on 204 status
      // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204
      $this->responseCode = 204;

      $dataToSend = self::constructResponseWithoutBid($bidderId, $bidderData);
    }

    //
    // Return on errors
    //
    if(array_key_exists('errors', $dataToSend)){
      $this->responseCode = 422;
      return $this->invalidResponse($dataToSend['errors']);
    }

    //
    // Send it
    //
    $this->responseData = $dataToSend['data'];
    return $this->renderResponse('json');
  }

  /**
   * Contsruct response data with bid.
   * Validates data according to specified JSON schema (hardcoded).
   *
   * @param string $bidderId
   * @param [] $campaign containing matched campaign
   *
   * @return [] $result with JSON schema validated data and/or errors.
   */
  private static function constructResponseWithBid($bidderId, $campaign){
    $result = [];

    //
    // Map/Translate campaign fields to required schema fields
    //
    $campaignSchemaMapper = [
      'id'=> 'campaignId',
      'price'=>'price',
      'adm'=>'adm'
    ];

    $responseData = [];
    $responseData['id'] = $bidderId;
    $responseData['bid'] = [];

    foreach ($campaignSchemaMapper as $field => $fieldToExpose) {
      $responseData['bid'][$fieldToExpose] = $campaign[$field];
    }

    //
    // To fail, use:
    // $schemaFile = SOURCE.'/specifications/json-schema/bid-request-body-schema.json';
    // $schemaValidator = new \Services\JSONSchemaValidator($responseData, $schemaFile, self::$Helpers);
    //
    $schemaFile = SOURCE.'/specifications/json-schema/bid-response-with-bid-body-schema.json';
    $schemaValidator = new \Services\JSONSchemaValidator(json_encode($responseData), $schemaFile, self::$Helpers);

    $result = $schemaValidator->validate();
    $result['data'] = $responseData;
    return $result;
  }

  /**
   * Contsruct response data without bid.
   * Validates data according to specified JSON schema (hardcoded).
   * @param string $bidderId
   * @param [] $campaign containing matched campaign
   *
   * @return [] $result with JSON schema validated data and/or errors.
   */
  private static function constructResponseWithoutBid($bidderId, $bidderData){
    $responseData = $bidderData;

    $schemaFile = SOURCE.'/specifications/json-schema/bid-response-without-bid-body-schema.json';
    $schemaValidator = new \Services\JSONSchemaValidator(json_encode($responseData), $schemaFile, self::$Helpers);

    $result = $schemaValidator->validate();
    $result['data'] = $responseData;
    return $result;
  }
}
