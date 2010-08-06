<?php
require_once('DatabaseRecord.php');
require_once('RingerPerformance.php');

class Performance extends DatabaseRecord {
  const TYPE_UNKNOWN = 0;
  const TYPE_TOWER = 1;
  const TYPE_HAND = 2;

  public $id = 0;
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
  public $composition = "";
  public $composer = "";
  public $footnote = "";
  public $type = self::TYPE_UNKNOWN;

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

  public function fetch_campanophile_details() {
    $c = Campanophile::getInstance();
    $c->get_performance($this->campano_id, $this);
  }

  public function post_db_fetch($db) {
    $this->ringers =
      $db->fetch_all('RingerPerformances', 'performance_id', $this->id);
  }
}
