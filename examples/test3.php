<?php
include( '../simple-thimble.php' );

$str = "

<html>
<body>
<img src='http://localhost:8000/examples/img/screenshot-1.jpg' />
<img src='http://localhost:8000/examples/img/screenshot-2.jpg' />
<img src='http://localhost:8000/examples/img/screenshot-3.jpg' />
</body>
</html>
";

$html = SimpleThimble::create( $config, $str )->embed()->html();

echo $html;


?>

