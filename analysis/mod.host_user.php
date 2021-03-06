<?php
require_once __DIR__ . '/common/config.php';
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/Gexf.class.php';
require_once __DIR__ . '/common/CSV.class.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>TCAT :: Host user co-occurence</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" href="css/main.css" type="text/css" />

        <script type="text/javascript" language="javascript">
	
	
	
        </script>

    </head>

    <body>

        <h1>TCAT :: Host user co-occurence</h1>

        <?php
        validate_all_variables();
        dataset_must_exist();
        $dbh = pdo_connect();
        pdo_unbuffered($dbh);
        $collation = current_collation();
        $filename = get_filename_for_export("hostUser");
        $csv = new CSV($filename, $outputformat);

        $sql = "SELECT COUNT(LOWER(t.from_user_name COLLATE $collation)) AS frequency, LOWER(t.from_user_name COLLATE $collation) AS username, u.domain AS domain FROM ";
        $sql .= $esc['mysql']['dataset'] . "_urls u, " . $esc['mysql']['dataset'] . "_tweets t ";
        $where = "t.id = u.tweet_id AND u.url_followed !='' AND ";
        $sql .= sqlSubset($where);
        $sql .= " GROUP BY u.domain, LOWER(t.from_user_name) ORDER BY frequency DESC";
        $csv->writeheader(array("frequency", "user", "domain"));
        $rec = $dbh->prepare($sql);
        $rec->execute();
        // Create a boolean variable that gives whether a dataset is marked for pseudonymization or not.
        $pseudonymized_bool = is_pseudonymized($esc['mysql']['dataset']);
        
        while ($res = $rec->fetch(PDO::FETCH_ASSOC)) {
            // Use that boolean value to determine whether we should send the fetched dataparts to the function pseudonymized.
            if ($pseudonymized_bool == 1) {
                $res=pseudonymize($res);
            }
            $csv->newrow();
            $csv->addfield($res['frequency']);
            $csv->addfield($res['username']);
            $csv->addfield($res['domain']);
            $csv->writerow();
            $urlUsernames[$res['domain']][$res['username']] = $res['frequency'];
            //$urlDomain[$res['url']] = $res['domain'];
            //$urlStatusCode[$res['url']] = $res['status_code'];
        }
        $csv->close();

        echo '<fieldset class="if_parameters">';

        echo '<legend>Your spreadsheet (CSV) file</legend>';

        echo '<p><a href="' . str_replace("#", urlencode("#"), str_replace("\"", "%22", $filename)) . '">' . $filename . '</a></p>';

        echo '</fieldset>';

	$userUniqueUrls = array(); $userTotalUrls = array();
	$urlUniqueUsers = array(); $urlTotalUsers = array();

        foreach ($urlUsernames as $url => $usernames) {
	    if (!isset($urlUniqueUsers[$url])) $urlUniqueUsers[$url] = 0;
	    if (!isset($urlTotalUsers[$url])) $urlTotalUsers[$url] = 0;
            foreach ($usernames as $username => $frequency) {
		if (!isset($userUniqueUrls[$username])) $userUniqueUrls[$username] = 0;
		if (!isset($userTotalUrls[$username])) $userTotalUrls[$username] = 0;
		$urlUniqueUsers[$url]++;
		$urlTotalUsers[$url] += $frequency;
		$userUniqueUrls[$username]++;
		$userTotalUrls[$username] += $frequency;
	    }
	}

        $gexf = new Gexf();
        $gexf->setTitle("Host-user " . $filename);
        $gexf->setEdgeType(GEXF_EDGE_UNDIRECTED);
        $gexf->setCreator("tools.digitalmethods.net");
        foreach ($urlUsernames as $url => $usernames) {
            foreach ($usernames as $username => $frequency) {
                $node1 = new GexfNode($url);
                $node1->addNodeAttribute("type", 'domain', $type = "string");
                $node1->addNodeAttribute('longlabel', $url, $type = "string");
                $node1->addNodeAttribute('unique_users', $urlUniqueUsers[$url], $type = "integer");
                $node1->addNodeAttribute('total_users', $urlTotalUsers[$url], $type = "integer");
                $gexf->addNode($node1);
                $node2 = new GexfNode($username);
                $node2->addNodeAttribute("type", 'user', $type = "string");
                $node2->addNodeAttribute('longlabel', $username, $type = "string");
                $node2->addNodeAttribute('unique_domains', $userUniqueUrls[$username], $type = "integer");
                $node2->addNodeAttribute('total_domains', $userTotalUrls[$username], $type = "integer");

                $gexf->addNode($node2);
                $edge_id = $gexf->addEdge($node1, $node2, $frequency);
            }
        }

        $gexf->render();

        $filename = get_filename_for_export("hostUser", '', 'gexf');
        file_put_contents($filename, $gexf->gexfFile);

        echo '<fieldset class="if_parameters">';

        echo '<legend>Your network (GEXF) file</legend>';

        echo '<p><a href="' . filename_to_url($filename) . '">' . $filename . '</a></p>';

        echo '</fieldset>';
        ?>

    </body>
</html>
