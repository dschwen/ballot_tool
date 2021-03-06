<?php

function get_election_title($db, $eid) {
	// get election title
	$result = $db->query('SELECT title FROM elections WHERE ele_id=' . $eid);
	if ($result->num_rows != 1) {
		die('Election not found');
	}
	$result->data_seek(0);
	$election = $result->fetch_array()[0];
	$result->close();

	return $election;
}

function build_ballot($db, $eid) {
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
	echo "</ul>";
}

?>

