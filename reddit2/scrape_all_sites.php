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
		p {
			margin: 0;
			width: 10%;
			display: inline-block;
			float: left;
		}
		form {
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
		}
	 </style>
</head>
<body>

	<div class="subreddit">
		<form id="get_all" action="" data-num="">
			<button id="all">GET ALL</button>
			<button id="stop" disabled="disabled">STOP</button>
		</form>
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
					<button>Get Info</button>
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
	    $('form').on('submit', function (e) {
			e.preventDefault();

			if($(this).attr("id") == "get_all") {
				$("#all").attr("disabled", "disabled");
				$("#stop").removeAttr("disabled");

				var totalSec = new Date().getTime() / 1000;
				var hours = parseInt( totalSec / 3600 ) % 24;
				var minutes = parseInt( totalSec / 60 ) % 60;
				var seconds = parseInt(totalSec % 60, 10);
				var time = hours+"-"+minutes+"-"+seconds;


				for(var i=1; i<21; i++) {
					var url = "scrape.php?num="+i+"&time="+time;
					var form_data = $("#form_"+i).serialize();
					var feedback_id = "#feedback_"+i;
					ajaxQueue.push([url, form_data, feedback_id]);
				}
				startAjaxQueue();
			} else {
				var feedback_id = "#feedback_"+$(this).attr("data-num");
				sendAjax($(this).attr('action'), $(this).serialize(), feedback_id);
			}
		});
		$("#stop").bind( "click", function(event) {
			event.preventDefault();
			ajaxQueue = [];
			$("#stop").attr("disabled", "disabled");
			$("#all").removeAttr("disabled");
		});
	});

	function startAjaxQueue() {
		if(ajaxQueue.length > 0) {
			var a = [ajaxQueue[0][0], ajaxQueue[0][1], ajaxQueue[0][2]];
			ajaxQueue = ajaxQueue.slice(1, ajaxQueue.length);
			sendAjax(a[0], a[1], a[2]);
		}
	}

	function sendAjax(url, form_data, feedback_id) {
		$.ajax({
			type	: 'post',
			cache	: false,
			url		: url,
			data	: form_data,
			beforeSend : function() {
				completeAjax(feedback_id, "...sending...");
			},
			complete : function(data){
				completeAjax(feedback_id, data["responseText"]);
				console.log(data);
				startAjaxQueue();
			}
		});
	}

	function completeAjax(div_id, msg) {
		$(div_id).text(msg);
	}

</script>


</body>
</html>
