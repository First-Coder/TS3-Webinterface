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
	$mysql_edit_keys	=	getBlockedServerEditRights();
	
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
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Get Link information
	*/
	$linkInformations	=	getLinkInformations();
	
	if(empty($linkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$linkInformations['instanz']]['ip'], $ts3_server[$linkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$linkInformations['instanz']]['user'], $ts3_server[$linkInformations['instanz']]['pw']);
		
		$tsAdmin->selectServer($linkInformations['sid'], 'serverId', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		$sgroups		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		$channelTree	=	getChannelTree($channels, false);
		
		// Json sgroups
		$sgroupJson	=	array();
		if(!empty($sgroups))
		{
			foreach($sgroups AS $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$sgroupJson[$value['sgid']]	=	xssSafe($value['name']);
				};
			};
		};
		
		// Json cgroups
		$cgroupJson	=	array();
		if(!empty($cgroups))
		{
			foreach($cgroups AS $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$cgroupJson[$value['cgid']]	=	xssSafe($value['name']);
				};
			};
		};
	}
	else
	{
		reloadSite();
	};
	
	/*
		No Client permission
	*/
	if((!isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			|| $user_right['right_web']['key'] != $mysql_keys['right_web'])
	{
		reloadSite();
	};
	/*
		Get Teamspeak spezific Server edit permissions
	*/
	$user_edit_right		=	getCheckedClientServerEditRights($_SESSION['user']['id'], $linkInformations['instanz'], $server['data']['virtualserver_port']);
?>

<!-- Modal: Channel View -->
<div id="modalChannelView" class="modal fade" data-backdrop="true" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"></div>

<!-- Modal: Client View -->
<div id="modalClientView" class="modal fade" data-backdrop="true" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"></div>

<!-- Modal: Channel erstellen -->
<div id="modalChannelCreate" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header alert-success">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modalLabel"><?php echo $language['create_channel']; ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label><?php echo $language['ts3_create_channel_in']; ?></label>
					<select id="cpid" class="form-control c-select" style="width:100%;">
						<option value="0" selected><?php echo $language['at_the_end']; ?></option>
						<?php echo $channelTree; ?>
					</select>
					<small class="form-text text-muted"><?php echo $language['ts3_create_channel_in_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['channel_name']; ?></label>
					<input type="text" class="form-control" id="channel_name">
					<small class="form-text text-muted"><?php echo $language['channel_name_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['topic']; ?></label>
					<input type="text" class="form-control" id="channel_topic">
					<small class="form-text text-muted"><?php echo $language['topic_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_discreption']; ?></label>
					<input type="text" class="form-control" id="channel_description">
					<small class="form-text text-muted"><?php echo $language['ts3_channel_discreption_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_codec']; ?></label>
					<select class="form-control c-select" id="channel_codec">
						<option value="0">Speex Narrowband (8 kHz)</option>
						<option value="1">Speex Wideband (16 kHz)</option>
						<option value="2" selected>Speex Ultra-Wideband (32 kHz)</option>
						<option value="3">CELT Mono (48 kHz)</option>
						<option value="4">Opus Voice</option>
						<option value="5">Opus Musik</option>
					</select>
					<small class="form-text text-muted"><?php echo $language['ts3_channel_codec_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_codec_quality']; ?></label>
					<select class="form-control c-select" id="channel_codec_quality">
						<option value="0" selected>0</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
					</select>
					<small class="form-text text-muted"><?php echo $language['ts3_channel_codec_quality_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_max_clients']; ?></label>
					<input type="number" class="form-control" id="channel_maxclients" value="-1">
					<small class="form-text text-muted"><?php echo $language['ts3_channel_max_clients_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_clients_family']; ?></label>
					<input type="number" class="form-control" id="channel_maxfamilyclients" value="-1">
					<small class="form-text text-muted"><?php echo $language['ts3_channel_clients_family_in']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_type']; ?></label>
					<select class="form-control c-select" id="channel_typ">
						<option value="1" selected><?php echo $language['permanent']; ?></option>
						<option value="2"><?php echo $language['semi_permanent']; ?></option>
						<option value="3"><?php echo $language['ts3_channel_default']; ?></option>
					</select>
					<small class="form-text text-muted"><?php echo $language['ts3_channel_type_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_clients_fam']; ?></label>
					<select class="form-control c-select" id="channel_flag_maxfamilyclients_inherited">
						<option value="0" selected>0</option>
						<option value="1">1</option>
					</select>
					<small class="form-text text-muted"><?php echo $language['ts3_channel_clients_fam_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_talk_power']; ?></label>
					<input type="number" class="form-control" id="channel_needed_talk_power" value="0">
					<small class="form-text text-muted"><?php echo $language['ts3_channel_talk_power_info']; ?></small>
				</div>
				<div class="form-group">
					<label><?php echo $language['ts3_channel_phonetic_name']; ?></label>
					<input type="text" class="form-control" id="channel_name_phonetic">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['abort']; ?></button>
				<button onClick="createChannel();" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-edit"></i> <?php echo $language['create']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Header -->
<?php if(isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_message_poke') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<i class="fa fa-eye"></i> <?php echo $language['server_overview']; ?>
			</h4>
		</div>
		<div class="card-block">
			<div class="row" style="margin-bottom:20px;">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="input-group">
						<input id="serverMessageContent" type="text" class="form-control" placeholder="<?php echo $language['server_message']; ?>">
						<span class="input-group-btn">
							<button onClick="serverMessage();" id="serverMessage" class="btn btn-custom" type="button" <?php if($server['data']['virtualserver_status'] != 'online') { echo "disabled"; } ?>>
								<i class="fa fa-pencil"></i> <?php echo $language['message']; ?>
							</button>
							<button onClick="serverPoke();" id="serverPoke" class="btn btn-custom" type="button" <?php if($server['data']['virtualserver_status'] != 'online') { echo "disabled"; } ?>>
								<i class="fa fa-hand-o-up"></i> <?php echo $language['poke']; ?>
							</button>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php }; ?>

<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i class="fa fa-server"></i> <font id="globalServername"><?php xssEcho($server['data']['virtualserver_name']); ?></font>
			</div>
			<div class="pull-xs-right">
				<a class="btn btn-secondary btn-sm" href="ts3server://<?php echo $ts3_server[$linkInformations['instanz']]['ip']; ?>:<?php echo $server['data']['virtualserver_port']; ?>">
					<i class="fa fa-fw fa-sign-in"></i> <?php echo $language['connect']; ?>
				</a>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	
	<div class="card-block">
		<ul class="nav nav-pills nav-justified">
			<li role="presentation" class="nav-item serverViewPills"><a class="nav-link active" data-toggle="pill" href="#overview"><?php echo $language['server_overview']; ?></a></li>
			<li role="presentation" class="nav-item serverViewPills"><a class="nav-link" data-toggle="pill" href="#settings"><?php echo $language['settings']; ?></a></li>
		</ul>
	</div>
	
	<div class="tab-content">
		<!-- Serverbaum -->
		<div id="overview" class="tab-pane fade in active">
			<div class="card-block">
				<div id="tree_loading" style="text-align:center;">
					<h3><?php echo $language['tree_loading']; ?></h3><br /><i style="font-size:100px;" class="fa fa-cogs fa-spin"></i>
				</div>
				<div class="tree <?php echo ($server['data']['virtualserver_status'] == 'online') ? 'tree-background-success' : 'tree-background-failed'; ?>" id="tree" style="padding:20;display:none;">
					<div class="row row-header" id="header_tree">
						<div class="col-md-12 top-margin">
							<div class="col-md-7 col-xs-7">
								<div id="server_name" class="servername">&nbsp;&nbsp;<?php xssEcho($server['data']['virtualserver_name']); ?></div>
							</div>
							<div class="col-md-5 col-xs-5" style="text-align:right;" id="server_icon"></div>
						</div>
					</div>
					<div id="tree_content">
						<?php if(isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
							<button class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modalChannelCreate" style="width: 100%;"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $language['create_channel']; ?></button>
						<?php }; ?>
					</div>
				</div>
			</div>
		</div>
		
		<div id="settings" class="tab-pane fade in">
			<!-- Teamspeakinformations -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleInformations', 'iconInformations', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-info"></i> <?php echo $language['teamspeak_information']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconInformations" class="fa fa-arrow-down"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleInformations">
				<table class="table table-condensed table-hover">
					<tbody>
						<!-- Serverstatus -->
						<tr>
							<td style="width:30%;">
								<div class="tableSlide">
									<?php echo $language['ts3_serverstatus']; ?>:
								</div>
							</td>
							<td>
								<i id="server_status">
									<?php if($server['data']['virtualserver_status'] == 'online')
									{
										echo '<font style="color:green;">';
									}
									else
									{
										echo '<font style="color:#900;">';
									};
									echo $server['data']['virtualserver_status'] . "</font>"; ?>
								</i>
							</td>
						</tr>
						<!-- Erstellt am -->
						<tr>
							<td>
								<?php echo $language['create_on']; ?>:
							</td>
							<td>
								<i>
									<?php echo date('d.m.Y - H:i', $server['data']['virtualserver_created']); ?>
								</i>
							</td>
						</tr>
						<!-- Laufzeit -->
						<tr>
							<td>
								<?php echo $language['online_since']; ?>:
							</td>
							<td>
								<i id="server_timeup">
									<?php 
										$Tage			= 	$server['data']['virtualserver_uptime'] / 86400;
										$Stunden		=	($server['data']['virtualserver_uptime'] - (floor($Tage) * 86400)) / 3600;
										$Minuten		=	($server['data']['virtualserver_uptime'] - (floor($Tage) * 86400) - (floor($Stunden) * 3600)) / 60;

										echo floor($Tage) . " " . $language['days'] . " " . floor($Stunden) . " " . $language['hours'] . " " . floor($Minuten) . " " . $language['minutes'];
									?>
								</i>
							</td>
						</tr>
						<!-- Clients -->
						<tr>
							<td>
								<?php echo $language['ts3_clients']; ?>:
							</td>
							<td>
								<i id="max_clients">
									<?php echo ($server['data']['virtualserver_clientsonline'] - $server['data']['virtualserver_queryclientsonline']); ?> / <?php echo $server['data']['virtualserver_maxclients']; ?> (<?php echo $server['data']['virtualserver_queryclientsonline']; ?>)
								</i>
							</td>
						</tr>
						<!-- Channelanzahl -->
						<tr>
							<td>
								<?php echo $language['ts3_count_channel']; ?>:
							</td>
							<td>
								<i id="server_channels">
									<?php echo $server['data']['virtualserver_channelsonline']; ?>
								</i>
							</td>
						</tr>
						<!-- Passwort -->
						<tr>
							<td>
								<?php echo $language['ts3_set_password']; ?>:
							</td>
							<td>
								<i id="server_password">
									<?php if ($server['data']['virtualserver_flag_password'] == 1)
									{
										echo $language['yes'];
									}
									else
									{
										echo $language['no'];
									}; ?>
								</i>
							</td>
						</tr>
						<!-- Teamspeakadresse -->
						<tr>
							<td>
								<?php echo $language['ts3_ts_adress']; ?>:
							</td>
							<td>
								<i>
									<?php xssEcho($ts3_server[$linkInformations['instanz']]['ip']); ?>:
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_port'] != $mysql_edit_keys['right_server_edit_port']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-pk="virtualserver_port" data-type="text">
									<?php } ?>
											<?php echo $server['data']['virtualserver_port']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_port'] != $mysql_edit_keys['right_server_edit_port']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Eindeutige ID -->
						<tr>
							<td>
								<?php echo $language['ts3_uniquie_id']; ?>:
							</td>
							<td>
								<i>
									<?php echo $server['data']['virtualserver_unique_identifier']; ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Teamspeakversions -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleInformationsVersion', 'iconInformationsVersion', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-info"></i> <?php echo $language['ts3_teamspeak_version']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconInformationsVersion" class="fa fa-arrow-down"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleInformationsVersion">
				<table class="table table-condensed table-hover">
					<tbody>
						<!-- Plattform -->
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_teamspeak_plattform']; ?>:
							</td>
							<td>
								<i>
									<?php echo $server['data']['virtualserver_platform']; ?>
								</i>
							</td>
						</tr>
						<!-- Version -->
						<tr>
							<td>
								<?php echo $language['ts3_teamspeak_version']; ?>:
							</td>
							<td>
								<i>
									<?php echo $server['data']['virtualserver_version']; ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Haupteinstellungen -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditMain', 'iconEditMain', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['main_settings']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditMain" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditMain" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<!-- Servername -->
						<tr style="width:30%;">
							<td style="width:30%;">
								<?php echo $language['ts3_servername']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_name">
									<?php } ?>
										<?php echo htmlspecialchars($server['data']['virtualserver_name']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
							&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
							<!-- Password -->
							<tr>
								<td style="width:30%;">
									<?php echo $language['ts3_set_password']; ?>:
								</td>
								<td>
									<i>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_password"></a>
									</i>
								</td>
							</tr>
							<!-- Autostart -->
							<tr>
								<td>
									<?php echo $language['ts3_autostart']; ?>:
								</td>
								<td>
									<i>
										<?php if($user_edit_right['right_server_edit_autostart'] != $mysql_edit_keys['right_server_edit_autostart']  || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
											<a href="#" id="selectAutostart" data-type="select" data-pk="virtualserver_autostart">
												<?php if($server['data']['virtualserver_autostart'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
											</a>
										<?php } else { ?>
											<?php if($server['data']['virtualserver_autostart'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										<?php } ?>
									</i>
								</td>
							</tr>
						<?php } ?>
						<!-- Max. Clients -->
						<tr>
							<td>
								<?php echo $language['ts3_max_clients']; ?>:
							</td>
							<td>
								<i id="max_clients">
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_slots'] != $mysql_edit_keys['right_server_edit_slots']
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_maxclients">
									<?php } ?>
										<?php echo $server['data']['virtualserver_maxclients']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_slots'] != $mysql_edit_keys['right_server_edit_slots']
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Min. Client Version -->
						<tr>
							<td>
								<?php echo $language['ts3_min_client_version']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_min_client_version'] != $mysql_edit_keys['right_server_edit_min_client_version']
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_min_client_version">
									<?php } ?>
										<?php echo $server['data']['virtualserver_min_client_version']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_min_client_version'] != $mysql_edit_keys['right_server_edit_min_client_version']
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Reservierte Slots -->
						<tr>
							<td>
								<?php echo $language['ts3_reservierte_slots']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_reserved_slots">
									<?php } ?>
										<?php echo $server['data']['virtualserver_reserved_slots']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Sicherheitslevel -->
						<tr>
							<td>
								<?php echo $language['ts3_securtiy_level']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_needed_identity_security_level">
									<?php } ?>
										<?php echo $server['data']['virtualserver_needed_identity_security_level']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Icon ID -->
						<tr>
							<td>
								<?php echo $language['icon_id']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_icon_id">
									<?php } ?>
										<?php
											if($server['data']['virtualserver_icon_id'] < 0)
											{
												echo sprintf('%u', $server['data']['virtualserver_icon_id'] & 0xffffffff);
											}
											else
											{
												echo $server['data']['virtualserver_icon_id'];
											};
										?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Min Clients befor silence -->
						<tr>
							<td>
								<?php echo $language['ts3_min_clients_to_be_quiet']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_min_clients_in_channel_before_forced_silence">
									<?php } ?>
											<?php echo $server['data']['virtualserver_min_clients_in_channel_before_forced_silence']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Willkommensnachricht -->
						<tr>
							<td>
								<?php echo $language['ts3_welcome_message']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="textarea" data-pk="virtualserver_welcomemessage">
									<?php } ?>
											<?php xssEcho($server['data']['virtualserver_welcomemessage']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_main_settings'] != $mysql_edit_keys['right_server_edit_main_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Standerdgroups -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditDefaultGroup', 'iconEditDefaultGroup', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['standertgroups']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditDefaultGroup" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditDefaultGroup" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<!-- Servergruppe -->
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_sgroup']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" id="selectDefaultServerGroup" data-type="select" data-pk="virtualserver_default_server_group">
									<?php } ?>
											<?php if(!empty($sgroups))
											{
												foreach($sgroups AS $key => $value)
												{
													if ($value['type'] != '2' AND $value['type'] != '0')
													{
														if($server['data']['virtualserver_default_server_group'] == $value['sgid'])
														{
															xssEcho($value['name']);
														};
													};
												};
											}; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Channelgruppe -->
						<tr>
							<td>
								<?php echo $language['ts3_cgroup']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" id="selectDefaultChannelGroup" data-type="select" data-pk="virtualserver_default_channel_group">
									<?php } ?>
											<?php if(!empty($sgroups))
											{
												foreach($sgroups AS $key => $value)
												{
													if ($value['type'] != '2' AND $value['type'] != '0' && isSet($value['cgid']))
													{
														if($server['data']['virtualserver_default_channel_group'] == $value['cgid'])
														{
															xssEcho($value['name']);
														};
													};
												};
											}; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Adminchannelgruppe -->
						<tr>
							<td>
								<?php echo $language['ts3_cgroup_admin']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" id="selectDefaultChannelAdminGroup" data-type="select" data-pk="virtualserver_default_channel_admin_group">
									<?php } ?>
											<?php if(!empty($sgroups))
											{
												foreach($cgroups AS $key => $value)
												{
													if ($value['type'] != '2' AND $value['type'] != '0')
													{
														if($server['data']['virtualserver_default_channel_admin_group'] == $value['cgid'])
														{
															echo $value['name'];
														};
													};
												};
											}; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_default_servergroups'] != $mysql_edit_keys['right_server_edit_default_servergroups']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Hostsettings -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditHostsettings', 'iconEditHostsettings', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['hostsettings']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditHostsettings" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditHostsettings" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<!-- Host Nachricht zeigen -->
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_host_message_show']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" id="selectHostMessage" data-type="select" data-pk="virtualserver_hostmessage_mode">
									<?php } ?>
											<?php if($server['data']['virtualserver_hostmessage_mode'] == 0) {
												echo $language['ts3_host_message_show_1'];
											} elseif ($server['data']['virtualserver_hostmessage_mode'] == 1) { 
												echo $language['ts3_host_message_show_2'];
											} elseif ($server['data']['virtualserver_hostmessage_mode'] == 2) {
												echo $language['ts3_host_message_show_3'];
											} elseif ($server['data']['virtualserver_hostmessage_mode'] == 3) {
												echo $language['ts3_host_message_show_4'];
											}; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Nachricht -->
						<tr>
							<td>
								<?php echo $language['ts3_host_message']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="textarea" data-pk="virtualserver_hostmessage">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostmessage']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Url -->
						<tr>
							<td>
								<?php echo $language['ts3_host_url']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbanner_url">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostbanner_url']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Banner Url -->
						<tr>
							<td>
								<?php echo $language['ts3_host_banner_url']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbanner_gfx_url">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostbanner_gfx_url']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php if($server['data']['virtualserver_hostbanner_gfx_url'] != '')
									{ ?>
										<img style="width:100%" src="<?php echo $server['data']['virtualserver_hostbanner_gfx_url']; ?>" alt="" />
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Banner Interval -->
						<tr>
							<td>
								<?php echo $language['ts3_host_banner_interval']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbanner_gfx_interval">
									<?php } ?>
										<?php echo $server['data']['virtualserver_hostbanner_gfx_interval']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Button GFX Url -->
						<tr>
							<td>
								<?php echo $language['ts3_host_buttton_gfx_url']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbutton_gfx_url">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostbutton_gfx_url']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Button Tooltip -->
						<tr>
							<td>
								<?php echo $language['ts3_host_button_tooltip']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbutton_tooltip">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostbutton_tooltip']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<!-- Host Button Url -->
						<tr>
							<td>
								<?php echo $language['ts3_host_button_url']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_hostbutton_url">
									<?php } ?>
										<?php xssEcho($server['data']['virtualserver_hostbutton_url']); ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_host_settings'] != $mysql_edit_keys['right_server_edit_host_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Complainsettings -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditComplainsettings', 'iconEditComplainsettings', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['complaintsettings']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditComplainsettings" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditComplainsettings" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_autoban_count']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_complain_autoban_count">
									<?php } ?>
										<?php echo $server['data']['virtualserver_complain_autoban_count']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_autoban_duration']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_complain_autoban_time">
									<?php } ?>
										<?php echo $server['data']['virtualserver_complain_autoban_time']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['seconds']; ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_autoban_delete_after']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_complain_remove_time">
									<?php } ?>
										<?php echo $server['data']['virtualserver_complain_remove_time']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_complain_settings'] != $mysql_edit_keys['right_server_edit_complain_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['seconds']; ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Anti Flood settings -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditAntiFloodsettings', 'iconEditAntiFloodsettings', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['anti_flood_settings']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditAntiFloodsettings" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditAntiFloodsettings" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_reduce_points']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-type="text" data-pk="virtualserver_antiflood_points_tick_reduce">
									<?php } ?>
										<?php echo $server['data']['virtualserver_antiflood_points_tick_reduce']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_points_block']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-type="text" data-pk="virtualserver_antiflood_points_needed_command_block">
									<?php } ?>
										<?php echo $server['data']['virtualserver_antiflood_points_needed_command_block']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_points_block_ip']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-type="text" data-pk="virtualserver_antiflood_points_needed_ip_block">
									<?php } ?>
										<?php echo $server['data']['virtualserver_antiflood_points_needed_ip_block']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_antiflood_settings'] != $mysql_edit_keys['right_server_edit_antiflood_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Transfersettings -->
			<div class="card-block card-block-header" onClick="slideMe('slideToggleEditTransfersettings', 'iconEditTransfersettings', true);" style="cursor:pointer;">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i class="fa fa-pencil"></i> <?php echo $language['transfersettings']; ?>
					</div>
					<div class="pull-xs-right">
						<i id="iconEditTransfersettings" class="fa fa-arrow-left"></i>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" id="slideToggleEditTransfersettings" style="display:none;">
				<table class="table table-condensed table-hover">
					<tbody>
						<tr>
							<td style="width:30%;">
								<?php echo $language['ts3_upload_limit']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_max_upload_total_bandwidth">
									<?php } ?>
										<?php echo ($server['data']['virtualserver_max_upload_total_bandwidth'] == 18446744073709551615) ? $language['unlimited'] : $server['data']['virtualserver_max_upload_total_bandwidth']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['byte_s']; ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_upload_kontigent']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_upload_quota">
									<?php } ?>
										<?php echo ($server['data']['virtualserver_upload_quota'] == 18446744073709551615) ? $language['unlimited'] : $server['data']['virtualserver_upload_quota']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['mbyte']; ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_download_limit']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_max_download_total_bandwidth">
									<?php } ?>
										<?php echo ($server['data']['virtualserver_max_download_total_bandwidth'] == 18446744073709551615) ? $language['unlimited'] : $server['data']['virtualserver_max_download_total_bandwidth']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['byte_s']; ?>
								</i>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo $language['ts3_download_kontigent']; ?>:
							</td>
							<td>
								<i>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										<a href="#" class="editableText" data-type="text" data-pk="virtualserver_download_quota">
									<?php } ?>
										<?php echo ($server['data']['virtualserver_download_quota'] == 18446744073709551615) ? $language['unlimited'] : $server['data']['virtualserver_download_quota']; ?>
									<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
										&& $user_edit_right['right_server_edit_transfer_settings'] != $mysql_edit_keys['right_server_edit_transfer_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
										</a>
									<?php } ?>
									<?php echo $language['mbyte']; ?>
								</i>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- Protokolsettings -->
			<?php if((isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit')
				&& $user_edit_right['right_server_edit_protokoll_settings'] != $mysql_edit_keys['right_server_edit_protokoll_settings']) || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
				<div class="card-block card-block-header" onClick="slideMe('slideToggleEditProtokolsettings', 'iconEditProtokolsettings', true);" style="cursor:pointer;">
					<h4 class="card-title">
						<div class="pull-xs-left">
							<i class="fa fa-pencil"></i> <?php echo $language['protokolsettings']; ?>
						</div>
						<div class="pull-xs-right">
							<i id="iconEditProtokolsettings" class="fa fa-arrow-left"></i>
						</div>
						<div style="clear:both;"></div>
					</h4>
				</div>
				<div class="card-block" id="slideToggleEditProtokolsettings" style="display:none;">
					<table class="table table-condensed table-hover">
						<tbody>
							<tr>
								<td style="width:30%;">
									<?php echo $language['ts3_upload_limit']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolClient" data-type="select" data-pk="virtualserver_log_client">
											<?php if($server['data']['virtualserver_log_client'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_protokol_query']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolQuery" data-type="select" data-pk="virtualserver_log_query">
											<?php if($server['data']['virtualserver_log_query'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_protokol_channel']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolChannel" data-type="select" data-pk="virtualserver_log_channel">
											<?php if($server['data']['virtualserver_log_channel'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_protokol_rights']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolRights" data-type="select" data-pk="virtualserver_log_permissions">
											<?php if($server['data']['virtualserver_log_permissions'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_protokol_server']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolServer" data-type="select" data-pk="virtualserver_log_server">
											<?php if($server['data']['virtualserver_log_server'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_protokol_transfer']; ?>:
								</td>
								<td>
									<i>
										<a href="#" id="selectProtokolTransfer" data-type="select" data-pk="virtualserver_log_filetransfer">
											<?php if($server['data']['virtualserver_log_filetransfer'] == 1) {echo $language['yes']; } else {echo $language['no']; } ?>
										</a>
									</i>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php }; ?>
		</div>
	</div>
</div>

<!-- IFrame -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-info"></i> Teamspeak Iframe</font>
		</h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<?php
				$iframeLink		=	str_replace("index", "iframeServerView", explode("?", $_SERVER['HTTP_REFERER'])[0]);
				$iframeText		=	"<iframe allowtransparency=\"true\" src=\"".$iframeLink."?port=".$server['data']['virtualserver_port']."&instanz=".$linkInformations['instanz']."&color=666&bodybgcolor=fff&spinbgcolor=fff&fontsize=1em\" style=\"height:100%;width:100%\" scrolling=\"auto\" frameborder=\"0\">Your Browser will not show Iframes</iframe>";
			?>
			<textarea readonly class="form-control" rows="5"><?php echo $iframeText; ?></textarea>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var rightEdit					=	<?php if(isPortPermission($user_right, $linkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_edit') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { echo '1'; } else { echo '0'; } ?>,
		treeInterval				= 	<?php echo TEAMSPEAKTREE_INTERVAL; ?>,
		instanz						=	'<?php echo $linkInformations['instanz']; ?>',
		port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		serverId					=	'<?php echo $linkInformations['sid']; ?>',
		sgroups 					= 	<?php echo json_encode($sgroupJson); ?>,
		cgroups 					= 	<?php echo json_encode($cgroupJson); ?>;
</script>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-editable.js"></script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/webinterface/teamspeakTree.js"></script>
<script>
	$(document).ready(function() {
		if(rightEdit == '1')
		{
			$.fn.editable.defaults.mode = 	'inline';
			
			// Select Standertgruppen
			var selectDefaultServerGroupValue	=	'<?php echo $server["data"]["virtualserver_default_server_group"]; ?>';
			$('#selectDefaultServerGroup').editable({
				value: selectDefaultServerGroupValue,
					source: sgroups,
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectDefaultChannelGroupValue	=	'<?php echo $server["data"]["virtualserver_default_channel_group"]; ?>';
			$('#selectDefaultChannelGroup').editable({
				value: selectDefaultChannelGroupValue,
					source: cgroups,
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectDefaultChannelAdminGroupValue	=	'<?php echo $server["data"]["virtualserver_default_channel_admin_group"]; ?>';
			$('#selectDefaultChannelAdminGroup').editable({
				value: selectDefaultChannelAdminGroupValue,
					source: cgroups,
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			
			// Select Protokolsettings
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_client"]; ?>';
			$('#selectProtokolClient').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_query"]; ?>';
			$('#selectProtokolQuery').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_channel"]; ?>';
			$('#selectProtokolChannel').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_permissions"]; ?>';
			$('#selectProtokolRights').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_server"]; ?>';
			$('#selectProtokolServer').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			var selectProtokolClientValue	=	'<?php echo $server["data"]["virtualserver_log_filetransfer"]; ?>';
			$('#selectProtokolTransfer').editable({
				value: selectProtokolClientValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			
			// Select Hostmessagemode
			var selectHostMessageValue	=	'<?php echo $server["data"]["virtualserver_hostmessage_mode"]; ?>';
			$('#selectHostMessage').editable({
				value: selectHostMessageValue,
					source: [
					{value: "0", text: lang.ts3_host_message_show_1},
					{value: "1", text: lang.ts3_host_message_show_2},
					{value: "2", text: lang.ts3_host_message_show_3},
					{value: "3", text: lang.ts3_host_message_show_4}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			
			// Select Autostart
			var selectAutostartValue	=	'<?php echo ($server["data"]["virtualserver_autostart"] == 1) ? "1" : "0"; ?>';
			$('#selectAutostart').editable({
				value: selectAutostartValue,
					source: [
					{value: "1", text: lang.yes},
					{value: "0", text: lang.no}
				],
				success: function(response, newValue)
				{
					var right	=	$(this).data('pk')
					serverEdit(right, newValue, instanz, serverId, port);
				}
			});
			
			// Alle Klassen
			$('.editableText').editable({
				success: function(response, newValue)
				{
					console.log(right);
					var right	=	$(this).data('pk');
					
					if((right == "virtualserver_max_upload_total_bandwidth" || right == "virtualserver_upload_quota"
						|| right == "virtualserver_max_download_total_bandwidth" || right == "virtualserver_download_quota") && newValue == lang.unlimited)
					{
						serverEdit(right, "18446744073709551615", instanz, serverId, port);
					}
					else
					{
						serverEdit(right, newValue, instanz, serverId, port);
					};
				}
			});
		};
	});
</script>
<script src="js/sonstige/preloader.js"></script>