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
        $pk = $this->_pk();
        if($this->$pk) {
          $this->_cache[$name] = Database::fetch_all($child, $this->_fk(), $this->$pk);
        } else {
          $this->_cache[$name] = new RecordCollection();
        }
      }

      return $this->_cache[$name];
    }

    // Catch failures
    trigger_error( 'Undefined property via __get(): ' . $name, E_USER_NOTICE);
    debug_print_backtrace();
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
    trigger_error( 'Undefined property via __set(): ' . $name, E_USER_NOTICE);
    debug_print_backtrace();
  }

  public function save($db = NULL) {
    // Will save any newly created 'parent' records
    // Will save all associated 'child' records
    if(!$db) {
      if($this->_db)
        $db = $this->_db;
      else
        $db = Database::get_instance();
    }

    foreach($this->_cache as $key => $item) {
      if(is_a($item, 'DatabaseRecord')) {
        $item->save();
        $ipk = $item->_pk();
        $fk = $item->_fk();
        $this->$fk = $item->$ipk;
        unset($this->_cache[$key]);
      }
    }
    
    $pk = $this->_pk();

    if($this->$pk) {
      $db->update($this);
    } else {
      $this->$pk = $db->insert($this);
    }

    foreach($this->_cache as $key => $item) {
      if(is_a($item, 'RecordCollection')) {
        $item->apply_field($this->_fk(), $this->$pk);
        $item->save();
        $item->delete_removed();
      }
    }
  }

  function _pk() {
    // Helper function to return pk
    // If PHP5.3 was our minimum, we'd just do static::pk...
    return constant(get_class($this) . '::pk');
  }

  function _fk() {
    // Helper function to return the fk field name referencing this object
    return Database::_class_to_table($this, false).'_'.$this->_pk();
  }

  // Placeholder for code to execute after fetching from db
  public function post_db_fetch($db) {}
}
