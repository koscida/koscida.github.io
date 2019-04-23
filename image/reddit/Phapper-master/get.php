<?php

require_once('/src/phapper.php');

$subreddit = "pics";
$limit = 5;
$after = null;
$before = null;

$p = new Phapper();
$res = $p->getHot($subreddit, $limit, $after, $before);

echo $res;
die();