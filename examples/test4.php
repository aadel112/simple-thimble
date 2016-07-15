<?php
include( '../simple-thimble.php' );

$str = "

<html>
<head>
<script type='text/javascript' src='js/jquery.js'></script>
</head>
<body>
</body>
<script>jQuery('body').html('test');</script>
</html>
";

$html = SimpleThimble::create( $config, $str )->embed()->html();

echo $html;

?>

