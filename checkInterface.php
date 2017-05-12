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
	$users						=	true;
	
	/*
		Check SQL Connection
	*/
	$verbindung					=	"done";
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
	
	if(!is_writable('config/config.php') || !is_writable('config/instance.php') || !is_writable('files/') || !is_writable('files/backups/') || !is_writable('files/backups/channelname/') || !is_writable('files/backups/channelnamesettings/')
		|| !is_writable('files/backups/server/') || !is_writable('files/news/') || !is_writable('files/ticket/') || !is_writable('files/ticket/ticketareas.txt') || !is_writable('files/wantServer/') || !is_writable('images/')
		|| !is_writable('images/ts_icons/') || !is_writable('logs/') || !is_writable('logs/system.log') || !is_writable('logs/user.log') || !is_writable('updater/'))
	{
		$permissions			=	false;
	}
	
	if(file_exists("install"))
	{
		if(!is_writable('install/'))
		{
			$permissions	=	false;
		};
	};
	
	/*
		Check Install folder
	*/
	if(file_exists("install") && $verbindung == "done" && $users)
	{
		rmdir("install");
	};
	
	if($verbindung != 'done' || !$users || !$permissions || file_exists("install") || !extension_loaded("soap"))
	{
		require_once("./lang/lang.php"); ?>
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
				<div class="navbar-fixed-top">
					<nav class="navbar navbar-default">
						<div class="container">
							<a class="navbar-brand hidden-xs-down pull-xs-left" href="#"><?php echo HEADING; ?></a>
						</div>
					</nav>
				</div>
				
				<?php if(($verbindung != 'done' || !$users) && $permissions) { ?>
					<section class="container first-row">
						<?php if(!file_exists("install")) { ?>
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
						<?php } else { ?>
							<div class="row">
								<div id="installContentLeft" class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
									<div class="card">
										<div class="card-block card-block-header">
											<h4 class="card-title"><i class="fa fa-database"></i> <?php echo $language['install_webinterface']; ?></h4>
										</div>
										<div id="installBttn" class="card-block">
											<p><?php echo $language['install_webinterface_info']; ?></p>
											<button onClick="installInterface()" style="width:100%;" class="btn btn-success"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $language['install']; ?></button>
										</div>
										<ul id="installHistory" class="list-group list-group-flush" style="display:none;">
											<li class="list-group-item text-success background-success"><i id="guidelinesIcon" class="fa fa-eye"></i> <?php echo $language['guidelines']; ?></li>
											<li id="databaseconnectionBg" class="list-group-item text-danger-no-cursor background-danger"><i id="databaseconnectionIcon" class="fa fa-ban"></i> <?php echo $language['databaseconnection']; ?></li>
											<li id="settingsBg" class="list-group-item text-danger-no-cursor background-danger"><i id="settingsIcon" class="fa fa-ban"></i> <?php echo $language['settings']; ?></li>
										</ul>
									</div>
								</div>
								<div id="installContentRight" class="col-lg-9 col-md-12 col-sm-12 col-xs-12">
									<div class="card">
										<div class="card-block card-block-header">
											<h4 class="card-title"><i class="fa fa-flag"></i> <?php echo $language['languagesettings']; ?></h4>
											<h6 class="card-subtitle  text-muted"><?php echo $language['languagesettings_install']; ?></h6>
										</div>
										<div class="card-block">
											<div class="row">
												<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
													<label class="c-input c-radio">
														<input id="checkGerman" name="langRadio" type="radio">
														<span class="c-indicator"></span>
														<img height="20" width="20" class="img-rounded hover_red" src="images/german.gif"> German
													</label>
												</div>
												<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
													<label class="c-input c-radio">
														<input name="langRadio" type="radio" checked>
														<span class="c-indicator"></span>
														<img height="20" width="20" class="img-rounded hover_red" src="images/english.gif"> English
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php }; ?>
					</section>
				<?php } else if(!$permissions) { ?>
					<!-- Permission Table -->
					<section class="container first-row">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="card">
									<div class="card-block card-block-header">
										<h4 class="card-title"><i class="fa fa-university" aria-hidden="true"></i>	<?php echo $language['check_permissions']; ?></h4>
										<h6 class="card-subtitle text-muted"><?php echo $language['check_permissions_info']; ?></h6>
									</div>
									<div class="card-block">
										<table class="table permission-table">
											<tbody>
												<?php if(file_exists("install")) { ?>
													<tr class="<?php echo (is_writable('install/')) ? "text-success" : "text-danger-no-cursor"; ?>">
														<td>/install/<p class="text-muted"><?php echo (is_writable('install/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
														<td class="icon"><i class="fa fa-<?php echo (is_writable('install/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
													</tr>
												<?php }; ?>
												<tr class="<?php echo (is_writable('config/config.php')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/config/config.php<p class="text-muted"><?php echo (is_writable('config/config.php')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('config/config.php')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('config/instance.php')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/config/instance.php<p class="text-muted"><?php echo (is_writable('config/instance.php')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('config/instance.php')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/<p class="text-muted"><?php echo (is_writable('files/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/backups/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/backups/<p class="text-muted"><?php echo (is_writable('files/backups/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/backups/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/backups/channelname/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/backups/channelname/<p class="text-muted"><?php echo (is_writable('files/backups/channelname/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/backups/channelname/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/backups/channelnamesettings/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/backups/channelnamesettings/<p class="text-muted"><?php echo (is_writable('files/backups/channelnamesettings/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/backups/channelnamesettings/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/backups/server/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/backups/server/<p class="text-muted"><?php echo (is_writable('files/backups/server/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/backups/server/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/news/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/news/<p class="text-muted"><?php echo (is_writable('files/news/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/news/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/ticket/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/ticket/<p class="text-muted"><?php echo (is_writable('files/ticket/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/ticket/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/ticket/ticketareas.txt')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/ticket/ticketareas.txt<p class="text-muted"><?php echo (is_writable('files/ticket/ticketareas.txt')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/ticket/ticketareas.txt')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('files/wantServer/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/files/wantServer/<p class="text-muted"><?php echo (is_writable('files/wantServer/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('files/wantServer/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('images/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/images/<p class="text-muted"><?php echo (is_writable('images/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('images/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('images/ts_icons/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/images/ts_icons/<p class="text-muted"><?php echo (is_writable('images/ts_icons/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('images/ts_icons/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('logs/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/logs/<p class="text-muted"><?php echo (is_writable('logs/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('logs/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('logs/system.log')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/logs/system.log<p class="text-muted"><?php echo (is_writable('logs/system.log')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('logs/system.log')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr class="<?php echo (is_writable('logs/user.log')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/logs/user.log<p class="text-muted"><?php echo (is_writable('logs/user.log')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('logs/user.log')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
												<tr style="border-bottom: 0;" class="<?php echo (is_writable('updater/')) ? "text-success" : "text-danger-no-cursor"; ?>">
													<td>/updater/<p class="text-muted"><?php echo (is_writable('updater/')) ? $language['file_folder_has_permission'] : $language['file_folder_hasnt_permission']; ?></p></td>
													<td class="icon"><i class="fa fa-<?php echo (is_writable('updater/')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
												</tr>
											</tbody>
										</table>
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
										<h4 class="card-title"><i class="fa fa-warning" aria-hidden="true"></i>	SOAP <?php echo $language['deactive']; ?></h4>
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
										<h4 class="card-title"><i class="fa fa-warning" aria-hidden="true"></i>	<?php echo $language['install_folder']; ?></h4>
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
				
				<script src="js/jquery/jquery-2.2.0.js"></script>
				<script src="js/bootstrap/tether.js"></script>
				<script src="js/bootstrap/bootstrap.js"></script>
				<?php if(file_exists("install/")) {
					require_once("./install/lang.php"); ?>
					<script>
						var datenbank_erstellen		=	'<?php echo $language['datenbank_erstellen']; ?>';
							weiter					=	'<?php echo $language['weiter']; ?>';
							falsches_passwort		=	'<?php echo $language['falsches_passwort']; ?>';
							passwort_info			=	'<?php echo $language['passwort_info']; ?>';
							falscher_benutzer		=	'<?php echo $language['falscher_benutzer']; ?>';
							benutzer_info			=	'<?php echo $language['benutzername_info']; ?>';
					</script>
					<script src="install/index.js"></script>
				<?php } ?>
			</body>
		</html>
<?php	// Weitere Bearbeitung unterbinden
		die();
	}
?>