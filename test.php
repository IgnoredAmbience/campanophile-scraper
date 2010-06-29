<?php
require('Campanophile.php');

$c = Campanophile::getInstance();

$r = $c->search(array('StartDate' => '26/05/2010', 'FinalDate' => '26/05/2010', 'Method' => 'Plain Bob Major'));
var_dump($r);
$r[0]->fetch_details();

print_r($r[0]);

