<?php
function __autoload($name) {
  @include_once $name . '.php';
//  if (!class_exists($name, false))
//    trigger_error("Unable to load class: $name", E_USER_NOTICE);
}

