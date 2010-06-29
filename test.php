<?php
require('Campanophile.php');

$c = Campanophile::getInstance();

/*
$r = $c->search(array('StartDate' => '26/05/2010', 'FinalDate' => '26/05/2010', 'Method' => 'Plain Bob Major'));
var_dump($r);
current($r)->fetch_details();

print_r(current($r));
*/

/* testing search_all */
$a = $c->search_all(array('StartDate' => '01/01/2009', 'FinalDate' => '31/12/2009', 'Guild' => 'Surrey Association'));
var_dump($a);

