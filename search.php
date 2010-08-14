<html><head>
<title>Campanophile Site Search</title>
</head>
<body>
<?php
require('Campanoscraper/load.php');
require('config.php');
require('html_functions.php');

$defaults = Campanophile::get_default_search_params();
$form_results = get_form($defaults);

if($form_results) {
  $c = Campanophile::getInstance();
  if(isset($_REQUEST['fetch_all'])) {
    $res = $c->search_all($form_results);
  } else {
    $res = $c->search($form_results);
  }
  echo html_performance_table($res);
} else {
?>
<form method="post">
<?php  echo produce_form($defaults); ?>
<input type="checkbox" name="fetch_all" /> Fetch more than 100 results?<br />
<input type="submit" />
</form>

<?php
}
?>

</body>
</html>

