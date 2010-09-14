<?php
function __autoload($name) {
  $file = $name . '.php';
  if(file_exists(dirname(__FILE__) . "/$file")) {
    include_once $file;
  } else {
    //trigger_error("Could not load class: $file");
  }
}

