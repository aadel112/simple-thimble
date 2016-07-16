<?php
include( '../simple-thimble.php' );

$str = "

<html>
<head>
<link href='css/test6.css'></link>
<link href='font-awesome/css/font-awesome.css'></link>
</head>
<body>
<img class='s1'/>
<div class='fa-google-plus-circle'></div>
</body>
</html>
";

$html = SimpleThimble::create( $config, $str )->embed()->html();
// $html = $str;

echo $html;


?>

