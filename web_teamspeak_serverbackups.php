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
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
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
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_server_backups'][$serverInstanz], $server['data']['virtualserver_port']) === false)
				&& $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server']) || $user_right['right_web'] != $mysql_keys['right_web'])
		{
			echo '<script type="text/javascript">';
			echo 	'window.location.href="'.$urlData[0].'";';
			echo '</script>';
		};
	}
	else
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	// Channelbackups abfragen
	$channelBackupFiles			=	getBackups("./backup/channel/");
?>

<!-- Server zuruecksetzen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-mail-reply-all"></i> <?php echo $language['reset_server']; ?>
		</h4>
	</div>
	<div class="card-block">
		<p style="text-align: center;"><?php echo $language['reset_server_info']; ?></p>
		
		<button onClick="resetServer('<?php echo $server['data']['virtualserver_port']; ?>', '<?php echo $serverInstanz; ?>')" class="btn btn-success" style="width: 100%;"><i class="fa fa-mail-reply-all" aria-hidden="true"></i> <?php echo $language['reset_server']; ?></button>
	</div>
</div>

<!-- Backup erstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-edit"></i> <?php echo $language['create_backup']; ?>
		</h4>
	</div>
	<div class="card-block">
		<p><?php echo $language['what_want_backup']; ?></p>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input id="backupChannel" onChange="slideBackups('down');" name="backupArtRadio" type="radio" checked>
					<span class="c-indicator"></span>
					<?php echo $language['ts_channel']; ?>
				</label>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input onChange="slideBackups('up');" name="backupArtRadio" type="radio">
					<span class="c-indicator"></span>
					<?php echo $language['ts_server']; ?>
				</label>
			</div>
		</div>
		
		<div id="slideBackups">
			<p><?php echo $language['what_kind_channel_backup']; ?></p>
			<div class="row">
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
					<label class="c-input c-radio">
						<input id="backupChannelName" name="backupArtChannelRadio" type="radio" checked>
						<span class="c-indicator"></span>
						<?php echo $language['just_channelname']; ?>
					</label>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
					<label class="c-input c-radio">
						<input name="backupArtChannelRadio" type="radio">
						<span class="c-indicator"></span>
						<?php echo $language['channelname_and_channelsettings']; ?>
					</label>
				</div>
			</div>
		</div>
		
		<button onClick="createBackup('<?php echo $server['data']['virtualserver_port']; ?>', '<?php echo $serverInstanz; ?>')" class="btn btn-success" style="width: 100%;"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $language['create']; ?></button>
	</div>
</div>

<!-- Backup hochladen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['backup_upload']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<form class="dropzone" drop-zone="" id="file-dropzone"></form>
			</div>
		</div>
		<p><?php echo $language['backup_upload_info2']; ?></p>
	</div>
</div>

<!-- Channelbackups settings -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['ts_backup_channel_all']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed table-hover">
			<thead>
				<tr>
					<th>
						<?php echo $language['dataname']; ?>
					</th>
					<th>
						<?php echo $language['actions']; ?>
					</th>
				</tr>
			</thead>
			<tbody id="channelBackupTable">
				<?php
					foreach($channelBackupFiles AS $value)
					{
						$valueParts				=	explode("_", $value);
						$valueParts[8]			=	str_replace(".json", "", $valueParts[8]);
						
						if(strpos($value, "_all") !== false)
						{
							if($user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $serverInstanz))
							{
								echo "<tr id=".str_replace(".json", "", $value).">";
								echo	"<td>";
								echo		$language['instance'].": ".$valueParts[1]."; Port: ".$valueParts[3]."; ".$language['date'].": ";
								echo		$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4];
								echo	"</td>";
								echo 	"<td>";
								echo		"<a onClick=\"activateBackupChannel('".$value."', 'activate_backup_channel_all')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
								echo		"<a download href=\"./backup/channel/".$value."\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
								echo 		"<a onClick=\"deleteBackup('".$value."', 'channel');\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
								echo	"</td>";
								echo "</tr>";
							};
						};
					};
				?>
			</tbody>
		</table>
	</div>
</div>

<!-- Channelbackups -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['ts_backup_channel_name']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed table-hover">
			<thead>
				<tr>
					<th>
						<?php echo $language['dataname']; ?>
					</th>
					<th>
						<?php echo $language['actions']; ?>
					</th>
				</tr>
			</thead>
			<tbody id="channelBackupTable">
				<?php
					foreach($channelBackupFiles AS $value)
					{
						$valueParts				=	explode("_", $value);
						$valueParts[8]			=	str_replace(".json", "", $valueParts[8]);
						
						if(strpos($value, "_all") === false)
						{
							if($user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $serverInstanz))
							{
								echo "<tr id=".str_replace(".json", "", $value).">";
								echo	"<td>";
								echo		$language['instance'].": ".$valueParts[1]."; Port: ".$valueParts[3]."; ".$language['date'].": ";
								echo		$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4];
								echo	"</td>";
								echo 	"<td>";
								echo		"<a onClick=\"activateBackupChannel('".$value."', 'activate_backup_channel')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
								echo		"<a download href=\"./backup/channel/".$value."\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
								echo 		"<a onClick=\"deleteBackup('".$value."', 'channel');\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
								echo	"</td>";
								echo "</tr>";
							};
						};
					};
				?>
			</tbody>
		</table>
	</div>
</div>

<!-- Serverbackups -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['ts_backup_server']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed table-hover">
			<thead>
				<tr>
					<th>
						<?php echo $language['dataname']; ?>
					</th>
					<th>
						<?php echo $language['actions']; ?>
					</th>
				</tr>
			</thead>
			<tbody id="serverBackupTable">
				<?php
					$serverBackupFiles		=	getBackups("./backup/server/");
					foreach($serverBackupFiles AS $value)
					{
						if($user_right['right_web_global_server'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $serverInstanz))
						{
							$valueParts				=	explode("_", $value);
							
							echo "<tr id=".str_replace(".json", "", $value).">";
							echo	"<td>";
							echo		$language['instance'].": ".$valueParts[1]."; Port: ".$valueParts[3]."; ".$language['date'].": ";
							echo		$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4];
							echo	"</td>";
							echo 	"<td>";
							echo		"<a onClick=\"activateBackupServer('".$value."')\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-check\"></i></a>";
							echo		"<a download href=\"./backup/server/".$value."\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-download\"></i></a>";
							echo 		"<a onClick=\"deleteBackup('".$value."', 'server');\" href=\"#\"><i style=\"text-align:center;width:33%;\" class=\"fa fa-trash\"></i></a>";
							echo	"</td>";
							echo "</tr>";
						};
					};
				?>
			</tbody>
		</table>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/sonstige/dropzone.js"></script>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	
	var ts_backup_restored			=	'<?php echo $language['ts_backup_restored']; ?>';
	var reset_server_success		=	'<?php echo $language['reset_server_success']; ?>';
	
	// Backup hochladen
	$('#file-dropzone').dropzone({
		url: "uploadBackup.php",
		method: "POST",
		acceptedFiles: '.json',
		destination: "/backup",
		dictDefaultMessage: '<?php echo $language['backup_upload_info']; ?>',
		accept: function(file, done)
		{
			done();
		},
		success: function(data) {
			if(data == "done")
			{
				$.notify({
					title: '<strong>'+success+'</strong><br />',
					message: "Upload erfolgreich!",
					icon: 'fa fa-check'
				},{
					type: 'success',
					allow_dismiss: true,
					placement:
					{
						from: 'bottom',
						align: 'right'
					}
				});
			}
			else
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: data,
					icon: 'fa fa-warning'
				},{
					type: 'danger',
					allow_dismiss: true,
					placement:
					{
						from: 'bottom',
						align: 'right'
					}
				});
			};
		}
	});
</script>
<script src="js/sonstige/preloader.js"></script>