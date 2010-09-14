<html><head>
<title>Campanophile Data Import</title>
</head>
<body>
<form method="post">
<?php
require('Campanoscraper/load.php');
require('config.php');
require('html_functions.php');

$c = Campanophile::getInstance();
$defaults = Campanophile::get_default_search_params();
$search_form_results = get_form($defaults);

if(isset($_REQUEST['selected_rows'])) {
  print_r($_REQUEST);
  foreach($_REQUEST['selected_rows'] as $id) {
    $id = (int) $id;
    $p = $c->get_performance($id);
    $p->save();
  }
} elseif($search_form_results) {
  if(isset($_REQUEST['fetch_all'])) {
    $res = $c->search_all($search_form_results);
  } else {
    $res = $c->search($search_form_results);
  }

  if($_REQUEST['name_filter']) {
    foreach($res as $perf) 
      $perf->fetch_campanophile_details();
    $res = Filters::ringer_name($res, $_REQUEST['name_filter']);
  }

  echo html_performance_table($res, true);
} else {
  echo produce_form($defaults);
  echo "Post-search filters:<br />";
  echo "Ringer name: <input type='text' name='name_filter' /><br />";
  echo "<input type='checkbox' name='fetch_all' /> Fetch more than 100 results?<br />";

}
?>

<input type="submit" />
</form>
</body>
</html>

