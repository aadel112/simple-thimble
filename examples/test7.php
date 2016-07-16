<?php
include( '../simple-thimble.php' );

$str = "

<html>
<head>
<link href='//fonts.googleapis.com/css?family=Sanchez%3A400%2C400italic&amp;ver=4.5.3'></link>
</head>
<body style=\"font-family: 'Sanchez', sans-serif;\">
Test
</body>
</html>
";

$html = SimpleThimble::create( $config, $str )->embed()->html();
// $html = $str;

echo $html;


?>

