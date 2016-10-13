<?php

include "subreddits_list.php";


if(array_key_exists("id", $_GET)) {

	// get things to save
	$id = $_GET["id"];
	$relevant = array_key_exists("relevant", $_GET) ? 1 : 0;
	$name = !empty($_GET["name"]) ? $_GET["name"] : "";
	$link_comment = !empty($_GET["link_comment"]) ? $_GET["link_comment"] : "";
	$comment_comment = !empty($_GET["comment_comment"]) ? $_GET["comment_comment"] : "";

	// get keys
	$keys = explode("_", $id);
	//print_and_die($keys, "keys");


	// get json file
	$directory_start = 'data_json';
	$json_files = array_diff(scandir($directory_start), array('..', '.', '.DS_Store'));
	//print_array($json_files, "json_files");
	$json_file = $json_files[$keys[1]];
	//print_array($json_file, "json_file");

	// get json into php array
	$string = file_get_contents("$directory_start/$json_file");
	$date_contents = json_decode($string, true);

	//print_and_die($date_contents, "date_contents");

	// loop through and get to specific match that we want to change
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

	// unset last pointer
	//unset($date_contents);

	//print_and_die($date_contents);
	$j = json_encode($date_contents);
	//print_and_die($j);

	// put contents into file
	$fp = fopen("$directory_start/$json_file", 'w');
	fwrite($fp, $j);
	fclose($fp);
	echo "update file";

}






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
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
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
	$results = getDirContents('data/data_collection');

	foreach ($results as $key1 => $date_contents) {
		$date_title = end(explode("/", $date_contents["date_path"]));

		$date_file = "data_json/$date_title.json";

		// check if file exists, if it does, create it
		if (!file_exists($date_file)) {

			$date_dirs_json = json_encode($date_contents);

			// put contents into file
			$fp = fopen($date_file, 'w');
			fwrite($fp, $date_dirs_json);
			fclose($fp);
			echo "write file";
		}
	}

	die();
}

if(array_key_exists("action", $_GET)) {
	if($_GET["action"] == createJSON) {
		createJsonFromCollectedData(); die();
	}
}

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
			margin-bottom: 10px;
			background: lightblue;
			border-collapse: collapse;
		}
			.match:last-child {
				margin: 0;
			}
			.match td {
				border: 1px solid lightseagreen;
			}
			.match input[type=text] {
				width: 100%;
			}
			.match textarea {
				width: 100%;
			}
		.hide { display: none; }
	</style>

	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>


</head>
<body>






<?php


$directory_start = 'data_json';
$json_files = array_diff(scandir($directory_start), array('..', '.', '.DS_Store'));
//print_array($json_files, "json_files");

// get the timed directories
foreach($json_files as $key1 => $json_file_name) {
	$string = file_get_contents("$directory_start/$json_file_name");
	$date_contents = json_decode($string, true);
	//print_array($date_contents); die();

	$date_title = end(explode("/", $date_contents["date_path"]));
	$date_dirs = $date_contents["date_dirs"];
	?>
	<div class="date">
		<h1><?php echo $date_title; ?></h1>

		<?php
		if(!empty($date_dirs)) {
			?>
			<div class="times hide">
			<?php
			foreach ($date_dirs as $key2 => $time_contents) {
				$time_title = end(explode("/", $time_contents["time_path"]));
				$time_dirs = $time_contents["time_dirs"];
				?>

				<h2 class="time_time"><?php echo $time_title; ?></h2>
				<div class="time_content hide">

					<?php
					if(!empty($time_dirs)) {
						?>
						<table>
						<?php
						foreach ($time_dirs as $key3 => $subreddit_contents) {
							$subreddit_title = end(explode("/", $subreddit_contents["subreddit_path"]));
							$subreddit_dirs = $subreddit_contents["subreddit_dirs"];
							$subreddit_page = $subreddit_dirs["subreddit_page"];
							$subreddit_matches = array_key_exists("matches", $subreddit_dirs) ? $subreddit_dirs["matches"] : null;
							?>
							<tr class="subreddit">
								<td><h3><?php echo $subreddit_title;?></h3></td>

								<td><a href="<?php echo $subreddit_page;?>" target="blank">Page</a></td>

								<?php
								if(!empty($subreddit_matches)) {
									?>
									<td class="matches">
									<?php
									foreach ($subreddit_matches as $key4 => $subreddit_match) {

										$match_title = end(explode("/", $subreddit_match["subreddit_matches_path"]));
										$match_dirs = $subreddit_match["subreddit_matches_dirs"];
										$match_comment = $match_dirs[0];
										$match_link = $match_dirs[1];

										$match_relevant = array_key_exists("subreddit_matches_relevant", $subreddit_match) ? $subreddit_match["subreddit_matches_relevant"] : -1;
										$match_name = array_key_exists("subreddit_matches_name", $subreddit_match) ? $subreddit_match["subreddit_matches_name"] : "";
										$match_link_comment = array_key_exists("subreddit_matches_link_comment", $subreddit_match) ? $subreddit_match["subreddit_matches_link_comment"] : "";
										$match_comments_comment = array_key_exists("subreddit_matches_comments_comment", $subreddit_match) ? $subreddit_match["subreddit_matches_comments_comment"] : "";

										if($match_relevant != 0 || TRUE) {
											?>
											<form action="data_collected.php">
												<input type="hidden" name="id" value="<?php echo "match_".$key1."_".$key2."_".$key3."_".$key4; ?>" />
												<table class="match">
													<tr>
														<td rowspan="3"><input type="text" name="name" value="<?php echo $match_name; ?>" placeholder="Match Name" /></td>

														<td><p><?php echo $match_title;?></p></td>
														<td>Relevant: <input type="checkbox" name="relevant" <?php echo ($match_relevant == 1) ? 'checked="checked"' : ""; ?> value="1" /></td>
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
										}
									}
									?>
									</td>
									<?php
								} else {
									echo "<td></td>";
								}
							?>
							</tr>
							<?php
						}
						?>
						</table>
					<?php
					}
					?>

				</div>
				<?php
			}
			?>
			</div> <!-- end times div -->
			<?php
		}
		?>

	</div>
	<?php
}







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
