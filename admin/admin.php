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

// message
$message = '';

//
// process commands 
//

if (isset($_GET['add_el'])) {
	if (!$db->query('INSERT INTO elections (title) VALUES ("' . $db->real_escape_string($_GET['add_el']) . '")')) {
		die('Failed to create new election');
	}
	$message .= '<h3>Election added.</h3>';
}

if (isset($_GET['del_el'])) {
	if (!$db->query('DELETE FROM elections WHERE ele_id=' . intval($_GET['del_el']))) {
		votefail('Unable to cast ballot, please try again. [E2]');
	}
	$message .= '<h3>Election deleted.</h3>';
}

if (isset($_GET['cln_el'])) {
	$db->query('START TRANSACTION');

	if (!$db->query('DELETE FROM elections WHERE ele_id=' . intval($_GET['del_el']))) {
		votefail('Unable to cast ballot, please try again. [E2]');
	}

	$db->query('COMMIT');
	$message .= '<h3>Election cloned.</h3>';
}


if (isset($_GET['tkn_el']) && isset($_GET['n_tkn'])) {
	$tkn_el = intval($_GET['tkn_el']);
	$n_tkn = intval($_GET['n_tkn']);

	$message .= '<h3>' . $n_tkn . ' tokens added to election.</h3><p>';

	//$db->query('START TRANSACTION');
	for ($i=0; $i < $n_tkn; ++$i) {

		// construct token
		$list = '01234567890abcdefghijklmnopqrstuvwxyz_';
		$max = strlen($list) -1;
		$tkn = '';
		for ($j = 0; $j < 10; ++$j) {
			$tkn .= $list[random_int(0, $max)];
		}
		$message .= $tkn . '<br/>';

		//if (!$db->query('DELETE FROM elections WHERE ele_id=' . intval($_GET['del_el']))) {
		//	votefail('Unable to cast ballot, please try again. [E2]');
		//}
	}

	$message .= '</p>';
}

echo $message;
?>

</div>
</body>
</html>

<?php
// close database connection
$db->close();
?>
