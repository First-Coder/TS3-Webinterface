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
	require_once("lang.php");
	require_once("functions.php");
	
	/*
		Get the Modul Keys
	*/
	$moduls			=	getModuls();
	$mysql_keys		=	getKeys();
	
	/*
		Check if Client is logged in
	*/
	$is_logged		=	false;
	
	if($_SESSION['login'] == $mysql_keys['login_key'])
	{
		$is_logged	=	true;
	};
	
	/*
		Get Client Permissions
	*/
	$user_right					=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Check if user get the updateinfo
	*/
	$hasPermission				=	"true";
	
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		$hasPermission			=	"false";
	}
	else
	{
		if($user_right['right_hp_main'] != $mysql_keys['right_hp_main'])
		{
			$hasPermission		=	"false";
		};
	};
	
	/*
		Check Link
	*/
	$urlData				=	explode("\?", $_SERVER['REQUEST_URI'], -1);
	$serverInstanz			=	$urlData[2];
	$serverId				=	$urlData[3];
	
	if($is_logged && $serverInstanz != "" && $serverId != "")
	{
		/*
			Teamspeak Functions
		*/
		$tsAdmin = new ts3admin($ts3_server[$serverInstanz]['ip'], $ts3_server[$serverInstanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$serverInstanz]['user'], $ts3_server[$serverInstanz]['pw']);
			
			// Server Select
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			// Server Info Daten abfragen
			$server = $tsAdmin->serverInfo();
		};
	};
?>

<!-- Updateinformation -->
<div id="updateAlert" class="container alert alert-warning">
	<div class="pull-xs-left"><?php echo $language['updater_info']; ?></div>
	<div class="pull-xs-right">
		<font id="updateTimer">10</font> <i class="fa fa-clock-o" aria-hidden="true"></i>
	</div>
	<div style="clear: both;"></div>
</div>

<!-- Navigationsleiste -->
<div class="navbar-fixed-top">
	<!-- Navigation -->
	<nav class="navbar navbar-default">
		<!-- Normales Menu -->
		<div class="container">
			<?php if($is_logged) { ?>
				<div class="pull-xs-left gn-menu-main" id="gn-menu">
					<a class="hidden-lg-up pull-xs-left gn-icon-menu" id="NaviCon">
						<i class="fa fa-bars" aria-hidden="true"></i>
					</a>
					<?php if($urlData[2] == "") { ?>
						<nav class="gn-menu-wrapper">
							<div class="gn-scroller">
								<ul class="gn-menu">
									<li>
										<a class="gn-icon gn-icon-user" style="cursor:default !important;"><?php echo $language['main_main_profil']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-dashboard profilDashboard" onClick="profilDashboardInit();">Dashboard</a></li>
											<li><a class="gn-icon gn-icon-edit profilEdit" onClick="profilEditInit();"><?php echo $language['profile']." ".$language['edit']; ?></a></li>
											<li><a class="gn-icon gn-icon-university profilPermission" onClick="profilPermissionInit();"><?php echo $language['profile']." ".$language['permission']; ?></a></li>
											<li><a class="gn-icon gn-icon-sign-out" onClick="ausloggenInit();"><?php echo $language['logout']; ?></a></li>
										</ul>
									</li>
									<li class="settingsarea">
										<a class="gn-icon gn-icon-wrench" style="cursor:default !important;"><?php echo $language['main_main_settings']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-gears adminSettings" onClick="adminSettingsInit();"><?php echo $language['settings']; ?></a></li>
											<li><a class="gn-icon gn-icon-globe adminInstanz" onClick="adminInstanzInit();"><?php echo $language['instances']; ?></a></li>
											<li><a class="gn-icon gn-icon-users adminUser" onClick="adminUserInit();"><?php echo $language['client']; ?></a></li>
											<li><a class="gn-icon gn-icon-mail adminMail" onClick="adminMailInit();">Mail <?php echo $language['settings']; ?></a></li>
										</ul>
									</li>
									<li class="webinterfacearea">
										<a class="gn-icon gn-icon-ts" style="cursor:default !important;"><?php echo $language['main_main_interface']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-server teamspeakServer" onClick="teamspeakServerInit();">Server</a></li>
											<li><a class="gn-icon gn-icon-paint-brush teamspeakServerCreate" onClick="teamspeakServerCreateInit();"><?php echo $language['create_server']; ?></a></li>
											<?php
												$wantServer = scandir('wantServer/');
												if(count($wantServer) > 2) { ?>
													<li><a class="gn-icon gn-icon-list teamspeakServerRequests" onClick="teamspeakServerRequestsInit();"><?php echo $language['server_requests']; ?></a></li>
											<?php }; ?>
										</ul>
									</li>
									<li>
										<a class="gn-icon gn-icon-ticket" style="cursor:default !important;">Ticket System</a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-ticket ticketMain" onClick="ticketInit();">Tickets</a></li>
										</ul>
									</li>
								</ul>
							</div>
						</nav>
					<?php } else { ?>
						<nav class="gn-menu-wrapper">
							<div class="gn-scroller">
								<ul class="gn-menu">
									<li>
										<a class="gn-icon gn-icon-arrow-left" style="cursor:default !important;"><?php echo $language['back']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-arrow-left" onClick="goBackToMain();"><?php echo $language['back']; ?></a></li>
										</ul>
									</li>
									<?php if(strpos($user_right['ports']['right_web_server_start_stop'][$serverInstanz], $server['data']['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
										<li>
											<a class="gn-icon gn-icon-server" style="cursor:default !important;">Server</a>
											<ul class="gn-submenu">
												<li onClick="toggleStartStopTeamspeakserver('<?php echo $serverId; ?>', '<?php echo $serverInstanz; ?>', '<?php echo $server['data']['virtualserver_port']; ?>')">
													<input id="serverStartStopTiny" type="checkbox" data-width="96%" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="<?php echo $language['online']; ?>" data-off="<?php echo $language['offline']; ?>" <?php if($server['data']['virtualserver_status'] == 'online') { echo 'checked'; } ?>>
												</li>
											</ul>
										</li>
									<?php }; ?>
									<li>
										<a class="gn-icon gn-icon-ts" style="cursor:default !important;"><?php echo htmlspecialchars($server['data']['virtualserver_name']); ?></a>
										<ul class="gn-submenu">
											<li>
												<a class="gn-icon gn-icon-eye teamspeakView" onClick="teamspeakViewInit();"><?php echo $language['ts_serveroverview']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_protokoll'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-list teamspeakProtokol <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakProtokolInit();" : ""; ?>"><?php echo $language['ts_protokol']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_mass_actions'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-users teamspeakMassActions <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakMassActionsInit();" : ""; ?>"><?php echo $language['ts_mass_actions']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_icons'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-file teamspeakIcons <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakIconsInit();" : ""; ?>"><?php echo $language['ts_icons']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_clients'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-user teamspeakClients <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakClientsInit();" : ""; ?>"><?php echo $language['client']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_bans'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-user-times teamspeakBans <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBansInit();" : ""; ?>"><?php echo $language['ts_bans']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_token'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-key teamspeakToken <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakTokenInit();" : ""; ?>"><?php echo $language['ts_token']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_file_transfer'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-file teamspeakFilelist <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakFilelistInit();" : ""; ?>"><?php echo $language['ts_filelist']; ?></a>
											</li>
											<li>
												<?php
													if(strpos($user_right['ports']['right_web_server_backups'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-upload teamspeakBackup <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBackupsInit();" : ""; ?>"><?php echo $language['ts_backups']; ?></a>
											</li>
										</ul>
									</li>
								</ul>
							</div>
						</nav>
					<?php }; ?>
				</div>
			<?php }; ?>
			<a class="navbar-brand hidden-xs-down pull-xs-left" href="#"><?php echo htmlspecialchars(HEADING); ?></a>
			<?php if($serverInstanz == "" && $serverId == "") { ?>
				<ul class="nav navbar-nav pull-xs-right text-uppercase hidden-md-down">
					<li class="nav-item">
						<a class="nav-link mainMain" href="#"><i class="fa fa-fw fa-home"></i> <?php echo $language['ts_main']; ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainApplyForServer" href="#" style="<?php if($moduls['free_ts3_server_application'] != "true") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-edit"></i> <?php echo $language['apply_for_server']; ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainMasterserver" href="#" style="<?php if($moduls['masterserver'] != "true" || MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-eye"></i> Masterserver</a>
					</li>
				</ul>
				<ul class="nav navbar-nav pull-xs-right text-uppercase hidden-lg-up">
					<li class="nav-item">
						<a class="nav-link mainMain" href="#" style="font-size:1.2em;"><i class="fa fa-fw fa-home"></i>&nbsp;</a>
					</li>
					
					<li class="nav-item">
						<a class="nav-link mainApplyForServer" href="#" style="font-size:1.2em;<?php if($moduls['free_ts3_server_application'] != "true") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-edit"></i>&nbsp;</a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainMasterserver" href="#" style="font-size:1.2em;<?php if($moduls['masterserver'] != "true" || MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-eye"></i>&nbsp;</a>
					</li>
				</ul>
			<?php }; ?>
		</div>
	</nav>
</div>

<section class="container first-row">
	<div class="row">
		<div id="menuContent" class="col-lg-3 col-lg-push-9 col-md-12 col-sm-12 col-xs-12">
			<?php
				if(!$is_logged)
				{
					include("web_login.php");
				}
				else
				{
					if($urlData[2] == "")
					{
						include("web_logged.php");
					}
					else
					{
						include("web_logged_serverview.php");
					};
				};
			?>
		</div>
		<div id="mainContent" class="col-lg-9 col-lg-pull-3 col-md-12 col-sm-12 col-xs-12"></div>
	</div>
</section>

<!-- (c)Copyright DO NOT REMOVE!!! -->
<nav class="navbar navbar-copyright navbar-fixed-bottom">
	<div id="copyright" class="col-xs-12 col-md-12">
		<i class="fa fa-copyright"></i> by <a href="http://first-coder.de/">First-Coder.de</a> || written by <strong>L. Gmann</strong>
	</div>
</nav>

<!-- Sprachdatein laden -->
<script>
	var bttn_login					=	'<?php echo $language['login']; ?>';
	var bttn_back					=	'<?php echo $language['js_back']; ?>';
	var write_user_and_pw			=	'<?php echo $language['write_user_and_pw']; ?>';
	var user_is_blocked				=	'<?php echo $language['user_is_blocked']; ?>';
	var user_blocked_info			=	'<?php echo $language['user_blocked_info']; ?>';
	var user_or_pw_wrong			=	'<?php echo $language['user_or_pw_wrong']; ?>';
	var login_try					=	'<?php echo $language['login_try']; ?>';
	var user_session_blocked		=	'<?php echo $language['user_session_blocked']; ?>';
	var user_session_blocked_info	=	'<?php echo $language['user_session_blocked_info']; ?>';
	
	var hp_user_change_pw1_failed	=	'<?php echo $language['hp_user_change_pw1_failed']; ?>';
	var hp_user_change_user_failed	=	'<?php echo $language['hp_user_change_user_failed']; ?>';
	
	var server_request_success		=	'<?php echo $language['server_request_created']; ?>';
	
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var ts3_server_create_default 	= 	<?php echo json_encode($ts3_server_create_default); ?>;
	var ts_server_create_wrong_port	=	'<?php echo $language['ts_server_create_wrong_port']; ?>';
	
	// Eingeloggt
	var logged 						=	'<?php echo ($is_logged) ? "true" : "false"; ?>';
	var sessionID					=	'<?php echo $_SESSION['user']['id']; ?>';
	
	// Für Server beantragen
	var wantServer 					= 	new Array();
</script>

<!-- Javascripte Laden -->
<script src="js/jquery/jquery-2.2.0.js"></script>
<script src="js/bootstrap/tether.js"></script>
<script src="js/bootstrap/bootstrap.js"></script>
<script src="js/bootstrap/bootstrap-notify.js"></script>
<script src="js/sonstige/classie.js"></script>
<script src="js/codrops/gnmenu.js"></script>
<script src="js/webinterface/main.js"></script>
<?php if($is_logged && $serverInstanz != "" && $serverId != "") { ?>
	<script src="js/bootstrap/bootstrap-toggle.js"></script>
	<script src="js/webinterface/teamspeak.js"></script>
<?php }; ?>

<script>
	// Google Hamburgermenu
	if(logged == "true")
	{
		new gnMenu( document.getElementById( 'gn-menu' ) );
	};
	
	// Updateinfo
	var updateAvalible		=	"<?php echo (checkNewVersion(false) != INTERFACE_VERSION) ? "true" : "false"; ?>";
	var hasPermission		=	"<?php echo $hasPermission; ?>";
	var timer				=	10;
	if(updateAvalible == "true" && hasPermission == "true")
	{
		setTimeout(function(){
			$("#updateAlert").slideDown("slow");
			updateInterval	=	setInterval(function(){
				if(timer == 0)
				{
					$("#updateAlert").slideUp("slow");
					clearInterval(updateInterval);
				}
				else
				{
					$("#updateTimer").text(--timer);
				};
			}, 1000);
		}, 3000);
	};
</script>