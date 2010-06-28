<?php
require_once('Ringer.php');

class Performance {
  const TYPE_UNKNOWN = 0;
  const TYPE_TOWER = 1;
  const TYPE_HAND = 2;

  public $campano_id = 0;
  public $date = 0; // as unix timestamp
  public $society = "";
  public $county = "";
  public $location = "";
  public $dedication = "";
  public $length = 0; // in minutes
  public $tenor_wt = "";
  public $changes = 0;
  public $method = "";
  public $method_details = "";
  public $composer = "";
  public $ringers = Array();
  public $footnote = "";
  
  private $type = self::TYPE_UNKNOWN;

  public function isHand() {
    return $this->type == self::TYPE_HAND;
  }

  public function setHand() {
    $this->type = self::TYPE_HAND;
  }

  public function isTower() {
    return $this->type == self::TYPE_TOWER;
  }

  public function setTower() {
    return $this->type = self::TYPE_TOWER;
  }

  public function fetch_details() {
    $c = Campanophile::getInstance();
    $c->get_performance($this->campano_id, self);
  }

  public function apply_array($arr) {
    foreach($arr as $k => $v) {
      if(property_exists($this, $k)) {
        $this->$k = $v;
      }
    }
  }
}

?>

