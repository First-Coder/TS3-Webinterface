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
	$tsAdmin 			= 	new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList());
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_file_transfer'))
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
		Function to get the correct File Path
	*/
	function getChannelFiles($filelist, $cid, $path = "/")
	{
		global $tsAdmin;
		
		$returnFilelist					=	array();
		
		if(!empty($filelist))
		{
			foreach($filelist AS $file)
			{
				if(empty($file['path']))
				{
					$file['path'] 			= 	$path;
				};
				
				if($file['size'] == 0)
				{
					foreach(getChannelFiles($tsAdmin->getElement('data', $tsAdmin->ftGetFileList($cid, "", $path.$file['name'])), $cid, $path.$file['name']."/") AS $subFile)
					{
						if(empty($subFile['cid']))
						{
							$subFile['cid']		=	$cid;
						};
						
						if(substr($subFile['path'], -1) != "/")
						{
							$subFile['path']	.=	"/";
						};
						$returnFilelist[]		=	$subFile;
					};
				}
				else
				{
					if(empty($file['cid']))
					{
						$file['cid']		=	$cid;
					};
					
					if(substr($file['path'], -1) != "/")
					{
						$file['path']		.=	"/";
					};
					$returnFilelist[]		=	$file;
				};
			};
		};
		
		return $returnFilelist;
	};
?>

<!-- Dateiliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-file"></i> <?php echo $language['filelist']; ?></font>
		</h4>
	</div>
	<div class="card-block">
		<table id="fileTable" data-card-view="true" data-classes="table-no-bordered table-hover table"
			data-striped="true" data-pagination="true" data-search="true" data-click-to-select="true">
			<thead>
				<th data-field="channel"><?php echo $language['channel']; ?></th>
				<th data-field="path"><?php echo $language['folder_path']; ?></th>
				<th data-field="dataname"><?php echo $language['dataname']; ?></th>
				<th data-field="create_on"><?php echo $language['create_on']; ?></th>
				<th data-field="filesize"><?php echo $language['filesize']; ?></th>
				<th data-field="actions"><?php echo $language['actions']; ?></th>
				<th data-field="id" data-visible="false"><!-- Temp --></th>
			</thead>
			<tbody>
				<?php if(!empty($channels))
				{
					foreach($channels AS $channel)
					{
						$filelist						=	$tsAdmin->getElement('data', $tsAdmin->ftGetFileList($channel['cid'], "", "/"));
						if(!empty($filelist))
						{
							$newFilelist				=	getChannelFiles($filelist, $channel['cid']);
							
							foreach($newFilelist AS $trNumber => $file)
							{
								if(empty($file['path']))
								{
									$file['path'] 		= 	"/";
								};
								
								echo '<tr>
										<td>'.xssSafe($channel['channel_name']).'</td>
										<td>'.$file['path'].'</td>
										<td>'.xssSafe($file['name']).'</td>
										<td>'.date('d.m.Y - H:i', $file['datetime']).'</td>
										<td>'.getFilesize($file['size']).'</td>
										<td>
											<a href="./php/functions/functionsDownload.php?action=downloadFileFromFilelist&port='.$server['data']['virtualserver_port'].'&instanz='.$LinkInformations['instanz'].'&path='.$file['path'].'&filename='.$file['name'].'&cid='.$file['cid'].'">
												<button class="btn btn-success btn-sm mini-left-right-margin"><i class="fa fa-download"></i> <font class="hidden-md-down">'.$language['download'].'</font>
											</a>
											<button class="btn btn-danger btn-sm mini-left-right-margin" onClick="deleteFile(\''.$file['path'].$file['name'].'\', \''.$file['cid'].'\', \''.$trNumber.'\');"><i class="fa fa-trash"></i> <font class="hidden-md-down">'.$language['delete'].'</font>
										</td>
										<td>'.$trNumber.'</td>
									</tr>';
							};
						};
					};
				}; ?>
			</tbody>
		</table>
	</div>
</div>

<script src="js/bootstrap/bootstrap-table.js"></script>
<script>
	var serverId					=	'<?php echo $LinkInformations['sid']; ?>';
	var instanz						=	'<?php echo $LinkInformations['instanz']; ?>';
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	$('#fileTable').bootstrapTable({
		formatNoMatches: function ()
		{
			return lang.filelist_none;
		}
	});
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>