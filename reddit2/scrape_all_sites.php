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
	 </style>
</head>
<body>

	<div class="subreddit">
		<button id="get_all">GET ALL</button>
		<button id="stop" disabled="disabled">STOP</button>
		<input type="text" id="time"/>
		<button id="startAjaxQueue">startAjaxQueue</button>
	</div>

<?php
$site_counter = 0;
foreach ($subreddits as $subredditKind) {
	$nameKind = $subredditKind[0];
	$subreddits = $subredditKind[1];

	foreach ($subreddits as $subreddit) {
		$site_counter++;

		$subredditURL = $subreddit[0];
		$subredditName = $subreddit[1];

		?>
			<div class="subreddit">
				<p><?php echo $subredditName;?></p>
				<form action="scrape.php?num=<?php echo $site_counter;?>" data-num="<?php echo $site_counter;?>" id="form_<?php echo $site_counter;?>">
					<button class="add_to_queue">Add to Queue</button>
				</form>
				<div id="feedback_<?php echo $site_counter;?>" class="feedback"></div>
			</div>
		<?php

	}

}
 ?>


<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
	var ajaxQueue = [];

	$(function () {
	
		// get single subreddit
	    $('form').on('submit', function (e) {
			e.preventDefault();

			console.log("indiv pressed");
			
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
			console.log("get all pressed");
			
			$("#all").attr("disabled", "disabled");
			$("#stop").removeAttr("disabled");
			$(".feedback").text("");

			var time = "";
			if(!$("#time").val()) {
				var totalSec = new Date().getTime() / 1000;
				var hours = parseInt( totalSec / 3600 ) % 24 - 6;
				var minutes = parseInt( totalSec / 60 ) % 60;
				var seconds = parseInt(totalSec % 60, 10);
				time = hours+"-"+minutes+"-"+seconds;
			} else {
				time = $("#time").val();
			}

			for(var i=1; i<21; i++) {
				var url = "scrape.php?num="+i+"&time="+time;
				var form_data = $("#form_"+i).serialize();
				var feedback_id = "#feedback_"+i;
				ajaxQueue.push([url, form_data, feedback_id]);
			}
			
			startAjaxQueue();
		});
		
		// stop ajax queue
		$("#stop").bind( "click", function(event) {
			event.preventDefault();
			ajaxQueue = [];
			$("#stop").attr("disabled", "disabled");
			$("#all").removeAttr("disabled");
		});
		
		// start ajax queue
		$("#startAjaxQueue").bind( "click", function(event) {
			startAjaxQueue();
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
				console.log(data);
				completeAjax(feedback_id, processCompleteResponse(data["responseText"]));
			}
		});
	}
	
	function processCompleteResponse(jsonStr) {
		if(isJSON(jsonStr)) {
			var json = $.parseJSON(jsonStr);
			console.log(json);

			var html = ''
				+	'<div class="path">'
				+		'<a href="' + json.subredditFile + '" target="_blank">' + json.subredditFile + '</a>'
				+	'</div>';
				if(json.matchFound) {
					html += '<div class="matches">';
					for(var i=0; i<json.matches.length; i++) {
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
</script>


</body>
</html>
