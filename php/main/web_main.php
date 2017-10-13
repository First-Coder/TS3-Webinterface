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
	require_once(__DIR__."/../../config/config.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	$LinkInformations	=	getLinkInformations();
	$Moduls				=	getModuls();
	$mysql_keys			=	getKeys();
	
	/*
		Check if user get the updateinfo
	*/
	$hasPermission				=	"true";
	
	if(!isSet($_SESSION['login']))
	{
		$hasPermission			=	"false";
	}
	else
	{
		if($_SESSION['login'] != $mysql_keys['login_key'])
		{
			$hasPermission		=	"false";
		}
		else
		{
			$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
			if($user_right['right_hp_main']['key'] != $mysql_keys['right_hp_main'])
			{
				$hasPermission	=	"false";
			};
		};
	};
	
	/*
		Teamspeak Connection
	*/
	if($LoggedIn && !empty($LinkInformations))
	{
		require_once(__DIR__."/../../config/instance.php");
		require_once(__DIR__."/../../php/classes/ts3admin.class.php");
		
		$tsAdmin = new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
			$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
			
			$server = $tsAdmin->serverInfo();
		};
	};
?>

<!-- Global are you sure modal -->
<div id="modalAreYouSure" class="modal fade" data-backdrop="true" tabindex="-1" role="dialog" aria-labelledby="modalAreYouSureLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header alert-danger">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modalAreYouSureLabel"></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12 modal-info">
						<p><?php echo $language['are_you_sure']; ?></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['no']; ?></button>
				<button id="areYouSureBttn" type="button" class="btn btn-danger"><i class="fa fa-fw fa-check"></i> <?php echo $language['yes']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Updateinformation -->
<div id="updateAlert" class="container alert alert-info">
	<div class="pull-xs-left"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $language['updater_info']; ?></div>
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
			<?php if($LoggedIn) { ?>
				<div class="pull-xs-left gn-menu-main" id="gn-menu">
					<a class="hidden-lg-up pull-xs-left gn-icon-menu" id="NaviCon">
						<i class="fa fa-bars" aria-hidden="true"></i>
					</a>
					<?php if(empty($LinkInformations)) { ?>
						<nav class="gn-menu-wrapper">
							<div class="gn-scroller">
								<ul class="gn-menu">
									<li>
										<a class="gn-icon gn-icon-user" style="cursor:default !important;"><?php echo $language['your_profile']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-dashboard profilDashboard" onClick="profilDashboardInit();">Dashboard</a></li>
											<li><a class="gn-icon gn-icon-edit profilEdit" onClick="profilEditInit();"><?php echo $language['edit_profile']; ?></a></li>
											<li><a class="gn-icon gn-icon-university profilPermission" onClick="profilPermissionInit();"><?php echo $language['profile_permissions']; ?></a></li>
											<li><a class="gn-icon gn-icon-sign-out" onClick="ausloggenInit();"><?php echo $language['logout']; ?></a></li>
										</ul>
									</li>
									<li class="settingsarea">
										<a class="gn-icon gn-icon-wrench" style="cursor:default !important;"><?php echo $language['global_settings']; ?></a>
										<ul class="gn-submenu">
											<?php $permission	=	$user_right["right_hp_main"]["key"] == $mysql_keys["right_hp_main"]; ?>
											<li><a class="gn-icon gn-icon-gears adminSettings <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminSettingsInit();\"" : ""; ?>><?php echo $language['settings']; ?></a></li>
											
											<?php $permission	=	$user_right["right_hp_ts3"]["key"] == $mysql_keys["right_hp_ts3"]; ?>
											<li><a class="gn-icon gn-icon-globe adminInstanz <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminInstanzInit();\"" : ""; ?>><?php echo $language['instances']; ?></a></li>
											
											<?php $permission	=	($user_right["right_hp_user_edit"]["key"] == $mysql_keys["right_hp_user_edit"] || $user_right["right_hp_user_create"]["key"] == $mysql_keys["right_hp_user_create"] || $user_right["right_hp_user_delete"]["key"] == $mysql_keys["right_hp_user_delete"]); ?>
											<li><a class="gn-icon gn-icon-users adminUser <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminUserInit();\"" : ""; ?>><?php echo $language['client']; ?></a></li>
											
											<?php if(USE_MAILS == "true") { ?>
												<?php $permission	=	$user_right["right_hp_mails"]["key"] == $mysql_keys["right_hp_mails"]; ?>
												<li><a class="gn-icon gn-icon-mail adminMail <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminMailInit();\"" : ""; ?>><?php echo $language['mail_settings']; ?></a></li>
											<?php }; ?>
											
											<?php $permission	=	$user_right["right_hp_logs"]["key"] == $mysql_keys["right_hp_logs"]; ?>
											<li><a class="gn-icon gn-icon-archive adminLogs <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"adminLogsInit();\"" : ""; ?>><?php echo $language['logs']; ?></a></li>
										</ul>
									</li>
									<?php if($Moduls['webinterface'] == "true" && ($user_right['right_web']['key'] == $mysql_keys['right_web'] || $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])) { ?>
										<li class="webinterfacearea">
											<a class="gn-icon gn-icon-ts" style="cursor:default !important;"><?php echo $language['interface']; ?></a>
											<ul class="gn-submenu">
												<?php $permission	=	$user_right["right_web"]["key"] == $mysql_keys["right_web"]; ?>
												<li><a class="gn-icon gn-icon-server teamspeakServer <?php echo (!$permission) ? "text-danger" : ""; ?>" onClick="teamspeakServerInit();"><?php echo $language['server']; ?></a></li>
												
												<?php $permission	=	$user_right["right_web_server_create"]["key"] == $mysql_keys["right_web_server_create"]; ?>
												<li><a class="gn-icon gn-icon-paint-brush teamspeakServerCreate <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"teamspeakServerCreateInit();\"" : ""; ?>><?php echo $language['create_server']; ?></a></li>
												<?php
													$wantServer = scandir(__dir__.'/../../files/wantServer/');
													if(count($wantServer) > 2) { ?>
														<li><a class="gn-icon gn-icon-list teamspeakServerRequests <?php echo (!$permission) ? "text-danger" : ""; ?>" <?php echo ($permission) ? "onClick=\"teamspeakServerRequestsInit();\"" : ""; ?>><?php echo $language['server_requests']; ?></a></li>
												<?php }; ?>
											</ul>
										</li>
									<?php }; ?>
									<li>
										<a class="gn-icon gn-icon-ticket" style="cursor:default !important;"><?php echo $language['ticket_system']; ?></a>
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-ticket ticketMain" onClick="ticketInit();"><?php echo $language['tickets']; ?></a></li>
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
										<ul class="gn-submenu">
											<li><a class="gn-icon gn-icon-arrow-left" onClick="goBackToMain();"><?php echo $language['back']; ?></a></li>
										</ul>
									</li>
									<?php if(isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<li>
											<a class="gn-icon gn-icon-server" style="cursor:default !important;">Server</a>
											<ul class="gn-submenu">
												<li onClick="toggleStartStopTeamspeakserver('<?php echo $LinkInformations['sid']; ?>', '<?php echo $LinkInformations['instanz']; ?>', '<?php echo $server['data']['virtualserver_port']; ?>')">
													<input id="serverStartStopTiny" type="checkbox" data-width="96%" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="<?php echo $language['online']; ?>" data-off="<?php echo $language['offline']; ?>" <?php if($server['data']['virtualserver_status'] == 'online') { echo 'checked'; } ?>>
												</li>
											</ul>
										</li>
									<?php }; ?>
									<li>
										<a class="gn-icon gn-icon-ts" style="cursor:default !important;"><?php xssEcho($server['data']['virtualserver_name']); ?></a>
										<ul class="gn-submenu">
											<li>
												<a class="gn-icon gn-icon-eye teamspeakView" onClick="teamspeakViewInit();"><?php echo $language['server_overview']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_protokoll') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-list teamspeakProtokol <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakProtokolInit();" : ""; ?>"><?php echo $language['protokoll']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_mass_actions') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-users teamspeakMassActions <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakMassActionsInit();" : ""; ?>"><?php echo $language['mass_actions']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_icons') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-file teamspeakIcons <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakIconsInit();" : ""; ?>"><?php echo $language['icons']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_clients') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
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
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_bans') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-user-times teamspeakBans <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBansInit();" : ""; ?>"><?php echo $language['bans']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_token') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-key teamspeakToken <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakTokenInit();" : ""; ?>"><?php echo $language['token']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_file_transfer') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-file teamspeakFilelist <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakFilelistInit();" : ""; ?>"><?php echo $language['filelist']; ?></a>
											</li>
											<li>
												<?php
													if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_backups') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
													{
														$permission		=	false;
													}
													else
													{
														$permission		=	true;
													};
												?>
												<a class="gn-icon gn-icon-upload teamspeakBackup <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBackupsInit();" : ""; ?>"><?php echo $language['backups']; ?></a>
											</li>
										</ul>
									</li>
								</ul>
							</div>
						</nav>
					<?php }; ?>
				</div>
			<?php }; ?>
			<a class="navbar-brand hidden-xs-down pull-xs-left" href="#"><?php xssEcho(HEADING); ?></a>
			<?php if(empty($LinkInformations)) { ?>
				<ul class="nav navbar-nav pull-xs-right text-uppercase hidden-md-down">
					<li class="nav-item">
						<a class="nav-link mainMain" href="#"><i class="fa fa-fw fa-home"></i> <?php echo $language['main_site']; ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainApplyForServer" href="#" style="<?php if($Moduls['free_ts3_server_application'] != "true") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-edit"></i> <?php echo $language['apply_for_server']; ?></a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainMasterserver" href="#" style="<?php if($Moduls['masterserver'] != "true" || MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-eye"></i> <?php echo $language['masterserver']; ?></a>
					</li>
				</ul>
				<ul class="nav navbar-nav pull-xs-right text-uppercase hidden-lg-up">
					<li class="nav-item">
						<a class="nav-link mainMain" href="#" style="font-size:1.2em;"><i class="fa fa-fw fa-home"></i>&nbsp;</a>
					</li>
					
					<li class="nav-item">
						<a class="nav-link mainApplyForServer" href="#" style="font-size:1.2em;<?php if($Moduls['free_ts3_server_application'] != "true") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-edit"></i>&nbsp;</a>
					</li>
					<li class="nav-item">
						<a class="nav-link mainMasterserver" href="#" style="font-size:1.2em;<?php if($Moduls['masterserver'] != "true" || MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "") { echo "display:none;"; }; ?>"><i class="fa fa-fw fa-eye"></i>&nbsp;</a>
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
				if(!$LoggedIn)
				{
					include(__DIR__."/../login/web_login.php");
				}
				else
				{
					if(empty($LinkInformations))
					{
						include(__DIR__."/../login/web_logged.php");
					}
					else
					{
						include(__DIR__."/../login/web_logged_serverview.php");
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

<!-- Javascripte Laden -->
<script src="js/jquery/jquery-2.2.0.js"></script>
<script src="js/bootstrap/tether.js"></script>
<script src="js/bootstrap/bootstrap.js"></script>
<script src="js/bootstrap/bootstrap-notify.js"></script>
<script src="js/sonstige/classie.js"></script>
<script src="js/codrops/gnmenu.js"></script>
<script>
	var ts3_server_create_default 	= 	<?php echo json_encode($ts3_server_create_default); ?>,
		jsonLang					=	'<?php echo str_replace('\"', "", json_encode($language)); ?>',
		lang						=	JSON.parse(jsonLang),
		logged 						=	'<?php echo ($LoggedIn) ? "true" : "false"; ?>',
		wantServer 					= 	new Array(),
		checkClientInterval			= 	<?php echo CHECK_CLIENT_PERMS; ?>,
		updateAvalible				=	"<?php echo (isUpdatePossible()) ? "true" : "false"; ?>",
		hasPermission				=	"<?php echo $hasPermission; ?>",
		timer						=	10,
		emailRegex					=	/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	if(logged == "true")
	{
		new gnMenu( document.getElementById( 'gn-menu' ) );
	};
	
	// Updateinfo
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
<script src="js/webinterface/main.js"></script>