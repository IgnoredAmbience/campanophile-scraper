<?php
require_once('PHPUnit/Framework.php');
require_once('Campanoscraper/load.php');

class CampanophileTest extends PHPUnit_Framework_TestCase {
/*  protected function setUpBeforeClass() {
    $this->c = Campanophile::getInstance();
}*/

  /**
   * @dataProvider lengths
   */
  public function test_parse_length($str, $mins) {
    $this->assertEquals($mins, Campanophile::parse_length($str));
  }

  public function lengths() {
    return array(
      array('30', 30),
      array('30m', 30),
      array('30 m', 30),
      array('30 minutes', 30),
      array('30mins', 30),
      array('30 mins', 30),
      array('00:30', 30),
      array('01:30', 90),
      array('11:30', 690),
      array('1h 30m', 90),
      array('1hr 30m', 90),
      array('1.30', 90),
      array('1h30', 90)
    );
  }
}
?>
