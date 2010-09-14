<?php
class Filters {
  static function ringer_name(RecordCollection $performances, $name) {
    return $performances->filter(array('$obj', 'has_ringer'), array($name));
  }
}
