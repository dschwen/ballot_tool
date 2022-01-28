<html>
<head>
<link rel="stylesheet" href="vote.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ballot submission</title>
</head>
<body>
<div class="body_container">

<?php
function votefail($msg) {
	echo '<p>' . $msg . '</p></div></body>';
	exit;
}

// load database credentials ($db_cred)
include '/opt/secrets/voting.php';

// open database connection
$db = new mysqli("127.0.0.1", $db_cred['user'], $db_cred['pass'], "voting");
if ($db->connect_error) {
	die('Connect Error (' . $db->connect_errno . ') ' . $db->connect_error);
}

// get URL parameters
$eid = intval($_GET['eid']);
$token = $db->real_escape_string($_GET['token']);

// check if election is open
$result = $db->query('SELECT title, open FROM elections WHERE ele_id=' . $eid);
if ($result->num_rows != 1) {
	$db->close();
	votefail('Did not find specified election.');
}
$result->data_seek(0);
$data = $result->fetch_array();
$elname = $data[0];
$open = $data[1];
if (!$open) {
	$db->close();
	votefail('The election "' . $elname . '" is not open for voting yet.');
}

// lock the token table
if (!$db->query('LOCK TABLES valid_tokens WRITE, candidates READ, positions READ, votes WRITE')) {
	votefail('Unable to cast ballot, please try again. [E1]');
}

// check if token is still valid
$result = $db->query('SELECT * FROM valid_tokens WHERE token="' . $token . '" AND used=false AND ele_id=' . $eid);
if ($result->num_rows != 1) {
	$db->close();
	votefail('The token "' . $token . '" is not valid for this election or has already been used to cast a vote.');
}

// begin a transaction
if (!$db->query('START TRANSACTION')) {
	votefail('Unable to cast ballot, please try again. [E2]');
}

// Obtain a list of positions and the respective candidates
$result = $db->query('SELECT cand_id, candidates.pos_id AS pos_id FROM candidates, positions WHERE candidates.pos_id = positions.pos_id AND ele_id = ' . $eid);
if (!$result) {
	$db->close();
	votefail('Unable to cast ballot, please try again. [E3]');
}

// count votes per position
$count = array();

// loop over candidate list
while ($obj = $result->fetch_object()) {
	$pos = intval($obj->pos_id);
	$cand = intval($obj->cand_id);

	$arg = 'vote_' . $cand;
	if (isset($_GET[$arg]) && $_GET[$arg] == "on") {
		$count[$pos] = intval($count[$pos]) + 1;

		// add vote
		if (!$db->query('INSERT INTO votes (cand_id) VALUES (' . $cand . ')')) {
			$db->query('ROLLBACK');
			votefail('Unable to cast ballot, please try again. [E4]');
		}
	}
}

// Obtain a list of positions and the permitted min/max amount of valid options
$result = $db->query('SELECT positions.pos_id AS pos_id, positions.title AS title, min, max, COUNT(positions.pos_id) AS max2 FROM positions LEFT JOIN candidates ON candidates.pos_id = positions.pos_id WHERE ele_id='. $eid .' GROUP BY candidates.pos_id');
if (!$result) {
	$db->close();
	votefail('Unable to cast ballot, please try again. [E3]');
}

// check ballot validity
while ($obj = $result->fetch_object()) {
	$pos = intval($obj->pos_id);
	$title = $obj->title;
	$min = intval($obj->min);
	$max1 = intval($obj->max);
	$max2 = intval($obj->max2);
	$max = min($max1, $max2);

	if ($count[$pos] < $min || $count[$pos] > $max) {
		$db->query('ROLLBACK');
		votefail('An incorrect number of options (' . $count[$pos] . ') was selected for the ballot line "' . $title . '". Between ' . $min . ' and ' . $max . ' options can be selected. Please try again. [E5]');
	}
}

// everything is ok, make token as used...
if (!$db->query('UPDATE valid_tokens SET used=true WHERE token="' . $token . '" AND ele_id=' . $eid)) {
	$db->query('ROLLBACK');
	votefail('Unable to cast ballot, please try again. [E6]');
}

// commit voting transaction
$db->query('COMMIT');
?>

<h1>Ballot received</h2>

</div>
</body>
</html>

<?php
// close database connection
$db->close();
?>
