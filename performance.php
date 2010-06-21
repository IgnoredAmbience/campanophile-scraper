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
  public $tower = true;
  public $ringers = Array();
  public $footnote = "";

  private $doc;

  function __construct() {
//    $this->doc = new DOMDocument();
  }

  function parse_url($url) {
    $this->doc->loadHTMLfile($url);
    $this->parse_doc();
  }

  function parse_html($html) {
    $this->doc->loadHTML($html);
    $this->parse_doc();
  }

  function parse_doc() {
    $divs = $this->doc->getElementsByTagName("div");
    foreach($divs as $div) {
      print_r($div->textContent);
    }
  }
}

?>

