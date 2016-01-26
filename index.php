<?php
	require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Gof-Tor</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="jquery-2.1.4.js"></script>
	<script type="text/javascript" src="torrent.js"></script>	
	<link rel="stylesheet" type="text/css" href="torrent.css"/>
</head>
<body>
	<div id='app'>
		<header>
			<span view='queue'>Queue</span><!-- 
		 --><span view='search'>Seach</span>
		</header>
		<div id='content'>
			<div id='queue_container' class='container'>
				
			</div>
			<div id='search_container' class='container'>
				<div id='search'>
					<input type='text' name='search' placeholder='Search'>
					<button>Go</button>
				</div>
				<div id='browse'>	
				</div>
				<div id='search_results'>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
