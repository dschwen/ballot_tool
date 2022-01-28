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

function is_election_open($db, $eid) {
	// get election state
	$result = $db->query('SELECT open FROM elections WHERE ele_id=' . $eid);
	if ($result->num_rows != 1) {
		die('Election not found');
	}
	$result->data_seek(0);
	$open = $result->fetch_array()[0];
	$result->close();

	return $open;
}

function build_ballot($db, $eid) {
	// Obtain a list of positions and the respective candidates
	$result = $db->query('SELECT cand_id, candidates.pos_id AS pos_id, candidates.title AS ctitle, positions.title AS ptitle, min, max FROM candidates, positions WHERE candidates.pos_id = positions.pos_id AND ele_id = ' . $eid);
	$cpos = 0;
	while ($obj = $result->fetch_object()) {
		if ($cpos != $obj->pos_id)
		{
			if ($cpos != 0) {
				echo "</ul>";
			}

			$cpos = $obj->pos_id;
			echo "<h2>" . $obj->ptitle . "</h2>";
			$min = $obj->min;
			$max = $obj->max;

			if ($min == $max)
				echo "<p>Select exactly <b>" . $min . "</b> options.</p><ul>";
			else if ($min == 0) {
				if ($max == 1) 
					echo "<p>Select one or no options.</p><ul>";
				else
					echo "<p>Select up to <b>" . $max . "</b> options.</p><ul>";
			}
			else
				echo "<p>Select between <b>" . $obj->min . "</b> and <b>" . $obj->max . "</b> options.</p><ul>";
		}
		?>

		<li class="candidate">
		<input data-pos="<?= $obj->pos_id ?>" data-min="<?= $obj->min ?>" data-max="<?= $obj->max ?>" name="vote_<?= $obj->cand_id ?>" type="checkbox"/> <?= $obj->ctitle ?>
		</li>

		<?php
	}
	echo "</ul>";
}

?>

