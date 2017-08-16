<?php


include "subreddits_list.php";

/* ******************************************** */
/*				HELPER FUNCTIONS 				*/
/* ******************************************** */
function getJSONFiles($num = null) {

	$directory_start = 'data_json';

	// get json files
	$json_files = array_diff(scandir($directory_start), array('..', '.', '.DS_Store'));
	//print_array($json_files, "json_files");

	if(!empty($num)) {
		return $json_files[$num];
	} else {
		return $json_files;
	}
	//print_array($json_file, "json_file");

	return $json_files;
}







/* ******************************************** */
/*				CREATE JSON FILES  				*/
/* ******************************************** */

function getDirContents($dir, &$results = array(), $depth = 0){
	$files = array_diff(scandir($dir), array('..', '.', '.DS_Store'));

	// depth
	// 0 = finding dates
	// 1 = finding times
	// 2 = finding subreddits
	// 3 = subreddit contents (html or match folder)
	// 4 = match files
	$count = 0;
    foreach($files as $key => $value){
        $path = "$dir/$value";
		if($depth == 0) {
			$results[$count] = array("date_path" => $path);
			getDirContents($path, $results[$count]["date_dirs"], ($depth+1));
		}
		if($depth == 1) {
			$results[$count] = array("time_path" => $path);
			getDirContents($path, $results[$count]["time_dirs"], ($depth+1));
		}
		if($depth == 2) {
			$results[$count] = array("subreddit_path" => $path);
			getDirContents($path, $results[$count]["subreddit_dirs"], ($depth+1));
		}
		if($depth == 3) {
			// if file
			if(!is_dir($path)) {
	            $results["subreddit_page"] = $path;
			// else dir
	        } else {
				$results["matches"][$count] = array("subreddit_matches_path" => $path);
				getDirContents($path, $results["matches"][$count]["subreddit_matches_dirs"], ($depth+1));
	        }
		}
		if($depth == 4) {
			$results[] = $path;
		}
		$count++;
    }

    return $results;
}


function createJsonFromCollectedData() {
	// get json for all data stored
	$results = getDirContents('raw_data');

	// loop through file structure
	foreach ($results as $key1 => $date_contents) {
		$date_title = end(explode("/", $date_contents["date_path"]));

		$date_file = "data_json/$date_title.json";

		// check if file exists, if it doesn't, create it
		if (!file_exists($date_file)) {
			updateJSONFile($date_contents, $date_file);
		}
	}

	//die();
}



function updateJSON() {

	// get things to save
	$id = $_GET["id"];
	$relevant = array_key_exists("relevant", $_GET) ? $_GET["relevant"] : -1;
	$name = !empty($_GET["name"]) ? $_GET["name"] : "";
	$link_comment = !empty($_GET["link_comment"]) ? $_GET["link_comment"] : "";
	$comment_comment = !empty($_GET["comment_comment"]) ? $_GET["comment_comment"] : "";

	// get keys
	$keys = explode("_", $id);
	$GLOBALS["keys"] = $keys;
	//print_and_die($keys, "keys");

	// get json files
	$json_file = getJSONFiles($keys[1]);


	// get json into php array
	$string = file_get_contents("data_json/$json_file");
	$date_contents = json_decode($string, true);

	//print_and_die($date_contents, "date_contents");
	$update_count = 0;

	// loop through and get to specific match that we want to change
	if(!empty($date_contents)) {
		$update_count++;
		foreach ($date_contents["date_dirs"] as $key2 => &$time_contents) {

			// check if want this time
			if($key2 == $keys[2]) {
				// loop through times dir -- ind subreddits
				foreach ($time_contents["time_dirs"] as $key3 => &$subreddit_contents) {

					// check if want this subreddit
					if($key3 == $keys[3]) {
						// loop through subreddit's matches
						foreach ($subreddit_contents["subreddit_dirs"]["matches"] as $key4 => &$subreddit_match) {

							// check if want this match
							if($key4 == $keys[4]) {

								// HERE - update this match
								$subreddit_match["subreddit_matches_relevant"] = $relevant;
								$subreddit_match["subreddit_matches_name"] = $name;
								$subreddit_match["subreddit_matches_link_comment"] = $link_comment;
								$subreddit_match["subreddit_matches_comments_comment"] = $comment_comment;

								// stop looping
								break;
							}
						}
					}

				}
			}
		}
	}

	// unset last pointer
	//unset($date_contents);

	// update file
	if($update_count > 0)
		updateJSONFile($date_contents, "data_json/$json_file");
}



function updateJSONFile($contents, $path) {

	//print_and_die($date_contents);
	$j = json_encode($contents);
	//print_and_die($j);

	// put contents into file
	$fp = fopen($path, 'w');
	fwrite($fp, $j);
	fclose($fp);
	echo "update file: $path <br/>";
}







/* ************************************************************ */
/*				CREATE A CSV FROM FINISHED JSON  				*/
/* ************************************************************ */

function createCSVFromFinishedJSON() {
	$json_files = getJSONFiles();

	// loop through all date files
	foreach ($json_files as $key1 => $json_file_name) {
		$string = file_get_contents("data_json/$json_file_name");
		$date_contents = json_decode($string, true);
		//print_array($date_contents); die();

		if(true || (array_key_exists("relevant_complete", $date_contents) && $date_contents["relevant_complete"] == 1) ) {

			$date_name = end(explode("/", $date_contents["date_path"]));
			$all_matches = array();
			$column_titles = array(
				//"relevant", "post name", "link notes", "comments notes",
				"comments link", "post link",
				"match path", "subreddit path", "time path", "date path",
				"subreddit title", "time title", "date title",
			);
			$all_matches[] = $column_titles;

			foreach ($date_contents["date_dirs"] as $key2 => $time_folder_contents) {

				// loop through times dir --  subreddits
				foreach ($time_folder_contents["time_dirs"] as $key3 => $subreddit_folder_contents) {

					if(!empty($subreddit_folder_contents["subreddit_dirs"]) && array_key_exists("matches", $subreddit_folder_contents["subreddit_dirs"])) {

						// loop through subreddit's matches
						foreach ($subreddit_folder_contents["subreddit_dirs"]["matches"] as $key4 => $subreddit_match) {


							// HERE!!
							//$match["relevant"] = $subreddit_match["subreddit_matches_relevant"];
							//$match["post_name"] = $subreddit_match["subreddit_matches_name"];
    						//$match["link_notes"] = $subreddit_match["subreddit_matches_link_comment"];
    						//$match["comments_notes"] = $subreddit_match["subreddit_matches_comments_comment"];

							$match["comments_link"] = $subreddit_match["subreddit_matches_dirs"][0];
							$match["post_link"] = (count($subreddit_match["subreddit_matches_dirs"]) >1) ? $subreddit_match["subreddit_matches_dirs"][1] : "";

							$match["subreddit_match_path"] = $subreddit_match["subreddit_matches_path"];
							$match["subreddit_path"] = $subreddit_folder_contents["subreddit_path"];
							$match["time_path"] = $time_folder_contents["time_path"];
							$match["date_path"] = $date_contents["date_path"];

							$match["subreddit_title"] = end(explode("/", $match["subreddit_path"]));
							$match["time_title"] = end(explode("/", $match["time_path"]));
							$match["date_title"] = $date_name;




							$all_matches[] = $match;
							//print_and_die($all_matches);
						}
					}
				}
			}

			// all matches have been created
			// save to csv
			$fp = fopen("data_csv/$date_name.csv", 'w');

			foreach ($all_matches as $fields) {
			    fputcsv($fp, $fields);
			}

			fclose($fp);
		}
	}

}




//createJsonFromCollectedData();

//createCSVFromFinishedJSON();


$json_files = getJSONFiles();
print_array($json_files);

foreach ($json_files as $key1 => $json_file_name) {
	$string = file_get_contents("data_json/$json_file_name");
	$date_contents = json_decode($string, true);
	//print_array($date_contents); die();

	if($key1 == 3) {



	foreach ($date_contents["date_dirs"] as $key2 => $time_folder_contents) {

		// loop through times dir --  subreddits
		foreach ($time_folder_contents["time_dirs"] as $key3 => $subreddit_folder_contents) {

			if(!empty($subreddit_folder_contents["subreddit_dirs"]) && array_key_exists("matches", $subreddit_folder_contents["subreddit_dirs"])) {

				// loop through subreddit's matches
				foreach ($subreddit_folder_contents["subreddit_dirs"]["matches"] as $key4 => $subreddit_match) {

					if(count($subreddit_match['subreddit_matches_dirs'] > 1)) {

						$m = $subreddit_match['subreddit_matches_dirs'];

						// HERE!!

						echo "
							<a href='$m[0]' target='_blank'>comment - $m[0]</a>
							<br/>
							<a href='$m[1]' target='_blank'>link - $m[1]</a>
							<br/><br/><br/>
						";
					}
				}
			}
		}
	}

	die();
	}




}




 ?>
