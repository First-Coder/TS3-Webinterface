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
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Get Link information
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	$serverInstanz			=	$urlData[2];
	$serverId				=	$urlData[3];
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	if($serverInstanz == '' || $serverId == '' || $mysql_modul['webinterface'] != 'true')
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
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
		
		// Channel abfragen
		$channels	=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		
		// Server- und Channelgruppen abfragen
		$sgroups	=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups	=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_mass_actions'][$serverInstanz], $server['data']['virtualserver_port']) === false)
				&& $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server']) || $user_right['right_web'] != $mysql_keys['right_web'])
		{
			echo '<script type="text/javascript">';
			echo 	'window.location.href="'.$urlData[0].'";';
			echo '</script>';
		};
		
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
		
		// Channelgruppen
		if(!empty($cgroups))
		{
			$cgroup			= "";
			foreach($cgroups AS $key => $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$cgroup .= "<option group='cgroup' value='ts3-channelgroup-id-" . $value['cgid'] . "'>" . htmlspecialchars($value['name']) . "</option>";
				};
			};
		};
		
		// Servergruppen
		if(!empty($sgroups))
		{
			$sgroup			= "";
			foreach($sgroups AS $key => $value)
			{
				if ($value['type'] != '2' AND $value['type'] != '0')
				{
					$sgroup .= "<option group='sgroup' value='ts3-servergroup-id-" . $value['sgid'] . "'>" . htmlspecialchars($value['name']) . "</option>";
				};
			};
		};
	}
	else
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<!-- Nachrichten / Poke -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['ts_mass_msg_poke']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke()" id="selectMessagePoke" class="form-control massactions-select">
					<option value="1" selected><?php echo $language['message']; ?></option>
					<option value="2"><?php echo $language['poke']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke()" id="selectMessagePokeChannel" class="form-control massactions-select">
					<option value="none" selected><?php echo $language['ts_mass_all_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke()" id="selectMessagePokeGroup" class="form-control massactions-select">
					<option group="all" selected><?php echo $language['ts_mass_all_groups']; ?></option>
					<optgroup label="<?php echo $language['ts_sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessagePoke" class="form-control" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="actionMessagePoke()" class="btn btn-success" style="width:100%;margin-top:10px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoMessagePoke"><?php echo $language['ts_mass_catched_clients']; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Verschieben -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-arrows"></i> <?php echo $language['ts_move']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove()" id="selectMoveFromChannel" class="form-control massactions-select">
					<option value="" style="display:none;" selected><?php echo $language['ts_mass_move_from_channel']; ?></option>
					<option value="none"><?php echo $language['ts_mass_all_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove()" id="selectMoveInChannel" class="form-control massactions-select">
					<option value="" style="display:none;" selected><?php echo $language['ts_mass_move_in_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove()" id="selectMoveGroup" class="form-control massactions-select">
					<option group="all" selected><?php echo $language['ts_mass_all_groups']; ?></option>
					<optgroup label="<?php echo $language['ts_sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-4 col-lg-offset-8 col-md-4 col-md-offset-8">
				<button onClick="actionMove()" class="btn btn-success" style="width:100%;margin-top:10px;margin-bottom:10px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoMove"><?php echo $language['ts_mass_catched_clients']; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Kicken -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-user-times"></i> <?php echo $language['ts_kick']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick()" id="selectKickStyle" class="form-control massactions-select">
					<option value="server" selected><?php echo $language['ts_serverkick']; ?></option>
					<option value="channel"><?php echo $language['ts_channelkick']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick()" id="selectKickChannel" class="form-control massactions-select">
					<option value="none"><?php echo $language['ts_mass_all_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick()" id="selectKickGroup" class="form-control massactions-select">
					<option group="all" selected><?php echo $language['ts_mass_all_groups']; ?></option>
					<optgroup label="<?php echo $language['ts_sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessageKick" class="form-control" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="actionKick()" class="btn btn-success" style="width:100%;margin-top:10px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoKick"><?php echo $language['ts_mass_catched_clients']; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Ban -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-ban"></i> <?php echo $language['ts_ban']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<input style="margin-top:0;" class="form-control" type="number" id="inputBanTime" placeholder="<?php echo $language['ts_mass_time_min']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeBan()" id="selectBanChannel" class="form-control massactions-select">
					<option value="none"><?php echo $language['ts_mass_all_channel']; ?></option>
					<optgroup label="<?php echo $language['ts_channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeBan()" id="selectBanGroup" class="form-control massactions-select">
					<option group="all" selected><?php echo $language['ts_mass_all_groups']; ?></option>
					<optgroup label="<?php echo $language['ts_sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['ts_cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessageBan" class="form-control" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="actionBan()" class="btn btn-success" style="width:100%;margin-top:10px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoBan"><?php echo $language['ts_mass_catched_clients']; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var ts3_mass_no_affected_user 	= 	'<?php echo $language['ts3_mass_no_affected_user']; ?>';
	
	var instanz						=	'<?php echo $serverInstanz; ?>';
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	// Funktionen ausfuehren
	massactionsChangeMessagePoke();
	massactionsChangeKick();
	massactionsChangeBan();
</script>
<script src="js/sonstige/preloader.js"></script>
