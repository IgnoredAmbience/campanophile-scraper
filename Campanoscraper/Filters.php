<?php
class Filters {
  static function ringer_name(RecordCollection $performances, $name) {
    return $performances->filter(array('$obj', 'has_ringer'), array($name));
  }

  static function not_in_db(RecordCollection $coll, Database $db, $key = '') {
    if(!$coll->size()) return $coll;

    if(!$key)
      $key = $coll->fetch(0)->_pk();
    $class = get_class($coll->fetch(0));
    $ids = implode(',', $coll->extract($key));
    $db_ids = $db->fetch_column($class, $key, "$key in ($ids)");

    return $coll->filter(array('Filters', 'not_in'), array('$obj', $key, $db_ids));
  }

  static function in($obj, $field, $items) {
    return in_array($obj->$field, $items);
  }

  static function not_in($obj, $field, $items) {
    return !self::in($obj, $field, $items);
  }
}
