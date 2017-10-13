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
	require_once(__DIR__."/../../config/instance.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	require_once(__DIR__."/../../php/functions/functionsTicket.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		For the Diagramms
	*/
	$arrayOfSlots	=	array();
	
	/*
		Get Diagramm backgroundColor
	*/
	if(STYLE != "")
	{
		$data 							= 	file("../../css/themes/".STYLE.".css");
	}
	else
	{
		$data 							= 	file("../../css/style.css");
	};
	
	foreach($data as $line)
	{
		if(strpos($line, "#dashboardbgcolor") !== false)
		{
			$tmpLine					=	explode(":", $line);
			$dashboardBgColor			=	trim($tmpLine[1]);
			
			break;
		};
	};
?>

<div id="activeDashboard"></div>

<?php if(CUSTOM_DASHBOARD_PAGE == "true") {
		include(__DIR__."/../../config/custompages/custom_dashboard.php");
	} else { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-child"></i> Hey <?php echo $_SESSION['user']['benutzer']; ?>!</h4>
		</div>
		<div class="card-block">
			<p><?php echo $language['dashboard_welcome_info']; ?></p>
			<p><?php echo $language['dashboard_welcome_info2']; ?></p>
			<p><?php echo $language['dashboard_welcome_info3']; ?></p>
		</div>
	</div>
<?php }; ?>

<!-- Serverübersicht -->
<?php if($mysql_modul['webinterface'] == "true")
{
	foreach($ts3_server AS $instanz => $server)
	{
		if(hasUserInstanz($_SESSION['user']['id'], "$instanz") || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'])
		{
			$errors						=	"";
			$connection					=	true;
			$tsAdmin 					= 	new ts3admin($server['ip'], $server['queryport']);
			
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$loginInfo				=	$tsAdmin->login($server['user'], $server['pw']);
				$serverList				=	$tsAdmin->getElement('data', $tsAdmin->serverList());
				
				$arrayOfSlots[$instanz]	=	array();
				$arrayOfSlots[$instanz]	=	getTeamspeakslotsArray($serverList, $instanz, false);
				$getTeamspeakInfo		=	getTeamspeakslotsArray($serverList, $instanz, true);
				
				if($loginInfo['success'] === false)
				{
					for($i=0; $i+1==count($loginInfo['errors']); $i++)
					{
						$errors		.=  $loginInfo['errors'][$i]."<br />";
					};
				};
			}
			else
			{
				$connection			=	false;
			};
			?>
			
			<div class="card">
				<div class="card-block card-block-header">
					<h4 class="card-title pull-xs-left">
						<i class="fa fa-pie-chart"></i> <?php echo $language['instance']; ?>: 
						<?php 
							if($server['alias'] != '')
							{
								xssEcho($server['alias']);
							}
							else
							{
								xssEcho($server['ip']);
							};
						?>
					</h4>
					<div class="label label-<?php if($connection) { echo "success"; } else { echo "danger"; }?> pull-xs-right" id="instanzTextBox<?php echo $instanz; ?>">
						<?php if($connection) { ?>
							<i class="fa fa-check"></i> <?php echo $language['success']; ?>
						<?php } else { ?>
							<i class="fa fa-close"></i> <?php echo $language['failed']; ?>
						<?php }; ?>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="card-block">
					<?php if(!$connection) { ?>
						<div class="alert alert-danger dashboard-alert-danger">
							<i class="fa fa-ban" aria-hidden="true"></i> <?php echo $language['ts_instanz_connection_failed']; ?>
						</div>
					<?php } else if($errors != "") { ?>
						<div class="alert alert-danger dashboard-alert-danger">
							<i class="fa fa-ban" aria-hidden="true"></i> <?php echo $errors; ?>
						</div>
					<?php } else { ?>
						<div class="row">
							<div class="col-lg-4 hidden-md-down">
								<div id="canvas-holder" style="width:100%">
									<canvas id="chart-instanz-<?php echo $instanz; ?>" width="300" height="300" />
								</div>
							</div>
							<div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
								<table class="table table-condensed table-hover">
									<thead>
										<th>
											<?php echo $language['name']; ?>
										</th>
										<th style="text-align: center;">
											<?php echo $language['port']; ?>
										</th>
										<th style="text-align: center;">
											<?php echo $language['status']; ?>
										</th>
										<th style="text-align: center;">
											<?php echo $language['client']; ?>
										</th>
									</thead>
									<tbody class="table-dashboard">
										<?php foreach($getTeamspeakInfo AS $server) {
											if(isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_view') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
												<tr class="rightClickContextMenu" onClick="showTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>');">
													<td port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														permission="<?php echo (isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php xssEcho($server['virtualserver_name']); ?>
													</td>
													<td style="text-align: center;" port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														permission="<?php echo (isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php echo $server['virtualserver_port']; ?>
													</td>
													<td port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>" style="text-align: center;cursor: pointer !important;"
														id="status-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" class="<?php echo ($server['virtualserver_status'] == "online") ? "text-success" : "text-danger"; ?>"
														permission="<?php echo (isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php xssEcho($server['virtualserver_status']); ?>
													</td>
													<td style="text-align: center;" port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														id="clients-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" 
														permission="<?php echo (isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php if($server['virtualserver_status'] == "online")
														{
															echo $server['virtualserver_clientsonline']." / ".$server['virtualserver_maxclients'];
														}
														else
														{
															echo "-";
														}; ?>
													</td>
												</tr>
											<?php };
										}; ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php }; ?>
					
					<ul id="contextMenu" class="dropdown-menu" role="menu" >
						<li><a action="show" tabindex="-1" href="#" onClick="return false;"><?php echo $language['server_overview']; ?></a></li>
						<li class="dropdown-divider"></li>
						<li><a id="contextMenuStart" action="start" tabindex="-1" href="#" onClick="return false;"><?php echo $language['server_start']; ?></a></li>
						<li><a id="contextMenuStop" action="stop" tabindex="-1" href="#" onClick="return false;"><?php echo $language['server_stop']; ?></a></li>
					</ul>
				</div>
			</div>
		<?php };
	};
}; ?>
	

<!-- Offene Tickets -->
<?php
	if($user_right['right_hp_ticket_system']['key'] == $mysql_keys['right_hp_ticket_system']) {
		$TicketInformations			=	array();
		$TicketInformations			=	getTicketInformations($_SESSION['user']['id'], true); ?>
		<div class="card">
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-ticket"></i> Tickets</h4>
			</div>
			<div class="card-block">
				<?php if(count($TicketInformations) == 0)
				{
					echo '<p style="text-align: center;">'.$language['no_tickets'].'</p>';
				}
				else
				{
					if(!empty($TicketInformations))
					{
						foreach($TicketInformations AS $text) { ?>
							<div id="MainTicket<?php echo $text['id']; ?>" class="card">
								<div class="card-block card-block-header" style="cursor:pointer;" onClick="slideMe('ticket<?php echo $text['id']; ?>', 'ticketIcon<?php echo $text['id']; ?>');">
									<h4 class="card-title">
										<div class="pull-xs-left">
											<i id="ticketIcon<?php echo $text['id']; ?>" class="fa fa-fw fa-arrow-right"></i> <?php echo "Ticket: ".$text['subject']; ?>
										</div>
										<div id="status<?php echo $text['id']; ?>" class="label label-<?php if($text['status'] == "open") { echo "success"; } else { echo "danger"; }?> pull-xs-right">
											<?php if($text['status'] == "open") { ?>
												<i class="fa fa-check"></i> <?php echo $language['open']; ?>
											<?php } else { ?>
												<i class="fa fa-close"></i> <?php echo $language['closed']; ?>
											<?php } ?>
										</div>
										<div style="clear:both;"></div>
									</h4>
								</div>
								<div class="card-block" style="display:none;" id="ticket<?php echo $text['id']; ?>">
									<div class="row" style="padding:.75rem;">
										<div class="col-lg-1"></div>
										<div class="col-lg-5 col-md-6">
											<?php echo $language['ticket_id']; ?>:
										</div>
										<div class="col-lg-5 col-md-6" style="text-align:center;">
											<b><?php echo $text['id']; ?></b>
										</div>
										<div class="col-lg-1"></div>
									</div>
									<div class="row" style="padding:.75rem;">
										<div class="col-lg-1"></div>
										<div class="col-lg-5 col-md-6">
											<?php echo $language['area']; ?>:
										</div>
										<div class="col-lg-5 col-md-6" style="text-align:center;">
											<b><?php xssEcho($text['department']); ?></b>
										</div>
										<div class="col-lg-1"></div>
									</div>
									<div class="row" style="padding:.75rem;">
										<div class="col-lg-1"></div>
										<div class="col-lg-5 col-md-6">
											<?php echo $language['create_on']; ?>:
										</div>
										<div class="col-lg-5 col-md-6" style="text-align:center;">
											<b><?php echo changeTimestamp($text['dateAded']); ?></b>
										</div>
										<div class="col-lg-1"></div>
									</div>
									<div class="row" style="padding:.75rem;">
										<div class="col-lg-1"></div>
										<div class="col-lg-5 col-md-6">
											<?php echo $language['last_activity']; ?>:
										</div>
										<div class="col-lg-5 col-md-6" style="text-align:center;">
											<b><?php echo changeTimestamp($text['dateActivity']); ?></b>
										</div>
										<div class="col-lg-1"></div>
									</div>
									<div class="row" style="padding:.75rem;">
										<div class="col-lg-1"></div>
										<div class="col-lg-5 col-md-6">
											<?php echo $language['closed']; ?>:
										</div>
										<div id="closedDate<?php echo $text['id']; ?>" class="col-lg-5 col-md-6" style="text-align:center;font-weight:bold;">
											<?php echo changeTimestamp($text['dateClosed']); ?>
										</div>
										<div class="col-lg-1"></div>
									</div>
									
									<!-- Nachrichten -->
									<div style="margin-left:20px;margin-right:20px;">
										<div class="alert alert-<?php echo ($text['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
											<div style="float:left;">
												<?php echo $language['client']; ?>: <b><?php echo getUsernameFromPk($text['pk']); ?></b>
											</div>
											<div style="float:right;">
												<?php echo changeTimestamp($text['dateAded']); ?>
											</div>
											<div style="clear:both;" class="alert alert-<?php echo ($text['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
												<?php xssEcho(urldecode($text['msg'])); ?>
											</div>
										</div>
										
										<?php 
											$answered		=	view_answered($text['id']);
											if(!empty($answered))
											{
												foreach($answered AS $answer) { ?>
													<div class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
														<div style="float:left;">
															<?php echo $language['client']; ?>: <b><?php echo $answer['moderator']; ?></b>
														</div>
														<div style="float:right;">
															<?php echo changeTimestamp($answer['dateAded']); ?>
														</div>
														<div style="clear:both;" class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
															<?php xssEcho(urldecode($answer['msg'])); ?>
														</div>
													</div>
											<?php };
											}; ?>
										
										<?php if($text['status'] == "open") { ?>
											<div id="answerbox<?php echo $text['id']; ?>" class="alert alert-warning">
												<?php echo $language['answer']; ?>:
												<div class="alert alert-warning">
													<textarea id="answer<?php echo $text['id']; ?>" style="width:100%;" rows="5"></textarea>
												</div>
												<div class="row">
													<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
														<button onClick="closeTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> <?php echo $language['close_ticket']; ?></button>
													</div>
													<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-md-offset-4 col-lg-offset-4">
														<button onClick="answerTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
													</div>
												</div>
											</div>
										<?php }; ?>
										<?php if(TICKET_CAN_BE_DELETED == "true") { ?>
											<div class="row" style="<?php if($text['status'] == "open") { echo "display: none;"; }; ?>" id="deleteTicketSection<?php echo $text['id']; ?>">
												<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
													<button onClick="deleteTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> <?php echo $language['delete_ticket']; ?></button>
												</div>
											</div>
										<?php }; ?>
									</div>
								</div>
							</div>
						<?php };
					};
				}; ?>
			</div>
		</div>
<?php }; ?>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-contextmenu.js"></script>
<script src="js/chart/Chart.js"></script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/webinterface/ticket.js"></script>

<script>
 	$(function ()
	{
		var arrayOfSlots					=	JSON.parse('<?php echo str_replace ("'", "", json_encode($arrayOfSlots)); ?>');
		
		// Menu Rechtsklick
		$(".rightClickContextMenu").contextMenu({
			menuSelector: "#contextMenu",
			menuSelected: function (invokedOn, selectedMenu) {
				if(!$(this).hasClass("text-danger") && selectedMenu.attr("action") != "none")
				{
					var sid			=	invokedOn.attr("sid");
					var instanz		=	invokedOn.attr("instanz");
					var port		=	invokedOn.attr("port");
					var action		=	selectedMenu.attr("action");
					
					if(action == "show")
					{
						showTeamspeakserver(sid, instanz);
					}
					else if(action == "start")
					{
						startTeamspeakserver(sid, instanz, port);
					}
					else if(action == "stop")
					{
						stopTeamspeakserver(sid, instanz, port);
					};
				};
			},
			menuShow: function (invokedOn) {
				var permission	=	invokedOn.attr("permission");
				
				if(permission == "true")
				{
					$("#contextMenuStart").removeClass("text-danger");
					$("#contextMenuStop").removeClass("text-danger");
				}
				else
				{
					$("#contextMenuStart").addClass("text-danger");
					$("#contextMenuStop").addClass("text-danger");
				};
			}
		});
		
		// Diagramm
		var pieObject				=	[];
		var instanzNumber			=	new Array();
		var tmpInstanzName			=	0;
		
		for (var instanz in arrayOfSlots)
		{
			var datasets													=	new Object();
			var serverNumber												=	0;
			datasets['datasets']											=	new Array();
			datasets['labels']												=	["<?php echo $language['client']; ?> Online", "Free Slots"];
			datasets['datasets']['label']									=	new Object();
			
			if(arrayOfSlots[instanz] != false && arrayOfSlots[instanz] != null)
			{
				for (var server of arrayOfSlots[instanz])
				{
					if(typeof(server['virtualserver_maxclients']) == "undefined" && typeof(server['virtualserver_name']) != "undefined")
					{
						server['virtualserver_maxclients']					=	32;
					};
					
					datasets['datasets'][serverNumber]						=	new Object();
					datasets['datasets'][serverNumber]['data']				=	[server['virtualserver_clientsonline'], server['virtualserver_maxclients'] - server['virtualserver_clientsonline']];
					datasets['datasets'][serverNumber]['backgroundColor']	=	["<?php echo $dashboardBgColor; ?>", "green"];
					if(typeof(server['virtualserver_name']) != "undefined")
					{
						datasets['datasets']['label'][serverNumber]			=	server['virtualserver_name'];
					}
					else
					{
						datasets['datasets']['label'][serverNumber]			=	server['instanz_name'];
					};
					serverNumber++;
				};
				
				var config =
				{
					type: 'pie',
					data: datasets,
					options:
					{
						responsive: true,
						tooltips:
						{
							callbacks:
							{
								label: function(tooltipItem, data)
								{
									var datasetLabel = data.datasets.label[tooltipItem.datasetIndex]+": ";
									var label = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
									return datasetLabel+label+" "+data.labels[tooltipItem.index];
								}
							}
						}
					},
				};
				
				pieObject.push(new Chart(document.getElementById("chart-instanz-"+instanz).getContext("2d"), config));
				instanzNumber[tmpInstanzName]		=	instanz;
				tmpInstanzName++;
			};
		};
		
		
		// Update Chart
		var chartUpdate = function() {
			if(!document.getElementById("activeDashboard"))
			{
				return;
			};
			
			// Array löschen
			arrayOfSlots							=	new Object();
			
			// Informationen aktualisieren
			for (var instanz of instanzNumber)
			{
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsTeamspeakPost.php",
					data: {
						action:		'getTeamspeakslots',
						instanz:	instanz
					},
					async: false,
					success: function(data){
						// Neue Daten übergeben
						var informations			= 	JSON.parse(data);
						arrayOfSlots[instanz]		=	informations;
					}
				});
			};
			
			
			tmpInstanzName							=	0;
			for(pie of pieObject)
			{
				var tmpServerNumber					=	0;
				
				for (var server of arrayOfSlots[instanzNumber[tmpInstanzName]])
				{
					if(typeof(server['virtualserver_maxclients']) == "undefined" && typeof(server['virtualserver_name']) != "undefined")
					{
						server['virtualserver_maxclients']					=	32;
					};
					
					pie.config.data.datasets[tmpServerNumber].data[0] 		= 	server['virtualserver_clientsonline'];
					pie.config.data.datasets[tmpServerNumber].data[1] 		= 	server['virtualserver_maxclients'] - server['virtualserver_clientsonline'];
					
					if(typeof(server['virtualserver_id']) != "undefined")
					{
						if(server['virtualserver_status'] == "online" && document.getElementById("clients-"+tmpInstanzName+"-"+server['virtualserver_id']))
						{
							document.getElementById("clients-"+tmpInstanzName+"-"+server['virtualserver_id']).innerText = server['virtualserver_clientsonline']+" / "+server['virtualserver_maxclients'];
						};
					};
					
					tmpServerNumber++;
				};
				
				pie.update();
				tmpInstanzName++;
			};
			
			setTimeout (function() { chartUpdate(); }, 10000);
		};
		
		setTimeout (function() { chartUpdate(); }, 1000);
	});
</script>

<script src="js/sonstige/preloader.js"></script>