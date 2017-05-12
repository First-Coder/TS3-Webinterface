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
		Get the Modul Keys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
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
	if((strpos($user_right['right_web_server_view'][$_REQUEST['instanz']], $_REQUEST['port']) === false && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])  || $user_right['right_web']['key'] != $mysql_keys['right_web'])
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
		
		$channelInfo	=	$tsAdmin->channelInfo($_REQUEST['id']);
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		$channelTree	=	getChannelTree($channels, false);
		$subChannelTree	=	getChannelTree($channels, true);
	};
?>

<div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
		<div class="modal-header alert-info">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="modalLabel"><?php echo htmlspecialchars($channelInfo['data']['channel_name']); ?></h4>
		</div>
		<div class="modal-body">
			<!-- Navigation -->
			<ul class="nav nav-pills">
				<li class="nav-item channelViewPills">
					<a class="nav-link active" href="#channelInformations" data-toggle="tab"><?php echo $language['informations']; ?></a>
				</li>
				<li class="nav-item channelViewPills">
					<?php if(strpos($user_right['right_web_channel_actions'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
						<a class="nav-link" href="#channelActions" data-toggle="tab"><?php echo $language['actions']; ?></a>
					<?php } else { ?>
						<a class="nav-link disabled" href="#"><?php echo $language['actions']; ?></a>
					<?php } ?>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="channelInformations">
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['informations']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['topic']; ?>:
								</td>
								<td>
									<?php xssEcho($channelInfo['data']['channel_topic']); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['description']; ?>:
								</td>
								<td>
									<?php xssEcho($channelInfo['data']['channel_description']); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['password']; ?>:
								</td>
								<td>
									<?php echo ($channelInfo['data']['channel_flag_password'] == 1) ? $language['yes'] : $language['no']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_type']; ?>:
								</td>
								<td>
									<?php echo ($channelInfo['data']['channel_flag_permanent'] == 1) ? "Permanent" : "Semi-Permanent"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_default']; ?>:
								</td>
								<td>
									<?php echo ($channelInfo['data']['channel_flag_default'] == 1) ?  $language['yes'] : $language['no']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_talk_power']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_needed_talk_power']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_silence']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_forced_silence']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['icon_id']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_icon_id']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['channel_id']; ?>
								</td>
								<td>
									<?php echo $_REQUEST['id']; ?>
								</td>
							</tr>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['userinformations']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['ts3_channel_max_clients']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_maxclients']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_clients_family']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_maxfamilyclients']; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="table table-condensed table-hover">
						<thead>
							<th colspan="2">
								<?php echo $language['audioinformaions']; ?>
							</th>
						</thead>
						<tbody>
							<tr>
								<td style="width:40%">
									<?php echo $language['ts3_channel_codec']; ?>:
								</td>
								<td>
									<?php 
										switch($channelInfo['data']['channel_codec'])
										{
											case 1: echo "Speex Wideband (16 kHz)";			break;
											case 2: echo "Speex Ultra-Wideband (32 kHz)";	break;
											case 3: echo "CELT Mono (48 kHz)";				break;
											case 4: echo "Opus Voice";						break;
											case 5: echo "Opus Musik";						break;
											default: echo "Speex Narrowband (8 kHz)";		break;
										};
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $language['ts3_channel_codec_quality']; ?>:
								</td>
								<td>
									<?php echo $channelInfo['data']['channel_codec_quality']; ?>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="row">
						<div class="col-lg-3 col-md-3"></div>
						<div class="col-lg-6 col-md-6">
							<button data-dismiss="modal" onClick="deleteTeamspeakChannel('<?php echo $_REQUEST['id']; ?>', '<?php echo $_REQUEST['serverId']; ?>');" class="btn btn-sm btn-danger" style="width: 100%;"><i class="fa fa-fw fa-trash"></i> <?php echo $language['delete']; ?></button>
						</div>
						<div class="col-lg-3 col-md-3"></div>
					</div>
				</div>
				<?php if(strpos($user_right['right_web_channel_actions'][$_REQUEST['instanz']], $_REQUEST['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
					<div class="tab-pane" id="channelActions">
						<table class="table table-condensed">
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
							<div class="col-lg-4 col-md-12">
								<input id="inputMessagePoke" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="massactionsMassInfo('all', '', '<?php echo $_REQUEST['id']; ?>', 'msg', 1);" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
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
								<button onClick="massactionsMassInfo('all', '', '<?php echo $_REQUEST['id']; ?>', 'move', 1);" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
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
								<select id="selectKickStyle" class="form-control input-sm contentElements c-select" <?php echo ($channelInfo['data']['channel_flag_default'] == 1) ?  "disabled" : ""; ?>>
									<option value="server" selected><?php echo $language['serverkick']; ?></option>
									<option value="channel"><?php echo $language['channelkick']; ?></option>
								</select>
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageKick" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="massactionsMassInfo('all', '', '<?php echo $_REQUEST['id']; ?>', 'kick', 1);" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
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
								<input id="inputBanTime" type="number" class="form-control input-sm contentElements" placeholder="0">
							</div>
							<div  class="col-lg-4 col-md-12">
								<input id="inputMessageBan" class="form-control input-sm contentElements" placeholder="<?php echo $language['message']; ?>">
							</div>
							<div  class="col-lg-4 col-md-12">
								<button onClick="massactionsMassInfo('all', '', '<?php echo $_REQUEST['id']; ?>', 'ban', 1);" class="btn btn-sm btn-success contentElements"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>