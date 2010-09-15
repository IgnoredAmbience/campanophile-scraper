<?php
require('Campanoscraper/load.php');
require('config.php');

$c = Campanophile::getInstance();

$defaults = array(
  'function' => 'browse',
  'function_params' => array(),
  'post_filter' => array(),
  'outline_only' => false
);

foreach($conf['imports'] as $import) {
  $import += $defaults;

  $results = $c->$import['function']($import['function_params']);
  $results = Filters::not_in_db($results, $db, 'campano_id');
  foreach($results as $result) {
    if(!$import['outline_only'])
      $result->fetch_campanophile_details();
  }

  if($import['post_filter']) {
    $results = Filters::$import['post_filter'][0]($results, $import['post_filter'][1]);
  }

  $results->save();
}
