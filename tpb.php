<?php
	require_once __DIR__ . '/config.php';

	$category = $_REQUEST['cat'];

	$sxe = get_sxe(TPB . '/browse/' . $category . '/0/7/0/');

	// echo $sxe->asXML();
	//var_dump($sxe);
	//echo file_get_contents(TPB . '/browse/' . $category);

	$trs = $sxe->xpath('//*[@id="searchResult"]/tr');

	for ($i=0; $i<count($trs)-1; $i++) {
		$tr = $trs[$i];
		$name = (string)$tr->td[1]->div->a;
		$detail_link = $tr->td[1]->div->a['href'];
		$magnet_link = $tr->td[1]->a[0]['href'];
		echo $name . '<br>';
		echo $detail_link . '<br>';
		echo $magnet_link;
		echo '<br><br>';
	}
	

?>