<?php
class Performance {
  public $campano_id = 0;
  public $date = 0;
  public $society = "";
  public $county = "";
  public $location = "";
  public $dedication = "";
  public $length = 0;
  public $tenor_wt = "";
  public $changes = 0;
  public $method = "";
  public $method_details = "";
  public $composer = "";
  public $tower = false;
  public $hand = false;
  public $ringers = Array();
  public $footnote = "";

  public function fetch_details() {
    $c = Campanophile::getInstance();
    $c->get_performance($this->campano_id, self);
  }
}

?>

