<?php
// for testing it could be helpful...
ini_set('display_errors', 1);

// include the files and libraries we need
require_once("functions.php");
require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';
require_once 'CloudConvert.class.php';

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

//start session
session_start();

// create an instance of the google client
$gClient = new Google_Client();
$gClient->setApplicationName('Login to Sanwebe.com');
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
$gClient->setDeveloperKey($google_developer_key);
$gClient->setScopes(
	'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'
);
// if a session exists, add the access token to the google client
if (isset($_SESSION['token'])) {
	$gClient->setAccessToken($_SESSION['token']);
}

// 1) DOES THE USER WANT TO SIGN OUT
if (isset($_REQUEST["logout"])) {
	unset($_SESSION['token']);
	$gClient->revokeToken();
	header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
// 2) GOOGLE OAUTH FLOW
} elseif (isset($_GET['code'])) { 
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
	return;
// 3) IS THE USER LOGGED IN
} elseif ($gClient->getAccessToken()) {
	//For logged in user, get details from google using access token
	$google_oauthV2 = new Google_Oauth2Service($gClient);
	$user 				= $google_oauthV2->userinfo->get();
	$user_id 			= $user['id'];
	$user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	$email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	$profile_url 		= filter_var($user['link'], FILTER_VALIDATE_URL);
	$profile_image_url 	= filter_var($user['picture'], FILTER_VALIDATE_URL);
	$personMarkup 		= "$email<div><img src='$profile_image_url?sz=50'></div>";
	$_SESSION['token'] 	= $gClient->getAccessToken();
	$isLoggedIn = true;
// 4) THE USER IS NOT LOGGED IN
} else  {
	//For Guest user, get google login url
	$authUrl = $gClient->createAuthUrl();
	header('Location: index.php');
	return;
}

if (isset($_POST['submit'])) {
		
	// GRAB FILE TMP AND TO UPLOAD
	$tmpfile = $_FILES['uploaded-file']["tmp_name"];
	$fileToUpload = $path_to_files . $_FILES['uploaded-file']["name"];
	
	// GET THE FILETYPE FROM THE FILE ($fileToUpload) 'google: find extension from filename'
	$ext=substr($fileToUpload,strpos($fileToUpload,'.')+1);
	// IS IT AN ACCEPTABLE TYPE (in_array('docx', 'jpg')
	
	
	// HIDE THE INPUT SELECT BOX
	
		
	//$formatInput = $_POST['format_input'];
	$formatOutput = $_POST['format_output'];
	
	// IS OUTPUT TYPE SET (not "--SELECT--") <option value="">-- Select</option> if ($formatOutput == "") throw exception
	
	$finalLocation = $path_to_files . "output." . $formatOutput; // FORMAT FINAL LOCATIN BASED ON SELECTED TYPE
	
	move_uploaded_file($tmpfile, $fileToUpload);
	
	$apikey="FHrxjAhlgBRH6leRlTLBcaP4Ed9tRTeQoQnvtWhDN_sCBfNlhM753LLIwpx9OzYiGhfXhxvPMTHO9mSLRsjOVg";

	$process = CloudConvert::createProcess(
		$ext, // GET THIS FILE UPLOADED
		$formatOutput, // SELEC
		$apikey
	);
	
	$process-> upload($fileToUpload, $formatOutput );
	if ($process-> waitForConversion()) {
		$process -> download($finalLocation);

		// DOWLOAD FILE FROM PHP "Header: Application/x-"... readfile();
		//http://stackoverflow.com/questions/40943/how-to-automatically-start-a-download-in-php
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($finalLocation) . '"');
		header('Content-Transfer-Encoding: binary');
		readfile($finalLocation);//$decrypted_file_path

		echo "Conversion done :-)";
		die();
	} else {
		echo "Something went wrong :-(";
		die();
	}	
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CloudConverting API</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom CSS -->
    <link href="css/full2.css" rel="stylesheet">
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="full">
<!-- Navigation -->    
<!--========================================================-->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">FILE CONVO</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><?php displayLogOutButton($isLoggedIn)?></li>
            </ul>
        </div>
    </div>
</div>
</nav>
<!--==============================================================-->

<!--==============================================================-->				
	<div class="container" align="centre">
	<div class="row" id="converter-container">
    <div class="span8 offset2">
    <br><br><h1>Start Converting files using CLOUD CONVERT</h1>
	<form class="form-inline" role="form" action="cloudconvert.php" method="POST" enctype="multipart/form-data">
	<br><div class="form-group">
	<label for="sel1">Select output format</label>
	<select name="format_output" class="form-control" id="<?php echo $_POST["$outputfile"];?>">
    <option>--Select--</option>
    <option>pdf</option>
    <option>jpg</option>
    <option>png</option>
	</select>
	</div><br>
	<br><div class="form-group">
    <input type="file" name="uploaded-file"><br>
    <button name="submit" type="submit" class="btn btn-default" value="Convert">Convert</button>
	</div>
	</form>
	</div>
	</div>
	</div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- jQuery Version 1.11.0 -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>