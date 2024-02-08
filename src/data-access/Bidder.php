<?php

namespace DataAccess;

class Bidder{
  /**
   * Mock find bidder by id in database.
   */
  public function findBidderById($bidderId){
    $result = [];
    $result['data'] = [];

    if($bidderId === 'e7fe51ce4f6376876353ff0961c2cb0d'){

      $result['data'] = [
        ['id'=>'e7fe51ce4f6376876353ff0961c2cb0d']
      ];
    }

    return $result;
  }
}
