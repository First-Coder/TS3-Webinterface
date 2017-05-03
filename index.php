<?php
	/*
		First-Coder Teamspeak 3 Webinterface for everyone
		Copyright (C) 2017 by L.Gmann

		This program is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program.  If not, see <http://www.gnu.org/licenses/>.
		
		for help look http://first-coder.de/
	*/
	
	/*
		Includes
	*/
	require_once("functions.php");
	
	/*
		Check the Database
	*/
	require_once("check_database.php");
	
	/*
		Get the Permissionkeys
	*/
	$mysql_keys				=	getKeys();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"> 
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="author" content="First Coder: L.Gmann" />
		<meta name="viewport" content="width=device-width, initial-scale=0.6, minimum-scale=0.6" />
		<meta name="description" content="Teamspeak 3 Control Panel to Manage your own Teamspeak 3 Server instance." />
		
		<title><?php echo HEADING; ?> - Teamspeak 3 Control Panel -- Control your own Teamspeakserver</title>
		
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<link rel="apple-touch-icon" sizes="120x120" href="images/apple-touch-icon-120x120-precomposed.png" />
		<link rel="apple-touch-icon" sizes="152x152" href="images/apple-touch-icon-152x152-precomposed.png" />
		
		<link href="https://fonts.googleapis.com/css?family=Courgette|Kreon" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="css/mail/codemirror.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/normalize.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/animate.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/dropzone.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/morris.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/tether.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-toggle.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-datetimepicker.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-editable.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-treeview.css" />
		<link rel="stylesheet" type="text/css" href="css/sonstige/prettify.css" />
		<link rel="stylesheet" type="text/css" href="css/styleNexusNavigation.css" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<?php if(STYLE != '') { ?>
			<link rel="stylesheet" type="text/css" href="css/themes/<?php echo STYLE; ?>.css" />
		<?php } ?>
		
		<script src="js/jquery/jquery-2.2.0.js"></script>
		
		<!--[if IE]>
  		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- Preloader -->
		<div class="preloader">
			<div id="preloader">
				<h1><?php echo htmlspecialchars(HEADING); ?></h1>
				<div class="aussenRing"></div>
				<div class="innenRing"></div>
				<h3>Loading...</h3>
			</div>
    	</div>
		
		<div id="hp"><?php include("web_main.php"); ?></div>
	</body>
</html>