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
$conf['admin_password'] = 'abcdef';

// Leave everything below here
$db = new Database($conf['db_host'], $conf['db_user'], $conf['db_pass'], $conf['db_name']);

