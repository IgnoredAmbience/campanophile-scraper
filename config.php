<?php
/***
 * Application-level configuration settings
 ***/
// Database
$conf['db_host'] = 'localhost';
$conf['db_user'] = 'root';
$conf['db_pass'] = '';
$conf['db_name'] = 'ulscr';

// 
$conf['imports'][0] = array(
  'function' => 'search',
  'function_params' => array(
    'Guild' => 'University of London Society'
  )
);
$conf['imports'][1] = array(
  'function' => 'search',
  'function_params' => array(
    'Guild' => "Saint Olave's Society"
  )
);

// Leave everything below here
$db = new Database($conf['db_host'], $conf['db_user'], $conf['db_pass'], $conf['db_name']);

