<?php

date_default_timezone_set("America/Denver");
ini_set('max_execution_time', 300);


include "simple_html_dom.php";
include "subreddits_list.php";


function replace_shortened_urls($contents) {
	// replace shortened urls
	$needle = array(
		'href="//',
		'src="//',
		'href="/r/',
	);
	$replace = array(
		'href="http://',
		'src="http://',
		'href="http://reddit.com/r/',
	);
	$contents_edited = str_replace($needle, $replace, $contents);
	return $contents_edited;
}

function echo_and_die($error) {
	$GLOBALS['result']["error"] = $error;
	echo json_encode($GLOBALS['result']);
	die();
}

function echo_and_dont_die($error) {
	$GLOBALS['result']["error"] = $error;
	echo json_encode($GLOBALS['result']);
}




// get today's date
$date = date("Y-m-d");
// get today's time
$time = array_key_exists("time", $_GET) ? $_GET["time"] : date("G-i-s");
$datapath = "data/$date/$time";

// check if date folder exists, if not, create it
if (!file_exists("data/$date")) {
    mkdir("data/$date", 0777, true);
}

// create time folder
if (!file_exists($datapath)) {
	mkdir($datapath, 0777, true);
}






$site_num = array_key_exists("num", $_GET) ? $_GET['num'] : 1;
$site_counter = 0;

$massive = array_key_exists("massive", $_GET) ? true : false;
$subreddit_list = $massive ? $subreddits_massive : $subreddits_v1;

$v2 = array_key_exists("v2", $_GET) ? true : false;
$subreddit_list = $v2 ? $subreddits_v2 : $subreddit_list;

//print_and_die($subreddit_list, "subreddit_list");

// the array that will get turned into json and returned
$result = array();
$result["status_subreddit"] = "incomplete";
$result["subredditName"] = "";
$result["subredditKind"] = "";
$result["datapath"] = "";
$result["subredditFile"] = "";
$result["matchFound"] = false;
$result["matches"] = "";
//echo_and_die("none");

foreach ($subreddit_list as $subredditKind) {
	$nameKind = $subredditKind[0];
	$subredditKind_list = $subredditKind[1];
	$subredditFilters = $subredditKind[2];
	//print_array($subredditFilters, "subredditFilters");

	foreach ($subredditKind_list as $subreddit) {
		$site_counter++;

		//echo "site_counter: $site_counter -- site_num: $site_num<br/>";
		if($site_counter == $site_num) {
			$subredditURL = $subreddit[0];
			$subredditName = $subreddit[1];
			$datapath_subreddit = "$datapath/$nameKind-$subredditName";
			$filenamepath = "$datapath_subreddit/$subredditName.html";

			$result["subredditName"] = $subredditName;
			$result["subredditKind"] = $nameKind;
			$result["datapath"] = $datapath_subreddit;
			$result["subredditFile"] = $filenamepath;

			// create subreddit dir
			if (!file_exists($datapath_subreddit)) {
				mkdir($datapath_subreddit, 0777, true);
			}


			// get contents
			$contents = @file_get_contents($subredditURL);
			if(!$contents) echo_and_die("postGetContents_Error");

			// replace shortened urls
			$contents_edited = replace_shortened_urls($contents);

			// put contents into file
			$post_put = @file_put_contents($filenamepath, $contents_edited);
			if(!$post_put) echo_and_die("postPutContents_Error");

			//echo "$nameKind-$subredditName success<br/>";
			$result["status_subreddit"] = "complete";


			$post_needle = '/<div class=" thing.{1,3500}<div class="clearleft"><\/div><\/div><div class="clearleft"><\/div>/i';
			preg_match_all($post_needle, $contents_edited, $posts_all);
			$posts = $posts_all[0];
			foreach($posts as $key => $post_string) {

				// get post name
				preg_match_all('/<a class="title[^>]*>([^<])+<\/a>/i', $post_string, $post_anchors_names);
				//print_array($post_anchors_names, "post_anchors_names");
				$post_anchor = $post_anchors_names[0][0];
				preg_match_all( '/>[^<]*<\/a>/', $post_anchor, $post_names );
				//print_array($post_names, "post_names");
				$post_name = substr($post_names[0][0], 1, -6);
				//echo "post_name: $post_name <br/>";

				// create normalized name
				$post_name_normalized = substr(preg_replace(array("/\s/", '/[^A-Za-z0-9\-\_]/'), array("_", ''), $post_name), 0, 30);
				//echo "post_name_normalized: $post_name_normalized <br/>";

				// get dir path
				$datapath_post = "$datapath_subreddit/$post_name_normalized";
				//echo "datapath_post: $datapath_post <br/>";

				// get post url
				preg_match_all('/href="([^"]*)"/i', $post_anchor, $post_anchor_urls);
				$post_url = substr($post_anchor_urls[0][0], 6, -1);
				//echo "post_url: $post_url <br/>";

				// get comments url
				preg_match_all( '/<a[^>]*>.{0,9}comment[^<]*<\/a>/i', $post_string, $post_comments );
				preg_match_all( '/href="[^"]*"/i', $post_comments[0][0], $post_comment_urls );
				$post_comment_url = substr($post_comment_urls[0][0], 6, -1);
				//echo "post_comment_url: $post_comment_url <br/>";

				// CHECK FOR KEYWORD matches
				$found_match = 0;
				$keywords_found = "";
				foreach($subredditFilters as $keyword) {
					//$keyword = "and";
					if( preg_match_all('/'.$keyword.'/i', $post_name) > 0) {
						$found_match++;
						//echo "match: $keyword<br/>";
						$keywords_found .= "$keyword &nbsp;";
					}
				}


				if($found_match > 0) {
					//echo "-------------------- KEYWORDS FOUND ----------------------<br/>";
					$result["matchFound"] = true;

					// create folder for this keyword match
					mkdir($datapath_post, 0777, true);

					// get post's contents
					$post_link_contents = @file_get_contents($post_url);
					if(!$post_link_contents) echo_and_die("matchPostGetContents_Error");
					$post_link_contents = replace_shortened_urls($post_link_contents);
					$post_link_filename = "$datapath_post/$post_name_normalized"."___LINK.html";
					$post_put_contents = @file_put_contents($post_link_filename, $post_link_contents);
					if(!$post_put_contents) echo_and_die("matchPostPutContents_Error");

					// get post's comments
					$post_contents_comments = @file_get_contents($post_comment_url);
					if(!$post_contents_comments) echo_and_die("matchCommentGetContents_Error");
					$post_contents_comments = replace_shortened_urls($post_contents_comments);
					$post_comments_filename = "$datapath_post/$post_name_normalized"."___COMMENTS.html";
					$post_put_comments = @file_put_contents($post_comments_filename, $post_contents_comments);
					if(!$post_put_comments) echo_and_die("matchPostPutComments_Error");

					//echo "$post_name_normalized ----- link and comments done<br/>";
					//echo "$post_name ----- link and comments done<br/>";
					//echo "<br/><br/>";
					$result["matches"][] = array(
						"keywords" => $keywords_found,
						"postName" => $post_name,
						"datapathPostLink" => $post_link_filename,
						"datapathPostComments" => $post_comments_filename
					);

					//echo_and_die("end if(found_match > 0)<br/>");
				} // END -- if($found_match > 0)

				//echo_and_die("foreach(posts as key => post_string)<br/>");
				//echo_and_dont_die("foreach(posts as key => post_string)<br/>");
			} // END -- foreach($posts as $key => $post_string)

			//echo_and_die("end if(site_counter == site_num)<br/>");
		} // END -- if($site_counter == $site_num)

	} // END -- foreach ($subreddits as $subreddit)
} // END -- foreach ($subreddits as $subredditKind)

echo json_encode($result);
