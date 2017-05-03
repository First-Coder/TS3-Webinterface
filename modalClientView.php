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
	
	/*
		Get the Modul Keys
	*/
	$mysql_keys		=	getKeys();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Get all Link Informations
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	}
	
	/*
		Has the Client the Permission
	*/
	if((strpos($user_right['ports']['right_web_server_view'][$_REQUEST['instanz']], $_REQUEST['port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])  || $user_right['right_web'] != $mysql_keys['right_web'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Teamspeak Funktions
	*/
	$tsAdmin = new ts3admin($ts3_server[$_REQUEST['instanz']]['ip'], $ts3_server[$_REQUEST['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		// Im Teamspeak Einloggen
		$tsAdmin->login($ts3_server[$_REQUEST['instanz']]['user'], $ts3_server[$_REQUEST['instanz']]['pw']);
		
		// Server Select
		$tsAdmin->selectServer($_REQUEST['serverId'], 'serverId', true);
		
		// Server Info Daten abfragen
		$server = $tsAdmin->serverInfo();
		
		// clientInformationen abfragen
		$clientInfo		=	$tsAdmin->clientInfo($_REQUEST['id']);
		
		// Client Avatar
		$clientAvatar	=	$tsAdmin->clientAvatar($clientInfo['data']['client_unique_identifier']);
		
		// Channel abfragen
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		
		$sgroups		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		
		// Channels
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if ($value['pid'] == 0)
				{
					if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0)
					{
						$getspacer=explode($treffer[0], $value['channel_name']);
						$checkspacer=$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
						if($treffer[1]=="*" or strlen($getspacer[1])==3 AND $checkspacer==$getspacer[1])
						{
							$spacer='';
							for($i=0; $i<=50; $i++)
							{
								if(strlen($spacer)<50)
								{
									$spacer.=$getspacer[1];
								}
								else
								{
									break;
								};
							};
							$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer)."</option>";
						}
						else
						{
							$spacer=explode($treffer[0], $value['channel_name']);
							$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer[1])."</option>";
						};
					}
					else
					{
						$channelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($value['channel_name'])."</option>";
					};
				};
			};
		};
		
		// SubChannels
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if ($value['pid'] != 0)
				{
					if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0)
					{
						$getspacer=explode($treffer[0], $value['channel_name']);
						$checkspacer=$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
						if($treffer[1]=="*" or strlen($getspacer[1])==3 AND $checkspacer==$getspacer[1])
						{
							$spacer='';
							for($i=0; $i<=50; $i++)
							{
								if(strlen($spacer)<50)
								{
									$spacer.=$getspacer[1];
								}
								else
								{
									break;
								};
							};
							$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer)."</option>";
						}
						else
						{
							$spacer=explode($treffer[0], $value['channel_name']);
							$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($spacer[1])."</option>";
						};
					}
					else
					{
						$subChannelTree .= "<option value='".$value['cid']."'>".htmlspecialchars($value['channel_name'])."</option>";
					};
				};
			};
		};
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
					if(!empty($clientInfo['data']['client_country']) && file_exists("images/ts_countries/".strtolower($clientInfo['data']['client_country']).".png"))
					{
						echo "<img height=\"16\" style=\"padding-bottom:5px;margin-right:10px;\" src=\"images/ts_countries/".strtolower($clientInfo['data']['client_country']).".png\" alt=\"\" />";
					};
					
					echo htmlspecialchars($clientInfo['data']['client_nickname']);
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
					<?php if(strpos($user_right['ports']['right_web_client_actions'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
						<a class="nav-link" href="#clientActions" data-toggle="tab"><?php echo $language['actions']; ?></a>
					<?php } else { ?>
						<a class="nav-link disabled" href="#"><?php echo $language['actions']; ?></a>
					<?php } ?>
				</li>
				<li class="nav-item clientViewPills">
					<?php if(strpos($user_right['ports']['right_web_client_rights'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
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
										}
									?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_away']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_away']) ? $language['yes'] : $language['no']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_away_since']; ?>:
								</td>
								<td>
									<?php echo getTime($clientInfo['data']['client_idle_time']); ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_client_commander']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_is_channel_commander']) ? $language['on'] : $language['off']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_record']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_is_recording']) ? $language['on'] : $language['off']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_mikrofon']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_input_muted']) ? $language['off'] : $language['on']; ?>
								</td>
							</tr>
							<tr class="table-hov">
								<td>
									<?php echo $language['ts_headset']; ?>:
								</td>
								<td>
									<?php echo ($clientInfo['data']['client_output_muted']) ? $language['off'] : $language['on']; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th>
								<?php echo $language['informations']; ?>: <?php echo $language['permission']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="text-align:center;">
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
														
														echo "<img src=\"".$icon_src."\" alt=\"".$sg_value['name']."\" style=\"margin-left:5px;margin-right:5px;\" 
															data-tooltip=\"tooltip\" data-placement=\"top\" title=\"".htmlspecialchars($sg_value['name'])."\" width=\"16\" height=\"16\">";
													};
												};
											};
										};
										
										// Channelgruppen
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
														if(file_exists($icon_src))
														{
															echo "<img src=\"".$icon_src."\" alt=\"".$cg_value['name']."\" style=\"margin-left:5px;margin-right:5px;\" 
																data-tooltip=\"tooltip\" data-placement=\"top\" title=\"".htmlspecialchars($cg_value['name'])."\" width=\"16\" height=\"16\">";
														};
													};
												};
											};
										};
									?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['informations']; ?>: Version / IDs
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['ts_client_version']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_version']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts_client_plattform']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_platform']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts_client_id']; ?>:
								</td>
								<td>
									<?php echo $_REQUEST['id']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts_client_database_id']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_database_id']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts_client_unique_id']; ?>:
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
									<?php echo htmlspecialchars($clientInfo['data']['client_description']); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts_client_talkpower']; ?>:
								</td>
								<td>
									<?php echo $clientInfo['data']['client_talk_power']; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php if(strpos($user_right['ports']['right_web_client_actions'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
					<div class="tab-pane" id="clientActions">
						<table class="table table-condensed table-hover">
							<thead>
								<th>
									<?php echo $language['ts_mass_msg_poke']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectMessagePoke" class="form-control input-sm contentElements">
									<option value="1" selected><?php echo $language['message']; ?></option>
									<option value="2"><?php echo $language['poke']; ?></option>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessagePoke" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientMsg('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['accept']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['ts_move']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectMoveInChannel" class="form-control input-sm contentElements">
									<option style="display:none;" selected><?php echo $language['where']; ?>?</option>
									<optgroup label="<?php echo $language['ts_channel']; ?>">
										<?php echo $channelTree; ?>
									</optgroup>
									<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
										<?php echo $subChannelTree; ?>
									</optgroup>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12 col-lg-offset-4">
								<button onClick="clientMove('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['accept']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['ts_kick']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<select id="selectKickStyle" class="form-control input-sm contentElements" <?php echo ($clientInfo['data']['channel_flag_default'] == 1) ?  "disabled" : ""; ?>>
									<option value="server" selected><?php echo $language['ts_serverkick']; ?></option>
									<option value="channel"><?php echo $language['ts_channelkick']; ?></option>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageKick" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientKick('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['accept']; ?></button>
							</div>
						</div>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['ts_ban']; ?>
								</th>
							</thead>
						</table>
						<div class="row">
							<div class="col-lg-4 col-md-12">
								<input id="inputBanTime" type="number" class="form-control input-sm contentElements" placeholder="0">
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageBan" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="clientBan('<?php echo $_REQUEST['id']; ?>')" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['accept']; ?></button>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if(strpos($user_right['ports']['right_web_client_rights'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
					<div class="tab-pane" id="clientRights">
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['permission']; ?>: <?php echo $language['ts_sgroup']; ?>
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
												if(in_array($sg_value['sgid'], $getsgroups))
												{
													echo "<tr><td id=\"sgroup_" . $sg_value['sgid'] . "\" onClick=\"addRemoveSRights('" . $clientInfo['data']['client_database_id'] . "', '" . $sg_value['sgid'] . "', 'sgroup_" . $sg_value['sgid'] . "')\" class=\"table-success text-success\" style=\"text-align:center;cursor:pointer;\">";
													$iconid									=	$sg_value['iconid'];
													if($iconid < 0) // Standertservergruppe
													{
														$iconid								=	sprintf('%u', $iconid & 0xffffffff);
													};
													if($iconid != 0) // Hochgeladene Servergruppe
													{
														$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
														
														echo "<img src=\"".$icon_src."\" alt=\"".htmlspecialchars($sg_value['name'])."\" style=\"margin-left:5px;margin-right:5px;\" width=\"16\" height=\"16\">".htmlspecialchars($sg_value['name']);
													}
													else
													{
														echo htmlspecialchars($sg_value['name']);
													};
													echo "</td></tr>";
												}
												else
												{
													echo "<tr><td id=\"sgroup_" . $sg_value['sgid'] . "\" onClick=\"addRemoveSRights('" . $clientInfo['data']['client_database_id'] . "', '" . $sg_value['sgid'] . "', 'sgroup_" . $sg_value['sgid'] . "')\" class=\"table-danger text-danger\" style=\"text-align:center;cursor:pointer;\">";
													$iconid									=	$sg_value['iconid'];
													if($iconid < 0) // Standertservergruppe
													{
														$iconid								=	sprintf('%u', $iconid & 0xffffffff);
													};
													if($iconid != 0) // Hochgeladene Servergruppe
													{
														$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
														
														echo "<img src=\"".$icon_src."\" alt=\"".htmlspecialchars($sg_value['name'])."\" style=\"margin-left:5px;margin-right:5px;\" width=\"16\" height=\"16\">".htmlspecialchars($sg_value['name']);
													}
													else
													{
														echo htmlspecialchars($sg_value['name']);
													};
													echo "</td></tr>";
												};
											};
										};
									};
								?>
							</tbody>
						</table>
						<table class="table table-condensed">
							<thead>
								<th>
									<?php echo $language['permission']; ?>: <?php echo $language['ts_cgroup']; ?>
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
												if($cg_value['cgid'] == $clientInfo['data']['client_channel_group_id'])
												{
													echo "<tr><td  id=\"cgroup_" . $cg_value['cgid'] . "\" onClick=\"addRemoveCRights('" . $clientInfo['data']['client_channel_group_inherited_channel_id'] . "', '" . $clientInfo['data']['client_channel_group_id'] . "', '" . $clientInfo['data']['client_database_id'] . "', '" . $cg_value['cgid'] . "', 'cgroup_" . $cg_value['cgid'] . "')\" class=\"table-success text-success\" style=\"text-align:center;cursor:pointer;\">";
													$iconid									=	$cg_value['iconid'];
													
													if($iconid < 0) // Standertservergruppe
													{
														$iconid								=	sprintf('%u', $iconid & 0xffffffff);
													};
													if($iconid != 0) // Hochgeladene Servergruppe
													{
														$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
														echo "<img src=\"".$icon_src."\" alt=\"".htmlspecialchars($cg_value['name'])."\" style=\"margin-left:5px;margin-right:5px;\" width=\"16\" height=\"16\">".htmlspecialchars($cg_value['name']);
													}
													else
													{
														echo htmlspecialchars($cg_value['name']);
													};
													echo "</td></tr>";
												}
												else
												{
													echo "<tr><td  id=\"cgroup_" . $cg_value['cgid'] . "\" onClick=\"addRemoveCRights('" . $clientInfo['data']['client_channel_group_inherited_channel_id'] . "', '" . $clientInfo['data']['client_channel_group_id'] . "', '" . $clientInfo['data']['client_database_id'] . "', '" . $cg_value['cgid'] . "', 'cgroup_" . $cg_value['cgid'] . "')\" class=\"table-danger text-danger\" style=\"text-align:center;cursor:pointer;\">";
													$iconid									=	$cg_value['iconid'];
													
													if($iconid < 0) // Standertservergruppe
													{
														$iconid								=	sprintf('%u', $iconid & 0xffffffff);
													};
													if($iconid != 0) // Hochgeladene Servergruppe
													{
														$icon_src 							= 	get_icon($ts3_server[$_REQUEST['instanz']]['ip'], "icon_".$iconid, $server['data']['virtualserver_port']);
														echo "<img src=\"".$icon_src."\" alt=\"".htmlspecialchars($cg_value['name'])."\" style=\"margin-left:5px;margin-right:5px;\" width=\"16\" height=\"16\">".htmlspecialchars($cg_value['name']);
													}
													else
													{
														echo htmlspecialchars($cg_value['name']);
													};
													echo "</td></tr>";
												};
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

<!-- Sprachdatein laden -->
<script language="JavaScript">
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var clientChannelGroupId		=	-1;
	
	var langClientMsg				=	'<?php echo $language['client_message']; ?>';
	var langClientPoke				=	'<?php echo $language['client_poke']; ?>';
	var langClientMove				=	'<?php echo $language['client_move']; ?>';
	var langClientKick				=	'<?php echo $language['client_kick']; ?>';
	var langClientBan				=	'<?php echo $language['client_ban']; ?>';
	
	$(document).ready(function() {
		$('[data-tooltip="tooltip"]').tooltip();
	});
</script>