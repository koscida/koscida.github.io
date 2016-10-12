<!DOCTYPE html>
<html>
<head>
	<style>
		.date {
			background: #ccc;
			padding: 5px;
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
			}
		.subreddit {
			background: lavender;
			padding: 5px;
			margin: 5px;
		}
			h3 {
				margin: 0 0 5px 0;
			}
		.match {

		}
	</style>

	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>


</head>
<body>






<?php
include "subreddits_list.php";

/*
$directory_master = array();

// get all day directories
$directory_start = 'data/data_collection';
$date_directories = array_diff(scandir($directory_start), array('..', '.', '.DS_Store'));
$directory_master = $date_directories;
//print_array($date_directories, "date_directories");
print_array($directory_master, "directory_master");

// get the timed directories
foreach($date_directories as $k => $date_dir_name) {
	$time_directories = array_diff(scandir("$directory_start/$date_dir_name"), array('..', '.', '.DS_Store'));
	$directory_master[$k] = array($date_dir_name, $time_directories);
	//print_array($time_directories, "time_directories");

	// get subreddit directories
	foreach($time_directories as $k2 => $subreddit_dir) {

	}
}
print_array($directory_master, "directory_master");
*/


function echo_and_die($error) {
	echo $error;
	die();
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
		$date_path = $date_contents["date_path"];
		$date_title = end(explode("/", $date_path));
		$date_dirs = $date_contents["date_dirs"];

		$date_file = "data_json/$date_title.json";

		// check if file exists, if it does, create it
		if (!file_exists($date_file)) {

			$date_dirs_json = json_encode($date_dirs);

			// put contents into file
			$fp = fopen($date_file, 'w');
			fwrite($fp, $date_dirs_json);
			fclose($fp);
			echo "write file";
		}
	}

	die();
}

//createJsonFromCollectedData();



/*
/* OLD - Div with PHP array, not the json files
foreach ($results as $key1 => $date_contents) {
	$date_title = end(explode("/", $date_contents["date_path"]));
	$date_dirs = $date_contents["date_dirs"];
	?>
	<div class="date">
		<h1><?php echo $date_title; ?></h1>

		<?php
		if(!empty($date_dirs)) {
			foreach ($date_dirs as $key2 => $time_contents) {
				$time_title = end(explode("/", $time_contents["time_path"]));
				$time_dirs = $time_contents["time_dirs"];
				?>
				<div class="time">
					<h2><?php echo $time_title; ?></h2>

					<?php
					if(!empty($time_dirs)) {
						foreach ($time_dirs as $key3 => $subreddit_contents) {
							$subreddit_title = end(explode("/", $subreddit_contents["subreddit_path"]));
							$subreddit_dirs = $subreddit_contents["subreddit_dirs"];
							$subreddit_page = $subreddit_dirs["subreddit_page"];
							$subreddit_matches = array_key_exists("matches", $subreddit_dirs) ? $subreddit_dirs["matches"] : null;
							?>
							<div class="subreddit">
								<h3><?php echo $subreddit_title;?></h3>

								<a href="<?php echo $subreddit_page;?>" target="blank">Page</a>

								<?php
								if(!empty($subreddit_matches)) {
									foreach ($subreddit_matches as $key4 => $subreddit_match) {
										$match_title = end(explode("/", $subreddit_match["subreddit_matches_path"]));
										$match_dirs = $subreddit_match["subreddit_matches_dirs"];
										$match_comment = $match_dirs[0];
										$match_link = $match_dirs[1];
										?>
										<div class="match">
											<p><?php echo $match_title;?></p>
											<a href="<?php echo $match_link;?>" target="blank">Link</a><br/>
											<a href="<?php echo $match_comment;?>" target="blank">Comments</a>
										</div>
										<?php
									}
								}
								?>

							</div>
							<?php
						}
					}
					?>

				</div>
				<?php
			}
		}
		?>

	</div>
	<?php
}
*/




$directory_start = 'data_json';
$json_files = array_diff(scandir($directory_start), array('..', '.', '.DS_Store'));
print_array($json_files, "json_files");

// get the timed directories
foreach($json_files as $k => $json_file_name) {
}


?>


<script>
	$("h1").click(function(){
		$(".time").hide();
		$(this).parent().children().show();
	});

</script>





</body>
</html>
