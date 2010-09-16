<?php
class RecordCollection implements Iterator {
  private $removed = array();
  private $current = array();

  function __construct($initial = array()) {
    $this->add_all($initial, TRUE);
  }

  function add($item, $key = NULL) {
    $removed_key = array_search($item, $this->removed);
    if($removed_key !== FALSE) 
      unset($this->removed[$removed_key]);
    if($key !== NULL) {
      $this->current[$key] = $item;
    } else {
      $this->current[] = $item;
    }
  }

  function add_all($items, $use_keys = FALSE) {
    foreach($items as $key => $item) {
      if($use_keys) {
        $this->add($item, $key);
      } else {
        $this->add($item);
      }
    }
  }

  function merge(RecordCollection $with, $use_keys = FALSE) {
    $this->add_all($with->to_array(), $use_keys);
  }

  function remove_item($item) {
    $key = array_search($item, $this->current);
    if($key !== FALSE) {
      return $this->remove_key($key);
    } else {
      return FALSE;
    }
  }

  function remove_key($key) {
    if(isset($this->current[$key])) {
      $this->removed[$key] = $this->current[$key];
      unset($this->current[$key]);
      return TRUE;
    } else {
      return FALSE;
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
    foreach($this->removed as $item) {
      $item->delete();
    }
    $this->removed = array();
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
    return reset($this->current);
  }
  function valid() {
    return key($this->current) !== NULL;
  }
  function end() {
    // Do not use within an iteration, not safe!
    return end($this->current);
  }
}

