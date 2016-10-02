<?php

$subreddit_filters_female = array(
	"female", "she", "\sher\s", "\shers\s",
	"woman", "women", "girl",
	"femini",
	"sister", "wife", "daughter", "\sson\s", "husband", "brother",);
$subreddit_filters_tech = array(
	"tech", "comput",
	"phone", "cell",
	"desktop", "laptop", "tower", "monitor",
	"\scs\s", "comp sci", "program", "code");
$subreddit_filters_both = array_merge($subreddit_filters_female, $subreddit_filters_tech);

$subreddits = array(
	array(	// 7 - 7
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
		$subreddit_filters_both,
	),
	array(	// 3 - 10
		"feminineCentric",
		array(
			array("https://www.reddit.com/r/MakeupAddiction",		"MakeupAddiction"),
			array("https://www.reddit.com/r/skincareaddiction",		"skincareaddiction"),
			array("https://www.reddit.com/r/femalefashionadvice",	"femalefashionadvice"),
		),
		$subreddit_filters_tech,
	),
	array(	// 4 - 14
		"technology",
		array(
			array("https://www.reddit.com/r/technology",			"technology"),
			array("https://www.reddit.com/r/programming",			"programming"),
			array("https://www.reddit.com/r/talesfromtechsupport",	"talesfromtechsupport"),
			array("https://www.reddit.com/r/learnprogramming",		"learnprogramming"),
		),
		$subreddit_filters_female,
	),
	array(	// 3 - 17
		"feminist",
		array(
			array("https://www.reddit.com/r/shitredditsays",	"shitredditsays"),
			array("https://www.reddit.com/r/feminism",			"feminism"),
			array("https://www.reddit.com/r/TwoXChromosomes",	"TwoXChromosomes"),
		),
		$subreddit_filters_tech,
	),
	array(	// 3 - 20
		"antiFeminist",
		array(
			array("https://www.reddit.com/r/TumblrInAction",	"TumblrInAction"),
			array("https://www.reddit.com/r/MensRights",		"MensRights"),
			array("https://www.reddit.com/r/TheRedPill",		"TheRedPill"),
		),
		$subreddit_filters_tech,
	)
);
