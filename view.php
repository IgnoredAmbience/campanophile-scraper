<html><head>
<title>Performance</title>
</head>
<body>
<?php
require('Campanoscraper/load.php');
require('config.php');
require('html_functions.php');

$form_fields = array('id' => '', 'database' => 0);
$form = get_form($form_fields);

if($form) {
  if($form['database'] == 0) {
    $c = Campanophile::getInstance();
    $p = $c->get_performance($form['id']);
  } else {
    // Do some finding stuff
    $p = new Performance();
  }

  foreach(get_object_vars($p) as $field => $val) {
    echo "$field: $val<br />";
  }
} else {
?>
<form method="get">
<?php
  $form_fields['database'] = array('Campanophile', 'Local Database');
  echo produce_form($form_fields);
?>
<input type="submit" />
</form>

<?php
}
?>

</body>
</html>

