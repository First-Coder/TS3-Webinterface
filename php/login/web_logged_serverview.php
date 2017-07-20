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
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Variables
	*/
	$LoggedIn				=	(checkSession()) ? true : false;
	$LinkInformations		=	getLinkInformations();
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRights('pk', $_SESSION['user']['id']);
	
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'] || empty($LinkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server 			= 	$tsAdmin->serverInfo();
		
		getTeamspeakIcons($tsAdmin, $server['data']['virtualserver_port'], $ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport'], $ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
	}
	else
	{
		reloadSite();
	};
?>

<!-- Zurueck -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 onClick="goBackToMain();" class="card-title navigationitem"><i class="fa fa-arrow-left"></i> <?php echo $language['back']; ?></h4>
	</div>
</div>

<!-- Server -->
<?php if(isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
	<div class="card hidden-md-down">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-server"></i> <?php echo $language['server']; ?></h4>
		</div>
		<div class="card-block">
			<div class="navigationitem" onClick="toggleStartStopTeamspeakserver('<?php echo $LinkInformations['sid']; ?>', '<?php echo $LinkInformations['instanz']; ?>', '<?php echo $server['data']['virtualserver_port']; ?>')">
				<input id="serverStartStop" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="<?php echo $language['online']; ?>" data-off="<?php echo $language['offline']; ?>" <?php if($server['data']['virtualserver_status'] == 'online') { echo 'checked'; } ?>>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Teamspeak 3 -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><img src="images/tsLogo.png" width="28" style="margin-top: -3px;"/> <?php echo htmlspecialchars($server['data']['virtualserver_name']); ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem teamspeakView" onClick="teamspeakViewInit();">
			<div style="float:left;">
				<?php echo $language['server_overview']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-eye"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_banner') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakServerBanner <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakServerBannerInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['serverbanner']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-address-card-o"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_protokoll') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakProtokol <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakProtokolInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['protokoll']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-list"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_mass_actions') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakMassActions <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakMassActionsInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['mass_actions']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-users"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_icons') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakIcons <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakIconsInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['icons']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-file"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_clients') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakClients <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakClientsInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['client']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-user"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_bans') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakBans <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBansInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['bans']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-ban"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_token') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakToken <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakTokenInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['token']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-key"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_file_transfer') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakFilelist <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakFilelistInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['filelist']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-file"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_backups') && $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server'])
			{
				$permission		=	false;
			}
			else
			{
				$permission		=	true;
			};
		?>
		<div class="navigationitem teamspeakBackup <?php echo ($permission) ? "" : "text-danger"; ?>" onClick="<?php echo ($permission) ? "teamspeakBackupsInit();" : ""; ?>">
			<div style="float:left;">
				<?php echo $language['backups']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-upload"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var serverId					=	'<?php echo $LinkInformations['sid']; ?>';
	var instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	var tsStatus					=	'<?php echo $server['data']['virtualserver_status']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/webinterface/teamspeak.js"></script>