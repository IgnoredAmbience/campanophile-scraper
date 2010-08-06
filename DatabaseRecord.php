<?php
require_once('Database.php');

abstract class DatabaseRecord {
  /**
   * Class that holds common implementation for Classes representing
   * database records
   */

  const pk = 'id';
  private $_cache = array();
  private $_db = NULL;

  public function apply_array($arr) {
    foreach($arr as $k => $v) {
      if(property_exists($this, $k)) {
        $this->$k = $v;
      }
    }
  }

  public function _set_db(Database $db) {
    $this->_db = $db;
  }

  public function __get($name) {
    $name = Database::_table_to_class($name);
    if(Database::_check_class($name)) {
    // Belongs to relationship
      $prop = $name . '_' . constant($name.'::pk');
      if(property_exists($this, $prop)) {
        if($this->$prop) {
          return Database::fetch($name, $this->$prop);
        } else {
          if(!isset($this->_cache[$name])) {
            $this->__set($name, new $name);
          }
          return $this->_cache[$name];
        }
      }
    } elseif (
      $name[strlen($name)-1] == 's'
      && Database::_check_class($child = substr($name, 0, -1))
    ) {
      // Has many relationship
      if(!isset($this->_cache[$name])) {
        $pk = constant(get_class($this).'::pk');
        if($this->$pk) {
          $fk = Database::_class_to_table($this) . "_$pk";
          $this->_cache[$name] = Database::fetch_all($child, $fk, $this->$pk);
        } else {
          $this->_cache[$name] = new RecordCollection();
        }
      }

      return $this->_cache[$name];
    }

    // Catch failures
    $trace = debug_backtrace();
    trigger_error(
      'Undefined property via __get(): ' . $name .  ' in '
      . $trace[0]['file'] .  ' on line ' . $trace[0]['line']
      , E_USER_NOTICE);
  }

  public function __set($name, $value) {
    $name = Database::_table_to_class($name);
    if(Database::_check_class($name)) {
      $pk = constant($name . '::pk');
      $prop = $name . '_' . $pk;

      if(property_exists($this, $prop)) {
        if($value->$pk) {
          $this->$prop = $value->$pk;
          unset($this->_cache[$name]);
          return;
        } else {
          $this->$prop = 0;
          $this->_cache[$name] = $value;
          return;
        }
      }
    }

    // Catch failures
    $trace = debug_backtrace();
    trigger_error(
      'Undefined property via __set(): ' . $name .  ' in '
      . $trace[0]['file'] .  ' on line ' . $trace[0]['line']
      , E_USER_NOTICE);
  }

  public function save($db = NULL) {
    if(!$db) {
      if($this->_db)
        $db = $this->_db;
      else
        $db = Database::get_instance();
    }

    $db->insert($this);
  }

  // Placeholder for code to execute after fetching from db
  public function post_db_fetch($db) {}
}
