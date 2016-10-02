<?php

date_default_timezone_set("America/Denver");
ini_set('max_execution_time', 300);


include "simple_html_dom.php";
include "subreddits_list.php";


$COLLECT_ALL = true;
$COLLECT_KEYWORD_POSTS = true;



function print_array($arr, $name = null) {
	echo "<pre> $name: ".print_r($arr, true)."</pre>";
}

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
$site_num = ($site_num > 0 && $site_num < 21) ? $site_num : 1;
$site_counter = 0;

// the array that will get turned into json and returned
$result = array();


foreach ($subreddits as $subredditKind) {
	$nameKind = $subredditKind[0];
	$subreddits = $subredditKind[1];
	$subredditFilters = $subredditKind[2];
	//print_array($subredditFilters, "subredditFilters");

	foreach ($subreddits as $subreddit) {
		$site_counter++;
		if($site_counter == $site_num) {
			$subredditURL = $subreddit[0];
			$subredditName = $subreddit[1];
			$datapath_subreddit = "$datapath/$nameKind-$subredditName";
			$filenamepath = "$datapath_subreddit/$subredditName.html";

			$result["subredditName"] = $subredditName;
			$result["subredditKind"] = $nameKind;
			$result["datapath"] = $datapath_subreddit;

			// create subreddit dir
			if (!file_exists($datapath_subreddit)) {
				mkdir($datapath_subreddit, 0777, true);
			}


			// get contents
			$contents = file_get_contents($subredditURL);

			// replace shortened urls
			$contents_edited = replace_shortened_urls($contents);

			// put contents into file
			file_put_contents($filenamepath, $contents_edited);

			//echo "$nameKind-$subredditName success<br/>";
			$result["status_subreddit"] = "complete";


			// search for keywords in each post
			// get each post
			if($COLLECT_KEYWORD_POSTS) {

				$html = str_get_html($contents_edited);
				foreach($html->find('div') as $div) {
				    if( isset($div->attr['class']) && preg_match('/entry unvoted/', $div->attr['class']) ) {
						//echo "-------------------- POST FOUND ----------------------<br/>";
						$post = $div;
						$post_string = $div->outertext;
						//echo $post_string . "\n";

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
						$result["keywords"] = "";
						foreach($subredditFilters as $keyword) {
							//$keyword = "and";
							if( preg_match_all('/'.$keyword.'/i', $post_name) > 0) {
								$found_match++;
								//echo "match: $keyword<br/>";
								$result["keywords"][] = $keyword;
							}
						}

						$result["matches"] = "";
						if($found_match > 0) {
							//echo "-------------------- KEYWORDS FOUND ----------------------<br/>";

							// create folder for this keyword match
							mkdir($datapath_post, 0777, true);

							// get post's contents
							$post_link_contents = file_get_contents($post_url);
							$post_link_contents = replace_shortened_urls($post_link_contents);
							$post_link_filename = "$datapath_post/$post_name_normalized"."___LINK.html";
							file_put_contents($post_link_filename, $post_link_contents);

							// get post's comments
							$post_contents_comments = file_get_contents($post_comment_url);
							$post_contents_comments = replace_shortened_urls($post_contents_comments);
							$post_comments_filename = "$datapath_post/$post_name_normalized"."___COMMENTS.html";
							file_put_contents($post_comments_filename, $post_contents_comments);

							//echo "$post_name_normalized ----- link and comments done<br/>";
							//echo "$post_name ----- link and comments done<br/>";
							//echo "<br/><br/>";
							$result["matches"][] = array("postName" => $post_name, "postNameNormalized" => $post_name_normalized);
						}


						//echo "<br/><br/>";

				    }
				} // END -- foreach($html->find('div') as $div)

			} // END -- if($COLLECT_KEYWORD_POSTS)

		} // END -- if($site_counter == $site_num)

	} // END -- foreach ($subreddits as $subreddit)
} // END -- foreach ($subreddits as $subredditKind)

echo json_encode($result);
