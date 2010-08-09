<?php
require_once('DatabaseRecord.php');
require_once('functions.php');

class RingerPerformance extends DatabaseRecord {
  public $id = 0;
  public $performance_id = 0;
  public $bell = 0;
  public $ringer_id = 0;
  public $credit = '';  // Unmodified
  public $conductor = false;
  public $footnote = ''; // At present, only footnotes derived from constructor

  public function __construct($str = '', $bell = 0) {
    if(!$str) return;

    $this->bell = (int) $bell;

    $name = utf8_decode($str);
    $this->credit = $name;

    // Check for conductor, and strip (C... if found
    if(($end = stripos($str, '(c')) !== false) {
      $name = mytrim(substr($str, 0, $end));
      $this->conductor = true;
    } else {
      $name = mytrim($str);
    }

    // Check for further in-name footnotes (will not find additional ones after (c))
    if(($footnote = strstr($name, '(')) !== false) {
      $this->footnote = mytrim($footnote, '()');
      $name = mytrim(str_replace($footnote, '', $name));
    }
  }
}

