<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Really?</title>
</head>
<body>
	<h2>Do you really want to do that?</h2>
<?php
	$ok = "foos.php?".$_SERVER['QUERY_STRING'];
	$cancel = "foos.php";
	echo "<a href=\"$cancel\">Ehm, I'm not sure</a> <br>";
	echo "<a href=\"$cancel\">No, take me back</a> <br>";
	echo "<a href=\"$ok\">Yes, do as I say</a> <br>";

?>
</body>
</html>