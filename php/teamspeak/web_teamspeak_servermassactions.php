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
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
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
	$user_right				=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Get Link information
	*/
	$LinkInformations	=	getLinkInformations();
	
	if(empty($LinkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin 			= 	new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
		$sgroups		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
		$cgroups		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
		
		$channelTree	=	getChannelTree($channels, false);
		$subChannelTree	=	getChannelTree($channels, true);
		
		$sgroup			=	getGroupTree($sgroups, false);
		$cgroup			=	getGroupTree($cgroups, true);
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_mass_actions'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite(RELOAD_TO_SERVERVIEW);
		};
	}
	else
	{
		reloadSite(RELOAD_TO_MAIN);
	};
?>

<!-- Nachrichten / Poke -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['msg_poke']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke(0)" id="selectMessagePoke" class="form-control small-bottom-margin c-select">
					<option value="1" selected><?php echo $language['message']; ?></option>
					<option value="2"><?php echo $language['poke']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke(0)" id="selectMessagePokeChannel" class="form-control small-bottom-margin c-select">
					<option value="none" selected><?php echo $language['all_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMessagePoke(0)" id="selectMessagePokeGroup" class="form-control small-bottom-margin c-select">
					<option group="all" selected><?php echo $language['all_groups']; ?></option>
					<optgroup label="<?php echo $language['sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessagePoke" class="form-control small-bottom-margin" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="massactionsChangeMessagePoke(1)" class="btn btn-success small-bottom-margin" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoMessagePoke"><?php echo $language['catched_clients']; ?></p>
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
			<i class="fa fa-arrows"></i> <?php echo $language['move']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove(0)" id="selectMoveFromChannel" class="form-control small-bottom-margin c-select">
					<option value="" style="display:none;" selected><?php echo $language['from_channel']; ?></option>
					<option value="none"><?php echo $language['all_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove(0)" id="selectMoveInChannel" class="form-control small-bottom-margin c-select">
					<option value="" style="display:none;" selected><?php echo $language['in_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeMove(0)" id="selectMoveGroup" class="form-control small-bottom-margin c-select">
					<option group="all" selected><?php echo $language['all_groups']; ?></option>
					<optgroup label="<?php echo $language['sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-4 col-lg-offset-8 col-md-4 col-md-offset-8">
				<button onClick="massactionsChangeMove(1)" class="btn btn-success small-bottom-margin" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoMove"><?php echo $language['catched_clients']; ?></p>
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
			<i class="fa fa-user-times"></i> <?php echo $language['kick']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick(0)" id="selectKickStyle" class="form-control small-bottom-margin c-select">
					<option value="server" selected><?php echo $language['serverkick']; ?></option>
					<option value="channel"><?php echo $language['channelkick']; ?></option>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick(0)" id="selectKickChannel" class="form-control small-bottom-margin c-select">
					<option value="none"><?php echo $language['all_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeKick(0)" id="selectKickGroup" class="form-control small-bottom-margin c-select">
					<option group="all" selected><?php echo $language['all_groups']; ?></option>
					<optgroup label="<?php echo $language['sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessageKick" class="form-control small-bottom-margin" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="massactionsChangeKick(1)" class="btn btn-success small-bottom-margin" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoKick"><?php echo $language['catched_clients']; ?></p>
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
			<i class="fa fa-ban"></i> <?php echo $language['ban']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-4 col-md-12">
				<input style="margin-top:0;" class="form-control small-bottom-margin" type="number" id="inputBanTime" placeholder="<?php echo $language['time_min']; ?>">
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeBan(0)" id="selectBanChannel" class="form-control small-bottom-margin c-select">
					<option value="none"><?php echo $language['all_channel']; ?></option>
					<optgroup label="<?php echo $language['channel']; ?>">
						<?php echo $channelTree; ?>
					</optgroup>
					<optgroup label="<?php echo $language['sub_channel']; ?>">
						<?php echo $subChannelTree; ?>
					</optgroup>
				</select>
			</div>
			<div class="col-lg-4 col-md-12">
				<select onChange="massactionsChangeBan(0)" id="selectBanGroup" class="form-control small-bottom-margin c-select">
					<option group="all" selected><?php echo $language['all_groups']; ?></option>
					<optgroup label="<?php echo $language['sgroup']; ?>">
						<?php echo $sgroup; ?>
					</optgroup>
					<optgroup label="<?php echo $language['cgroup']; ?>">
						<?php echo $cgroup; ?>
					</optgroup>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<input id="inputMessageBan" class="form-control small-bottom-margin" type="text" placeholder="<?php echo $language['message']; ?>">
			</div>
			<div class="col-lg-4 col-md-4">
				<button onClick="massactionsChangeBan(1)" class="btn btn-success small-bottom-margin" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="jumbotron jumbotron-fluid">
					<div class="container">
						<p style="text-align:center;" id="infoBan"><?php echo $language['catched_clients']; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var instanz						=	'<?php echo $LinkInformations['instanz']; ?>',
		port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	massactionsChangeMessagePoke(0);
	massactionsChangeKick(0);
	massactionsChangeBan(0);
</script>
<script src="js/sonstige/preloader.js"></script>
