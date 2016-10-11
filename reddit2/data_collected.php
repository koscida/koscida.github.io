<?php

include "subreddits_list.php";

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
}
print_array($directory_master, "directory_master");









?>
