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
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_icons'))
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
		Load the icons
	*/
	$handler2	=	@opendir('../../images/ts_icons/'.$ts3_server[$LinkInformations['instanz']]['ip'].'-'.$server['data']['virtualserver_port'].'/');
	if($handler2)
	{
		while($datei = readdir($handler2))
		{
			if($datei!='.' AND $datei!='..')
			{
				$icon_id						=	str_replace("icon_", "", $datei);
				$allicons[$datei]['name']		=	$icon_id;
				$allicons[$datei]['id']			=	sprintf('%u', $icon_id & 0xffffffff);
				$allicons[$datei]['info']		=	getimagesize('../../images/ts_icons/'.$ts3_server[$LinkInformations['instanz']]['ip'].'-'.$server['data']['virtualserver_port'].'/'.$datei);
			};
		};
	};
?>

<!-- Icon hochalden -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-upload"></i> <?php echo $language['icon_upload']; ?>
		</h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<form class="dropzone" drop-zone="" id="file-dropzone"></form>
			</div>
		</div>
	</div>
</div>

<!-- Iconliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-list"></i> <?php echo $language['icon_avalible']; ?>
		</h4>
	</div>
	<div class="card-block">
		<table id="fileTable" data-card-view="false" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-click-to-select="true">
			<thead>
				<tr>
					<th data-field="picture" data-align="center"><?php echo $language['picture']; ?></th>
					<th data-field="id" data-align="center" data-halign="left"><?php echo $language['icon_id']; ?></th>
					<th data-field="actions" data-align="center" data-halign="left"><?php echo $language['actions']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($allicons))
				{
					foreach($allicons AS $value)
					{
						$filename		=	'../../images/ts_icons/'.$ts3_server[$LinkInformations['instanz']]['ip'].'-'.$server['data']['virtualserver_port'].'/icon_'.$value['name'];
						$imgbinary 		= 	fread(fopen($filename, "r"), filesize($filename));
						echo '<tr>
								<td><img src="data:image/png;base64,'.base64_encode($imgbinary).'" width="16" height="16" alt="Icon Image"></td>
								<td>'.$value['name'].'</td>
								<td>
									<a href="./php/functions/functionsDownload.php?action=downloadIconFromIconlist&port='.$server['data']['virtualserver_port'].'&instanz='.$LinkInformations['instanz'].'&filename='.$value['name'].'">
										<button class="btn btn-success btn-sm mini-left-right-margin"><i class="fa fa-download"></i> <font class="hidden-md-down">'.$language['download'].'</font>
									</a>
									<button class="btn btn-danger btn-sm mini-left-right-margin" onClick="deleteIcon(\''.$value['name'].'\')"><i class="fa fa-trash"></i> <font class="hidden-md-down">'.$language['delete'].'</font>
								</td>
							</tr>';
					};
				}; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/sonstige/dropzone.js"></script>
<script src="js/bootstrap/bootstrap-table.js"></script>
<script>
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		serverId					=	'<?php echo $LinkInformations['sid']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	
	Dropzone.autoDiscover = false;
	
	$('#fileTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
	
	$('#file-dropzone').dropzone({
		url: "./php/functions/functionsUploadIcon.php",
		method: "POST",
		acceptedFiles: 'image/*',
		destination: "/images/ts_icons",
		dictDefaultMessage: lang.icon_upload_info,
		init: function() {
			this.on('thumbnail', function(file) {
				if ( file.width > 16 || file.height > 16 )
				{
					file.rejectDimensions();
				}
				else
				{
					file.acceptDimensions();
				};
			});
		},
		accept: function(file, done)
		{
			file.acceptDimensions = done;
			file.rejectDimensions = function() {
				done(lang.icon_upload_size)
			};
		}
	});
</script>
<script src="js/sonstige/preloader.js"></script>