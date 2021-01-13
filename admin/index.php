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

// get URL parameters
if (isset($_GET['eid']))
{
	$eid = intval($_GET['eid']);

	// administer election
	$result = $db->query('SELECT title FROM elections WHERE ele_id=' . $eid);
	if ($result->num_rows != 1) {
		die('Election not found');
	}
	$result->data_seek(0);
	$election = $result->fetch_array()[0];
	$result->close();
?>

<h1><?= $election ?></h2>
<p>results</p>

<?php
}
else
{
?>

<h2>Select an election</h2>
<ol>

<?php
	// get election titles
	$result = $db->query('SELECT * FROM elections');
	while ($obj = $result->fetch_object()) {
		echo '<li><a href="?eid=' . $obj->ele_id . '">' . $obj->title . '</a></li>';
	}
	$result->close();
?>

</ol>

<?php

}

// Obtain a list of positions and the respective candidates
$result = $db->query('SELECT cand_id, candidates.pos_id AS pos_id, candidates.title AS ctitle, positions.title AS ptitle FROM candidates, positions WHERE candidates.pos_id = positions.pos_id AND ele_id = ' . $eid);
$cpos = 0;
while ($obj = $result->fetch_object()) {
	if ($cpos != $obj->pos_id)
	{
		if ($cpos != 0) {
			echo "</ul>";
		}

		$cpos = $obj->pos_id;
		echo "<h2>" . $obj->ptitle . "</h2><ul>";
	}

?>

<li class="candidate">
<input name="vote_<?= $obj->cand_id ?>" type="checkbox"/> <?= $obj->ctitle ?>
</li>

<?php

}

?>

</ul>

</div>
</body>
</html>

<?php
// close database connection
$db->close();
?>
