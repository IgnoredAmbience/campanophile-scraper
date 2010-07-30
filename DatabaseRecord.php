<?php
abstract class DatabaseRecord {
  /**
   * Class that holds common implementation for Classes representing
   * database records
   */

  const pk = 'id';
  private $_cache = array();

  public function apply_array($arr) {
    foreach($arr as $k => $v) {
      if(property_exists($this, $k)) {
        $this->$k = $v;
      }
    }
  }

  public function __get($name) {
    try {
      Database::_check_class($name);
      $prop = $name . '_' . constant($name.'::pk');
      if($this->$prop) {
        return Database::fetch($name, $this->$prop);
      } else {
        if(!isset($this->_cache[$name])) {
          $this->__set($name, new $name);
        }
        return $this->_cache[$name];
      }
    } catch (Exception $e) {
      $trace = debug_backtrace();
      trigger_error(
        'Undefined property via __set(): ' . $name .  ' in '
        . $trace[0]['file'] .  ' on line ' . $trace[0]['line']
        , E_USER_NOTICE);
    }
  }

  public function __set($name, $value) {
    try {
      Database::_check_class($name);
      $pk = constant($name . '::pk');
      $prop = $name . '_' . $pk;
      if($value->$pk) {
        $this->$prop = $value->$pk;
        unset($this->_cache[$name]);
      } else {
        $this->$prop = 0;
        $this->_cache[$name] = $value;
      }
    } catch (Exception $e) {
      $trace = debug_backtrace();
      trigger_error(
        'Undefined property via __set(): ' . $name .  ' in '
        . $trace[0]['file'] .  ' on line ' . $trace[0]['line']
        , E_USER_NOTICE);
    }
  }

  // Placeholder for code to execute after fetching from db
  public function post_db_fetch($db) {}
}
