<?php
include( '../simple-thimble.php' );

$str = "

<html>
<head>

<link type='text/css' href='http://localhost:8000/examples/latte/style.css'></link>
</head>
<body>

<div class='post-header'>test</div>


</body>
</html>
";

// $html = SimpleThimble::create( $config, $str )->embed()->html();
$html = $str;

echo $html;


?>

