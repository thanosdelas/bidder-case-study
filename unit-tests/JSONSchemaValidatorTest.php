<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/config.php';
require_once SOURCE."/Helpers.php";
require_once SOURCE."/services/JSONSchemaValidator.php";

// echo SOURCE."/Helpers.php";
// exit("\n\nBreakpoint.\n\n");

final class JSONSchemaValidatorTest extends TestCase{
  protected $schemaValidator;

  /**
   * @dataProvider constructorArguments
   */
  public function setUp(): void{
    $arguments = $this->constructorArguments();

    $this->schemaValidator = new \Services\JSONSchemaValidator( ...$arguments );
  }

  public function tearDown(): void{
    $this->schemaValidator = null;
  }

  public function testGetArray(){
    $this->assertIsArray($this->schemaValidator->getArray());
  }

  public function testGetObject(){
    $this->assertIsObject($this->schemaValidator->getObject());
  }

  public function testValidateIsArray(){
    $this->assertIsArray($this->schemaValidator->validate());
  }

  public function constructorArguments(){
    return [
      $this->mockJson(),
      'schema_file',
      new \Helpers()
    ];
  }

  public function mockJson(){

    return '
      {
        "id": "e7fe51ce4f6376876353ff0961c2cb0d",
        "app": {
          "id": "e7fe51ce-4f63-7687-6353-ff0961c2cb0d",
          "name": "Morecast Weather"
        },
        "device": {
          "os": "Android",
          "geo": {
            "country": "USA",
            "lat": 0,
            "lon": 0
          }
        }
      }
    ';
  }
}
