<?php

namespace Controllers;

interface MethodDispatcher{
  public function methodDispatcher($args = []);
}

/**
 * All REST API controllers must extend this class
 */
abstract class AppController implements MethodDispatcher{
  protected $request;
  protected $response;
  protected $responseCode = 200;
  protected $responseData = null;

  public function __construct(){
    $this->responseData = [
      'status'=>'ok',
      'message'=>'Success',
      'data'=>[]
    ];
  }

  /**
   * Call this in case an HTTP request method or a controller action is not implemented.
   * @param [] $errors in format ['error' => 'Error string', 'errorCode' => 'code', ... ]
   * @return $this->renderResponse()
   */
  protected function invalidRequest($errors = [], $responseCode = null){
    $this->responseData['status'] = 'failed';
    $this->responseData['status'] = 'Invalid request';

    if(count($errors) >0){

      $this->responseData['data']['errors'] = $errors;
    }

    $this->responseCode = 400;
    if($responseCode !== null && is_int($responseCode)){
      $this->responseCode = $responseCode;
    }

    return $this->renderResponse();
  }

  /**
   * Call this in case an HTTP request method or a controller action is not implemented.
   * @param [] $errors in format ['error' => 'Error string', 'errorCode' => 'code', ... ]
   * @return $this->renderResponse()
   */
  protected function invalidResponse($errors = [], $responseCode = null){
    $this->responseData['status'] = 'failed';
    $this->responseData['message'] = 'Request is ok. Something went wrong while processing information.';

    if(count($errors) >0){

      $this->responseData['data']['errors'] = $errors;
    }

    $this->responseCode = 422;
    if($responseCode !== null && is_int($responseCode)){
      $this->responseCode = $responseCode;
    }

    return $this->renderResponse();
  }


  /**
   * Ensure request headers contains CONTENT_TYPE: application/json
   * @return bool
   */
  protected function isJSONRequest(){
    //
    // To debug request headers:
    // $requestHeaders = $this->request->getHeaders();
    // $this->responseData['DEBUG'] = $requestHeaders;
    //

    //
    // PHP's build in server returns different headers than apache2.
    // Find out why this happens.
    //
    $requestHeaders = $this->request->getHeaders();
    return (
      (
        array_key_exists('CONTENT_TYPE', $requestHeaders) &&
        in_array('application/json', $requestHeaders['CONTENT_TYPE'])
      ) ||
      (
        array_key_exists('HTTP_CONTENT_TYPE', $requestHeaders) &&
        in_array('application/json', $requestHeaders['HTTP_CONTENT_TYPE'])
      )
    );
  }

  /**
   * Output JSON response.
   * @param string $type default 'json'
   */
  protected function renderResponse($type='json'){
    if($type!== 'json'){
      throw new \Exception("[*] Currently only json supported.");
    }

    // Slim framework response
    // $jsonResponse = $this->response->withJson($this->responseData, $this->responseCode);
    // $jsonResponse = $jsonResponse->withHeader('Content-type', 'application/json;charset=utf-8');
    // return $jsonResponse;

    // Plain PHP response
    header('Content-Type: application/json;charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    http_response_code($this->responseCode);
    echo json_encode($this->responseData);
    exit();
  }
}
