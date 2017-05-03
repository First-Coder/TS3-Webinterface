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
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Get Link information
	*/
	$urlData				=	split("\?", $_SERVER['REQUEST_URI'], -1);
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
		
		// Teamspeak Icons Herunterladen
		getTeamspeakIcons($server['data']['virtualserver_port'], $ts3_server[$serverInstanz]['ip'], $ts3_server[$serverInstanz]['queryport'], $ts3_server[$serverInstanz]['user'], $ts3_server[$serverInstanz]['pw']);
	}
	else
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<!-- Zurueck -->
<div class="card hidden-md-down">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-arrow-left"></i> <?php echo $language['back']; ?></h4>
	</div>
	<div class="card-block">
		<div class="navigationitem" onClick="goBackToMain();">
			<div style="float:left;">
				<?php echo $language['back']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-arrow-left"></i>
			</div>
			<div style="clear:both;"></div>
		</div>
	</div>
</div>

<!-- Server -->
<?php if(strpos($user_right['ports']['right_web_server_start_stop'][$serverInstanz], $server['data']['virtualserver_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']) { ?>
	<div class="card hidden-md-down">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-server"></i> Server</h4>
		</div>
		<div class="card-block">
			<div class="navigationitem" onClick="toggleStartStopTeamspeakserver('<?php echo $serverId; ?>', '<?php echo $serverInstanz; ?>', '<?php echo $server['data']['virtualserver_port']; ?>')">
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
				<?php echo $language['ts_serveroverview']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-eye"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_protokoll'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_protokol']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-list"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_mass_actions'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_mass_actions']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-users"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_icons'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_icons']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-file"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_clients'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
			if(strpos($user_right['ports']['right_web_server_bans'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_bans']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-ban"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_token'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_token']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-key"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_file_transfer'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_filelist']; ?>
			</div>
			<div style="float:right;padding-top:5px;">
				<i class="fa fa-file"></i>
			</div>
		</div>
		<div style="clear:both;"></div>
		
		<?php
			if(strpos($user_right['ports']['right_web_server_backups'][$serverInstanz], $server['data']['virtualserver_port']) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
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
				<?php echo $language['ts_backups']; ?>
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
	var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	var tsStatus					=	'<?php echo $server['data']['virtualserver_status']; ?>';
	
	var ts_server_started 			=	'<?php echo $language['ts_server_started']; ?>';
	var ts_server_stoped 			=	'<?php echo $language['ts_server_stoped']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/webinterface/teamspeak.js"></script>