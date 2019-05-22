<?php
require __DIR__ . '/functions.php';

function fetch_pseudonymized_data(){
    $pseudo_list = array();
    //Fetching the pseudonymize table and buffer it in an array.
    $dbh = pdo_connect();
    // Check if the pseudonymization table is empty.zm
    $sql = "SELECT COUNT(pseudo_val) from tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    $is_empty = $rec -> fetch(PDO::FETCH_NUM);
    
    
    // If not empty, store a copy of the table in $pseudo_list
    if ($is_empty != 0) {

    $sql = "SELECT pseudo_val ,original_data, fieldtype FROM tcat_pseudonymized_data;";
    $rec = $dbh->prepare($sql);
    $rec->execute();
    
    while ($row = $rec -> fetch(PDO::FETCH_ASSOC)) {
        $pseudo_list[$row['pseudo_val']] = array('original_data'=>$row['original_data'], 'fieldtype'=>$row['fieldtype']);
    }
    }
    // Close the database connection before returning.
    $dbh = NULL;
    return $pseudo_list;
}

function save_pseudonymized_data($pseudo_list, $insert_start_value ,$last_pseudo_index){
    $dbh = pdo_connect();
    
    // We only want to add the new pseudovalues and keep the old ones without changing the database. 
    $pseudo_list = array_slice($pseudo_list, $insert_start_value, $last_pseudo_index, TRUE);            
    foreach ($pseudo_list as $key => $value) {

        $pseudo_val = $key;
        $original_data=$value['original_data']; 
        $fieldtype=$value['fieldtype'];

        $sql = "INSERT INTO tcat_pseudonymized_data (pseudo_val, original_data, fieldtype) VALUES (?, ?, ?);";
        $stmt = $dbh -> prepare($sql);
        $stmt->execute([$pseudo_val, $original_data, $fieldtype]);        
    }
  
    $dbh = NULL;
}

// The function queries the database to determine however the chosen dataset is flagged for pseudonymization or not.
function is_pseudonymized($dataset) {	
	$dbh = pdo_connect();
	$sql = "SELECT pseudonymization FROM tcat_query_bins WHERE querybin ='".$dataset."';";
	$rec = $dbh->prepare($sql);
    $rec->execute();
	$boolindicator = $rec -> fetchcolumn();	
	$dbh = NULL;
        
	return $boolindicator;
}

function pseudonymize($data) {  
    $pseudo_list = fetch_pseudonymized_data();
    end($pseudo_list);
    $insert_start_value = $last_pseudo_index = key($pseudo_list);

    if (array_key_exists('tweetid', $data) && ($data['tweetid'] != NULL)) {
        $mask = array_search($data['tweetid'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['tweetid'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['tweetid'],
                'fieldtype' => 'tweetid'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('tweetid')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }   
    if (array_key_exists('id', $data) && ($data['id'] != NULL)) {
        $mask = array_search($data['id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['id'],
                'fieldtype' => 'id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('id_string', $data) && ($data['id_string'] != NULL)) {
        $mask = array_search($data['id_string'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['id_string'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['id_string'],
                'fieldtype' => 'id_string'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('id_string')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('from_user_id', $data) && ($data['from_user_id'] != NULL)) {
        $mask = array_search($data['from_user_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['from_user_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['from_user_id'],
                'fieldtype' => 'from_user_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('from_user_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('from_user_name', $data) && ($data['from_user_name'] != NULL)) {
        $mask = array_search($data['from_user_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['from_user_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['from_user_name'],
                'fieldtype' => 'from_user_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('from_user_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('from_user_realname', $data) && ($data['from_user_realname'] != NULL)) {
        $mask = array_search($data['from_user_realname'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['from_user_realname'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['from_user_realname'],
                'fieldtype' => 'from_user_realname'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('from_user_realname')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('user_from_name', $data) && ($data['user_from_name'] != NULL)) {
        $mask = array_search($data['user_from_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['user_from_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['user_from_name'],
                'fieldtype' => 'user_from_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('user_from_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('user_from_id', $data) && ($data['user_from_id'] != NULL)) {
        $mask = array_search($data['user_from_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['user_from_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['user_from_id'],
                'fieldtype' => 'user_from_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('user_from_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('user_to_id', $data) && ($data['user_to_id'] != NULL)) {
        $mask = array_search($data['user_to_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['user_to_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['user_to_id'],
                'fieldtype' => 'user_to_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('user_to_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('user_to_name', $data) && ($data['user_to_name'] != NULL)) {
        $mask = array_search($data['user_to_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['user_to_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['user_to_name'],
                'fieldtype' => 'user_to_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('user_to_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('to_user_id', $data) && ($data['to_user_id'] != NULL)) {
        $mask = array_search($data['to_user_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['to_user_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['to_user_id'],
                'fieldtype' => 'to_user_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('to_user_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('to_user_name', $data) && ($data['to_user_name'] != NULL)) {
        $mask = array_search($data['to_user_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['to_user_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['to_user_name'],
                'fieldtype' => 'to_user_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('to_user_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('in_reply_to_status_id', $data) && ($data['in_reply_to_status_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['in_reply_to_status_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['in_reply_to_status_id'],
                'fieldtype' => 'in_reply_to_status_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('in_reply_to_status_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('in_reply_to_status_id_str', $data) && ($data['in_reply_to_status_id_str'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id_str'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['in_reply_to_status_id_str'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['in_reply_to_status_id_str'],
                'fieldtype' => 'in_reply_to_status_id_str'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('in_reply_to_status_id_str')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('in_reply_to_user_id', $data) && ($data['in_reply_to_user_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_user_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['in_reply_to_user_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['in_reply_to_user_id'],
                'fieldtype' => 'in_reply_to_user_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('in_reply_to_user_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('in_reply_to_screen_name', $data) && ($data['in_reply_to_screen_name'] != NULL)) {
        $mask = array_search($data['in_reply_to_screen_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['in_reply_to_screen_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['in_reply_to_screen_name'],
                'fieldtype' => 'in_reply_to_screen_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('in_reply_to_screen_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('to_user_name', $data) && ($data['to_user_name'] != NULL)) {
        $mask = array_search($data['to_user_name'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['to_user_name'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['to_user_name'],
                'fieldtype' => 'to_user_name'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('to_user_name')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('in_reply_to_status_id', $data) && ($data['in_reply_to_status_id'] != NULL)) {
        $mask = array_search($data['in_reply_to_status_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['in_reply_to_status_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['in_reply_to_status_id'],
                'fieldtype' => 'in_reply_to_status_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('in_reply_to_status_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('quoted_status_id', $data) && ($data['quoted_status_id'] != NULL)) {
        $mask = array_search($data['quoted_status_id'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['quoted_status_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['quoted_status_id'],
                'fieldtype' => 'quoted_status_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('quoted_status_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('retweeted_status', $data) && ($data['retweeted_status'] != NULL)) {
        $mask = array_search($data['retweeted_status'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['retweeted_status'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['retweeted_status'],
                'fieldtype' => 'retweeted_status'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('retweeted_status')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('retweeted', $data) && ($data['retweeted'] != NULL)) {
        $mask = array_search($data['retweeted'],array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['retweeted'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['retweeted'],
                'fieldtype' => 'retweeted'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('retweeted')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('retweet_id', $data) && ($data['retweet_id'] != NULL)) {
        $mask = array_search($data['retweet_id'], array_column($pseudo_list, 'original_data' ));
        if ($mask) {
            $data['retweet_id'] = $mask;
        } else {
            $newData = array(
                'original_data' => $data['retweet_id'],
                'fieldtype' => 'retweet_id'
            );
            $pseudo_list[($last_pseudo_index+1)] = $newData;
            $data[('retweet_id')]=($last_pseudo_index+1);
            $last_pseudo_index = ($last_pseudo_index + 1);
        }
    }
    if (array_key_exists('from_user_profile_image_url', $data) && ($data['from_user_profile_image_url'] != NULL)) {
        $data['from_user_profile_image_url'] = "Omitted, see original table";
    }
    if (array_key_exists('from_user_url', $data) && ($data['from_user_url'] != NULL)) {
        $data['from_user_url'] = "Omitted, see original table";
    }
    if (array_key_exists('text', $data) && ($data['text'] != NULL)) {
        $regexp = '/(^|[^@\w])@(\w{1,15})\b/';
        preg_match_all($regexp, $data['text'], $matches);
        foreach ($matches[0] as $key => $value) {
            $mask = array_search($value, array_column($pseudo_list, 'original_data' ));
                if ($mask) {
                    $data['text'] = str_replace($value, " ".($last_pseudo_index+1), $data['text']);            
                } else {
                    $newData = array(
                        'original_data' => $value,
                        'fieldtype' => 'Mention in text'
                    );
                    $pseudo_list[($last_pseudo_index+1)] = $newData;
                    $message = str_replace($value, " ".($last_pseudo_index+1), $data['text']);         
                    $last_pseudo_index = ($last_pseudo_index+1);
                }
            }
    }
    save_pseudonymized_data($pseudo_list, $insert_start_value , $last_pseudo_index);
    return $data;
    
}
	
	
	
