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

<?php
// Obtain a list of positions and the respective candidates
$result = $db->query('SELECT candidates.cand_id, candidates.pos_id AS pos_id, candidates.title AS ctitle, positions.title AS ptitle, COUNT(votes.cand_id) AS votes ' .
		     'FROM positions, candidates ' .
		     'LEFT JOIN votes ON votes.cand_id = candidates.cand_id ' .
		     'WHERE candidates.pos_id = positions.pos_id AND ele_id=' . $eid .' GROUP BY cand_id ORDER BY pos_id');

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
	<b><?= $obj->votes ?></b> <?= $obj->ctitle ?>
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
