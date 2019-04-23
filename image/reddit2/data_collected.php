<?php

include "subreddits_list.php";

$keys = array("match", -1, -1, -1, -1);
$directory_start = 'data_json';

if(array_key_exists("action", $_GET)) {
	switch($_GET["action"]) {
		case "createJSON":
			createJsonFromCollectedData(); //die();
			break;
		case "page":
			if(array_key_exists("id", $_GET)) {
				$keys = explode("_", $_GET["id"]);
			}
			break;
		case "date":
			if(array_key_exists("id", $_GET)) {
				$keys = array("match", $_GET["id"], "-1", "-1", "-1");
			}
			break;
		case "update":
			updateJSON();
			break;
		case "updateMatchNames":
			updateMatchNames();
			break;
	}
}


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





/* ************************************************ */
/*				UPDATE NAMES IN JSON  				*/
/* ************************************************ */

function updateMatchNames() {
	$json_files = getJSONFiles();


	// loop through all date files
	foreach ($json_files as $key1 => $json_file_name) {
		$string = file_get_contents("data_json/$json_file_name");
		$date_contents = json_decode($string, true);
		//print_array($date_contents); die();
		$should_update = 0;

		// loop through and get to specific match that we want to change
		//echo "line 82<br/>";
		// check if date has times dirs
		if(
			( array_key_exists("date_name", $date_contents) && $date_contents["date_name"] == 0 ) ||
			( array_key_exists("date_dirs", $date_contents) && !empty($date_contents["date_dirs"]) )
			){

			foreach ($date_contents["date_dirs"] as $key2 => &$time_contents) {

				// loop through times dir -- ind subreddits
				foreach ($time_contents["time_dirs"] as $key3 => &$subreddit_contents) {

					// check if subreddit has matches
					if(!empty($subreddit_contents["subreddit_dirs"]) && array_key_exists("matches", $subreddit_contents["subreddit_dirs"])) {

						// loop through subreddit's matches
						foreach ($subreddit_contents["subreddit_dirs"]["matches"] as $key4 => &$subreddit_match) {

							// check if that match doesn't have a name
							if(!array_key_exists("subreddit_matches_name", $subreddit_match) || empty($subreddit_match["subreddit_matches_name"])) {
								// goal = get the post name

								// 1a. get the comments file path
								$comments_file_and_path = $subreddit_match["subreddit_matches_dirs"][0];

								if(!empty($comments_file_and_path)) {

									// 1b. get the comments file - this will have complete post name
									$match_comment_string = file_get_contents($comments_file_and_path);

									// 2a. search with regex for title
									$regex = '/<a class="title[^>]*>([^<])+<\/a>/i';
									preg_match_all($regex, $match_comment_string, $match_titles);


									if( !empty($match_titles) && !empty($match_titles[0][0]) ) {
										//print_and_die($match_titles);
										$should_update++;

										// 2b. format the match
										$title_anchor = $match_titles[0][0];
										preg_match_all( '/>[^<]*<\/a>/', $title_anchor, $names);
										//print_array($names, "names");
										$name = substr($names[0][0], 1, -4);
										//print_and_die($name, "name");

										// set name
										$subreddit_match["subreddit_matches_name"] = $name;
									}
								}

							}



						}
					}

				}

			} // end -- foreach ($date_contents["date_dirs"] as $key2 => &$time_contents) {
		} // end -- if(array_key_exists("date_dirs", $date_contents)) {

		// update that json file
		if($should_update > 0) {
			$date_contents["date_name"] = 1;
			updateJSONFile($date_contents, "data_json/$json_file_name");
		}
	}
}



/* **************************************************** */
/*				UPDATE MATCH DATA IN JSON  				*/
/* **************************************************** */

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
	$results = getDirContents('data/data_collection');

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


//print_array($keys);









?>



<!DOCTYPE html>
<html>
<head>
	<style>
		* {
			box-sizing: border-box;
		}
		.date {
			background: #ccc;
			margin: 5px;
		}
			h1 {
				font-size: 18px;
				margin: 0;
			}
			.date_table {
				width: 100%;
				border-collapse: collapse;
			}
			.date_table td {
				padding: 0;
			}
		.time {
			display: none;
			border: 1px solid #aaa;
			padding: 5px;
		}
			h2 {
				margin: 0;
				font-size: 16px;
			}
			.time_content table {
				width: 100%;
				border-collapse: collapse;
			}
			.time_content table td {
				border: 2px solid #ccc;
			}
		.subreddit {
			background: lavender;
			padding: 5px;
			margin: 5px;
		}
			h3 {
				margin: 0 0 5px 0;
				text-align: center;
				font-size: 16px;
			}
		.match {

			background: lightblue;
			border-collapse: collapse;
			width: 100%;
		}
			.match:last-child {
				margin: 0;
			}
			.match td {
				border: 1px solid lightseagreen;
			}
				.match td:nth-child(3n+1) { width: 40%; }
				.match td:nth-child(3n+2) { width: 40%; }
				.match td:nth-child(3n+0) { width: 20%; }
			.match input[type=text] {
				width: 100%;
			}
			.match textarea {
				width: 100%;
			}
			.match + button {
				float: right;
				margin-bottom: 10px;

			}
		.hide { display: none; }
	</style>

	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>


</head>
<body>
<a href="data_collected.php?action=createJSON">create JSON</a>
<br/>
<a href="data_collected.php?action=updateMatchNames">updateMatchNames</a>
<br/>
<a href="data_collected.php?action=page&id=<?php echo implode("_", $keys); ?>">normal</a>
<br/>




<?php

$min = false;
$show_subreddit_meta = false;

$show_all_matches = false;
$show_unk_matches = true;
$show_yes_matches = false;

$show_folder_num = 11;

$json_files = getJSONFiles();
//print_array($json_files, "json_files");

// get the timed directories
foreach($json_files as $key1 => $json_file_name) {
	$string = file_get_contents("data_json/$json_file_name");
	$date_contents = json_decode($string, true);
	//print_array($date_contents); die();

	$date_title = end(explode("/", $date_contents["date_path"]));
	$date_dirs = $date_contents["date_dirs"];

		// check if there is a date folder
		// and check if correct date
		if( !empty($date_dirs) && $key1 == $show_folder_num ) {

			// loop through the date_dirs, which are each time
			foreach ($date_dirs as $key2 => $time_contents) {
				$time_title = end(explode("/", $time_contents["time_path"]));
				$time_dirs = $time_contents["time_dirs"];

					// check if there is a time folder
					if(!empty($time_dirs)) {

						// loop throug the time_dirs, which are each subreddit's folder
						foreach ($time_dirs as $key3 => $subreddit_contents) {
							$subreddit_title = end(explode("/", $subreddit_contents["subreddit_path"]));
							$subreddit_dirs = $subreddit_contents["subreddit_dirs"];
							if(!empty($subreddit_dirs)) {
								$subreddit_page = $subreddit_dirs["subreddit_page"];
								$subreddit_matches = array_key_exists("matches", $subreddit_dirs) ? $subreddit_dirs["matches"] : null;

									// check if there are matches in the subreddit's folder
									if(!empty($subreddit_matches)) {

										// loop through all the matches
										foreach ($subreddit_matches as $key4 => $subreddit_match) {

											// get data about matches
											$match_title = end(explode("/", $subreddit_match["subreddit_matches_path"]));
											$match_dirs = $subreddit_match["subreddit_matches_dirs"];
											$match_comment = $match_dirs[0];
											//echo $match_comment;
											$match_link = $match_dirs[1];

											$match_relevant = array_key_exists("subreddit_matches_relevant", $subreddit_match) ? $subreddit_match["subreddit_matches_relevant"] : -1;
											//echo $match_relevant;
											$match_name = array_key_exists("subreddit_matches_name", $subreddit_match) ? $subreddit_match["subreddit_matches_name"] : "";
											//echo $match_name;
											$match_link_comment = array_key_exists("subreddit_matches_link_comment", $subreddit_match) ? $subreddit_match["subreddit_matches_link_comment"] : "";
											$match_comments_comment = array_key_exists("subreddit_matches_comments_comment", $subreddit_match) ? $subreddit_match["subreddit_matches_comments_comment"] : "";

											$show_this_match = true;
											if($show_all_matches) {
												$show_this_match = true;
											} else {
												if($show_unk_matches)
													$show_this_match = ($match_relevant == -1);
												if($show_yes_matches)
													$show_this_match = ($match_relevant == 1);
											}
											?>
											<form action="data_collected.php" class="<?php echo ($show_this_match) ? "" : "hide" ;?>" >
												<input type="hidden" name="id" value="<?php echo "match_".$key1."_".$key2."_".$key3."_".$key4; ?>" />
												<input type="hidden" name="action" value="update" />
												<table class="match">
													<tr>
														<td>
															<h1 class="<?php echo ($min) ? "hide1": ""; ?>"><?php echo $date_title; ?></h1>
															<h2 class="time_time <?php echo ($min) ? "hide1": ""; ?>"><?php echo $time_title; ?></h2>
														</td>
														<td><h3><?php echo $subreddit_title;?></h3></td>
														<td><a href="<?php echo $subreddit_page;?>" target="blank">Page</a></td>
													</tr>

													<tr>
														<td rowspan="3">
															<?php
																echo empty($match_name) ? "<input type='text' name='name' value='$match_name' placeholder='Match Name' />" :  $match_name ;
															 ?>
														</td>

														<td><p><?php echo $match_title;?></p></td>
														<td>Relevant:
															<input id="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4; ?>__-1" type="radio" name="relevant" <?php echo ($match_relevant == -1) ? 'checked="checked"' : ""; ?> value="-1" /><label for="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4;?>__-1">Unk</label>
															<input id="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4; ?>__0" type="radio" name="relevant" <?php echo ($match_relevant == 0) ? 'checked="checked"' : ""; ?> value="0" /><label for="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4;?>__0">NO</label>
															<input id="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4; ?>__1" type="radio" name="relevant" <?php echo ($match_relevant == 1) ? 'checked="checked"' : ""; ?> value="1" /><label for="<?php echo "radio_".$key1."_".$key2."_".$key3."_".$key4;?>__1">YES</label>
														</td>
													</tr>

													<tr>
														<td><a href="<?php echo $match_link;?>" target="blank">Link</a><br/></td>
														<td><textarea name="link_comment" rows="4"><?php echo $match_link_comment; ?></textarea></td>
													</tr>

													<tr>
														<td><a href="<?php echo $match_comment;?>" target="blank">Comments</a></td>
														<td><textarea name="comment_comment" rows="4"><?php echo $match_comments_comment; ?></textarea></td>
													</tr>
												</table>
												<button>Save</button>
											</form>
											<?php

										} // end -- foreach ($subreddit_matches as $key4 => $subreddit_match) {

									// end -- if(!empty($subreddit_matches)) {
									} else {
										echo "<td></td>";
									}
							} // end -- if(!empty($subreddit_dirs)) {

						} // end -- foreach ($time_dirs as $key3 => $subreddit_contents) {

					} // end -- if(!empty($time_dirs)) {

			} // end -- foreach ($date_dirs as $key2 => $time_contents) {

		} // end -- if(!empty($date_dirs)) {

} // end -- foreach($json_files as $key1 => $json_file_name) {







?>


<script>
	$("h1").click(function(){
		$(this).next().slideToggle();
	});
	$("h2").click(function(){
		$(this).next().slideToggle();
	});

</script>





</body>
</html>
