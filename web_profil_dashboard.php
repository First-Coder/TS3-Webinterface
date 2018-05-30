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
	require_once("functionsTeamspeak.php");
	require_once("functionsTicket.php");
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("\?", $_SERVER['HTTP_REFERER'], -1);
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	function sys_getloadavg_hack() 
	{ 
		$str = substr(strrchr(shell_exec("uptime"),":"),1); 
		$avs = array_map("trim",explode(",",$str)); 

		return $avs; 
	};
	
	// written by cjmwid https://r4p3.net/members/cjmwid.5528/
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		$tmp 				=	'WindowsStats.tmp';
		if(file_exists($tmp) && (filemtime($tmp) > (time() - 60 * DASHBOARD_REFRESHTIME )))
		{
			$file			= file($tmp);
		}
		else
		{
			exec('systeminfo', $retval);
			$time 			= 	substr($retval[11] ,17);	
			$uparr 			= 	explode(",",$time);
			$uptime 		= 	strtotime($uparr[0].$uparr[1]);
			$ut 			= 	time() - $uptime;
			$dtF 			= 	new \DateTime('@0');
			$dtT 			= 	new \DateTime("@$ut");
			$data 			=   $dtF->diff($dtT)->format('%a ').$language['days'].$dtF->diff($dtT)->format(' %h ').$language['hours'].$dtF->diff($dtT)->format(' %i ').$language['minutes'].PHP_EOL;	
			$ram 			= 	shell_exec('systeminfo | find "In Use:"');
			$data		   .=	substr($ram,27);
			$load			=	array();
			$wmi 			= 	new COM("Winmgmts://");
			$server 		= 	$wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");  
			$cpu_num 		= 	0;
			$load_total 	= 	0;
			foreach($server as $cpu)
			{
				$cpu_num++;
				$load_total+= 	$cpu->loadpercentage;
			};
			$load[]			= 	round($load_total/$cpu_num);
			$data		   .= implode(' ',$load)."%";
			file_put_contents($tmp, $data);
			$file			= file($tmp);
		};
	};
	
	/*
		For the Diagramms
	*/
	$arrayOfSlots	=	array();
?>

<div id="activeDashboard"></div>

<!-- Serverauslastung -->
<?php if($user_right['right_hp_main'] == $mysql_keys['right_hp_main']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-server"></i> <?php echo $language['system_utilization']; ?></h4>
		</div>
		<div class="card-block">
			<table class="table table-condensed">
				<tbody>
					<tr>
						<td>
							<?php echo $language['rootserver_online_since']; ?>:
						</td>
						<td>
							<?php 
								if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
								{
									// written by cjmwid https://r4p3.net/members/cjmwid.5528/
									echo $file[0];
								}
								else
								{
									$uptime_array 	= 	explode(" ", exec("cat /proc/uptime")); 
									$seconds 		= 	round($uptime_array[0], 0); 
									$minutes 		= 	$seconds / 60; 
									$hours 			=	$minutes / 60; 
									$days 			= 	floor($hours / 24); 
									$hours 			= 	floor($hours - ($days * 24)); 
									$minutes 		= 	floor($minutes - ($days * 24 * 60) - ($hours * 60)); 
									$seconds 		= 	floor($seconds - ($days * 24 * 60 * 60) - ($hours * 60 * 60) - ($minutes * 60)); 
									$uptime_array 	= 	array($days, $hours, $minutes, $seconds); 
									echo $days . " " . $language['days'] . " " . $hours . " " . $language['hours'] . " " . $minutes . " " . $language['minutes'];
								};
							?>
						</td>
					</tr>
					<tr>
						<td>
							RAM <?php echo $language['capacity_utilization']; ?>:
						</td>
						<td>
							<?php
								if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
								{
									// written by cjmwid https://r4p3.net/members/cjmwid.5528/
									echo $file[1];
								}
								else
								{
									$free 				= 	shell_exec('free');
									$free 				= 	(string)trim($free);
									$free_arr 			= 	explode("\n", $free);
									$mem 				= 	explode(" ", $free_arr[1]);
									$mem 				= 	array_filter($mem);
									$mem 				= 	array_merge($mem);
									$memory_usage 		= 	$mem[2]/$mem[1]*100;
									echo number_format($memory_usage, 2) . " %";
								};
							?>
						</td>
					</tr>
					<tr>
						<td>
							CPU <?php echo $language['capacity_utilization']; ?>:
						</td>
						<td>
							<?php
								if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
								{
									// written by cjmwid https://r4p3.net/members/cjmwid.5528/
									echo $file[2];
								}
								else
								{
									echo sys_getloadavg_hack()[0];
								};
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php } else { ?>
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
		if(hasUserInstanz($_SESSION['user']['id'], "$instanz") || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'])
		{
			$connection				=	checkTS3Connection($server['ip'], $server['queryport'], $server['user'], $server['pw']);
			$getTeamspeakInfo		=	getTeamspeakslots($instanz, true);
			$loginConnection		=	true;
			$arrayOfSlots[$instanz]	=	array();
			$arrayOfSlots[$instanz]	=	getTeamspeakslots($instanz); ?>
			
			<div class="card">
				<div class="card-block card-block-header">
					<h4 class="card-title pull-xs-left">
						<i class="fa fa-pie-chart"></i> <?php echo $language['instance']; ?>: 
						<?php 
							if($server['alias'] != '')
							{
								echo $server['alias'];
							}
							else
							{
								echo $server['ip'];
							};
						?>
					</h4>
					<div class="label label-<?php if($connection) { echo "success"; } else { echo "danger"; }?> pull-xs-right" id="instanzTextBox<?php echo $instanz; ?>">
						<?php if($connection) { ?>
							<i class="fa fa-check"></i> <?php echo $language['success']; ?>
						<?php } else { ?>
							<i class="fa fa-close"></i> <?php echo $language['failed']; ?>
						<?php } ?>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="card-block">
					<?php
						$errors			=	"";
						$tsAdmin 		= 	new ts3admin($server['ip'], $server['queryport']);
						
						if($tsAdmin->getElement('success', $tsAdmin->connect()))
						{
							$loginInfo	=	$tsAdmin->login($server['user'], $server['pw']);
							
							if($loginInfo['success'] === false)
							{
								$loginConnection	=	false;
								for($i=0; $i+1==count($loginInfo['errors']); $i++)
								{
									$errors			.=  $loginInfo['errors'][$i]."<br />";
								};
							};
						}; 
					?>
					
					<?php if(!$connection) { ?>
						<div class="alert-danger" style="margin: 20px;text-align: center;font-weight: bold;">
							<?php echo $language['teamspeak_instanz_connection_failed']; ?>
						</div>
					<?php } else if(!$loginConnection) { ?>
						<div class="alert-danger" style="margin: 20px;text-align: center;font-weight: bold;">
							<?php echo $errors; ?>
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
											Name
										</th>
										<th style="text-align: center;">
											Port
										</th>
										<th style="text-align: center;">
											Status
										</th>
										<th style="text-align: center;">
											<?php echo $language['client']; ?>
										</th>
									</thead>
									<tbody class="table-dashboard">
										<?php foreach($getTeamspeakInfo AS $server) {
											if(strpos($user_right['ports']['right_web_server_view'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
												<tr onClick="showTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>');">
													<td port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														permission="<?php echo (strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php echo htmlspecialchars($server['virtualserver_name']); ?>
													</td>
													<td style="text-align: center;" port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														permission="<?php echo (strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php echo $server['virtualserver_port']; ?>
													</td>
													<td port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>" style="text-align: center;cursor: pointer !important;"
														id="status-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" class="<?php echo ($server['virtualserver_status'] == "online") ? "text-success" : "text-danger"; ?>"
														permission="<?php echo (strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
														<?php echo $server['virtualserver_status']; ?>
													</td>
													<td style="text-align: center;" port="<?php echo $server['virtualserver_port']; ?>" instanz="<?php echo $instanz; ?>" sid="<?php echo $server['virtualserver_id']; ?>"
														id="clients-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" 
														permission="<?php echo (strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) ? "true" : "false"; ?>">
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
						<p style="text-align: center;margin-top: 10px;"><?php echo $language['dashbord_right_click_info']; ?></p>
					<?php }; ?>
					
					<ul id="contextMenu" class="dropdown-menu" role="menu" >
						<li><a action="show" tabindex="-1" href="#" onClick="return false;"><?php echo $language['ts3_main_settings']; ?></a></li>
						<li class="dropdown-divider"></li>
						<li><a id="contextMenuStart" action="start" tabindex="-1" href="#" onClick="return false;"><?php echo $language['ts_server_start']; ?></a></li>
						<li><a id="contextMenuStop" action="stop" tabindex="-1" href="#" onClick="return false;"><?php echo $language['ts_server_stop']; ?></a></li>
					</ul>
				</div>
			</div>
		<?php };
	};
}; ?>
	

<!-- Offene Tickets -->
<?php
	if($user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system']) {
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
					foreach($TicketInformations AS $text) { ?>
						<div id="MainTicket<?php echo $text['id']; ?>" class="card">
							<div class="card-block card-block-header" style="cursor:pointer;" onClick="slideTicket('ticket<?php echo $text['id']; ?>', 'ticketIcon<?php echo $text['id']; ?>');">
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
										<?php echo $language['area']; ?>:
									</div>
									<div class="col-lg-5 col-md-6" style="text-align:center;">
										<b><?php echo htmlspecialchars($text['department']); ?></b>
									</div>
									<div class="col-lg-1"></div>
								</div>
								<div class="row" style="padding:.75rem;">
									<div class="col-lg-1"></div>
									<div class="col-lg-5 col-md-6">
										<?php echo $language['ts3_create_on']; ?>:
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
											<?php echo htmlspecialchars(urldecode($text['msg'])); ?>
										</div>
									</div>
									
									<?php foreach(view_answered($text['id']) AS $answer) { ?>
										<div class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
											<div style="float:left;">
												<?php echo $language['client']; ?>: <b><?php echo $answer['moderator']; ?></b>
											</div>
											<div style="float:right;">
												<?php echo changeTimestamp($answer['dateAded']); ?>
											</div>
											<div style="clear:both;" class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
												<?php echo htmlspecialchars(urldecode($answer['msg'])); ?>
											</div>
										</div>
									<?php }; ?>
									
									<?php if($text['status'] == "open") { ?>
										<div id="answerbox<?php echo $text['id']; ?>" class="alert alert-warning">
											<?php echo $language['answer']; ?>:
											<div class="alert alert-warning">
												<textarea id="answer<?php echo $text['id']; ?>" style="width:100%;" rows="5"></textarea>
											</div>
											<div class="row">
												<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
													<button onClick="closeTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> Ticket <?php echo strtolower($language['tick_close']); ?></button>
												</div>
												<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-md-offset-4 col-lg-offset-4">
													<button onClick="answerTicket('<?php echo $text['id']; ?>', '<?php echo $_SESSION['user']['id']; ?>', '<?php echo $_SESSION['user']['benutzer']; ?>');" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
												</div>
											</div>
										</div>
									<?php }; ?>
									<div class="row" style="<?php if($text['status'] == "open") { echo "display: none;"; }; ?>" id="deleteTicketSection<?php echo $text['id']; ?>">
										<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
											<button onClick="deleteTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> Ticket <?php echo strtolower($language['delete']); ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php };
				}; ?>
			</div>
		</div>
<?php }; ?>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var ts_server_started			=	'<?php echo $language['ts_server_started']; ?>';
	var ts_server_stoped			=	'<?php echo $language['ts_server_stoped']; ?>';
	var ts_server_deleted			=	'<?php echo $language['ts_server_deleted']; ?>';
	
	var ticket_add_moderator			=	'<?php echo $language['ticket_add_moderator']; ?>';
	var ticket_edit_moderator			=	'<?php echo $language['ticket_edit_moderator']; ?>';
	var ticket_delete_moderator			=	'<?php echo $language['ticket_delete_moderator']; ?>';
	var ticket_fill_all					=	'<?php echo $language['ticket_fill_all']; ?>';
	var ticket_create					=	'<?php echo $language['ticket_create']; ?>';
	var ticket_close					=	'<?php echo $language['ticket_close']; ?>';
	var ticket_answer					=	'<?php echo $language['ticket_answer']; ?>';
	var closedText						=	'<?php echo $language['closed']; ?>';
	var ticket_deleted					=	'<?php echo $language['ticket_deleted']; ?>';
	
	var arrayOfSlots				=	JSON.parse('<?php echo str_replace ("'", "", json_encode($arrayOfSlots)); ?>');
</script>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-contextmenu.js"></script>
<script src="js/chart/Chart.js"></script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/webinterface/ticket.js"></script>

<script>
 	$(function ()
	{
		// Menu Rechtsklick
		$("tr").contextMenu({
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
			//console.log(datasets);
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
					datasets['datasets'][serverNumber]['backgroundColor']	=	["green", "grey"];
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
				var dataString						=	'action=getTeamspeakslots&instanz='+instanz;
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					cache: true,
					async: false,
					success: function(data){
						var informations			= 	JSON.parse(data);
						arrayOfSlots[instanz]		=	informations;
					}
				});
			};
			
			// Neue Daten übergeben
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