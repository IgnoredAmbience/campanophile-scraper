<?php
class Ringer extends DatabaseRecord {
  public $id = 0;
  public $first_name = '';
  public $middle_names = '';
  public $last_name = '';

  public function __construct($name = '') {
    if($name) {
      $name = str_replace('.', '', $name);
      $parts = explode(' ', $name);
      $this->first_name = array_shift($parts);
      $this->last_name = array_pop($parts);
      $this->middle_names = implode(' ', $parts);
    }
  }

  public function initials() {
    $parts = explode(' ', $this->middle_names);
    return array_map(create_function('$name', 'return $name[0];'), $parts);
  }

  function middle_names_regex($names = '') {
    $ret = '';
    if(!is_array($names)) {
      $names = explode(' ', $this->middle_names);
      if(!$names[0]) return '.*';
      $ret = '(';
    }

    $name = array_shift($names);
    $initial = $name[0];
    $rest = substr($name, 1);

    $ret .= $initial;
    if($rest)
      $ret .= "($rest)?";
    else
      $ret .= '[^[:space:]]*';
    if($names)
      // Open next iteration's brackets
      $ret .= "( " . $this->middle_names_regex($names);

    // Close this iteration's brackets
    $ret .= ')?';

    return $ret;
  }


  static function find($name) {
    $parsed = new self($name);

    try {
      $db = Database::get_instance();
      $fn = $db->escape($parsed->first_name);
      $ln = $db->escape($parsed->last_name);
      $regex = $db->escape($parsed->middle_names_regex());

      $ringers = $db->raw_fetch_all("
        first_name = '$fn'  AND
        last_name = '$ln'   AND
        middle_names REGEXP '^$regex\$'
      ", get_class());
      if(!$ringers->size()) {
        return $parsed;
      } else {
        // The database would appear to return records by strength of match to
        // the regex, so most plausible match will be set here
        return $ringers->fetch(0);
      }
    } catch (Exception $e) {
      print_r($e);
      return $parsed;
    }
  }
}

