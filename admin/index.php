<html>
<head>
<link rel="stylesheet" href="../vote.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Interface</title>
</head>
<body>
<div class="body_container">

<?php
// load database credentials ($db_cred)
include '/opt/secrets/voting.php';

// open database connection
$db = new mysqli("127.0.0.1", $db_cred['user'], $db_cred['pass'], "voting");
if ($db->connect_error) {
	die('Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);
}
?>

<h2>Select an election</h2>
<ol>

<?php
// get election titles
$result = $db->query('SELECT * FROM elections');
while ($obj = $result->fetch_object()) {
	echo '<li><a href="results.php?eid=' . $obj->ele_id . '">' . $obj->title . '</a></li>';
}
$result->close();
?>

</ol>
</div>
</body>
</html>

<?php
// close database connection
$db->close();
?>
