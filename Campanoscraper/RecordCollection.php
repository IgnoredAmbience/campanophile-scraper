<?php
class RecordCollection implements Iterator {
  private $original = array();
  private $current = array();

  function __construct($initial = array()) {
    $this->add_all($initial, true);
  }

  function add($item, $original = false) {
    if(!in_array($item, $this->current))
      $this->current[] = $item;
    if($original && !in_array($item, $this->original))
      $this->original[] = $item;
  }

  function add_all($array, $original = false) {
    foreach($array as $item) {
      $this->add($item, $original);
    }
  }

  function size() {
    return count($this->current);
  }

  function apply_field($key, $value) {
    foreach($this as $item) {
      $item->$key = $value;
    }
  }

  function filter($callback, $params = array()) {
    $obj = NULL;
    $return = new self;
    if(is_array($callback) && $callback[0] == '$obj')
      $callback[0] =& $obj;

    $idx = array_search('$obj', $params);
    if($idx !== false)
      $params[$idx] =& $obj;


    foreach($this as $obj) {
      if(call_user_func_array($callback, $params))
        $return->add($obj, true);
    }

    return $return;
  }

  function extract($field) {
    // Extracts a field from all records to an array
    $return = array();
    foreach($this as $record) {
      $return[] = $record->$field;
    }
    return $return;
  }

  function save() {
    foreach($this as $item) {
      $item->save();
    }
  }

  function delete_removed() {
    foreach($this->original as $item) {
      if(!in_array($item, $this->current))
        $item->delete();
    }
    $this->original = $this->current;
  }

  function to_array() {
    return $this->current;
  }

  function fetch($idx) {
    return $this->current[$idx];
  }

  // Iterator
  function current() {
    return current($this->current);
  }
  function key() {
    return key($this->current);
  }
  function next() {
    next($this->current);
  }
  function rewind() {
    reset($this->current);
  }
  function valid() {
    return key($this->current) !== NULL;
  }
}

