<?php

// set variables
########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
//$google_client_id 		= '689191895905-c3vkvbhl0pha67mevqdb2d1tpf5va4q8.apps.googleusercontent.com';
//$google_client_secret 	= 'ZvNJv2dndY93GK039wut06Xk';
//$google_redirect_url 	= 'https://localhost/CloudConvert/index.php'; //path to your script
//$google_developer_key 	= 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
if ($_SERVER['SERVER_NAME'] == "fileconvo.azurewebsites.net") {
	$google_client_id 		= '689191895905-ko9ep5g9pue74t6u8a4bolhi85njo438.apps.googleusercontent.com';
	$google_client_secret 	= 'Aj8-3xhPBsrDbx8lsBMvsG98';
	$google_redirect_url 	= 'http://fileconvo.azurewebsites.net/'; //path to your script
	$google_developer_key 	= 'AIzaSyCnGzomO_DjAHBrJgTf_UKqjSJwiev8AT8';
	$path_to_files = "D:/home/site/wwwroot/";
} elseif ($_SERVER['SERVER_NAME'] == "young-shore-9280-965.herokuapp.com") {
	$google_client_id 		= '689191895905-fcqe546dgjhc7c85531955na285409k2.apps.googleusercontent.com';
	$google_client_secret 	= 'RGK4iL2lgBhyX2ElzZOUDOa-';
	$google_redirect_url 	= 'https://young-shore-9280.herokuapp.com/'; //path to your script
	$google_developer_key 	= 'AIzaSyCnGzomO_DjAHBrJgTf_UKqjSJwiev8AT8';
	$path_to_files = "D:/home/site/wwwroot";
} else {
	$google_client_id 		= '689191895905-c3vkvbhl0pha67mevqdb2d1tpf5va4q8.apps.googleusercontent.com';
	$google_client_secret 	= 'ZvNJv2dndY93GK039wut06Xk';
	$google_redirect_url 	= 'https://localhost/CloudConvert/index.php'; //path to your script
	$google_developer_key 	= 'AIzaSyCnGzomO_DjAHBrJgTf_UKqjSJwiev8AT8';
	$path_to_files = "C:/xampp/htdocs/";
}

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