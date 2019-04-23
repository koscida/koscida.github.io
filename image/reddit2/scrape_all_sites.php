<!DOCTYPE html>
<html>
<head>
	<?php
		include "subreddits_list.php";
	 ?>

	 <style>
	 	.subreddit {
			border: 1px solid grey;
			margin: 0 10px 20px;
			overflow: hidden;
		}
		.subreddit p {
			margin: 0;
			width: 10%;
			display: inline-block;
			float: left;
		}
		.subreddit form {
			width: 5%;
			display: inline-block;
			float: left;
		}
			#get_all {
				display: block;
				margin-bottom: 20px;
			}
		.feedback {
			width: 85%;
			display: inline-block;
			float: left;
			background: pink;
			padding: 5px;
			box-sizing: border-box;
		}
		.path {
			margin: 0 0 10px;
		}
		.match {
			padding: 1px 5px;
			margin: 0 0 10px;
			border: 1px solid grey;
			box-sizing: border-box;
		}
		.hide {
			display: none;
		}
	 </style>
</head>
<body>

	<div class="subreddit">
		<button id="get_all">GET ALL</button>
		<button id="stop" disabled="disabled">STOP</button>
		<input type="text" id="time"/>
		<button id="startAjaxQueue">startAjaxQueue</button>
		<button id="createCSV">createCSV</button>
		<br/>
		<br/>
		<button id="get_all_v2">GET V2</button>
		<br/>
		<br/>
		<button id="get_all_massive">GET ALL MASSIVE</button>
	</div>

<?php
$site_counter = 0;
$sub_lists = array(
	array($subreddits_v1, "Version 1", "form_", "feedback_"),
	array($subreddits_v2, "Version 2", "form_v2_", "feedback_v2_"),
	array($subreddits_massive, "Massive List", "form_massive_", "feedback_massive_"),
);
foreach ($sub_lists as $sub_list) {
	$site_counter = 0;

	$subs = $sub_list[0];
	$list_name = $sub_list[1];
	$list_form_id = $sub_list[2];
	$list_feedback_id = $sub_list[3];

	echo "<h1>$list_name</h1>";
	foreach ($subs as $subredditKind) {
		$nameKind = $subredditKind[0];
		$subreddits = $subredditKind[1];

		foreach ($subreddits as $subreddit) {
			$site_counter++;

			$subredditURL = $subreddit[0];
			$subredditName = $subreddit[1];

			?>
				<div class="subreddit">
					<p><?php echo $subredditName;?></p>
					<form data-num="<?php echo $site_counter;?>" id="<?php echo $list_form_id.$site_counter;?>">
						<button class="add_to_queue">Add to Queue</button>
					</form>
					<div id="<?php echo $list_feedback_id.$site_counter;?>" class="feedback"></div>
				</div>
			<?php

		}

	}

}
?>


<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
	var ajaxQueue = [];
	var time = "";
	var responseDataForCSV = [["subredditKind", "subredditName", "subredditFile", "keywords", "postName", "postLink", "postComments"]];
	var getAllPressed = false;
	var downloadCSV = false;


	$(function () {

		// get single subreddit
	    $('form').on('submit', function (e) {
			e.preventDefault();
			//console.log("indiv pressed");

			$(feedback_id).text("");

			var id = $(this).attr("data-num");
			var feedback_id = "#feedback_"+id;

			var url = "";
			if(!$("#time").val()) {
				url = "scrape.php?num="+id;
			} else {
				var time = $("#time").val();
				url = "scrape.php?num="+id+"&time="+time;
			}

			var form_data = $(this).serialize();

			ajaxQueue.push([url, form_data, feedback_id]);
		});

		// get all
		$("#get_all").bind( "click", function(event) {
			console.log("get all");
			doGetAll(20, "#form_", "#feedback_", "");
		});

		$("#get_all_v2").bind( "click", function(event) {
			console.log("get all v2");
			doGetAll(83, "#form_v2_", "#feedback_v2_", "&v2=true");
		});

		$("#get_all_massive").bind( "click", function(event) {
			console.log("get all massive");
			doGetAll(304, "#form_massive_", "#feedback_massive_", "&massive=true");
		});

		function doGetAll(numSubreddits, formIdPrefix, fedbackIdPrefix, urlData) {
			responseDataForCSV = [["subredditKind", "subredditName", "subredditFile", "keywords", "postName", "postLink", "postComments"]];
			getAllPressed = true;
			downloadCSV = true;

			$("#all").attr("disabled", "disabled");
			$("#stop").removeAttr("disabled");
			$(".feedback").text("");

			if(!$("#time").val()) {
				var totalSec = new Date().getTime() / 1000;
				var hours = parseInt( totalSec / 3600 ) % 24 - 6;
				var minutes = parseInt( totalSec / 60 ) % 60;
				var seconds = parseInt(totalSec % 60, 10);
				time = hours+"-"+minutes+"-"+seconds;
			} else {
				time = $("#time").val();
			}

			for(var i=1; i<(numSubreddits+1); i++) {
				var url = "scrape.php?num="+i+"&time="+time+urlData; console.log(url);
				var form_data = $(formIdPrefix+i).serialize();
				var feedback_id = fedbackIdPrefix+i;
				ajaxQueue.push([url, form_data, feedback_id]);
			}

			startAjaxQueue();
		}

		// stop ajax queue
		$("#stop").bind( "click", function(event) {
			event.preventDefault();
			ajaxQueue = [];
			$("#stop").attr("disabled", "disabled");
			$("#all").removeAttr("disabled");
		});

		// start ajax queue
		$("#startAjaxQueue").bind( "click", function(event) {
			responseDataForCSV = [["subredditKind", "subredditName", "subredditFile", "keywords", "postName", "postLink", "postComments"]];
			startAjaxQueue();
		});

		// start ajax queue
		$("#createCSV").bind( "click", function(event) {
			createCSV();
		});

	});


	function startAjaxQueue() {
		if(ajaxQueue.length > 0) {
			var a = [ajaxQueue[0][0], ajaxQueue[0][1], ajaxQueue[0][2]];
			ajaxQueue = ajaxQueue.slice(1, ajaxQueue.length);
			sendAjax(a[0], a[1], a[2]);
		} else {
			$("#all").attr("disabled", "disabled");
			$("#stop").removeAttr("disabled");
			if(getAllPressed && downloadCSV) {
				createCSV();
				getAllPressed = false;
				downloadCSV = false;
			}
		}
	}

	function sendAjax(url, form_data, feedback_id) {
		$.ajax({
			type	: 'post',
			cache	: false,
			url		: url,
			data	: form_data,
			dataType: 'json',
			beforeSend : function() {
				completeAjax(feedback_id, "...sending...");
			},
			complete : function(data){
				startAjaxQueue();
				//console.log(data);
				completeAjax(feedback_id, processCompleteResponse(data["responseText"]));
			}
		});
	}

	function processCompleteResponse(jsonStr) {
		if(isJSON(jsonStr)) {
			var json = $.parseJSON(jsonStr);
			//console.log(json);

			var html = ''
				+	'<div class="path">'
				+		'<a href="' + json.subredditFile + '" target="_blank">' + json.subredditFile + '</a>'
				+	'</div>';
				if(json.matchFound) {
					html += '<div class="matches">';
					for(var i=0; i<json.matches.length; i++) {

						// create the json data to save to csv
						responseDataForCSV.push(
							[json.subredditKind, json.subredditName, json.subredditFile, json.matches[i].keywords, json.matches[i].postName, json.matches[i].datapathPostLink, json.matches[i].datapathPostComments]
						);

						// create the html element to show on the webpage
						html += ''
						+	'<div class="match">'
						+		'<div>Keywords: ' + json.matches[i].keywords + '</div>'
						+		'<div>Name: ' + json.matches[i].postName + '</div>'
						+		'<div>Post link: <a href="' + json.matches[i].datapathPostLink + '" target="_blank">' + json.matches[i].datapathPostLink + '</a></div>'
						+		'<div>Post comments: <a href="' + json.matches[i].datapathPostComments + '" target="_blank">' + json.matches[i].datapathPostComments + '</a></div>'
						+	'</div>'
						+ '';

					}
					html += '</div>';
				}
			return html;
		} else {
			downloadCSV = false;
			return "malformed json";
		}
	}

	function isJSON(str) {
		try {
			JSON.parse(str);
		} catch (e) {
    		return false;
    	}
    	return true;
	}

	function completeAjax(div_id, msg) {
		$(div_id).html(msg);
	}

	function createCSV() {
		//console.log(responseDataForCSV);
		// create csv
		var csvContent = "data:text/csv;charset=utf-8,";
		responseDataForCSV.forEach(function(infoArray, index){
			var infoArrayEsc = infoArray;
			for(var i=0; i<infoArrayEsc.length;i++)
				infoArrayEsc[i] = infoArrayEsc[i].replace(/,/g, "-");
			//var infoArrayEsc = infoArray.replace(",", "-");
			//console.log(infoArrayEsc);

			dataString = infoArrayEsc.join(",");
			csvContent += index < responseDataForCSV.length ? dataString+ "\n" : dataString;
		});

		// download csv
		var encodedUri = encodeURI(csvContent);
		var link = document.createElement("a");
		link.setAttribute("href", encodedUri);
		link.setAttribute("download", "reddit-data_"+time+".csv");
		document.body.appendChild(link); // Required for FF

		link.click(); // This will download the data file named "my_data.csv".
	}
</script>


</body>
</html>
