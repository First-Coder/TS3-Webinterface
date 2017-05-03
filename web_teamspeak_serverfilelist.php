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
		$server 	= 	$tsAdmin->serverInfo();
		
		// Channel abfragen
		$channels	=	$tsAdmin->getElement('data', $tsAdmin->channelList());
		
		// Keine Rechte
		if(((strpos($user_right['ports']['right_web_server_view'][$serverInstanz], $server['data']['virtualserver_port']) === false || strpos($user_right['ports']['right_web_file_transfer'][$serverInstanz], $server['data']['virtualserver_port']) === false)
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
?>

<!-- Dateiliste -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-file"></i> <?php echo $language['ts_filelist']; ?></font>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tbody id="channelTokenTable">
				<?php if(!empty($channels))
				{
					$filelistBackground					=	false;
					$fileFounded						=	false;
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
								
								$fileFounded			=	true;
								
								echo '<tr id="'.$trNumber."_".$file['cid'].'" style="';
								if($filelistBackground)
								{
									echo "background-color:rgba(0,0,0,0.06);";
								};
								echo '">';
								echo	'<td>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				$language['ts_channel'];
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo 				htmlspecialchars($channel['channel_name']);
								echo			'</span>';
								echo 		'</div>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				'Path';
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo 				$file['path'];
								echo			'</span>';
								echo 		'</div>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				$language['dataname'];
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo 				htmlspecialchars($file['name']);
								echo			'</span>';
								echo 		'</div>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				$language['ts3_create_on'];
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo 				date('d.m.Y - H:i', $file['datetime']);
								echo			'</span>';
								echo 		'</div>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				$language['filesize'];
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo 				getFilesize($file['size']);
								echo			'</span>';
								echo 		'</div>';
								echo 		'<div class="hover">';
								echo			'<span class="filelist-headline">';
								echo 				$language['actions'];
								echo 			'</span>';
								echo			'<span class="filelist-subline">';
								echo				'<a href="functionsTeamspeakGet.php?action=downloadFileFromFilelist&port='.$server['data']['virtualserver_port'].'&instanz='.$serverInstanz.'&path='.$file['path'].'&filename='.$file['name'].'&cid='.$file['cid'].'">';
								echo 					'<i style="margin-left: 10px; margin-right: 10px;" class="fa fa-fw fa-download"></i>';
								echo				'</a>';
								echo 				'<i onClick="deleteFile(\''.$file['path'].$file['name'].'\', \''.$file['cid'].'\', \''.$trNumber.'\');" style="margin-left: 10px; margin-right: 10px;" class="fa fa-fw fa-trash"></i>';
								echo			'</span>';
								echo 		'</div>';
								echo 	'</td>';
								echo '</tr>';
								
								$filelistBackground			=	!$filelistBackground;
							};
						};
					};
					
					if(!$fileFounded)
					{ ?>
						<tr style="text-align:center;">
							<td>
								<span>
									<?php echo $language['filelist_none']; ?>
								</span>
							</td>
						</tr>
					<?php };
				};
				
				function getChannelFiles($filelist, $cid, $path = "/")
				{
					global $tsAdmin;
					
					$returnFilelist					=	array();
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
					
					return $returnFilelist;
				};
				?>
			</tbody>
		</table>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var serverId					=	'<?php echo $serverId; ?>';
	var instanz						=	'<?php echo $serverInstanz; ?>';
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>';
	
	var file_delete_success			=	'<?php echo $language['file_delete_success']; ?>';
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>