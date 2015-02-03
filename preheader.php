<?php
    #a session variable is needed to support ajaxCRUD functionality
    session_start();

    #for pesky IIS configurations without silly notifications turned off
    error_reporting(E_ALL - E_NOTICE);

	define('ENV', 'LINUX');
	define('DB', 'MYSQL');

	$globals = array();

	$globals['bootstrap'] = true; //yes, this site uses bootstrap :-)

	$globals['site_root_dir'] = $_SERVER['DOCUMENT_ROOT'] . "/";

	$globals['domain_name'] = "eventswithfriends";
	$globals['domain_ext'] = ".org";
	$globals['site_name'] = $globals['domain_name'] . $globals['domain_ext'];
	$globals['site_url'] = "http://www." . $globals['domain_name'] . $globals['domain_ext'];

	define('SITE_URL', $globals['site_url']); //site url

	$globals['site_year'] = date("Y");

	$globals['webmaster_email'] = 'sdempsey@loudcanvas.com';
	//$globals['contact_email'] = 'info@' . $globals['site_name'];
	$globals['contact_email'] = "dssmllc@gmail.com";

	$globals['contact_email_name'] = "Nate";

	//$datasource = "/home/loudcanv/www/";
	if ($_SERVER['HTTP_HOST'] == "localhost"){
		//$datasource = $_SERVER['DOCUMENT_ROOT'] . "/other/$datasource";
	}

	//require_once($datasource . "includes/functions.php");
	//require_once($datasource . "includes/login_functions.php");
	//require_once($datasource . "includes/form_functions.php");
	//require_once($datasource . "includes/emailing.php");
	//require_once($datasource . "includes/uploading.php");
	require_once ("database.php");

	global $globals;

?>
