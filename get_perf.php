#!/usr/bin/php
<?php
require('Campanoscraper/load.php');

if($argc < 2) {
  die("Needs integer parameter of Campanophile ID\n");
}

$c = Campanophile::getInstance();
print_r($c->get_performance((int) $argv[1]));

