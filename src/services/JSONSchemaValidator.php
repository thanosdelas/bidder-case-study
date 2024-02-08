<?php

namespace Services;

/**
 * JSON Schema Validator Factory
 */
class JSONSchemaValidator{
  //
  // Initialized through constructor
  //
  private static $Helpers;
  private $jsonRaw;
  private $jsonToValidate;
  private $schemaFile;
  private $schemaValidator;

  /**
   * @param string $jsonToValidate. Must be decoded.
   * @param string $schemaFile. Full path to json schema file.
   * @param \Helpers $Helpers
   */
  public function __construct($jsonToValidate, $schemaFile, \Helpers $Helpers){
    self::$Helpers = $Helpers;

    if(!is_string($jsonToValidate)){
      $jsonToValidate = '';
    }

    //
    // Important: Decode json as object, not array.
    //
    $this->jsonRaw = self::$Helpers::removeUTF8BOM($jsonToValidate);
    $this->jsonToValidate = $this->getObject($jsonToValidate);
    $this->schemaFile = $schemaFile;
    $this->schemaValidator = new \JsonSchema\Validator;
  }

  /**
   * Decode $jsonRaw to object (second parameter false)
   */
  public function getObject(){
    return json_decode($this->jsonRaw, false);
  }

  /**
   * Decode $jsonRaw to array (second parameter true)
   */
  public function getArray(){
    return json_decode($this->jsonRaw, true);
  }

  /**
   * Run JSON Validation for this options.
   * @return [] $result with errors or empty on success.
   */
  public function validate(){
    $result = [];

    if(!self::$Helpers::validateJSON($this->jsonToValidate)){

      $result['errors'][] = ['error' => 'Invalid json.'];
      return $result;
    }

    if(!file_exists($this->schemaFile)){
      $result['errors'][] = ['error' => 'Could not find validation schema. Validation failed.'];
      return $result;
    }

    $this->schemaValidator->validate($this->jsonToValidate, (object)['$ref' => 'file://'.$this->schemaFile]);

    if(!$this->schemaValidator->isValid()){

      $result['errors'][] = ['error' => 'Could not validate JSON according to specifications.'];

      //
      // Uncomment to append all error details for debugging.
      // $errors[] = $this->schemaValidator->getErrors();
      //

      foreach ($this->schemaValidator->getErrors() as $error){
        $result['errors'][] = [
          'error' => $error['message'],
          'errorProperty' => $error['property'],
        ];
      }
    }

    return $result;
  }
}
