<?php
class Database {
  private $handle;
  private $cache = array(); // caches retrieved records
  private $last_query = "";
  private static $instances = array();

  function __construct($host, $user, $pass, $db) {
    $this->handle = mysql_connect($host, $user, $pass, true);
    if(!$this->handle)
      throw $this->error();

    if(!mysql_select_db($db, $this->handle))
      throw $this->error();

    self::$instances[] = $this;
  }

  function __destruct() {
    mysql_close($this->handle);
  }

  static function get_instance($id = 0) {
    if(self::$instances[$id])
      return self::$instances[$id];
    else
      throw new Exception('No Databases instantiated');
  }

  public function raw_query($query) {
    $this->last_query = $query;
    $result = mysql_query($query, $this->handle);
    if(!$result) throw $this->error();
    return $result;
  }

  public function raw_fetch_all($where, $class) {
    if(!self::_check_class($class))
      throw new Exception("Invalid class: $class");
    $table = self::_class_to_table($class);

    $result = $this->raw_query("SELECT * FROM $table WHERE $where");

    $objects = new RecordCollection();

    while($object = mysql_fetch_object($result, $class)) {
      $object->post_db_fetch($this);
      $objects->add($object);
    }

    return $objects;
  }

  public function fetch($class, $id, $force = false, $pk = '') {
    // Fetches a record of given class an Primary Key
    if(!self::_check_class($class))
      throw new Exception('Invalid class');

    $object = $this->_fetch_cache($class, $id);
    
    if($object === NULL || $force) {
      $id = (int) $id;
      $pk = $pk ? $pk : constant($class.'::pk');
      $table = self::_class_to_table($class);

      $result = $this->raw_query("
        SELECT * FROM $table
        WHERE $pk = $id
        LIMIT 0,1
      ");

      $object = mysql_fetch_object($result, $class);
      $this->_put_cache($class, $id, $object);
      $object->_set_db($this);
      $object->post_db_fetch($this);
    }

    return $object;
  }

  public function fetch_all($class, $field, $value) {
    $value = $this->escape($value);

    return $this->raw_fetch_all("$field = '$value'", $class);
  }

  public function fetch_column($class, $field, $where = '') {
    if(!self::_check_class($class))
      throw new Exception('Invalid class');

    if($where) $where = " WHERE $where";

    $table = self::_class_to_table($class);
    $result = $this->raw_query("SELECT $field FROM $table $where;");
    $results = array();
    $numrows = mysql_num_rows($result);
    for($row = 0; $row < $numrows; $row++) {
      $results[] = mysql_result($result, $row);
    }
    return $results;
  }

  public function insert($object) {
    $data = get_object_vars($object); // public context
    unset($data[$object->_pk()]); // (auto-increments)

    $fields = $this->_field_list($data);
    $values = $this->_value_list($data);

    $table = self::_class_to_table($object);

    $result = $this->raw_query("
      INSERT INTO `$table` $fields
      VALUES $values;
    ");

    if(!$result)
      throw $this->error();

    $id = mysql_insert_id($this->handle);
    $this->_put_cache(get_class($object), $id, $object);
    $object->_set_db($this);
    return $id;
  }

  public function update($object, $key = '') {
    $data = get_object_vars($object);
    $table = self::_class_to_table($object);

    $pk = $object->_pk();
    $pkv = $data[$pk];
    unset($data[$pk]);

    // An alternative key to select by has been used, pk should be left out of
    // new query as well as the condition key
    if($key && array_key_exists($key, $data)) {
      $pk = $key;
      $pkv = $data[$pk];
      unset($data[$pk]);
    }

    $data_list = $this->_data_list($data);

    $result = $this->raw_query("
      UPDATE $table
      SET $data_list
      WHERE $pk = $pkv;
    ");

    if(!$result)
      throw $this->error();
  }

  public function escape($str) { 
    return mysql_real_escape_string($str);
  }

  public function error() {
    $code = mysql_errno($this->handle);
    $str = "MySQL Error #$code: ".mysql_error($this->handle);
    $str .= "\nOn query: '$this->last_query'";
    switch($code) {
      case 1062:
        return new DBKeyViolation($str, $code);
        break;
      default:
        return new DBException($str, $code);
    }
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

  function _field_list($data) {
    return '('.implode(',', array_keys($data)).')';
  }

  function _value_list($data) {
    $ret = '(';
    foreach($data as $value) {
      $ret .= "'".$this->escape($value)."',";
    }
    $ret[strlen($ret)-1] = ')';
    return $ret;
  }

  function _data_list($data) {
    $ret = '';
    foreach($data as $field => $value) {
      if($ret) {
        $ret .= ', ';
      }
      $value = $this->escape($value);
      $ret .= "$field = '$value'";
    }
    return $ret;
  }

  static function _check_class($class) {
    return class_exists($class) && is_subclass_of($class, 'DatabaseRecord');
  }

  static function _class_to_table($class, $plural = true) {
    // Converts class name to table name
    // eg: DatabaseRecord => database_records
    if(is_object($class)) $class = get_class($class);
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
    if($plural)
      $table .= 's';
    return $table;
  }

  static function _table_to_class($table) {
    $table[0] = strtoupper($table[0]);
    for($i = 1; $i < strlen($table); $i++) {
      if($table[$i-1] == '_')
        $table[$i] = strtoupper($table[$i]);
    }
    $table = str_replace('_', '', $table);
    return $table;
  }
}

class DBException extends Exception {}
class DBKeyViolation extends DBException {
  public $field = '';
  public function __construct($message, $code) {
    parent::__construct($message, $code);
    $field = explode("'", $message);
    $this->field = @$field[3];
  }
}

