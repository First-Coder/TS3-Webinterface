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
		Has the Client the Permission
	*/
	if($user_right['right_web']['key'] != $mysql_keys['right_web'] || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
?>

<!-- Global Message / Poke -->
<?php if($user_right['right_web_global_message_poke']['key'] == $mysql_keys['right_web_global_message_poke']) { ?>
	<div class="card" id="globalServerlist">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<i class="fa fa-fw fa-edit"></i> <?php echo $language['global_msg_poke']; ?>
			</h4>
		</div>
		<div class="card-block">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="input-group">
						<input type="text" id="instanzMessagePokeContent" style="margin-top:0;" class="form-control" placeholder="<?php echo $language['message']; ?>">
						<span class="input-group-btn" data-toggle="buttons">
							<label class="btn btn-custom active" id="instanzMode" style="margin: 0;border-radius: 0;">
								<input type="radio" autocomplete="off" checked><i class="fa fa-pencil"></i> <?php echo $language['message']; ?>
							</label>
							<label class="btn btn-custom" style="margin: 0;border-radius: 0;">
								<input type="radio" autocomplete="off"><i class="fa fa-hand-o-up"></i> <?php echo $language['poke']; ?>
							</label>
						</span>
					</div>
				</div>
			</div>
			<div class="row" style="text-align:center;">
				<?php if(count($ts3_server) <= 3) { ?>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
						<label class="c-input c-radio">
							<input value="all" name="instanzMsgPoke" type="radio" checked>
							<span class="c-indicator"></span>
							<?php echo $language['to_all_instanz']; ?>
						</label>
					</div>
					<?php foreach($ts3_server AS $instanz => $values) { ?>
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
							<label class="c-input c-radio">
								<input value="<?php echo $instanz; ?>" name="instanzMsgPoke" type="radio">
								<span class="c-indicator"></span>
								<?php xssEcho($values['alias']); ?>
							</label>
						</div>
					<?php };
				} else { ?>
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 margin-row">
						<select id="selectInstanzMsgPoke" class="form-control c-select" style="width: 100%;">
							<option value="all" selected><?php echo $language['to_all_instanz']; ?></option>
							<?php foreach($ts3_server AS $instanz => $values) { ?>
								<option value="<?php echo $instanz; ?>"><?php xssEcho($values['alias']); ?></option>
							<?php }; ?>
						</select>
					</div>
				<?php }; ?>
			</div>
			<button onClick="instanzMessagePoke()" class="btn btn-success" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
		</div>
	</div>
<?php } else { ?>
	<div id="globalServerlist"></div>
<?php }; ?>

<!-- Teamspeakserver -->
<?php
	$globalServers							=	array();
	$globalServerCount						=	0;
	
	foreach($ts3_server AS $instanz => $values)
	{
		$tsAdmin 							= 	new ts3admin($values['ip'], $values['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($values['user'], $values['pw']);
			
			$servers 						= 	$tsAdmin->serverList();
			$globalServers[$instanz]		=	array();
			
			$tsAdmin->logout();
			
			foreach($servers['data'] AS $number => $server)
			{
				if(isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_view') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'])
				{
					$globalServers[$instanz][$number] = $server;
					$globalServerCount++;
				};
			};
		}; 
	};
	
	foreach($globalServers AS $instanz => $myservers)
	{
		if($globalServerCount <= 5)
		{
			foreach($myservers AS $server)
			{ ?>
				<div class="card" id="serverbox_<?php echo $instanz.'_'.$server['virtualserver_port']; ?>">
					<div class="card-block card-block-header">
						<h4 class="card-title">
							<div class="pull-xs-left">
								<img style="margin-top:-5px;" width="30" src="images/tsLogo.png"/> <?php xssEcho($server['virtualserver_name']); ?>
							</div>
							<div class="pull-xs-right hidden-sm-down">
								<?php echo $ts3_server[$instanz]['ip']; ?>:<?php echo $server['virtualserver_port']; ?>
								&nbsp;&nbsp;
								<a href="ts3server://<?php echo $ts3_server[$instanz]['ip']; ?>:<?php echo $server['virtualserver_port']; ?>">
									<i data-tooltip="tooltip" data-placement="top" title="<?php echo $language['ts_connect_to_server']; ?>" class="fa fa-sign-in"></i>
								</a>
							</div>
							<div style="clear:both;"></div>
						</h4>
						<h6 class="card-subtitle text-muted"><i class="fa fa-info"></i> <?php echo $language['server_id'].": ".$server['virtualserver_id']; ?></h6>
					</div>
					<div class="card-block">
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<button onClick="showTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>');" type="button" class="btn btn-info mini-top-bottom-margin" style="width: 100%;"><i class="fa fa-fw fa-eye"></i> <?php echo $language['server_overview']; ?></button>
							</div>
							<?php if(!isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'] && $user_right['right_web_server_delete']['key'] != $mysql_keys['right_web_server_delete'])
							{
								$tmpPermission	=	false;
							}
							else
							{
								$tmpPermission	=	true;
							} ?>
							<div class="<?php echo ($tmpPermission) ? "col-lg-6 col-md-6 col-sm-6" : "col-lg-12 col-md-12 col-sm-12"; ?> col-xs-12">
								<label><?php echo $language['ts3_serverstatus']; ?></label>
								<div iconid="serverstatus-icon-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" class="mini-top-bottom-margin serverstatus <?php echo ($server['virtualserver_status'] == 'online') ? "text-success" : "text-danger"; ?>" style="margin-top: 0;text-align: center;">
									<i id="serverstatus-icon-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" class="fa fa-circle-o"></i> 
									<font id="online-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>"><?php echo ($server['virtualserver_status'] == 'online') ? $language['online'] : $language['offline']; ?></font>
								</div>
								<label><?php echo $language['slots']; ?></label>
								<div class="progress mini-top-bottom-margin" style="margin-top: 0;text-align: center;">
									<div id="progress-bar-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" class="progress-bar text-muted" role="progressbar" style="display: <?php echo (isSet($server['virtualserver_clientsonline'])) ? "inline" : "none"; ?>;width: <?php echo ($server['virtualserver_clientsonline']/$server['virtualserver_maxclients'])*100; ?>%;min-width: 5em;" aria-valuenow="<?php echo $server['virtualserver_clientsonline']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $server['virtualserver_maxclients']; ?>">
										<?php echo $server['virtualserver_clientsonline']; ?>&nbsp;/&nbsp;<?php echo $server['virtualserver_maxclients']; ?>
									</div>
								</div>
							</div>
							<?php if($tmpPermission) { ?>
								<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
									<?php if(isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<button onClick="startTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>');" type="button" class="btn btn-success btn-sm mini-top-bottom-margin" style="width: 100%;"><i class="fa fa-fw fa-play"></i> <?php echo $language['server_start']; ?></button>
										<button onClick="stopTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>');" type="button" class="btn btn-warning btn-sm mini-top-bottom-margin" style="width: 100%;"><i class="fa fa-fw fa-stop"></i> <?php echo $language['server_stop']; ?></button>
									<?php }; ?>
									<?php if($user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete']) { ?>
										<button onClick="AreYouSure('<?php echo $language['delete_server']; ?>', 'deleteTeamspeakserver(\'<?php echo $server['virtualserver_id']; ?>\', \'<?php echo $instanz; ?>\', \'<?php echo $server['virtualserver_port']; ?>\')');" type="button" class="btn btn-danger btn-sm mini-top-bottom-margin" style="width: 100%;"><i class="fa fa-fw fa-trash"></i> <?php echo $language['delete_server']; ?></button>
									<?php }; ?>
								</div>
							<?php }; ?>
						</div>
					</div>
					<div class="card-footer">
						<div class="hidden-sm-down">
							<div class="pull-xs-left">
								<?php
									echo $language['instance'].": ";
									
									if($ts3_server[$instanz]['alias'] != '')
									{
										xssEcho($ts3_server[$instanz]['alias']);
									}
									else
									{
										xssEcho($ts3_server[$instanz]['ip']);
									};
								?>
							</div>
							<div class="pull-xs-right uptime" id="uptime-<?php echo $instanz; ?>-<?php echo $server['virtualserver_id']; ?>" uptime-timestamp="<?php echo $server['virtualserver_uptime']; ?>">
								<?php
									echo $language['online_since'].": ";
									
									if(isset($server['virtualserver_uptime']))
									{
										echo $tsAdmin->convertSecondsToStrTime($server['virtualserver_uptime']);
									}
									else
									{
										echo "-";
									};
								?>
							</div>
							<div style="clear: both;"></div>
						</div>
						<div class="hidden-md-up" style="text-align: center;">
							<?php
								echo $language['instance'].": ";
								
								if($ts3_server[$instanz]['alias'] != '')
								{
									xssEcho($ts3_server[$instanz]['alias']);
								}
								else
								{
									xssEcho($ts3_server[$instanz]['ip']);
								};
							?>
						</div>
					</div>
				</div>
			<?php };
		} else {
			if(!empty($myservers)) { ?>
				<div class="card">
					<div class="card-block card-block-header">
						<h4 class="card-title">
							<img style="margin-top:-5px;" width="30" src="images/tsLogo.png"/> 
							<?php
								echo $language['instance'].": ";
								
								if($ts3_server[$instanz]['alias'] != '')
								{
									xssEcho($ts3_server[$instanz]['alias']);
								}
								else
								{
									xssEcho($ts3_server[$instanz]['ip']);
								};
							?>
						</h4>
						<h6 class="card-subtitle text-muted"><?php xssEcho($language['ip_adress'].": ".$ts3_server[$instanz]['ip']); ?></h6>
					</div>
					<div class="card-block">
						<table id="serverTable_<?php echo $instanz; ?>" data-toggle="table" data-card-view="false" data-classes="table-font-smaller table-no-bordered table-hover table"
							data-striped="false" data-pagination="true" data-search="true" 	data-detail-view="true">
							<thead>
								<tr>
									<th data-field="id" data-align="center" class="table-max-width-xs hidden-lg-down">ID</th>
									<th data-field="name"><?php echo $language['ts3_servername']; ?></th>
									<th data-field="port" data-align="center" class="table-max-width-sm"><?php echo $language['port']; ?></th>
									<th data-field="slots" data-align="center" class="table-max-width-sm"><?php echo $language['slots']; ?></th>
									<th data-field="actions" data-align="center" class="table-max-width"><!-- Temp --></th>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($myservers AS $server)
									{
										$trClass	=	($server['virtualserver_status'] == "online") ? "text-success" : "text-danger-no-cursor";
										$clients	=	(isSet($server['virtualserver_clientsonline'])) ? $server['virtualserver_clientsonline']." / ".$server['virtualserver_maxclients'] : "-";
										$btnDelete	=	($user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete']) ? "<div class=\"dropdown-divider\"></div><div class=\"dropdown-item text-danger-no-cursor\" style=\"cursor: pointer\" onClick=\"AreYouSure('".$language['delete_server']."', 'deleteTeamspeakserver(\'".$server['virtualserver_id']."\', \'".$instanz."\', \'".$server['virtualserver_port']."\')');\">".$language['delete_server']."</div>" : "";
										echo "<tr id=\"serverlist-".$instanz."-".$server['virtualserver_id']."\" class=\"".$trClass."\">
												<td>".$server['virtualserver_id']."</td>
												<td>".xssSafe($server['virtualserver_name'])."</td>
												<td>".$server['virtualserver_port']."</td>
												<td id=\"serverlist-slots-".$instanz."-".$server['virtualserver_id']."\">".$clients."</td>
												<td>
													<div class=\"btn-group\">
														<button type=\"button\" class=\"btn btn-info btn-sm\" onClick=\"showTeamspeakserver('".$server['virtualserver_id']."', '".$instanz."');\"><i class=\"fa fa-eye\"></i> <font class=\"hidden-md-down\">".$language['server_overview']."</font></button>
														<button type=\"button\" class=\"btn btn-info btn-sm dropdown-toggle dropdown-toggle-split\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
															<span class=\"sr-only\">Toggle Dropdown</span>
														</button>
														<div class=\"dropdown-menu\">
															<div class=\"dropdown-item text-success\" style=\"cursor: pointer;\" onClick=\"startTeamspeakserver('".$server['virtualserver_id']."', '".$instanz."', '".$server['virtualserver_port']."');\">".$language['server_start']."</div>
															<div class=\"dropdown-item text-warning\" style=\"cursor: pointer;\" onClick=\"stopTeamspeakserver('".$server['virtualserver_id']."', '".$instanz."', '".$server['virtualserver_port']."');\">".$language['server_stop']."</div>
															".$btnDelete."
														</div>
													</div>
												</td>
											</tr>";
									};
								?>
							</tbody>
						</table>
					</div>
				</div>
			<?php };
		};
	};
?>

<!-- Javascripte Laden -->
<?php if($globalServerCount > 5) { ?>
	<script src="js/bootstrap/bootstrap-table.js"></script>
<?php }; ?>
<script>
	var timeElements 	= 	document.getElementsByClassName('uptime'),
		statusElements	=	document.getElementsByClassName('serverstatus'),
		serverCount		=	<?php echo $globalServerCount; ?>;
	
	$('[data-tooltip="tooltip"]').tooltip();
	
	if(typeof(serverRefresh) != "undefined")
	{
		clearInterval(serverRefresh);
	};
	
	if(serverCount <= 5)
	{
		setTimeout(reloadServerInformations, 10000);
	};
	
	var serverRefresh = setInterval(function()
	{
		if(document.getElementById('globalServerlist'))
		{
			for (var i = 0; i < timeElements.length; ++i)
			{
				var timestamp					=	parseInt(timeElements[i].getAttribute('uptime-timestamp'));
				if(timestamp)
				{
					timestamp++;
					
					var newTime					=	convertTime(timestamp);
					
					timeElements[i].innerHTML 	=	lang.online_since+": "+newTime['days']+"d "+newTime['hours']+"h "+newTime['minutes']+"m "+newTime['seconds']+"s";
					timeElements[i].setAttribute('uptime-timestamp', timestamp);
				}
				else
				{
					timeElements[i].innerHTML 	=	lang.online_since+": -";
				};
			};
			
			for (var i = 0; i < statusElements.length; ++i)
			{
				if(statusElements[i].innerHTML.includes(lang.online))
				{
					var statusIcon				=	$('#'+statusElements[i].getAttribute('iconid'));
					
					if(statusIcon.hasClass("fa-circle-o"))
					{
						statusIcon.removeClass("fa-circle-o");
						statusIcon.addClass("fa-dot-circle-o");
					}
					else
					{
						statusIcon.removeClass("fa-dot-circle-o");
						statusIcon.addClass("fa-circle-o");
					};
					
					statusElements[i].classList.remove("text-danger");
					statusElements[i].classList.add("text-success");
				}
				else
				{
					statusElements[i].classList.remove("text-success");
					statusElements[i].classList.add("text-danger");
				};
			};
		}
		else
		{
			clearInterval(serverRefresh);
		};
	}, 1000);
	
	function reloadServerInformations()
	{
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsTeamspeakPost.php",
			data: {
				action:		'getInstanzServerlistInformations'
			},
			success: function(data)
			{
				var informations 	=	JSON.parse(data);
				
				for(var instanz in informations)
				{
					for(var sid in informations[instanz])
					{
						var objectUptime			=	document.getElementById('uptime-'+instanz+'-'+sid),
							objectOnline			=	document.getElementById('online-'+instanz+'-'+sid),
							objectClients			=	document.getElementById('progress-bar-'+instanz+'-'+sid);
							
						if(objectUptime && objectOnline && objectClients)
						{
							if(!informations[instanz][sid].uptime)
							{
								objectUptime.removeAttribute('uptime-timestamp');
								objectUptime.innerHTML			=	lang.online_since+": -";
							}
							else if(informations[instanz][sid].uptime && objectUptime.getAttribute('uptime-timestamp') == null)
							{
								objectUptime.setAttribute('uptime-timestamp', '1');
							};
							
							if(!informations[instanz][sid].online)
							{
								objectOnline.innerHTML			=	lang.offline;
								objectClients.style.display		=	"none";
							}
							else
							{
								if(objectOnline.innerHTML != lang.online)
								{
									objectOnline.innerHTML		=	lang.online;
								};
								
								objectClients.style.display		=	"inline";
								objectClients.innerHTML			=	informations[instanz][sid].clients+" / "+informations[instanz][sid].maxclients;
								objectClients.style.width		=	(informations[instanz][sid].clients/informations[instanz][sid].maxclients) * 100+"%";
							};
						};
					};
				};
			}
		});
		
		setTimeout(reloadServerInformations, 10000);
	};
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>