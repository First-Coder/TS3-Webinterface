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
	require_once("../lang.php");
	require_once("../functions.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	$hasRights		=	true;
	
	if($_SESSION['login'] != $mysql_keys['login_key'] || $user_right['right_hp_main'] != $mysql_keys['right_hp_main'])
	{
		$hasRights	=	false;
	};
?>

<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge"> 
		<meta name="viewport" content="width=700, initial-scale=0.5"> 
		
		<title>First Coder - Teamspeak 3 - Updater</title>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="author" content="First Coder: L.Gmann" />
		
		<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon" />
		<link href="https://fonts.googleapis.com/css?family=Courgette|Kreon" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="../css/sonstige/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/bootstrap/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="../css/style.css" />
		<?php if(STYLE != '') { ?>
			<link rel="stylesheet" type="text/css" href="../css/themes/<?php echo STYLE; ?>.css" />
		<?php } ?>
	</head>
	<body>
		<!-- Navigationsleiste -->
		<div class="navbar-fixed-top">
			<!-- Navigation -->
			<nav class="navbar navbar-default">
				<!-- Normales Menu -->
				<div class="container">
					<a class="navbar-brand hidden-xs-down pull-xs-left" href="#"><?php echo HEADING; ?> Updater</a>
				</div>
			</nav>
		</div>
		
		<!-- Updatercontent -->
		<section class="container first-row">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="card">
						<?php if(!$hasRights) { ?>
							<div class="card-block card-block-header">
								<h4 class="card-title"><i class="fa fa-ban"></i> <?php echo $language['no_access']; ?></h4>
							</div>
							<div class="card-block">
								<p><?php echo $language['no_access_info']; ?></p>
							</div>
						<?php } else { ?>
							<div class="card-block card-block-header">
								<h4 class="card-title"><i class="fa fa-random"></i> <?php echo $language['updater_welcome']; ?></h4>
							</div>
							<div class="card-block">
								<div class="step-1">
									<?php
										try
										{
											$updater 			= 	new SoapClient(null, array(
												'location' => 'http://wiki.first-coder.de/soap/soap_server.php',
												'uri' => 'https://wiki.first-coder.de/soap/soap_server.php'
											));
											
											$versionList		=	array();
											$versionList		=	array_reverse(json_decode($updater->getVersionList()));
											$tmpVersion			=	false;
											$tmpVersionNumber	=	count($versionList)-1;
										}
										catch(Exception $e)
										{ ?>
											<div class="alert alert-danger">
												<b><i class="fa fa-warning"></i> <?php echo $language['failed']; ?></b><br /><?php echo $language['message'].": ".$e->getMessage(); ?>
											</div>
											<a href="../index.php"><button class="btn btn-info"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo $language['back']; ?></button></a>
										<?php exit(); };
									?>
									<table class="table table-hover">
										<thead>
											<th>
												Version
											</th>
											<th></th>
										</thead>
										<tbody>
											<?php foreach($versionList AS $version)
											{
												if($version == INTERFACE_VERSION)
												{
													$tmpVersion		=	true;
													echo "<tr class=\"text-warning\"><td>".$version."</td><td>Momentane Version</td></tr>";
												}
												else if(!$tmpVersion)
												{
													echo "<tr class=\"text-success\"><td>".$version."</td><td><button class=\"btn btn-success\" onClick=\"ShowChangelog('".$tmpVersionNumber."')\"><i class\"fa fa-check\" aria-hidden=\"true\"> ".$language['choose']."</button></td></tr>";
												}
												else
												{
													echo "<tr class=\"text-danger\"><td>".$version."</td><td>Veraltete Version</td></tr>";
												};
												
												$tmpVersionNumber--;
											}; ?>
										</tbody>
									</table>
									<a href="../index.php"><button class="btn btn-info"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo $language['back']; ?></button></a>
								</div>
								<div class="step-2" style="display: none;">
									<h1 class="changelogHeadline" style="font-size: 22px;"></h1>
									<h6 class="changelogTime" style="color: #aaa;"></h6>
									<div id="changelogContent"></div>
									<button id="updateAction" class="btn btn-success" style="width: 100%;"><i class="fa fa-plug" aria-hidden="true"></i> Update</button>
								</div>
								<div class="step-3" style="display: none;text-align: center;">
									<i style="font-size:100px;" class="fa fa-cogs fa-spin"></i>
									<h1 style="font-size: 22px;margin-top: 20px;">Install... Please wait...</h1>
								</div>
								<div class="step-4" style="display: none;">
									<div id="changeOutput"></div>
									<button onClick="backToMainMenu();" class="btn btn-info"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo $language['back']; ?></button>
								</div>
							</div>
						<?php }; ?>
					</div>
				</div>
			</div>
		</section>
	
		<!-- Copyright -->
		<nav class="navbar navbar-copyright navbar-fixed-bottom">
			<div id="copyright" class="col-xs-12 col-md-12">
				<i class="fa fa-copyright"></i> by <a href="http://first-coder.de/">First-Coder.de</a> || written by <strong>L. Gmann</strong>
			</div>
		</nav>
		
		<!-- Lanugage -->
		<script>
			var weiter		=	'<?php echo $language['next']; ?>';
		</script>
		<script src="../js/jquery/jquery-2.2.0.js"></script>
		<script src="../js/bootstrap/tether.js"></script>
		<script src="../js/bootstrap/bootstrap.js"></script>
		<script src="updater.js"></script>
	</body>
</html>