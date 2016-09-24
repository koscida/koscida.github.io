<?php


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

date_default_timezone_set("America/Denver");

// get today's date
$date = date("Y-m-d");
// get today's time
$time = date("G-i-s");
$datapath = "data/$date/$time";

// check if date folder exists, if not, create it
if (!file_exists("data/$date")) {
    mkdir("data/$date", 0777, true);
}

// create time folder
mkdir($datapath, 0777, true);


$subreddits = array(
	array(
		"general",
		array(
			array("https://www.reddit.com/",				"frontpage"),
			array("https://www.reddit.com/r/all",			"all"),
			array("https://www.reddit.com/r/AskReddit",		"AskReddit"),
			array("https://www.reddit.com/r/funny",			"funny"),
			array("https://www.reddit.com/r/todayilearned",	"todayilearned"),
			array("https://www.reddit.com/r/pics", 			"pics"),
			array("https://www.reddit.com/r/bestof",		"bestof"),
		),
		array("cat"),
	),
	array(
		"feminineCentric",
		array(
			array("https://www.reddit.com/r/MakeupAddiction",		"MakeupAddiction"),
			array("https://www.reddit.com/r/skincareaddiction",		"skincareaddiction"),
			array("https://www.reddit.com/r/femalefashionadvice",	"femalefashionadvice"),
		),
		array("tech", "comput"),
	),
	array(
		"technology",
		array(
			array("https://www.reddit.com/r/technology",			"technology"),
			array("https://www.reddit.com/r/programming",			"programming"),
			array("https://www.reddit.com/r/talesfromtechsupport",	"talesfromtechsupport"),
			array("https://www.reddit.com/r/learnprogramming",		"learnprogramming"),
		),
		array("female", "woman", "women", "girl", "she", "her"),
	),
	array(
		"feminist",
		array(
			array("https://www.reddit.com/r/shitredditsays",	"shitredditsays"),
			array("https://www.reddit.com/r/feminism",			"feminism"),
			array("https://www.reddit.com/r/TwoXChromosomes",	"TwoXChromosomes"),
		),
		array("tech", "comput"),
	),
	array(
		"antiFeminist",
		array(
			array("https://www.reddit.com/r/TumblrInAction",	"TumblrInAction"),
			array("https://www.reddit.com/r/MensRights",		"MensRights"),
			array("https://www.reddit.com/r/TheRedPill",		"TheRedPill"),
		),
		array("tech", "comput"),
	)
);

foreach ($subreddits as $subredditKind) {
	$nameKind = $subredditKind[0];
	$subreddits = $subredditKind[1];
	$subredditFilters = $subredditKind[2];
	//print_array($subredditFilters, "subredditFilters");

	foreach ($subreddits as $subreddit) {
		$subredditURL = $subreddit[0];
		$subredditName = $subreddit[1];
		$datapath_subreddit = "$datapath/$nameKind-$subredditName";
		$filenamepath = "$datapath_subreddit/$subredditName.html";

		// create subreddit dir
		mkdir($datapath_subreddit, 0777, true);


		// get contents
		$contents = file_get_contents($subredditURL);

		// replace shortened urls
		$contents_edited = replace_shortened_urls($contents);
		//echo $contents_edited; die();

		// put contents into file
		file_put_contents($filenamepath, $contents_edited);


		// search for keywords in each post
		$pattern_keyword = '/<a class="title[^>]*>([^<]*and[^<]*)+<\/a>/i';
		$matches_keywords_all = array();
		preg_match_all($pattern_keyword, $contents_edited, $matches_keywords_all);
		//print_array($matches_keywords_all, "matches_keywords_all");
		$matches_keywords_links = $matches_keywords_all[0];
		//print_array($matches_keywords_links, "matches_keywords_links");
		$matches_keywords_names = $matches_keywords_all[1];
		//print_array($matches_keywords_names, "matches_keywords_names");

		// get url for matches
		$pattern_url = '/href="([^"]*)"/i';
		$matches_urlsnames = array();
		foreach($matches_keywords_links as $key => $match) {
			// get raw url
			preg_match_all($pattern_url, $match, $match_url);
			$matches_urlsnames[$key]["url"] = substr($match_url[0][0], 6, -1);

			// get name
			$matches_urlsnames[$key]["name"] = $matches_keywords_names[$key];
		}
		//print_array($matches_urlsnames, "matches_urlsnames"); //die();

		// follow links, save content of page
		foreach($matches_urlsnames as $key => $match_urlname) {
			// create name and path for new match
			$match_normal_name = substr(preg_replace(array("/\s/", '/[^A-Za-z0-9\-\_]/'), array("_", ''), $match_urlname["name"]), 0, 30);
			$datapath_subreddit_match = "$datapath_subreddit/$match_normal_name";

			// create folder for this keyword match
			mkdir($datapath_subreddit_match, 0777, true);

			// get contents of keyword matches
			$contents = file_get_contents($match_urlname["url"]);

			// replace shortened urls
			$contents = replace_shortened_urls($contents);

			// put keyword contents into file
			$matchnamepath = "$datapath_subreddit_match/$match_normal_name"."___LINK.html";
			file_put_contents($matchnamepath, $contents);

			echo "$match_normal_name done<br/>";
		}






		/*
		<p class="title">
			<a class="title may-blank " href="http://reddit.com/r/funny/comments/5400s1/my_dad_is_claustrophobic_and_needs_to_have_an_mri/" tabindex="1" rel="">My dad is claustrophobic and needs to have an MRI scan. He's practicing.</a>
*/

		echo "$subredditName success<br/>";
		break;
	}
	break;
}
