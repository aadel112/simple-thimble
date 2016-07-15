<?php
include( '../simple-thimble.php' );

$str = "

<html>
<body>
<img src='img/screenshot-1.jpg?ver=1.1' />
<img src='img/screenshot-2.jpg?ver=6&img=1' />
<img src='img/screenshot-3.jpg' />
</body>
</html>
";

$html = SimpleThimble::create( $config, $str )->embed()->html();

echo $html;


?>

