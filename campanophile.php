<?php
require_once('performance.php');

class Campanophile {
  const CBASE = 'http://www.campanophile.com/';
  private $session_key = '';

  function __construct($session = '') {
    if(!$session) {
      $this->begin_session();
    } else {
      $this->session_key = $session;

      // Check the given session is still active
      if(!$this->test_session()) {
        $this->begin_session();
      }
    }
  }

  private function gen_url($name) {
    return self::CBASE . $name . '.aspx?' . $this->session_key;
  }

  private function get_page($name) {
    return file_get_contents($this->gen_url($name));
  }

  private function begin_session() {
    $homepage = file_get_contents(self::CBASE . 'default.aspx');
    if(preg_match('/"menu.aspx\?([^\"]*)"/', $homepage, $matches)) {
      $this->session_key = $matches[1];
      return true;
    }
    return false; // Should probably try catching the error
  }

  private function test_session() {
    $menu = $this->get_page('menu');
    return strpos($menu, 'expired') === FALSE; // Since can return 0
  }

  public function search($params = array()) {
    /***
     * Returns outline set of matching performances
     *
     * $params is an array of any of the following:
     *   StartDate: "dd/mm/yyyy" // defaults to today - 1 year
     *   FinalDate: "dd/mm/yyyy" // defaults to today
     *   Guild: ""
     *   Location: ""
     *   County: ""
     *   Dedication: ""
     *   Method: ""
     *   Composer: ""
     *   Ringer: ""
     *   TypeCode: 0 // Peals
     *   TypeCode: 1 // Quarters
     *   TypeCode: 2 // Both the above (default)
     ***/

    $defaults = array(
      'StartDate' => date('d/m/Y', time()-31556926),
      'FinalDate' => date('d/m/Y'),
      'TypeCode'  => 2
    );

    $curl = curl_init($this->gen_url('find2'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
        http_build_query($params + $defaults));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $results_html = curl_exec($curl);
    return $this->parse_results_table($results_html);
  }

  private function parse_results_table($html) {
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $rows = $dom->getElementsByTagName('tr');

    $performances = array();

    for($i = 1; $i < $rows->length; $i++) {
      $node = $rows->item($i);
      $p = new Performance();

      // First cell, date and hyperlink, including ID code appended to sessid
      $p->date = strtotime($node->firstChild->textContent);
      $loc = $node->firstChild->firstChild->attributes->getNamedItem('href')->textContent;
      $loc = explode($this->session_key, $loc);
      $p->campano_id = $loc[1];

      // Second cell, locational details
      // Location (Dedication), County
      $loc = $node->firstChild->nextSibling->textContent;
      preg_match('/^(.*) \((.*)\), (.*)$/', $loc, $matches);
      $p->location = $matches[1];
      $p->dedication = $matches[2];
      $p->county = $matches[3];

      // Third cell, method details
      // 1234 Funny Principle Major
      preg_match('/(\d+) (.*)/', $node->lastChild->textContent, $matches);
      $p->changes = (int) $matches[1];
      $p->method = $matches[2];
      $performances[] = $p;
    }

    return $performances;
  }
}

$c = new Campanophile('u_5Cy8dzS0uoDV23z0Bejg');
print_r($c);

print_r($c->search(array('StartDate' => '23/05/2010', 'FinalDate' => '23/05/2010')));

