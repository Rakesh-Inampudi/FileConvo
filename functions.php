<?php

function isUserLoggedIn()
{
	return false;
}

function displayLogOutButton($isLoggedIn) {
	if ($isLoggedIn == false) {
		return;
	}
	echo '<a href="index.php?logout=true">Logout</a>';
}

function convert() {

	//$options = array(), $inputfile, $outputfile, &$result = NULL

	if ($inputfile !== NULL)  {
		$options = array_merge(array('file' =>  '@' . $inputfile), $options);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_URL, "https://api.cloudconvert.org/convert");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	
	// If you have SSL cert errors, try to disable SSL verifyer.
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	
	$output = curl_exec($ch);
	
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	$curlerr = curl_error($ch);
	curl_close($ch);
	
	if ($curlerr && $result !== NULL) {
		$result = array('error' => $curlerr);
	} elseif (strpos($content_type, "application/json") === 0 && $result !== NULL) {
		$result = @json_decode($output, true);
	} elseif ($http_status == 200 && $outputfile !== NULL) {
		$file = fopen($outputfile, "w+");
		fputs($file, $output);
		fclose($file);
	}
	
	return $http_status == 200;
}

?>