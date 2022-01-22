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

// toggle open/closed
if (isset($_GET['toggle'])) {
	$eid = intval($_GET['toggle']);
        if (!$db->query('UPDATE elections SET open = NOT open WHERE ele_id=' . $eid)) {
                die('Failed to toggle election');
        }
}
?>

<h2>Select an election</h2>
<ol>

<?php
// get election titles
$result = $db->query('SELECT * FROM elections');
while ($obj = $result->fetch_object()) {
	echo '<li><b><a href="results.php?eid=' . $obj->ele_id . '">' . $obj->title . '</a></b> (<a href="?toggle=' . $obj->ele_id . '">';
	if ($obj->open) 
		echo "close";
	else
		echo "open";
	echo '</a> this election)</li>';
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
