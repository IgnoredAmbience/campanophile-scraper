<?php
class Campanophile {
  private $session_key = '';
 
  /***
   * Configuration
   ***/

  const CBASE = 'http://www.campanophile.com/';
  // The maximum number of results the website will return for a search
  const MAX_RETURNED = 100;

  static function get_default_search_params() {
    return array(
      'StartDate' => date('d/m/Y', time()-31556926), // 1 year
      'FinalDate' => date('d/m/Y'),
      'Guild' => '',
      'Location' => '',
      'County' => '',
      'Dedication' => '',
      'Method' => '',
      'Composer' => '',
      'Ringer' => '',
      'TypeCode'  => 2
    );
  }

  static function get_default_browse_params() {
    return array(
      'DateCode' => 0,
      'Type1' => true,
      'Type2' => true,
      'Type3' => true,
      'Type4' => true,
      'OrderBySubmission' => true
    );
  }

  /***
   * Constructors
   ***/

  private function __construct($session) {
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

  private function __clone() {}

  public function getInstance($session = '') {
    // Singleton class
    static $instance = null;
    if(!$instance) $instance = new self($session);

    return $instance;
  }

  /***
   * Public methods
   ***/

  public function search($params = array()) {
    /***
     * Returns outline set of matching performances
     * Only a maximum of 100 (or as many as Campanophile allow) will be returned
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
    
    $defaults = self::get_default_search_params();

    $curl = curl_init($this->gen_url('find2'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
        http_build_query($params + $defaults));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $results_html = curl_exec($curl);
    return $this->parse_search_results($results_html);
  }

  public function search_all($params = array()) {
    /***
     * Returns outline set of all matching performances without the 100 limit
     * 
     * $params as per search
     ***/

    $defaults = self::get_default_search_params();
    $params += $defaults;
    
    $results = $result = $this->search($params);
    while(count($result) == 100) {
      $params['FinalDate'] = $this->reverse_date(end($result)->date);
      $result = $this->search($params);
      $results += $result;
    }
    return $results;
  }

  public function get_performance($campano_id, $perf = null) {
    /***
     * Returns Performance object of specific Campanophile (Quarter) Peal
     * specified by given reference number, $campano_id
     *
     * Optionally updates given Performance object $perf
     ***/
    
    $campano_id = (int) $campano_id;
    if(!$campano_id) throw new Exception("Invalid id");

    if(!$perf) $perf = new Performance();
    $perf->campano_id = $campano_id;

    $page_content = $this->get_page('view2', 'F'.$campano_id);

    $this->parse_perf_page($page_content, $perf);

    return $perf;
  }

  public function browse($params = array()) {
    /***
     * Returns outline set of results from the Campanophile 'Browse' pages
     * It is recommended to only use this function for browse by submission
     * date, as browsing by date rung is probably best done through the search
     * function.
     *
     * $params is an array containing any of the following:
     *   DateCode: starting date offset from today, negative integer,
     *     defaults to 0 (today)
     *   Type1: boolean flag for peals, default true
     *   Type2: boolean flag for quarters, default true
     *   Type3: boolean flag for tower performances, default true
     *   Type4: boolean flag for performances in hand, default true
     *   OrderBySubmission: boolean flag for ordering by date submitted or rung,
     *     default true (date submitted)
     *
     * Returns: 2 dimensional array of Performances, indexed by date
     * submitted/rung and campanophile id
     ***/

    $defaults = self::get_default_browse_params();

    $curl = curl_init($this->gen_url('list4'));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
      http_build_query($params));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($curl);

    return $this->parse_browse_results($html);
  }

  /***
   * Helper functions
   ***/
  
  //
  // Site access
  //

  private function gen_url($name, $suff='') {
    return self::CBASE . $name . '.aspx?' . $this->session_key . $suff;
  }

  private function get_page($name, $suff='') {
    return file_get_contents($this->gen_url($name, $suff));
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

  // 
  // Page parsing
  //

  private function parse_browse_results($html) {
    // Will return 2D array first indexed by Submission/Rung date
    // and then by campanophile id
    $dom = new DOMDocument();
    // The Campanophile HTML is invalid but still just about parsable!
    @$dom->loadHTML($html);
    $rows = $dom->getElementsByTagName('tr');

    $mode = 0;  // 1 if browsing by submission date

    $perfs = array();

    foreach($rows as $row) {
      $cell = $row->firstChild;
      if($cell->nodeName == 'th') {
        // A date row
        if(!$mode && $cell->getAttribute('colspan') == 3)
          $mode = 1;

        $today = $this->parse_date($cell->textContent);
        $perfs[$today] = array();
      } elseif($cell->nodeName == 'td') {
        // A performance row

        $p = new Performance();

        // Location/ID
        $p->apply_array($this->parse_location($cell->textContent));
        $cid  = $cell->firstChild;
        $cid = $cid->getAttribute('href');
        $cid = explode($this->session_key, $cid);
        $p->campano_id = (int) $cid[1];

        $this->advptr($cell);

        // Date rung
        if($mode) {
          $p->date = $this->parse_date($cell->textContent);
          $this->advptr($cell);
        } else {
          $p->date = $today;
        }

        // Method
        $p->apply_array($this->parse_method($cell->textContent));

        $perfs[$today][$p->campano_id] = $p;
      } else {
        // We have no idea what sort of row it is
      }
    }
    return $perfs;
  }

  private function parse_search_results($html) {
    // find2.aspx
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $rows = $dom->getElementsByTagName('tr');

    $performances = array();

    for($i = 1; $i < $rows->length; $i++) {
      $node = $rows->item($i);
      $p = new Performance();

      // First cell, date and hyperlink, including ID code appended to sessid
      $p->date = $this->parse_date($node->firstChild->textContent);
      $loc = $node->firstChild->firstChild->attributes->getNamedItem('href')->textContent;
      $loc = explode($this->session_key, $loc);
      $p->campano_id = (int) $loc[1];

      // Second cell, locational details
      $matches = $this->parse_location($node->firstChild->nextSibling->textContent);
      $p->apply_array($matches);

      // Third cell, method details
      $matches = $this->parse_method($node->lastChild->textContent);
      $p->apply_array($matches);

      $performances[$p->campano_id] = $p;
    }

    return $performances;
  }

  private function parse_perf_page($html, $perf) {
    /***
     * Page format:
     * <div><b>Society</b></div> (optional)
     * <div><b>Location</b>, County</div> (county optional)
     * <div>Dedication</div> (optional)
     * <div><b>Method</b></div>
     * <div>Method details</div> (optional)
     * <div>Composed by: Composer</div> (optional)
     *  <span style="width: 42px;">1-2</span>   Name<br> 
     *  <span style="display: inline-block; text-align: right; width: 24px">1</span>   Name<br> 
     * ...
     * <div>Footnotes</div> (always present)
     ***/
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $div = $dom->getElementsByTagName('div')->item(0)->firstChild;

    if($div == NULL) return;

    // Check the following node - if it is also bold, then this is the society
    if($div->nextSibling->firstChild->nodeName == 'b') {
      $perf->society = $div->textContent;
      $this->advptr($div);
    }

    // Location, County
    $perf->location = $div->firstChild->textContent;
    if($div->lastChild->nodeType == XML_TEXT_NODE)
      $perf->county = trim($div->lastChild->textContent, " ,.");

    $this->advptr($div);
    
    // Dedication
    if(!preg_match('/^(Mon|Tues|Wednes|Thurs|Fri|Sat|Sun)day,/', $div->textContent)) {
      $perf->dedication = $div->textContent;
      $this->advptr($div);
    }

    // Date in Time (Weight)
    preg_match('/^(?P<date>.*?)(?: in (?P<length>.*?))?(?: \((?P<tenor_wt>.*)\))?$/',
      $div->textContent, $matches);
    $perf->date = $this->parse_date($matches['date']);
    $perf->length = $this->parse_length($matches['length']);
    $perf->tenor_wt = $matches['tenor_wt'];
    
    $this->advptr($div);

    //Method
    $perf->apply_array($this->parse_method($div->textContent));
    
    $this->advptr($div);

    // Composition
    // nodeName check to ensure ringers list not yet begun
    // This is either composer or composition
    if($div->nodeName == 'div') {

      // If next node is a div, this must be composition
      // Composer field *often* prefixed by Arranged or Composed but not always
      // So will sometimes end up in Composition field if Composition not present
      if($div->nextSibling->nodeName == 'div'
      || !preg_match('/^(Arranged|Composed): /', $div->textContent)) {
        $perf->composition = $div->textContent;
        $this->advptr($div);
      }
    }

    // Composer
    if($div->nodeName == 'div') {
      $perf->composer = str_replace(array('Arranged: ', 'Composed: '), '', $div->textContent);
      $this->advptr($div);
    }

    // Check tower/hand
    if(strstr($div->textContent, '-') === false) 
      $perf->setTower();
    else
      $perf->setHand();

    // Get Ringers
    while($div->nodeName == 'span') {
      $perf->ringer_performances->add(new RingerPerformance($div->nextSibling->textContent,
        $perf->ringer_performances->size() + 1));
      $this->advptr($div);
      $this->advptr($div);
    }

    // The Footnote
    $perf->footnote = trim($this->dombr2nl($div));
  }

  private function parse_date($str) {
    // Parses date and returns in form YYYY/MM/DD
    return date('Y/m/d', strtotime($str));
  }

  private function reverse_date($str) {
    // Converts a date from YYYY/MM/DD to DD/MM/YYYY and vice versa
    return implode('/', array_reverse(explode('/', $str)));
  }

  private function parse_location($str) {
    // String of form "Location (Dedication), County"
    // Returns array containing keys of 'location', 'dedication' and 'county'
    preg_match(
      '/^(?P<location>.*?)(?: \((?P<dedication>.*)\))?(?:, (?P<county>[^,]*))?$/',
      $str, $matches);
    return $matches;
  }

  private function parse_method($str) { 
    // 1234 Funny Principle Major
    preg_match('/(?P<changes>\d+) (?P<method>.*)/', $str, $matches);
    $matches['changes'] = (int) $matches['changes'];
    return $matches;
  }

  private function parse_length($str) {
    $len = 0;
    preg_match('/(?:(?P<h>\d{1,2})\D*)?(?P<m>\d{1,2})\D*$/', $str, $matches);
    if(isset($matches['h']))
      $len += $matches['h'] * 60;
    if(isset($matches['m']))
      $len += $matches['m'];
    return $len;
  }

  private function advptr(&$div) {
    // Advances a DOM pointer to the next Element
    do {
      $div = $div->nextSibling;
    } while($div->nodeType != XML_ELEMENT_NODE);
  }

  private function dombr2nl($div) {
    if($div == NULL) return '';

    if($div->nodeType == XML_TEXT_NODE) {
      return $div->textContent . $this->dombr2nl($div->nextSibling);
    } elseif($div->nodeType == XML_ELEMENT_NODE) {
      if($div->nodeName == 'br') {
        return "\n" . $this->dombr2nl($div->nextSibling);
      } else {
        return $this->dombr2nl($div->firstChild);
      }
    } else {
      return $this->dombr2nl($div->nextSibling);
    }
  }
}

