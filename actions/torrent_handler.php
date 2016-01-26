<?php
	require_once __DIR__ . '/../config.php';

	extract($_REQUEST);

	switch ($action) {
		case 'get_browse':
				try {
					$browse = get_browse();
				} catch (Exception $e) {
					echo_json(array('error' => $e->getMessage()));
					exit();
				}
				
				echo_json(array('browse' => $browse));
			break;
		case 'get_category':
				$torrents = search_torrents($category);
				echo_json(array('torrents' => $torrents));
			break;
		case 'get_queue':
				$queue = get_queue();
				echo_json(array('queue' => $queue));
			break;
		case 'download_torrent':
				download_torrent($magnet_link);
			break;
		case 'torrent_action';
				torrent_action($t_action, $gid);
			break;
	}


?>