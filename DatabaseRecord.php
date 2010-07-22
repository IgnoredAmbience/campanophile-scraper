<?php
abstract class DatabaseRecord {
  /**
   * Class that holds common implementation for Classes representing
   * database records
   */

  const pk = 'id';

  public function apply_array($arr) {
    foreach($arr as $k => $v) {
      if(property_exists($this, $k)) {
        $this->$k = $v;
      }
    }
  }

  // Placeholder for code to execute after fetching from db
  public function post_db_fetch($db) {}
}
