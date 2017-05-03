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
		Get Clients
	*/
	$users			=	true;
	
	/*
		Check SQL Connection
	*/
	$verbindung		=	"done";
	try
	{
		$databaseConnection 	= 	new PDO(SQL_Mode.':host='.SQL_Hostname.';port='.SQL_Port.';dbname='.SQL_Datenbank.'', SQL_Username, SQL_Password);
		
		$_sql					=	"SELECT benutzer FROM main_clients";
		$data 					= 	$databaseConnection->prepare($_sql);
		
		if ($data->execute())
		{
			if ($data->rowCount() <= 0)
			{
				$users			=	false;
			};
		}
		else
		{
			$verbindung			=	$data->errorInfo()[2];
			writeInLog(2, "check_database.php (SQL Error):".$data->errorInfo()[2]);
		};
	}
	catch (PDOException $e)
	{
		$verbindung				=	$e->getMessage();
		writeInLog(1, "check_database.php (SQL Error):".$e->getMessage());
	};
	
	/*
		Check Permissions
	*/
	$permissions				=	true;
	$wantedFilePermissions		=	"0766";
	
	// Check Files
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		$wantedFilePermissions	=	"0666";
	};
	
	if(substr(sprintf('%o', fileperms('config.php')), -4) != $wantedFilePermissions || substr(sprintf('%o', fileperms('config_instanz.php')), -4) != $wantedFilePermissions || substr(sprintf('%o', fileperms('TicketBereich.txt')), -4) != $wantedFilePermissions
		|| substr(sprintf('%o', fileperms('logs/system.log')), -4) != $wantedFilePermissions || substr(sprintf('%o', fileperms('logs/user.log')), -4) != $wantedFilePermissions || !is_executable('shell/teamspeakCommands.sh'))
	{
		$permissions		=	false;
	};
	
	// Check folders
	if(substr(sprintf('%o', fileperms('backup')), -4) != '0777' || substr(sprintf('%o', fileperms('backup/channel')), -4) != '0777' || substr(sprintf('%o', fileperms('backup/server')), -4) != '0777'
		|| substr(sprintf('%o', fileperms('images')), -4) != '0777' || substr(sprintf('%o', fileperms('images/ts_icons')), -4) != '0777' || substr(sprintf('%o', fileperms('logs')), -4) != '0777'
		|| substr(sprintf('%o', fileperms('news')), -4) != '0777' || substr(sprintf('%o', fileperms('updater')), -4) != '0777' || substr(sprintf('%o', fileperms('wantServer')), -4) != '0777')
	{
		$permissions			=	false;
	}
	else
	{
		// Check install folder, if it exists
		if(file_exists("install") && file_exists("install/js"))
		{
			if(substr(sprintf('%o', fileperms('install')), -4) != '0777' || substr(sprintf('%o', fileperms('install/js')), -4) != '0777')
			{
				$permissions	=	false;
			};
		};
	};
	
	/*
		Check Install folder
	*/
	if(file_exists("install"))
	{
		rmdir("install");
	};
	$installFolderExists		=	file_exists("install");
	
	if($verbindung != 'done' || !$users || !$permissions || $installFolderExists || !extension_loaded("soap"))
	{ ?>
		<html>
			<head>
				<meta charset="UTF-8" />
				<meta http-equiv="X-UA-Compatible" content="IE=edge"> 
				<meta name="viewport" content="width=700, initial-scale=0.5"> 
				
				<title>First Coder - Teamspeak 3 - Databaseerror</title>
				
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<meta name="author" content="First Coder: L.Gmann" />
				
				<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
				<link href="https://fonts.googleapis.com/css?family=Courgette|Kreon" rel="stylesheet">
				<link rel="stylesheet" type="text/css" href="css/sonstige/font-awesome.min.css" />
				<link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap.css" />
				<link rel="stylesheet" type="text/css" href="css/style.css" />
				<?php if(STYLE != '') { ?>
					<link rel="stylesheet" type="text/css" href="css/themes/<?php echo STYLE; ?>.css" />
				<?php } ?>
			</head>
			<body>
				<!-- Navigationsleiste -->
				<div class="navbar-fixed-top">
					<!-- Navigation -->
					<nav class="navbar navbar-default">
						<!-- Normales Menu -->
						<div class="container">
							<a class="navbar-brand hidden-xs-down pull-xs-left" href="#"><?php echo HEADING; ?></a>
						</div>
					</nav>
				</div>
				
				<?php if(($verbindung != 'done' || !$users) && $permissions) { ?>
					<!-- Error Information -->
					<section class="container first-row">
						<div class="row">
							<div id="installContentRight" class="col-lg-3 col-lg-push-9 col-md-12 col-sm-12 col-xs-12">
								<?php if(is_dir("install")) { ?>
									<div class="card">
										<div class="card-block card-block-header">
											<h4 class="card-title"><i class="fa fa-check"></i> <?php echo $language['install_webinterface']; ?></h4>
										</div>
										<div class="card-block">
											<p id="installSubText"><?php echo $language['install_webinterface_info']; ?></p>
											<button id="installMainBttn" onClick="installInterface()" style="width:100%;" class="btn btn-success"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $language['install']; ?></button>
										</div>
									</div>
								<?php } else { ?>
									<div class="card">
										<div class="card-block card-block-header">
											<h4 class="card-title"><i class="fa fa-ban"></i> <?php echo $language['repair_webinterface']; ?></h4>
										</div>
										<div class="card-block">
											<p><?php echo $language['repair_webinterface_info']; ?></p>
										</div>
									</div>
								<?php }; ?>
							</div>
							<div id="installContentLeft" class="col-lg-9 col-lg-pull-3 col-md-12 col-sm-12 col-xs-12">
								<div class="card">
									<div class="card-block card-block-header">
										<h4 class="card-title"><i class="fa fa-warning"></i> <?php echo $language['error_webinterface']; ?></h4>
									</div>
									<div class="card-block">
										<p><?php echo $language['error_webinterface_info1']; ?></p>
										<p><?php echo $language['error_webinterface_info2']; ?>:</p>
										<div class="alert alert-danger">
											<b><?php echo ($verbindung == 'done') ? $language['database_not_found'] : $language['database_connection_failed']; ?></b>
											<p>
												<?php if($verbindung != 'done')
												{
													echo $verbindung;
												}
												else
												{
													echo $language['exist_no_users'];
												}; ?>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				<?php } else if(!$permissions) { ?>
					<!-- Permission Table -->
					<section class="container first-row">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="card">
									<div class="card-block card-block-header">
										<i class="fa fa-university" aria-hidden="true"></i>	<?php echo $language['check_permissions']; ?>
									</div>
									<div class="card-block">
										<table class="table">
											<thead>
												<tr>
													<th><?php echo $language['foldername']; ?></th>
													<th><?php echo $language['permission']; ?></th>
												</tr>
											</thead>
											<tbody>
												<?php if(file_exists("install") && file_exists("install/js")) { ?>
													<!-- Install -->
													<tr style="<?php if(substr(sprintf('%o', fileperms('install')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
														<td>/install</td>
														<td><?php echo substr(sprintf('%o', fileperms('install')), -4); ?> (0777 needed)</td>
													</tr>
													<!-- Install js -->
													<tr style="<?php if(substr(sprintf('%o', fileperms('install/js')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
														<td>/install/js</td>
														<td><?php echo substr(sprintf('%o', fileperms('install/js')), -4); ?> (0777 needed)</td>
													</tr>
												<?php }; ?>
												<!-- Backup -->
												<tr  style="<?php if(substr(sprintf('%o', fileperms('backup')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/backup</td>
													<td><?php echo substr(sprintf('%o', fileperms('backup')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Channels -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('backup/channel')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/backup/channel</td>
													<td><?php echo substr(sprintf('%o', fileperms('backup/channel')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Server -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('backup/server')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/backup/server</td>
													<td><?php echo substr(sprintf('%o', fileperms('backup/server')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Images -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('images')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/images</td>
													<td><?php echo substr(sprintf('%o', fileperms('images')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- TS Icons -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('images/ts_icons')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/images/ts_icons</td>
													<td><?php echo substr(sprintf('%o', fileperms('images/ts_icons')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Logs -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('logs')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/logs</td>
													<td><?php echo substr(sprintf('%o', fileperms('logs')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Systemlogs -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('logs/system.log')), -4) == $wantedFilePermissions) { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/logs/system.log</td>
													<td><?php echo substr(sprintf('%o', fileperms('logs/system.log')), -4); ?> (<?php echo $wantedFilePermissions; ?> needed)</td>
												</tr>
												<!-- Userlogs -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('logs/user.log')), -4) == $wantedFilePermissions) { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/logs/user.log</td>
													<td><?php echo substr(sprintf('%o', fileperms('logs/user.log')), -4); ?> (<?php echo $wantedFilePermissions; ?> needed)</td>
												</tr>
												<!-- News -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('news')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/news</td>
													<td><?php echo substr(sprintf('%o', fileperms('news')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Shell -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('shell/teamspeakCommands.sh')), -4) == "0777") { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/shell/teamspeakCommands.sh</td>
													<td><?php echo substr(sprintf('%o', fileperms('shell/teamspeakCommands.sh')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Updater -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('updater')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/updater</td>
													<td><?php echo substr(sprintf('%o', fileperms('updater')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Want Server -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('wantServer')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/wantServer</td>
													<td><?php echo substr(sprintf('%o', fileperms('wantServer')), -4); ?> (0777 needed)</td>
												</tr>
												<!-- Config -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('config.php')), -4) == $wantedFilePermissions) { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/config.php</td>
													<td><?php echo substr(sprintf('%o', fileperms('config.php')), -4); ?> (<?php echo $wantedFilePermissions; ?> needed)</td>
												</tr>
												<!-- Config instanz -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('config_instanz.php')), -4) == $wantedFilePermissions) { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/config_instanz.php</td>
													<td><?php echo substr(sprintf('%o', fileperms('config_instanz.php')), -4); ?> (<?php echo $wantedFilePermissions; ?> needed)</td>
												</tr>
												<!-- Ticket -->
												<tr style="<?php if(substr(sprintf('%o', fileperms('TicketBereich.txt')), -4) == $wantedFilePermissions) { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
													<td>/TicketBereich.txt</td>
													<td><?php echo substr(sprintf('%o', fileperms('TicketBereich.txt')), -4); ?> (<?php echo $wantedFilePermissions; ?> needed)</td>
												</tr>
											</tbody>
										</table>
										<!--<p style="text-align: center;"><?php echo $language['check_permissions_info']; ?></p>-->
										<a href="index.php"><button style="width: 100%;" class="btn btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo $language['refresh']; ?></button></a>
									</div>
								</div>
							</div>
						</div>
					</section>
				<?php } else if(!extension_loaded("soap")) { ?>
					<section class="container first-row">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="card">
									<div class="card-block card-block-header">
										<i class="fa fa-warning" aria-hidden="true"></i>	SOAP <?php echo $language['deactive']; ?>
									</div>
									<div class="card-block">
										<p style="text-align: center;"><?php echo $language['soap_deactive']; ?></p>
										<a href="index.php"><button style="width: 100%;" class="btn btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo $language['refresh']; ?></button></a>
									</div>
								</div>
							</div>
						</div>
					</section>
				<?php } else { ?>
					<!-- Install folder exists -->
					<section class="container first-row">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="card">
									<div class="card-block card-block-header">
										<i class="fa fa-warning" aria-hidden="true"></i> <?php echo $language['install_folder']; ?>
									</div>
									<div class="card-block">
										<p style="text-align: center;"><?php echo $language['install_folder_info']; ?></p>
										<a href="index.php"><button style="width: 100%;" class="btn btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo $language['refresh']; ?></button></a>
									</div>
								</div>
							</div>
						</div>
					</section>
				<?php }; ?>
			
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
				<script src="js/jquery/jquery-2.2.0.js"></script>
				<script src="js/bootstrap/tether.js"></script>
				<script src="js/bootstrap/bootstrap.js"></script>
				<?php if(is_dir("install")) { ?>
					<script src="install/js/error.js"></script>
				<?php } ?>
			</body>
		</html>
<?php	// Weitere Bearbeitung unterbinden
		die();
	}
?>