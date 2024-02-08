<?php

namespace DataAccess\Database;

use DataAccess\CampainDataAccess;

class Campaign implements CampainDataAccess{

  public function getByTargetedCountries($countryCode){

    $result = [];
    $result['data'] = [];

    return $result;
  }
}
