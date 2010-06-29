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
$r = $c->search(array('Guild' => 'Surrey Association'));
var_dump($r);

$a = $c->search_all(array('Guild' => 'Surrey Association'));
var_dump($a);

printf("search: %d elements, search_all: %d elements\n", count($r), count($a));
?>

