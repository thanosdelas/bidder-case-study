<?php
/**
 * App Helpers
 */
class Helpers{
  public function __construct(){
    // pass
  }

  public static function preDump($array){
    echo '<pre>';print_r($array);echo '<pre>';
  }

  public static function collectErrors($results){
    $errors = [];

    foreach ($results as $result) {
      //
      // Collect database errors
      // Result entry contains 'execute'
      //
      if(array_key_exists('execute', $result)){
        foreach ($result['execute'] as $execute) {
          if(array_key_exists('error', $execute)){
            foreach ($execute as $statement) {
              $errors['errors'][] = $execute['error'];
            }
          }
        }
      }

      //
      // Collect custom errors
      //
      if(array_key_exists('errors', $result)){
        foreach ($result['errors'] as $error) {
          $errors['errors'][] = $error;
        }
      }
    }

    return $errors;
  }

  /**
   * @param string $json
   * @return boolean
   */
  public static function validateJSON($json){
    if(is_array($json) || is_object($json)){
      return true;
    }

    json_decode($json);

    return (json_last_error() == JSON_ERROR_NONE);
  }

  /**
   * Remove byte order mark
   * https://en.wikipedia.org/wiki/Byte_order_mark
   * @param string $text
   * @return string $text
   */
  public static function removeUTF8BOM($text){
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);

    return $text;
  }
}
