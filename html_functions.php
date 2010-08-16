<?php
/***
 * Functions to aid generating HTML output
 ***/

function produce_form($params = array()) {
  $return = '';

  foreach($params as $key => $value) {
    if($key == 'TypeCode' ) {
      foreach(array('Peals', 'Quarters', 'Both') as $idx => $type) {
        $return .= "$type: <input type='radio' name='TypeCode' value='$idx'".
          ($value == $idx ? " checked='checked'" : "") .  " /><br />";
      }
    } elseif(is_string($value) || is_int($value)) {
      $return .= "$key: <input type='text' name='$key' value='$value' /><br />";
    } elseif(is_array($value)) {
      foreach($value as $idx => $name) {
        $return .= "$name: <input type='radio' name='$key' value='$idx' /><br />";
      }
    }
  }
  return $return;
}

function get_form($template = array()) {
  $return = array();
  
  foreach(array_keys($template) as $key) {
    if(isset($_REQUEST[$key])) {
      $return[$key] = $_REQUEST[$key];
    }
  }
  return $return;
}

function html_performance_table($performances, $check = false) {
  // This needs a rewrite
  $ch = $check ? '<th></th>' : '';
  $html = <<<EOF
<table><tr>
  $ch
  <th>#</th>
  <th>Date</th>
  <th>Location</th>
  <th>Method</th>
</tr>
EOF;

  $performances = array_reverse($performances);
  foreach($performances as $k => $performance) {
    $cb = $check
      ? "<td><input type='checkbox' name='selected_rows[]' value='$performance->campano_id' checked='checked'/></td>"
      : '';
    $location = $performance->location . 
      ($performance->dedication ? " ($performance->dedication)" : '') .
      ($performance->county ? ", $performance->county" : '');
    $html .= <<<EOF
<tr>$cb<td>$k</td><td>$performance->date</td><td>$location</td><td>$performance->changes $performance->method</td></tr>
EOF;
  }

  return $html;
}
