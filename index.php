<html>
<head>
<link rel="stylesheet" href="vote.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
// helper functions
include 'ballot.php';

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

// get election title
$election = get_election_title($db, $eid);
$election_open = is_election_open($db, $eid);
?>

<title><?= $election ?></title>

</head>
<body>
<div class="body_container">
<h1><?= $election ?></h1>

<?php
// check if token is still valid
$result = $db->query('SELECT * FROM valid_tokens WHERE token="' . $token . '" AND used=false AND ele_id=' . $eid);
if ($result->num_rows != 1 && $election_open) {
	?>

	<p>The token '<?= $token ?>' is not valid for this election or has already been used to cast a vote.</p>
	</div>
	</body>

	<?php
	exit;
}
else
{
	?>

	<form action="submit.php">

	<?php
}

$result->close();

// Output the ballot
build_ballot($db, $eid);

if ($election_open) 
{
?>
	<p class="submit">Your vote is <b>final</b>. Once you press submit no further changes can be made!</p>
	<input type="submit"/>
	<input type="hidden" name="eid" value="<?= $eid ?>"/>
	<input type="hidden" name="token" value="<?= $token ?>"/>
	</form>
<?php
}
?>

</div>
<script>

document.addEventListener("click", function(ev){
	// which position did the user click on
	var pos = ev.target.dataset.pos;
	if (pos === undefined) return;

	var checked = document.querySelectorAll('input[data-pos="' + pos + '"]:checked').length;
	var item1 = document.querySelector('input[data-pos="' + pos + '"]');
	var list = item1.parentElement.parentElement;

	if (checked < item1.dataset.min || checked > item1.dataset.max) {
		list.classList.add('invalid');
	} else {
		list.classList.remove('invalid');
	}

});

</script>
</body>
</html>

<?php
// close database connection
$db->close();
?>
