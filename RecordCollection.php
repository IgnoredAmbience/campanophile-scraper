<?php
class RecordCollection {
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
    foreach($this->current as $item) {
      $item->$key = $value;
    }
  }

  function save() {
    foreach($this->current as $item) {
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
}

