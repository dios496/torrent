<?php
	function get_sxe($url) {
		$html = file_get_contents($url);

		// disable PHP errors
		$old = libxml_use_internal_errors(true);

		$dom = new DOMDocument;
		$dom->loadHTML($html);

		// restore the old behaviour
		libxml_use_internal_errors($old);

		$sxe = simplexml_import_dom($dom);

		if (is_null($sxe)) {
			throw new Exception("Failed to parse HTML", 1);
			
		}
		return $sxe;
	}

	function get_browse () {
		$browse = array();
		$sxe = get_sxe(TPB . '/browse');
	
		$result = $sxe->xpath('//*[@id="categoriesTable"]/tr/td/dl');
	
		foreach ($result AS $dl) {
			for ($i=0; $i<count($dl->dt); $i++) { 
		 		$dt = $dl->dt[$i];
		 		$cat= end(explode('/', $dt->a['href']));

		 		$browse[$cat] = array();
		 		$browse[$cat]['name'] = (string)$dt->a;
		 		$browse[$cat]['sub_cats'] = array();

		 		$dd = $dl->dd[$i];
		 		foreach ($dd->span as $span) {
		 			$sub_cat = end(explode('/', $span->a['href']));
		 			$browse[$cat]['sub_cats'][$sub_cat]['name'] = (string)$span->a;		 		
		 		}
		 	} 
		}

		return $browse;
	}

	function search_torrents ($category = false, $search_text = false, $page = 0) {
		$sxe = get_sxe(TPB . '/browse/' . $category . '/' . $page . '/7/0/');

		$trs = $sxe->xpath('//*[@id="searchResult"]/tr');
		$torrents = array();
		for ($i=0; $i<count($trs)-1; $i++) {
			$tr = $trs[$i];

			$torrent = array();
			$torrent['name'] = (string)$tr->td[1]->div->a;
			$torrent['id'] = preg_replace("/\/torrent\/(\d+)\/.*/", "$1", $tr->td[1]->div->a['href']);
			$torrent['magnet_link'] = (string)$tr->td[1]->a[0]['href'];
			$torrent['seeds'] = (string)$tr->td[2];
			$torrent['leeches'] = (string)$tr->td[3];
			
			$torrents[] = $torrent;
		}

		return $torrents;
	}

	function get_queue () {
		$aria2 = new Aria2('http://127.0.0.1:6800/jsonrpc');
		//echo '<pre>';
		//$aria2->pause("a13c96791491f191");
		//$aria2->remove("a799887389ff970b");
		//var_dump($aria2->addUri(array('magnet:?xt=urn:btih:4604dc920de7654d19b940556bd8125dce9d9ccc&dn=Avengers+Age+of+Ultron+2015+TRUEFRENCH+WEBRip+MD+XviD-SVR&tr=udp%3A%2F%2Ftracker.openbittorrent.com%3A80&tr=udp%3A%2F%2Fopen.demonii.com%3A1337&tr=udp%3A%2F%2Ftracker.coppersurfer.tk%3A6969&tr=udp%3A%2F%2Fexodus.desync.com%3A6969')));
		//var_dump($aria2->getGlobalStat());
		//var_dump($aria2->tellStatus('6f4738955eb0529d'));
		//var_dump($aria2->tellWaiting(0, 100));
		//var_dump($aria2->tellStopped());
		//var_dump($aria2->getGlobalOption('max-overall-download-limit'));
		//$aria2->saveSession();
		//echo '<br><br>';

		$active = $aria2->tellActive();
		$waiting = $aria2->tellWaiting(0, 100);
		$stopped =  $aria2->tellStopped(0, 100);

		// error_log(var_export($active,true));
		// error_log(var_export($waiting,true));
		// error_log(var_export($stopped,true));
		// echo '<pre>';
		// var_dump($aria2->tellStopped(0, 100));
		// echo '<br><br>';

		$results = array_merge($active['result'], $waiting['result'], $stopped['result']);

		$queue = array();

		foreach ($results as $result) {
			$torrent = array();

			$torrent['name'] = (isset($result['bittorrent'])) ? $result['bittorrent']['info']['name'] : $result['files'][0]['path'];
			$torrent['completed_length'] = format_bytes($result['completedLength'], 2);
			$torrent['download_speed'] = format_bytes($result['downloadSpeed'], 2);
			$torrent['upload_speed'] =format_bytes( $result['uploadSpeed'], 2);
			$torrent['gid'] = $result['gid'];
			$torrent['status'] = ucwords($result['status']);
			$torrent['total_length'] = format_bytes($result['totalLength'], 2);
			$torrent['percent_complete'] = ($result['completedLength'] != 0) ? number_format(($result['completedLength'] / $result['totalLength']) * 100, 2) : 0;

			if (preg_match("/^\[METADATA\]/", $torrent['name']) && $torrent['status'] == 'Complete') {
				$active = $aria2->remove($torrent['gid']);
			}
			// else if ($torrent['status'] == 'Error') {
			// 	$active = $aria2->removeDownloadResult($torrent['gid']);
			// }
			else {
				$queue[] = $torrent; 
			}
		}

		return $queue;
	}
	function download_torrent ($magnet_link) {
		$aria2 = new Aria2('http://127.0.0.1:6800/jsonrpc');
		$aria2->addUri(array($magnet_link));
	}
	function torrent_action ($t_action, $gid) {
		$aria2 = new Aria2('http://127.0.0.1:6800/jsonrpc');
		$aria2->{$t_action}($gid);
		if ($t_action == 'remove') {
			$aria2->removeDownloadResult($gid);
		}
	}

	function format_bytes($bytes, $precision = 2) { 
	    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

	    $bytes = max($bytes, 0); 
	    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	    $pow = min($pow, count($units) - 1); 

	    // Uncomment one of the following alternatives
	    $bytes /= pow(1024, $pow);
	    // $bytes /= (1 << (10 * $pow)); 

	    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 


	function echo_json ($array) {
		echo json_encode($array);
	}
?>