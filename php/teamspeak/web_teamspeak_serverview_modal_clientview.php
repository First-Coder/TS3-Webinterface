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
		Has the Client the Permission
	*/
	if((!isPortPermission($user_right, $_REQUEST['instanz'], $_REQUEST['port'], 'right_web_server_view') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])  || $user_right['right_web']['key'] != $mysql_keys['right_web'])
	{
		reloadSite(RELOAD_TO_SERVERVIEW);
	};
	
	/*
		Teamspeak Funktions
	*/
	$tsAdmin = new ts3admin($ts3_server[$_REQUEST['instanz']]['ip'], $ts3_server[$_REQUEST['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$_REQUEST['instanz']]['user'], $ts3_server[$_REQUEST['instanz']]['pw']);
		$tsAdmin->selectServer($_REQUEST['serverId'], 'serverId', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		$clientInfo		=	$tsAdmin->clientInfo($_REQUEST['id']);
		$clientAvatar	=	$tsAdmin->clientAvatar($clientInfo['data']['client_unique_identifier']);
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		$sgroups		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		$channelTree	=	getChannelTree($channels, false);
		$subChannelTree	=	getChannelTree($channels, true);
	};
	
	/*
		Function: getTime
	*/
	function getTime($timestamp)
	{
		$timestamp		=	floor($timestamp / 1000);
		
		$Tage			= 	$timestamp / 86400;
		$Stunden		=	($timestamp - (floor($Tage) * 86400)) / 3600;
		$Minuten		=	($timestamp - (floor($Tage) * 86400) - (floor($Stunden) * 3600)) / 60;
		$Sekunden		=	($timestamp - (floor($Tage) * 86400) - (floor($Stunden) * 3600) - (floor($Minuten) * 60));

		return floor($Stunden) . "h " . floor($Minuten) . "m " . floor($Sekunden) . "s";
	};
?>

<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-header alert-info">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="modalLabel">
				<?php
					if(!empty($clientInfo['data']['client_country']) && file_exists("../../images/ts_countries/".strtolower($clientInfo['data']['client_country']).".png"))
					{
						echo "<img height=\"16\" style=\"padding-bottom:5px;margin-right:10px;\" src=\"./images/ts_countries/".strtolower($clientInfo['data']['client_country']).".png\" alt=\"\" />";
					};
					
					xssEcho($clientInfo['data']['client_nickname']);
				?>
			</h4>
		</div>
		<div class="modal-body">
			<!-- Navigation -->
			<ul class="nav nav-pills">
				<li class="nav-item clientViewPills">
					<a class="nav-link active" href="#clientInformations" data-toggle="tab"><?php echo $language['informations']; ?></a>
				</li>
				<li class="nav-item clientViewPills">
					<?php if(isPortPermission($user_right, $_REQUEST['instanz'], $_REQUEST['port'], 'right_web_client_actions') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
						<a class="nav-link" href="#clientActions" data-toggle="tab"><?php echo $language['actions']; ?></a>
					<?php } else { ?>
						<a class="nav-link disabled" href="#"><?php echo $language['actions']; ?></a>
					<?php } ?>
				</li>
				<li class="nav-item clientViewPills">
					<?php if(isPortPermission($user_right, $_REQUEST['instanz'], $_REQUEST['port'], 'right_web_client_rights') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
						<a class="nav-link" href="#clientRights" data-toggle="tab"><?php echo $language['permission']; ?></a>
					<?php } else { ?>
						<a class="nav-link disabled" href="#"><?php echo $language['permission']; ?></a>
					<?php } ?>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="clientInformations">
					<table class="table table-condensed">
						<thead>
							<th colspan="3">
								<?php echo $language['informations']; ?>
							</th>
						</thead>
						<tbody>
							<tr class="hidden-sm-down">
								<td rowspan="7" style="width:40%;">
									<?php 
										if($clientInfo['data']['client_flag_avatar'])
										{
											echo '<img class="img-rounded img-responsive" src="data:image/png;base64,'.$clientAvatar["data"].'" />';
										}
										else
										{
											echo '<i class="fa fa-fw fa-picture-o" style="font-size:180px;margin-left:25px;"></i>';
										};
									?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['away']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_away']) ? $language['yes'] : $language['no']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['away_since']; ?>:
								</td>
								<td>
									<?php echo getTime($clientInfo['data']['client_idle_time']); ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['client_commander']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_is_channel_commander']) ? $language['on'] : $language['off']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['record']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_is_recording']) ? $language['on'] : $language['off']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['mikrofon']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_input_muted']) ? $language['off'] : $language['on']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['headset']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_output_muted']) ? $language['off'] : $language['on']; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['permission'].": ".$language['ts3_sgroup']; ?>
							</th>
						</thead>
						<tbody>
							<?php 
								$getsgroups											=	explode(',', trim($clientInfo['data']['client_servergroups']));
								if(!empty($sgroups))
								{
									foreach($sgroups AS $key=>$sg_value)
									{
										if(in_array($sg_value['sgid'], $getsgroups))
										{
											$iconid									=	$sg_value['iconid'];
											if($iconid < 0) // Standertservergruppe
											{
												$iconid								=	sprintf('%u', $iconid & 0xffffffff);
											};
											if($iconid != 0) // Hochgeladene Servergruppe
											{
												$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
												
												echo "<tr>
														<td style=\"text-align: center;width: 40%;\">
															<img src=\"".$icon_src."\" width=\"16\" height=\"16\">
														</td>
														<td>
															".xssSafe($sg_value['name'])."
														</td>
													</tr>";
											};
										};
									};
								};
							?>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['permission'].": ".$language['ts3_cgroup']; ?>
							</th>
						</thead>
						<tbody>
							<?php 
								if(!empty($cgroups))
								{
									foreach($cgroups AS $key=>$cg_value)
									{
										if($cg_value['cgid'] == $clientInfo['data']['client_channel_group_id'])
										{
											$iconid									=	$cg_value['iconid'];
											
											if($iconid < 0) // Standertservergruppe
											{
												$iconid								=	sprintf('%u', $iconid & 0xffffffff);
											};
											if($iconid != 0) // Hochgeladene Servergruppe
											{
												$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
												
												echo "<tr>
														<td style=\"text-align: center;width: 40%;\">
															<img src=\"".$icon_src."\" width=\"16\" height=\"16\">
														</td>
														<td>
															".xssSafe($cg_value['name'])."
														</td>
													</tr>";
											};
										};
									};
								};
							?>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['version_ids']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['client_version']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_version']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['client_plattform']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_platform']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['client_id']; ?>:
								</td>
								<td>
									<?php echo $_REQUEST['id']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['client_database_id']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_database_id']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_uniquie_id']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_unique_identifier']; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['other']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['description']; ?>:
								</td>
								<td>
									<?php xssEcho($clientInfo['data']['client_description']); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['client_talkpower']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_talk_power']; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php if(isPortPermission($user_right, $_REQUEST['instanz'], $_REQUEST['port'], 'right_web_client_actions') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
					<div class="tab-pane" id="clientActions">
						<table class="table table-condensed table-hover">
							<thead>
								<th>
									<?php echo $language['msg_poke']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectMessagePoke" class="form-control input-sm contentElements c-select">
									<option value="1" selected><?php echo $language['message']; ?></option>
									<option value="2"><?php echo $language['poke']; ?></option>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessagePoke" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientMsg('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['move']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectMoveInChannel" class="form-control input-sm contentElements c-select">
									<option style="display:none;" selected><?php echo $language['where']; ?>?</option>
									<optgroup label="<?php echo $language['channel']; ?>">
										<?php echo $channelTree; ?>
									</optgroup>
									<optgroup label="<?php echo $language['sub_channel']; ?>">
										<?php echo $subChannelTree; ?>
									</optgroup>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12 col-lg-offset-4">
								<button onClick="clientMove('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['kick']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectKickStyle" class="form-control input-sm contentElements c-select" <?php echo ($clientInfo['data']['channel_flag_default'] == 1) ?  "disabled" : ""; ?>>
									<option value="server" selected><?php echo $language['serverkick']; ?></option>
									<option value="channel"><?php echo $language['channelkick']; ?></option>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageKick" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientKick('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['ban']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<input id="inputBanTime" type="number" class="form-control input-sm contentElements c-select" placeholder="0">
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageBan" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientBan('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if(isPortPermission($user_right, $_REQUEST['instanz'], $_REQUEST['port'], 'right_web_client_rights') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
					<div class="tab-pane" id="clientRights">
						<table class="table table-condensed table-hover">
							<thead>
								<th colspan="2">
									<?php echo $language['sgroups']; ?>
								</th>
							</thead>
							<tbody>
								<?php
									$getsgroups											=	explode(',', trim($clientInfo['data']['client_servergroups']));
									if(!empty($sgroups))
									{
										foreach($sgroups AS $key=>$sg_value)
										{
											if($sg_value['type'] != '2' AND $sg_value['type'] != '0')
											{
												$btnClass								=	"btn-danger";
												$iconClass								=	"ban";
												$textClass								=	$language['blocked'];
												$iconid									=	$sg_value['iconid'];
												$imgTag									=	"";
												
												if($iconid < 0)
												{
													$iconid								=	sprintf('%u', $iconid & 0xffffffff);
												};
												
												if($iconid != 0)
												{
													$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
													$imgTag								=	($icon_src != "") ? "<img class=\"mini-left-right-margin\" src=\"".$icon_src."\" width=\"16\" height=\"16\">" : "";
												};
												
												if(in_array($sg_value['sgid'], $getsgroups))
												{
													$btnClass							=	"btn-success";
													$iconClass							=	"check";
													$textClass							=	$language['unblocked'];
												};
												
												echo "<tr>
														<td>
															".$imgTag.xssSafe($sg_value['name'])."
														</td>
														<td class=\"clientview-table\">
															<button id=\"sgroup_" . $sg_value['sgid'] . "\" onClick=\"addRemoveSRights('" . $clientInfo['data']['client_database_id'] . "', '" . $sg_value['sgid'] . "', 'sgroup_" . $sg_value['sgid'] . "')\" style=\"width: 100%;\"
																class=\"btn btn-sm ".$btnClass." button-transition\"><i id=\"sgroup_" . $sg_value['sgid'] . "_icon\" class=\"fa fa-".$iconClass."\" aria-hidden=\"true\"></i><font id=\"sgroup_" . $sg_value['sgid'] . "_text\" class=\"hidden-md-down\"> ".$textClass."</font></button>
														</td>
													</tr>";
											};
										};
									};
								?>
							</tbody>
						</table>
						<table class="table table-condensed table-hover">
							<thead>
								<th colspan="2">
									<?php echo $language['cgroup']; ?>
								</th>
							</thead>
							<tbody>
								<?php
									if(!empty($cgroups))
									{
										foreach($cgroups AS $key=>$cg_value)
										{
											if ($cg_value['type'] != '2' AND $cg_value['type'] != '0')
											{
												$btnClass								=	"btn-danger";
												$iconClass								=	"ban";
												$textClass								=	$language['blocked'];
												$iconid									=	$cg_value['iconid'];
												$imgTag									=	"";
												
												if($iconid < 0)
												{
													$iconid								=	sprintf('%u', $iconid & 0xffffffff);
												};
												
												if($iconid != 0)
												{
													$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
													$imgTag								=	($icon_src != "") ? "<img class=\"mini-left-right-margin\" src=\"".$icon_src."\" width=\"16\" height=\"16\">" : "";
												};
												
												if($cg_value['cgid'] == $clientInfo['data']['client_channel_group_id'])
												{
													$btnClass							=	"btn-success";
													$iconClass							=	"check";
													$textClass							=	$language['unblocked'];
												};
												
												echo "<tr>
														<td>
															".$imgTag.xssSafe($cg_value['name'])."
														</td>
														<td class=\"clientview-table\">
															<button id=\"cgroup_" . $cg_value['cgid'] . "\" onClick=\"addRemoveCRights('" . $clientInfo['data']['client_channel_group_inherited_channel_id'] . "', '" . $clientInfo['data']['client_channel_group_id'] . "', '" . $clientInfo['data']['client_database_id'] . "', '" . $cg_value['cgid'] . "', 'cgroup_" . $cg_value['cgid'] . "')\"
																style=\"width: 100%;\" class=\"btn btn-sm ".$btnClass." button-transition\"><i id=\"cgroup_" . $cg_value['cgid'] . "_icon\" class=\"fa fa-".$iconClass."\" aria-hidden=\"true\"></i><font id=\"cgroup_" . $cg_value['cgid'] . "_text\" class=\"hidden-md-down\"> ".$textClass."</font></button>
														</td>
													</tr>";
											};
										};
									};
								?>
							</tbody>
						</table>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<script language="JavaScript">
	var clientChannelGroupId		=	-1;
</script>