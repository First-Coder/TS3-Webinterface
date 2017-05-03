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
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_web'] != $mysql_keys['right_web'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<!-- Modal: Benutzer löschen -->
<div id="modalAreYouSure" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalAreYouSureLabel" aria-hidden="true">
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

<!-- Global Message / Poke -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-fw fa-edit"></i> <?php echo $language['global_msg_poke']; ?>
		</h4>
	</div>
	<div class="card-block">
		<?php if($user_right['right_web_global_message_poke'] == $mysql_keys['right_web_global_message_poke']) { ?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="input-group">
						<input type="text" id="instanzMessagePokeContent" style="margin-top:0;" class="form-control" placeholder="<?php echo $language['message']; ?>">
						<span class="input-group-btn" data-toggle="buttons">
							<label class="btn btn-primary active" id="instanzMode" style="margin: 0;border-radius: 0;">
								<input type="radio" autocomplete="off" checked><i class="fa fa-pencil"></i> <?php echo $language['message']; ?>
							</label>
							<label class="btn btn-primary" style="margin: 0;border-radius: 0;">
								<input type="radio" autocomplete="off"><i class="fa fa-hand-o-up"></i> <?php echo $language['poke']; ?>
							</label>
						</span>
					</div>
				</div>
			</div>
			<div class="row" style="text-align:center;">
				<div class="col-lg6 col-md-6 col-sm-12 col-xs-12 margin-row">
					<label class="c-input c-radio">
						<input value="all" name="instanzMsgPoke" type="radio" checked>
						<span class="c-indicator"></span>
						<?php echo $language['ts_main_to_all_instanz']; ?>
					</label>
				</div>
				<?php foreach($ts3_server AS $instanz => $values) { ?>
					<div class="col-lg6 col-md-6 col-sm-12 col-xs-12 margin-row">
						<label class="c-input c-radio">
							<input value="<?php echo $instanz; ?>" name="instanzMsgPoke" type="radio">
							<span class="c-indicator"></span>
							<?php echo $values['alias']; ?>
						</label>
					</div>
				<?php } ?>
			</div>
			<button onClick="instanzMessagePoke()" class="btn btn-success" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
		<?php } ?>
	</div>
</div>

<!-- Teamspeakserver -->
<?php
	$globalServers							=	array();
	$globalServerCount						=	0;
	
	foreach($ts3_server AS $instanz => $values)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 							= 	new ts3admin($values['ip'], $values['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($values['user'], $values['pw']);
			
			// Server Infos abfragen
			$servers 						= 	$tsAdmin->serverList();
			
			$globalServers[$instanz]		=	array();
			$globalServers[$instanz] 		= 	$servers['data'];
			
			// Ausloggen
			$tsAdmin->logout();
			
			foreach($servers['data'] AS $number => $server)
			{
				if(strpos($user_right['ports']['right_web_server_view'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'])
				{ 
					$globalServers[$instanz][$number] = $server;
					$globalServerCount++;
				};
			};
		}; 
	};
	
	foreach($globalServers AS $instanz => $servers)
	{
		foreach($servers AS $server)
		{
			if(strpos($user_right['ports']['right_web_server_view'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'])
			{ ?>
				<!-- Tempid-->
				<?php if($globalServerCount <= 5) { ?>
					<div id="tsMain"></div>
				<?php }; ?>
				<div class="card" id="serverbox_<?php echo $instanz.'_'.$server['virtualserver_port']; ?>">
					<div class="card-block card-block-header">
						<h4 class="card-title">
							<div class="pull-xs-left">
								<img style="margin-top:-5px;" width="30" src="images/tsLogo.png"/> <?php echo htmlspecialchars($server['virtualserver_name']); ?>&nbsp;&nbsp;(<font data-tooltip="tooltip" data-placement="top" title="ID"><?php echo $server['virtualserver_id']; ?></font>)
							</div>
							<div class="pull-xs-right">
								<?php echo $ts3_server[$instanz]['ip']; ?>:<?php echo $server['virtualserver_port']; ?>
								&nbsp;&nbsp;
								<a href="ts3server://<?php echo $ts3_server[$instanz]['ip']; ?>:<?php echo $server['virtualserver_port']; ?>">
									<i data-tooltip="tooltip" data-placement="top" title="<?php echo $language['ts_connect_to_server']; ?>" class="fa fa-sign-in"></i>
								</a>
							</div>
							<div style="clear:both;"></div>
						</h4>
					</div>
					<div class="card-block">
						<?php if(strpos($user_right['ports']['right_web_server_message_poke'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<div class="input-group">
										<input id="serverMessageContent_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" type="text" class="form-control teamspeak-input" placeholder="<?php echo $language['ts_server_message']; ?>">
										<span class="input-group-btn">
											<button onClick="serverMessage('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>')" id="serverMessage_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="btn btn-primary" type="button" <?php if($server['virtualserver_status'] != 'online') { echo "disabled"; } ?>>
												<i class="fa fa-pencil"></i> <?php echo $language['message']; ?>
											</button>
											<button onClick="serverPoke('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>')" id="serverPoke_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="btn btn-primary" type="button" <?php if($server['virtualserver_status'] != 'online') { echo "disabled"; } ?>>
												<i class="fa fa-hand-o-up"></i> <?php echo $language['poke']; ?>
											</button>
										</span>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="row home-text-margin">
							<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
								<div class="row home-text-margin">
									<div class="col-lg-6 col-md-6">
										<?php echo $language['ts3_serverstatus']; ?>:
									</div>
									<div id="status_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="col-lg-6 col-md-6" style="text-align:center;">
										<?php if($server['virtualserver_status'] == 'online') { ?>
											<strong style="color:green;"><?php echo $language['online']; ?></strong>
										<?php } else { ?>
											<strong style="color:red;"><?php echo $language['offline']; ?></strong>
										<?php } ?>
									</div>
								</div>
								<div class="row home-text-margin">
									<div class="col-lg-6 col-md-6">
										<?php echo $language['client']; ?>:
									</div>
									<div id="clientsonline_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="col-lg-6 col-md-6" style="text-align:center;">
										<?php if(isset($server['virtualserver_clientsonline'])) { ?>
											<strong><?php echo $server['virtualserver_clientsonline']; ?>&nbsp;/&nbsp;<?php echo $server['virtualserver_maxclients']; ?></strong>
										<?php } else { ?>
											<strong>-</strong>
										<?php } ?>
									</div>
								</div>
								<div class="row home-text-margin">
									<div class="col-lg-6 col-md-6">
										<?php echo $language['ts_main_query_user']; ?>:
									</div>
									<div id="queryclients_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="col-lg-6 col-md-6" style="text-align:center;">
										<?php if(isset($server['virtualserver_clientsonline'])) { ?>
											<strong><?php echo $server['virtualserver_queryclientsonline']; ?></strong>
										<?php } else { ?>
											<strong>-</strong>
										<?php } ?>
									</div>
								</div>
								<div class="row home-text-margin">
									<div class="col-lg-6 col-md-6">
										<?php echo $language['ts3_online_since']; ?>:
									</div>
									<div id="onlinesince_<?php echo $instanz; ?>_<?php echo $server['virtualserver_id']; ?>" class="col-lg-6 col-md-6" style="text-align:center;">
										<strong>
											<?php if(isset($server['virtualserver_uptime'])) {
												$uptime 			= 		$tsAdmin->convertSecondsToStrTime($server['virtualserver_uptime']);
												echo $uptime;
											}
											else
											{
												echo "-";
											} ?>
										</strong>
									</div>
								</div>
								<div class="row home-text-margin">
									<div class="col-lg-6 col-md-6">
										<?php echo $language['instance']; ?>: 
									</div>
									<div class="col-lg-6 col-md-6" style="text-align:center;">
										<strong>
											<?php if($ts3_server[$instanz]['alias'] != '')
											{
												echo $ts3_server[$instanz]['alias'];
											}
											else
											{
												echo $ts3_server[$instanz]['ip'];
											} ?>
										</strong>
									</div>
								</div>
							</div>
							<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12" style="text-align:center;">
								<div class="btn-group-vertical btn-group-md" role="group" style="width:100%;max-width:600px;">
									<?php if(strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $server['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
										<button onClick="startTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>')" type="button" class="btn btn-success"><i class="fa fa-fw fa-play"></i> <?php echo $language['ts_server_start']; ?></button>
										<button onClick="stopTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>', '<?php echo $server['virtualserver_port']; ?>')" type="button" class="btn btn-warning"><i class="fa fa-fw fa-stop"></i> <?php echo $language['ts_server_stop']; ?></button>
									<?php } ?>
									<?php if($user_right['right_web_server_delete'] == $mysql_keys['right_web_server_delete'] || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
										<button onClick="AreYouSure('<?php echo $language['ts_server_delete']; ?>', 'deleteTeamspeakserver(\'<?php echo $server['virtualserver_id']; ?>\', \'<?php echo $instanz; ?>\', \'<?php echo $server['virtualserver_port']; ?>\')');" type="button" class="btn btn-danger"><i class="fa fa-fw fa-trash"></i> <?php echo $language['ts_server_delete']; ?></button>
									<?php } ?>
									<button onClick="showTeamspeakserver('<?php echo $server['virtualserver_id']; ?>', '<?php echo $instanz; ?>')" type="button" class="btn btn-info"><i class="fa fa-fw fa-info"></i> <?php echo $language['more']; ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php };
		};
	};
?>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var ts_msg_poke_done			=	'<?php echo $language['ts_msg_poke_done']; ?>';
	var ts_msg_done					=	'<?php echo $language['ts_msg_done']; ?>';
	var ts_poke_done				=	'<?php echo $language['ts_poke_done']; ?>';
	var ts_server_started			=	'<?php echo $language['ts_server_started']; ?>';
	var ts_server_stoped			=	'<?php echo $language['ts_server_stoped']; ?>';
	var ts_server_deleted			=	'<?php echo $language['ts_server_deleted']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/teamspeak.js"></script>
<script>
	$(function () {
		$('[data-tooltip="tooltip"]').tooltip();
		$('.dropdown-toggle').dropdown();
		
		var refresh_serverlist = setInterval(function()
		{
			if (!document.getElementById('tsMain'))
			{
				clearInterval(refresh_serverlist);
			}
			else
			{
				// Traffic sofort darstellen
				var dataString = 'action=getTs3ServerlistInformations';
				$.ajax({
					type: "POST",
					url: "functionsTeamspeakPost.php",
					data: dataString,
					dataTyp: "json",
					cache: false,
					success: function(data)
					{
						var informations 	=	JSON.parse(data);
						
						// Daten übergeben
						for(instanz in informations)
						{
							for(serverid in informations[instanz])
							{
								if(document.getElementById('status_'+instanz+'_'+serverid))
								{
									if(informations[instanz][serverid]['virtualserver_status'] == 'Online')
									{
										document.getElementById("status_"+instanz+"_"+serverid).innerHTML			=	'<strong style="color:green;">'+informations[instanz][serverid]['virtualserver_status']+'</strong>';
									}
									else
									{
										document.getElementById("status_"+instanz+"_"+serverid).innerHTML			=	'<strong style="color:red;">'+informations[instanz][serverid]['virtualserver_status']+'</strong>';
									};
								};
								
								if(document.getElementById('onlinesince_'+instanz+'_'+serverid))
								{
									document.getElementById("onlinesince_"+instanz+"_"+serverid).innerHTML			=	'<strong>'+informations[instanz][serverid]['virtualserver_uptime']+'</strong>';
								};
								
								if(document.getElementById('clientsonline_'+instanz+'_'+serverid))
								{
									document.getElementById("clientsonline_"+instanz+"_"+serverid).innerHTML		=	'<strong>'+informations[instanz][serverid]['virtualserver_clientmaxclients']+'</strong>';
								};
								
								if(document.getElementById('queryclients_'+instanz+'_'+serverid))
								{
									document.getElementById("queryclients_"+instanz+"_"+serverid).innerHTML			=	'<strong>'+informations[instanz][serverid]['virtualserver_queryclientsonline']+'</strong>';
								};
							};
						};
					}
				});
			};
		}, 5000);
	});
</script>
<script src="js/sonstige/preloader.js"></script>