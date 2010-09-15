<?php
/***
 * Application-level configuration settings
 ***/
// Database
$conf['db_host'] = 'localhost';
$conf['db_user'] = 'root';
$conf['db_pass'] = '';
$conf['db_name'] = 'campanophile';

// 
$conf['imports'][0] = array(
  'function' => 'search',
  'function_params' => array(
    'Ringer' => 'Thomas Wood'
  ),
  // Since there is a Thomas Wood Something also ringing, this filter is required
  // It turns the string into a Ringer object and does all the smart first/last/initial
  // matching
  'post_filter' => array('ringer_name', 'Thomas Wood'),
);

// Leave everything below here
$db = new Database($conf['db_host'], $conf['db_user'], $conf['db_pass'], $conf['db_name']);

