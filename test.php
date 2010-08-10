<?php
require('Campanoscraper/load.php');

$c = Campanophile::getInstance();
$db = new Database('localhost', 'root', '', 'campanophile');

//$r = $c->search(array('StartDate' => '01/06/2010', 'FinalDate' => '30/06/2010', 'Guild' => 'University of London Society'));
//var_dump($r);
//$r = current($r);
//$r->fetch_campanophile_details();

$r = $c->get_performance(103998);

print_r($r);
$r->save();
//print_r($r);

/* testing search_all */
//$a = $c->search_all(array('StartDate' => '01/01/2009', 'FinalDate' => '31/12/2009', 'Guild' => 'Surrey Association'));
//var_dump($a);

