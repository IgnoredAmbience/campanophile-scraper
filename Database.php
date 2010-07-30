<?php
require_once('functions.php');
require_once('Performance.php');

class Database {
  private $handle;
  private $cache = array(); // caches retrieved records

  function __construct($host, $user, $pass, $db) {
    $this->handle = mysql_connect($host, $user, $pass, true);
    if(!$this->handle)
      throw new Exception(mysql_error());

    if(!mysql_select_db($db, $this->handle))
      throw new Exception(mysql_error($this->handle));
  }

  function __destruct() {
    mysql_close($this->handle);
  }

  function raw_query($query) {
    $result = mysql_query($query, $this->handle);
    if(!$result) throw new Exception(mysql_error());
    return $result;
  }

  private function _fetch_cache($class, $id) {
    $class = strtolower($class);
    if(isset($this->cache[$class]) && isset($this->cache[$class][$id])) {
      return $this->cache[$class][$id];
    } else
      return NULL;
  }

  private function _put_cache($class, $id, $object) {
    $class = strtolower($class);
    $this->cache[$class][$id] = $object;
  }

  function fetch($class, $id, $force = false) {
    // Fetches a record of given class an Primary Key
    self::_check_class($class);

    $object = $this->_fetch_cache($class, $id);
    
    if($object === NULL || $force) {
      $id = (int) $id;
      $pk = $class::pk;
      $table = self::_class_to_table($class);

      $result = $this->raw_query("
        SELECT * FROM $table
        WHERE $pk = $id
        LIMIT 0,1
      ");

      $object = mysql_fetch_object($result, $class);
      $this->_put_cache($class, $id, $object);
      $object->post_db_fetch($this);
    }

    return $object;
  }

  /* TODO review this
   * function fetch_all($class, $field, $value) {
    self::_check_class($class);

    $value = mysql_real_escape_string($value);
    $table = self::_class_to_table($class);

    $result = $this->raw_query("
      SELECT * FROM $table
      WHERE $field = '$value'
    ");

    $objects = Array();

    while($row = mysql_fetch_assoc($result)) {
      $object = new $class();
      $object->apply_array($row);
      $object->post_db_fetch($this);
      $objects[] = $object;
    }

    return $objects;
  }
   */

  static function _check_class($class) {
    if(!(class_exists($class) && is_subclass_of($class, 'DatabaseRecord')))
      throw new Exception('Class not a DatabaseRecord');
  }

  static function _class_to_table($class) {
    // Converts class name to table name
    // eg: DatabaseRecord => database_record
    if(!is_string($class)) return '';

    $table = strtolower($class[0]);
    for($i = 1; $i < strlen($class); $i++) {
      if($class[$i] >= 'A' && $class[$i] <= 'Z') {
        $table .= '_';
        $table .= strtolower($class[$i]);
      } else {
        $table .= $class[$i];
      }
    }
    $table .= 's';
    return $table;
  }
}

