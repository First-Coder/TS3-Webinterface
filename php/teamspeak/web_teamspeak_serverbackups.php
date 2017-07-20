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
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
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
	$tsAdmin = new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_backups'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite(RELOAD_TO_SERVERVIEW);
		};
	}
	else
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Function to get Backups
	*/
	function getBackups($kind)
	{
		$path		=	__DIR__."/../../files/backups/".$kind."/";
		$files		=	array();
		$i			=	0;
		
		if(is_dir($path))
		{
			if($handle = opendir($path))
			{
				while (($file = readdir($handle)) !== false)
				{
					if(strpos($file, '.json') !== false)
					{
						$files[$i++]	=	$file;
					};
				};
				closedir($handle);
			};
		};
		
		return $files;
	};
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
		
		<button onClick="AreYouSure('<?php echo $language['reset_server']; ?>', 'resetServer();');" class="btn btn-success" style="width: 100%;"><i class="fa fa-mail-reply-all" aria-hidden="true"></i> <?php echo $language['reset_server']; ?></button>
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
					<?php echo $language['channel']; ?>
				</label>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input onChange="slideBackups('up');" name="backupArtRadio" type="radio">
					<span class="c-indicator"></span>
					<?php echo $language['server']; ?>
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
						<?php echo $language['channelname_and_settings']; ?>
					</label>
				</div>
			</div>
		</div>
		
		<button onClick="createBackup()" class="btn btn-success" style="width: 100%;"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo $language['create']; ?></button>
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
		
		<p style="margin-top: 20px;"><?php echo $language['backup_upload_info2']; ?></p>
		<ol class="custom-list">
			<li><a onClick="return false;"><?php echo $language['backup_upload_info2_all']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['backup_upload_info2_none']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['backup_upload_info2_server']; ?></a></li>                 
		</ol>
	</div>
</div>

<!-- Channelbackups settings -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['backup_channel_all']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="channelBackupNameSettingsTable" data-card-view="false" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-click-to-select="true">
			<thead>
				<th data-field="instanz" data-align="center" data-halign="left"><?php echo $language['instance']; ?></th>
				<th data-field="port" data-align="center" data-halign="left"><?php echo $language['port']; ?></th>
				<th data-field="date" data-align="center" data-halign="left" class="hidden-md-down"><?php echo $language['date']; ?></th>
				<th data-field="actions" data-align="center" data-halign="left"><?php echo $language['actions']; ?></th>
				<th data-field="id" data-visible="false"><!-- Temp --></th>
			</thead>
			<tbody>
				<?php
					foreach(getBackups("channelnamesettings") AS $value)
					{
						$valueParts				=	explode("_", $value);
						$valueParts[8]			=	str_replace(".json", "", $valueParts[8]);
						
						if(strpos($value, "_all") !== false && $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $LinkInformations['instanz']))
						{
							echo "<tr>
									<td>
										".$valueParts[1]."
									</td>
									<td>
										".$valueParts[3]."
									</td>
									<td class=\"hidden-md-down\">
										".$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4]."
									</td>
									<td>
										<button class=\"btn btn-success btn-sm\" onClick=\"AreYouSure('".$language['activate_backup']."', 'activateBackup(\'".$value."\', \'channelnamesettings\');');\"><i class=\"fa fa-check\"></i> <font class=\"hidden-md-down\">".$language['activate']."</font></button>
										<a href=\"./php/functions/functionsDownload.php?action=downloadBackup&port=".$server['data']['virtualserver_port']."&instanz=".$LinkInformations['instanz']."&type=channelnamesettings&name=".$value."\">
											<button class=\"btn btn-primary btn-sm\"><i class=\"fa fa-download\"></i> <font class=\"hidden-md-down\">".$language['download']."</font></button>
										</a>
										<button class=\"btn btn-danger btn-sm\" onClick=\"AreYouSure('".$language['delete_backup']."', 'deleteBackup(\'channelnamesettings/".$value."\', \'channelBackupNameSettingsTable\', \'".str_replace(".json", "", $value)."\');');\"><i class=\"fa fa-trash\"></i> <font class=\"hidden-md-down\">".$language['delete']."</font></button>
									</td>
									<td>".str_replace(".json", "", $value)."</td>
								</tr>";
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
			<i class="fa fa-upload"></i> <?php echo $language['backup_channel_name']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="channelBackupNameTable" data-card-view="false" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-click-to-select="true">
			<thead>
				<th data-field="instanz" data-align="center" data-halign="left"><?php echo $language['instance']; ?></th>
				<th data-field="port" data-align="center" data-halign="left"><?php echo $language['port']; ?></th>
				<th data-field="date" data-align="center" data-halign="left" class="hidden-md-down"><?php echo $language['date']; ?></th>
				<th data-field="actions" data-align="center" data-halign="left"><?php echo $language['actions']; ?></th>
				<th data-field="id" data-visible="false"><!-- Temp --></th>
			</thead>
			<tbody>
				<?php
					foreach(getBackups("channelname") AS $value)
					{
						$valueParts				=	explode("_", $value);
						$valueParts[8]			=	str_replace(".json", "", $valueParts[8]);
						
						if(strpos($value, "_all") === false && $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $LinkInformations['instanz']))
						{
							echo "<tr>
									<td>
										".$valueParts[1]."
									</td>
									<td>
										".$valueParts[3]."
									</td>
									<td class=\"hidden-md-down\">
										".$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4]."
									</td>
									<td>
										<button class=\"btn btn-success btn-sm\" onClick=\"AreYouSure('".$language['activate_backup']."', 'activateBackup(\'".$value."\', \'channelname\');');\"><i class=\"fa fa-check\"></i> <font class=\"hidden-md-down\">".$language['activate']."</font></button>
										<a href=\"./php/functions/functionsDownload.php?action=downloadBackup&port=".$server['data']['virtualserver_port']."&instanz=".$LinkInformations['instanz']."&type=channelname&name=".$value."\">
											<button class=\"btn btn-primary btn-sm\"><i class=\"fa fa-download\"></i> <font class=\"hidden-md-down\">".$language['download']."</font></button>
										</a>
										<button class=\"btn btn-danger btn-sm\" onClick=\"AreYouSure('".$language['delete_backup']."', 'deleteBackup(\'channelname/".$value."\', \'channelBackupNameTable\', \'".str_replace(".json", "", $value)."\');');\"><i class=\"fa fa-trash\"></i> <font class=\"hidden-md-down\">".$language['delete']."</font></button>
									</td>
									<td>".str_replace(".json", "", $value)."</td>
								</tr>";
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
			<i class="fa fa-upload"></i> <?php echo $language['backup_server']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="serverBackupTable" data-card-view="false" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-click-to-select="true">
			<thead>
				<th data-field="instanz" data-align="center" data-halign="left"><?php echo $language['instance']; ?></th>
				<th data-field="port" data-align="center" data-halign="left"><?php echo $language['port']; ?></th>
				<th data-field="date" data-align="center" data-halign="left" class="hidden-md-down"><?php echo $language['date']; ?></th>
				<th data-field="actions" data-align="center" data-halign="left"><?php echo $language['actions']; ?></th>
				<th data-field="id" data-visible="false"><!-- Temp --></th>
			</thead>
			<tbody>
				<?php
					foreach(getBackups("server") AS $value)
					{
						$valueParts				=	explode("_", $value);
						$valueParts[8]			=	str_replace(".json", "", $valueParts[8]);
						
						if(strpos($value, "_all") === false && $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'] || ($valueParts[3] == $server['data']['virtualserver_port'] && $valueParts[1] == $LinkInformations['instanz']))
						{
							echo "<tr>
									<td>
										".$valueParts[1]."
									</td>
									<td>
										".$valueParts[3]."
									</td>
									<td class=\"hidden-md-down\">
										".$valueParts[7].":".$valueParts[8]."-".$valueParts[6].".".$valueParts[5].".".$valueParts[4]."
									</td>
									<td>
										<button class=\"btn btn-success btn-sm\" onClick=\"AreYouSure('".$language['activate_backup']."', 'activateBackup(\'".$value."\', \'server\');');\"><i class=\"fa fa-check\"></i> <font class=\"hidden-md-down\">".$language['activate']."</font></button>
										<a href=\"./php/functions/functionsDownload.php?action=downloadBackup&port=".$server['data']['virtualserver_port']."&instanz=".$LinkInformations['instanz']."&type=server&name=".$value."\">
											<button class=\"btn btn-primary btn-sm\"><i class=\"fa fa-download\"></i> <font class=\"hidden-md-down\">".$language['download']."</font></button>
										</a>
										<button class=\"btn btn-danger btn-sm\" onClick=\"AreYouSure('".$language['delete_backup']."', 'deleteBackup(\'server/".$value."\', \'serverBackupTable\', \'".str_replace(".json", "", $value)."\');');\"><i class=\"fa fa-trash\"></i> <font class=\"hidden-md-down\">".$language['delete']."</font></button>
									</td>
									<td>".str_replace(".json", "", $value)."</td>
								</tr>";
						};
					};
				?>
			</tbody>
		</table>
	</div>
</div>

<script src="js/sonstige/dropzone.js"></script>
<script src="js/bootstrap/bootstrap-table.js"></script>
<script>
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	
	Dropzone.autoDiscover = false;
	
	$('#file-dropzone').dropzone({
		url: "./php/functions/functionsUploadBackup.php",
		method: "POST",
		acceptedFiles: '.json',
		dictDefaultMessage: lang.backup_upload_info,
		destination: "/files/backups",
		accept: function(file, done)
		{
			done();
		},
		success: function(file, data) {
			if(data == "done")
			{
				setNotifySuccess(lang.upload_successful);
			}
			else
			{
				setNotifyFailed(data);
			};
		}
	});
	
	$('#channelBackupNameTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
	
	$('#channelBackupNameSettingsTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
	
	$('#serverBackupTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>