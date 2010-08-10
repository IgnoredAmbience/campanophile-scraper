<?php
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

    $str = utf8_decode($str);

    // Strip conductor tag, set flag using the replacement count (max 1)
    $str = preg_replace( '|\(C(ond.?(uctor)?)? ?\)|i', '', $str, 1, $this->conductor);

    // Find other bracketed comments and add them to the footnote
    $str = preg_replace_callback( '|\((.+)\)|', array($this, 'replace_footnote'), $str);

    $this->credit = $this->mytrim($str);
  }

  private function replace_footnote($matches) {
    $this->footnote .= $matches[1] . "\n";
    return "";
  }

  private function mytrim($str, $more='') {
  // Because &nbsp; is annoying
    return trim($str, "$more .\t\r\n\0\x0B\xA0");
  }
}

