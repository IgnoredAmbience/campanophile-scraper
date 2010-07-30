<?php
require('Campanophile.php');

$c = Campanophile::getInstance();
$db = new Database('localhost', 'root', '', 'campanophile');

$r = $c->search(array('StartDate' => '26/05/2010', 'FinalDate' => '26/05/2010', 'Method' => 'Plain Bob Major'));
var_dump($r);
$r = current($r);
$r->fetch_campanophile_details();

print_r($r);

$r->save();

/* testing search_all */
//$a = $c->search_all(array('StartDate' => '01/01/2009', 'FinalDate' => '31/12/2009', 'Guild' => 'Surrey Association'));
//var_dump($a);

