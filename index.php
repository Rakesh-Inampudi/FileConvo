<?php
// for testing it could be helpful...
ini_set('display_errors', 1);
//COMMENT
// include the files and libraries we need
require_once("functions.php");
require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';

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
} elseif ($_SERVER['SERVER_NAME'] == "herokuapp.com") {
	$google_client_id 		= '220072187471-09fugmebgf3d3h82bu40jg4lr4mv861f.apps.googleusercontent.com';
	$google_client_secret 	= 'JUNH3BvQhvgYImMqBBi4aOMG';
	$google_redirect_url 	= 'https://young-shore-9280-965.herokuapp.com'; //path to your script
	$google_developer_key 	= 'AIzaSyCj0odv7UqxfBcSedHuT_n78Mc6sA5T13M';
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
$gClient->setApplicationName('login');
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
	
	header('Location: cloudconvert.php');
	return;
	
// 4) THE USER IS NOT LOGGED IN
} else  {
	//For Guest user, get google login url
	$authUrl = $gClient->createAuthUrl();
	$isLoggedIn = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<!-- Make sure the <html> tag is set to the .full CSS class. Change the background image in the full.css file. -->

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>CONVO</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/full.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

</head>

<body class="full" >

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">FILE CONVO</a>
				<?php displayLogOutButton($isLoggedIn)?>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            
			<%
			
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

</body>
</html>
    <!-- Put your page content here! -->

<!--====================================================================================================-->
<?php


########## MySql details (Replace with yours) #############
$db_username = "cloud"; //Database Username
$db_password = "Gj9HQNtNGWxfHWJQ"; //Database Password
$hostname = "localhost"; //Mysql Hostname
$db_name = 'cloudconvert'; //Database Name
###################################################################



//If user wish to log out, we just unset Session variable
if (isset($_REQUEST['reset'])) 
{
}

//If code is empty, redirect user to google authentication page for code.
//Code is required to aquire Access Token from google
//Once we have access token, assign token to session variable
//and we can redirect user back to page and login.







//HTML page start
echo '<div style=" width: 40%; margin: 300px auto;">';
if(isset($authUrl)) //user is not logged in, show login button
{
	echo '<a class="login" href="'.$authUrl.'"><img src="images/google-login-button.png" /></a>';
} 
else // user logged in 
{

	//header('Location: cloudconvert.php');
	//die;

   /* connect to database using mysqli */
	$mysqli = new mysqli($hostname, $db_username, $db_password, $db_name);
	
	if ($mysqli->connect_error) {
		die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}
	
	//compare user id in our database
	$user_exist = $mysqli->query("SELECT COUNT(google_id) as usercount FROM google_users WHERE google_id=$user_id")->fetch_object()->usercount; 
	if($user_exist)
	{
		echo 'Welcome back '.$user_name.'!';
	}else{ 
		//user is new
		echo 'Hi '.$user_name.', Thanks for Registering!';
		$mysqli->query("INSERT INTO google_users (google_id, google_name, google_email, google_link, google_picture_link) 
		VALUES ($user_id, '$user_name','$email','$profile_url','$profile_image_url')");
	}

	echo '</div>';
	echo '<br /><a href="'.$profile_url.'" target="_blank"><div style="width:10%; margin:700px auto;"><img src="'.$profile_image_url.'?sz=100" /></div></a>';
	echo '<br /><a class="logout" href="?reset=1">Logout</a>';
	
	//list all user details
	//echo '<pre>'; 
	//print_r($user);
	//echo '</pre>';	
}
 
?>

