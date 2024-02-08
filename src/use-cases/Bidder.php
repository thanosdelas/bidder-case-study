<?php

namespace UseCases;

require_once SOURCE.'/entities/Bidder.php';

/**
 * Bidder use cases.
 * Tightly coupled to \Entity\Bidder
 * Loosely coupled to \DataAccess\ ...
 *
 * Bidder use cases perform operations on what to do on a bid request.
 *
 * We could add more methods here like:
 * matchCampaignsBySomeOtherCriteria(), addBidder, updateBidder etc ...
 *
 * Or we could split responsibilities in more classes like
 * BidderAdd, BidderDelete, BidderUpdate, BiddderMatchByDeviceOS etc ...
 *
 */
class Bidder{
	private $bidderDataAccess;
	private $campaignDataAccess;

  /**
   * We inject bidder and campaign data access.
   * @param \DataAccess\Bidder
   * @param \DataAccess\Campaign
   */
  public function __construct(
  	\DataAccess\Bidder $bidderDataAccess,
  	\DataAccess\Campaign $campaignDataAccess
  ){
    $this->bidderDataAccess = $bidderDataAccess;
    $this->campaignDataAccess = $campaignDataAccess;
  }

  /**
   * Macth campaign for passed bidder.
   * Contains logic on what to do when matched campaigns found.
   * If campaigns found return the one with highest price.
   * Else return empty.
   *
   * @param [] $bidderData
   * @return [] $result
   */
	public function matchCampaigns($bidderData){
    //
    // Data to return
    //
    $result = [];
    $result['bidderId'] = null;
    $result['data'] = [];

    //
    // Validate bidder data with Entity\Bidder
    // Return on error
    //
    $bidder = new \Entities\Bidder();
    $makeBidder = $bidder->make($bidderData);
    if(array_key_exists('errors', $makeBidder)){

      $result['errors'] = $makeBidder['errors'];
      return $result;
    }

    //
    // Mock error
    // $result['errors'][] = ['error'=>'Force use cases error.'];
    // return $result;
    //

    //
    // Ensure bidder exists in data access, in case we have saved bidders somewhere.
    // Return on errors.
    //
    $findBidder = $this->bidderDataAccess->findBidderById($bidder->getBidderId());
    if(count($findBidder['data']) !== 1){
      $result['errors'][] = ['error'=>'Could not find bidder.'];
      return $result;
    }

    //
    // Find all campaigns that match bidder country.
    // Return on errors.
    //
    $findCampaigns = $this->campaignDataAccess->findCampaignsByTargetCountry($bidder->getCountry());
    if(array_key_exists('errors', $findCampaigns)){
      $result['errors'] = $findCampaigns['errors'];
      return $result;
    }

    //
    // If campaigns found for bidder country, find the one with highest price.
    //
    if(count($findCampaigns['data']) >0){
      $result['bidderId'] = $bidder->getBidderId();

      $campaignsWithHighestPrice = $this->campaignDataAccess->findCampaignWithHighestPrice();

      //
      // [NOTE]:
      // According to the API specifications, only one campaign should be matched, so return the first one.
      // In case we have more than one campaigns (with the same highest price), and we must return them, then changes
      // have to be made to the layers above this one to support multiple bids.
      //
      if(count($campaignsWithHighestPrice) > 0){
        $result['data'] = $campaignsWithHighestPrice[0];
      }
    }

    return $result;
	}
}
