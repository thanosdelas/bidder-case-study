<?php

namespace Entities;

class Bidder{
	private $bidderId;
  private $country;

  /**
   * Bidder entity for business rules.
   *
   * [NOTE]:
   * Some of the validation rules we are applying here,
   * may have already been applied on JSON schema validation or on any other layer above entities.
   *
   * However these are business logic specific rules, while other validation rules
   * apply to application logic layers.
   *
   * We could further divide some of the validation, by creating more entities (app, device, geo)
   * and import them here.
   *
   * Entities should depend only on other entities.
   */
  public function make($bidderData){
  	$result = [];
    //
    // Apply validation rules and return on error.
    //

    //
    // Validate bidder id
    //
  	if(!array_key_exists('id', $bidderData) || strlen(trim($bidderData['id'])) !== 32){
  		$result['errors'][] = [
        'error'=>'Invalid bidder id.',
        'field'=>'id',
      ];

      return $result;
  	}

    //
    // Validate biddder app
    //
    if(!array_key_exists('app', $bidderData) || !is_array($bidderData['app'])){
      $result['errors'][] = [
        'error'=>'Field app is required.',
        'field'=>'app',
      ];

      return $result;
    }

    //
    // Validate biddder device
    //
    if(!array_key_exists('device', $bidderData) || !is_array($bidderData['device'])){
      $result['errors'][] = [
        'error'=>'Field device is required.',
        'field'=>'device',
      ];

      return $result;
    }

    //
    // Validate biddder device geo
    //
    if(!array_key_exists('geo', $bidderData['device']) || !is_array($bidderData['device']['geo'])){
      $result['errors'][] = [
        'error'=>'Field geo is required in device.',
        'field'=>'geo',
      ];

      return $result;
    }

    //
    // Validate biddder device geo country
    //
    if(
      !array_key_exists('country', $bidderData['device']['geo']) ||
      strlen(trim($bidderData['device']['geo']['country'])) !== 3
    ){

      $result['errors'][] = [
        'error'=>'Country is invalid.',
        'field'=>'country',
      ];

      return $result;
    }

    //
    // Return if errors found
    //
  	if(array_key_exists('errors', $result)){
  		return $result;
  	}

    //
    // If no errors, set class variables.
    //
  	$this->bidderId = $bidderData['id'];
    $this->country = $bidderData['device']['geo']['country'];

  	return $result;
  }

  public function getBidderId(){
  	return $this->bidderId;
  }

  public function getCountry(){
    return $this->country;
  }
}
