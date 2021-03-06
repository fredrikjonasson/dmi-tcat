<?php
require_once __DIR__ . '/functions.php';


/*
function sql_maker ($header, $dataset, $table) {
	$dbh = pdo_connect();
	$key_array =array('location','username','user','id','tweetid','id_string','from_user_id','from_user_name','from_user_realname','user_from_name','user_from_id','user_to_id','user_to_name','to_user','to_user_id','to_user_name','in_reply_to_status_id','in_reply_to_status_id_str','in_reply_to_user_id','in_reply_to_screen_name','quoted_status_id','retweeted_status','retweeted','retweet_id');
	$n = sizeof($key_array);
	$column_array = explode(",", $header);
	$array_for_sql = array();
	$pseudonymized_columns = array();
	try{
		$copy_table_sql = "CREATE TABLE IF NOT EXISTS ".$dataset."".$table."_TMP SELECT * FROM ".$dataset."".$table.";";
		$rec = $dbh->prepare($copy_table_sql);
		$rec->execute();
	} catch (Exception $e){
		Die("Error when querying". $copy_table_sql ."pseudonymized data to database");
	}
	for ($i=0; $i < $n; $i++) {
		if((in_array($column_array[$i], $key_array)) !== FALSE){
			$result_string = "'" . str_replace(",", "','", $column_array[$i] . "'");
			array_push($pseudonymized_columns, $column_array[$i]);
			try{
				$save_to_pseudonymize_sql = "INSERT IGNORE INTO tcat_pseudonymized_data(original_data, fieldtype) SELECT ". $column_array[$i] .", ".$result_string." FROM ".$dataset."".$table." WHERE ".$column_array[$i] ." is not NULL;";
				$rec = $dbh->prepare($save_to_pseudonymize_sql);
				$rec->execute();
			}
			catch (Exception $e){
				Die("Error when saving". $save_to_pseudonymize_sql ."pseudonymized data to database");
			}
		}
	}
	$no_pzeudonymized_columns=count($pseudonymized_columns);
	$send_text = serialize($pseudonymized_columns);
	$file = 'nocol.txt';
	file_put_contents($file, $send_text);
	for ($i=0; $i < $no_pzeudonymized_columns; $i++) {
		try{
			$prepare_pseudonymized_table_sql = "UPDATE ".$dataset."".$table."_TMP FT, tcat_pseudonymized_data ST SET FT.".$pseudonymized_columns[$i]." = ST.pseudo_val WHERE FT.".$pseudonymized_columns[$i]."= ST.original_data;";
			$send_text = serialize($prepare_pseudonymized_table_sql);
			$file = 'pseudoQ.txt';
			file_put_contents($file, $send_text, FILE_APPEND);
			$rec = $dbh->prepare($prepare_pseudonymized_table_sql);
			$rec->execute();
		}
		catch (Exception $e){
			Die("Error when saving". $save_to_pseudonymize_sql ."pseudonymized data to database");
		}
	}
}


*/

//	$sql = "UPDATE pseudotrack_tweets PT, tcat_pseudonymized_data TD SET PT.from_user_name = CAST(TD.pseudo_val AS CHAR) WHERE PT.from_user_name = TD.original_data;";
	//$rec = $dbh->prepare($sql);
	//$rec->execute();


/**
 * Fetch the data needed for de-pseudonymization from the database, and save it to a multidimensional array.
 *

 */

function fetch_pseudonymized_data() {
	global $pseudo_list;
	//$pseudo_list = array();

	// If not empty, store a copy of the table in $pseudo_list
	$dbh = pdo_connect();
	$sql = "SELECT * FROM tcat_pseudonymized_data;";
	$rec = $dbh->prepare($sql);
	$rec->execute();

	while ($row = $rec->fetch(PDO::FETCH_ASSOC)) {
		$pseudo_list[$row['pseudo_val']] = array($row['original_data'], $row['fieldtype']);
	}
	//$pseudo_list=array();
	// Close the database connection before returning.
	$dbh = NULL;
	return $pseudo_list;
}

/**
 * Save the array consisting of the original data and the corresponding pseudonymisation number. By using start value and last value the function makes sure to only add the entries in the array that is not already in the database. Startvalue - Lastvalue = content added.
 *
 * @param array $pseudo_list An array consisting of the original data and pseudonymization ID.
 * @param integer $insert_start_value the value that corresponds to the last entry in the database.
 * @param integer $last_pseudo_index the value that corresponds to the index of the last added value to the array.
 */
function save_pseudonymized_data($pseudo_list, $insert_start_value, $last_pseudo_index) {
	$dbh = pdo_connect();

	// We only want to add the new pseudovalues and keep the old ones without changing the database.
	$pseudo_list = array_slice($pseudo_list, $insert_start_value, $last_pseudo_index, TRUE);
		foreach ($pseudo_list as $key => $value) {
		try{
			// Compensationg for the pseudo_index which starts at 0 when the
			$pseudo_val = $key+1;
			$original_data = $value[0];
			$fieldtype = $value[1];
			$sql = "INSERT INTO tcat_pseudonymized_data (pseudo_val, original_data, fieldtype) VALUES (?, ?, ?);";
			$stmt = $dbh->prepare($sql);
			$stmt->execute([$pseudo_val, $original_data, $fieldtype]);
		}
		catch (Exception $e){
			Die($e->getMessage());
		}
	}

	$dbh = NULL;
}

/**
 * Querys the database with the given dataset to see whether the actual dataset is marked for pseudonymization or not.
 *
 * @param string $dataset A string consisting of the name of the database that we want to check up whether it is flagged for pseudonymization.
 */
function is_pseudonymized($dataset) {
	$dbh = pdo_connect();
	$sql = "SELECT pseudonymization FROM tcat_query_bins WHERE querybin ='" . $dataset . "';";
	$rec = $dbh->prepare($sql);
	$rec->execute();
	$boolindicator = $rec->fetchcolumn();
	$dbh = NULL;

	return $boolindicator;
}


/**1
 * Pseudonymizes a certain value (or from a tweet perspective, field) in the given array-based copy of the saved pseudonymisation data. It either fetches the existing pseudonymisation value our creates a new one.
 *
 *
 * @param array $pseudo_list An array copying the saved content on the database for modification outside the database.
 * @param array $data An array consisting of the information that we want to pseoudonymize.
 * @param string $datakey A string consisting of the name of the key in the array which corresponding value we want to change.
 * @param int $last_pseudo_index A integer keeping track of the last added value in the $pseudo_list array making sure that we dont overwrite anything.
 */
function pseudonymize_field($pseudo_list, $data, $datakey, $last_pseudo_index) {
	$data_with_datakey = $data[$datakey];
	$mask=FALSE;
	if (($data_with_datakey != NULL)) {
		// @TODO rewrite comment. The usage of the function array_column who returns a index beginning with 0 forces us to add one to the index ($mask) to follow the 1-indexed pseudonymized_table/array we use everywhere else.
		for ($i=1; $i <= $last_pseudo_index; $i++) {
			if ($data_with_datakey == $pseudo_list[$i][0]) {
				$mask = $i;
				break;
			}
		}
		if ($mask !== FALSE) {
			$data[$datakey] = $mask;
		} else {
			$newData = array($data_with_datakey, $datakey);
			$pseudo_list[$last_pseudo_index+1] = $newData;
			$data[$datakey] = ($last_pseudo_index+1);
			$last_pseudo_index = ($last_pseudo_index + 1);
		}
	}
	$argument_array = array($last_pseudo_index, $data, $pseudo_list);
	return $argument_array;
}

function pseudonymize($data, $pseudolist) {
	$key_array = $GLOBALS['keyarray'];
	//$pseudo_list = $GLOBALS['pseudo_list'];
	$start_index = $last_index = $GLOBALS['last_index'];

	foreach ($data as $key => &$value) {
		if (array_key_exists($key, $key_array) !== FALSE && $value != NULL) {
			$exists = array_search($value, $pseudolist);
			if ($exists) {
				$value = $exists;
			} else {
				$last_index = $last_index+1;
				$pseudolist[$last_index] = array($data[$key], $key);
				$value = $last_index;
				}
		}
	}
  if ($pseudolist != NULL) {
		$pseudo_list5 = array_slice($pseudolist, $start_index, $last_index, TRUE);
	}
	else {
		$pseudo_list5 = array();
	}
	//$pseudo_list3 = array_merge($pseudo_list, $pseudo_list2);
	//$GLOBALS['pseudo_list'] = $pseudo_list3;
	//$GLOBALS['last_index'] = $last_index;
	//return $data;

return array($data, $last_index ,$pseudo_list5);
}



/*
    $last_index = $GLOBALS['last_pseudo_index']; //@TODO fix
	  $pseudo_list = $GLOBALS['pseudo_list'];
	  $key_array = $GLOBALS['keyarray'];



		if(isset(($key_array[$key]))) {

			$mask = FALSE;
			for ($i=1; $i <= $last_index; $i++) { // @TODO - Culprit
				/*if ($value == $pseudo_list[$i][0]) {

					//$mask = $i;
					//break;
				}/
			}
/*
			if ($mask !== FALSE) {
				$value = $mask;
			} else {
				$pseudo_list[$last_index+1] = array($value, $key); // @TODO - Culprit
				$value = $last_index + 1;
				$last_index = $last_index + 1;

				$GLOBALS['last_pseudo_index'] = $last_index;
				$GLOBALS['pseudo_list'] = $pseudo_list;

		}
		}*/
/*
		if (($key == 'text')) {

			$regexp = '/([@][\w_-]+)/';
			// Search for all occurrences of the regexp in the data['text'] field. Return the matching strings in the array $matches
			$matches = array();
			preg_match_all($regexp, $value, $matches);
			// For every match given in the $matches array.
			foreach ($matches[0] as $key => $val) {
				// Search if the match(now saved as $val) is already pseudonymized.
				// The usage of the function array_column who returns a index beginning with 0 forces us to add one to the index ($mask) to follow the 1-indexed pseudonymized_table/array we use everywhere else.
				$mask = FALSE;

				for ($i=1; $i <= $last_index; $i++) {
					if ($val == $pseudo_list[$i][0]) {
						$mask = $i;
						break;
					}
				}
				if ($mask !== FALSE) {
					$value = str_replace($val, "@" . ($mask), $value);
				} else {

					// If not already existing in the pseudonymisation table, add it and pseudonymize it.
					$newData = array($val, 'Mention in text');
					$pseudo_list[($last_index + 1)] = $newData;
					$value = str_replace($val, "@" . ($last_index+1), $value);
					$last_index = ($last_index + 1);
				}
			}
		}
		$GLOBALS['last_pseudo_index'] = $last_index;
		$GLOBALS['pseudo_list'] = $pseudo_list;*/

//}




/**
 * A special variant of the function above that works with more frequiency oriented tables. The function works in lion-part as pseudonymize_field but for a specific field.
 *
 * @param array $results an array consisting of a specific array from a specific function where we want to pseudonymize a specific value.
 */
// @TODO - Adjust this function and corresponding code in functions.php.
/*
function pseudonymize_user_name($results) {
	$pseudo_list = fetch_pseudonymized_data();
	end($pseudo_list);
	$insert_start_value = $last_pseudo_index = key($pseudo_list);
	$stored_key;
	foreach ($results as $key => $value) {
		$stored_key=$key;
		$value2 = array();
		foreach ($value as $key => $val) {
			// The usage of the function array_column who returns a index beginning with 0 forces us to add one to the index ($mask) to follow the 1-indexed pseudonymized_table/array we use everywhere else.
			$mask = array_search($key, array_column($pseudo_list, 'original_data'));
			if ($mask !== FALSE) {
				$value2[$mask+1] = $val;
			} else {
				$newData = array(
					'original_data' => $key,
					'fieldtype' => 'to_user'
				);
				$pseudo_list[($last_pseudo_index + 1)] = $newData;
				$value2[($last_pseudo_index + 1)] = $val;
				$last_pseudo_index = ($last_pseudo_index + 1);
			}
		}
		$new_results=array(
			$stored_key => $value2
		);
	}
	save_pseudonymized_data($pseudo_list, $insert_start_value, $last_pseudo_index);
	return $new_results;
}

/**
 * A general function that takes an array consisting of multiple tweet-related keys and corresponds some of the corresponding values by sending them to the pseudonymize_field function above.
 *
 * @param array $data an array consisting of the information that we have about a collected tweet where some of the information is of a specific kind that we want to pseudonymize.
 */
function pseudonymize_old($data, $pseudo_list) {
	//If the array is empty we force index 0.
	if (empty($pseudo_list)) {
		$last_pseudo_index = 0;
	} else {
		end($pseudo_list);
		$last_pseudo_index = key($pseudo_list);
	}

	// If the key to the $data is in the $key_array below, then it will be handled by the foreach.
	foreach ($data as $key => $value) {
		if (in_array($key, $key_array)) {
			$argument_array = pseudonymize_field($pseudo_list, $data, $key, $last_pseudo_index);
			$last_pseudo_index = $argument_array[0];
			$data = $argument_array[1];
			$pseudo_list = $argument_array[2];
		}
	}
	if (($data['from_user_profile_image_url'] != NULL)) {
		$data['from_user_profile_image_url'] = "Omitted, see original table";
	}
	if (($data['from_user_url'] != NULL)) {
		$data['from_user_url'] = "Omitted, see original table";
	}
	// Check if there exists any key for the $data array and that key isn't null.
	if (($data['text'] != NULL)) {
		$regexp = '/([@][\w_-]+)/';
		// Search for all occurrences of the regexp in the data['text'] field. Return the matching strings in the array $matches
		$matches = array();
		preg_match_all($regexp, $data['text'], $matches);
		// For every match given in the $matches array.
		foreach ($matches[0] as $key => $value) {
			// Search if the match(now saved as $value) is already pseudonymized.
			// The usage of the function array_column who returns a index beginning with 0 forces us to add one to the index ($mask) to follow the 1-indexed pseudonymized_table/array we use everywhere else.
			$mask = FALSE;
			for ($i=1; $i <= $last_pseudo_index; $i++) {
				if ($value == $pseudo_list[$i][0]) {
					$mask = $i;
					break;
				}
			}
			if ($mask !== FALSE) {
				$data['text'] = str_replace($value, "@" . ($mask), $data['text']);
			} else {
				// If not already existing in the pseudonymisation table, add it and psseudonymize it.
				$newData = array($value, 'Mention in text');
				$pseudo_list[($last_pseudo_index + 1)] = $newData;
				$data['text'] = str_replace($value, "@" . ($last_pseudo_index+1), $data['text']);
				$last_pseudo_index = ($last_pseudo_index + 1);
			}
		}
	}
	$return_array = array($data, $pseudo_list, $last_pseudo_index);
	return $return_array;
}
