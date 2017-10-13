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
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
	{
		exit();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$_POST['instanz']]['ip'], $ts3_server[$_POST['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$_POST['instanz']]['user'], $ts3_server[$_POST['instanz']]['pw']);
		$tsAdmin->selectServer($_POST['port'], 'port', true);
		
		$server 		= 	$tsAdmin->serverInfo();
		
		if(((!isPortPermission($user_right, $_POST['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $_POST['instanz'], $server['data']['virtualserver_port'], 'right_web_server_backups'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			exit();
		};
	}
	else
	{
		die("Connection failed");
	};
	
	/*
		Create Backup
	*/
	if($_POST['action'] == 'createBackup' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_backups') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo createBackup($tsAdmin, $_POST['instanz'], $_POST['port'], $_POST['kind'], $_POST['kindChannel']);
	};
	
	/*
		Activate Channel Backup
	*/
	if($_POST['action'] == 'activateBackup' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_backups') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if($_POST['subaction'] == "channelname")
		{
			$defaultCid							=		deleteAllTeamspeakChannels($tsAdmin, $_POST['port']);
			$fileContent						= 		file("../../files/backups/".$_POST['subaction']."/".$_POST['file']);
			
			foreach(json_decode($fileContent[0]) AS $channel)
			{
				createTeamspeakChannel($tsAdmin, $channel, true, $_POST['port'], $_POST['instanz']);
			};
			
			 if(deleteTeamspeakChannel($defaultCid, $_POST['port'], $_POST['instanz']))
			 {
				 echo "done";
			 }
			 else
			 {
				echo "Ups... Something goes Wrong!";
			 };
		}
		else if($_POST['subaction'] == "channelnamesettings")
		{
			$handler							=		file("../../files/backups/".$_POST['subaction']."/".$_POST['file']);
			if($handler === false)
			{
				echo "File could be not opened!";
				return;
			}
			else
			{
				$getdata						=		explode('||',$handler[0]);
				
				foreach($getdata AS $key=>$value)
				{
					$channelsettings			=		explode('<perms>',$value);
					$channelperms				=		explode('</perms>', $channelsettings[1]);
					$getsettings				=		explode(' ', $channelsettings[0]);
					$getperms					=		explode('|', $channelperms[0]);
					
					foreach($getperms AS $key2=>$value2)
					{
						$getpermsettings		=		explode(' ', $value2);
						foreach($getpermsettings AS $key3=>$value3)
						{
							$settings			=		explode('=', $value3);
							if(!empty($settings[0]))
							{
								if($settings[0] == 'permid')
								{
									$permid		=	$settings[1];
								}
								elseif($settings[0] != 'permnegated' AND $settings[0] != 'permskip')
								{
									$permissions[$key][$permid]		=		$settings[1];
								};
							};
						};
					};
					
					foreach($getsettings AS $key2=>$value2)
					{
						$equalCount 			= 		substr_count($value2, '=');
						if($equalCount > 1)
						{
							$settings 			= 		explode('=', $value2);
							for($i = 2; $i <= $equalCount; $i++) 
							{
								if(!empty($settings[$i])) 
								{
									$settings[1].= 		'='.$settings[$i];
								}
								else
								{
									$settings[1].= 		'=';
								};
							};
						}
						else
						{
							$settings			=		explode('=', $value2);
						}

						if(!empty($settings[0]))
						{
							$backup[$key][$settings[0]]		=		$settings[1];
						};
						$backup[$key]['perms']	=		$permissions[$key];
					};
				};
			};
			
			if(channelChannelAllBackupDeploy($tsAdmin, 0, $backup, 0))
			{
				echo "done";
			};
		}
		else if($_POST['subaction'] == "server")
		{
			$snapshot_deploy				=		$tsAdmin->serverSnapshotDeploy(file("../../files/backups/".$_POST['subaction']."/".$_POST['file'])[0]);
			
			if($snapshot_deploy['success'] === false)
			{
				for($i=0; $i+1 == count($snapshot_deploy['errors']); $i++)
				{
					echo $snapshot_deploy['errors'][$i]."<br />";
				};
			}
			else
			{
				echo "done";
			};
		};
	};
	
	/*
		Function to deploy a Teamspeakchannel with settings
	*/
	function channelChannelAllBackupDeploy($tsAdmin, $pid, $backup, $newcid, $firstrun = 1)
	{
		if($firstrun == 1)
		{
			$channellist										=	$tsAdmin->getElement('data', $tsAdmin->channelList());
			$rename_def											=	0;
			foreach($channellist AS $key => $value)
			{
				if($rename_def == 0)
				{
					$newsettings['channel_name']				=	'Auto delete after backup';
					$newsettings['channel_flag_permanent']		=	'1';
					$newsettings['channel_flag_semi_permanent']	=	'0';
					$newsettings['channel_flag_default']		=	'1';
					$tsAdmin->channelEdit($value['cid'], $newsettings);
					$rename_def									=	$value['cid'];
				}
				else
				{
					$tsAdmin->channelDelete($value['cid']);
				};
			};
		};
		
		foreach($backup AS $key=>$value)
		{
			if ($pid == $value['pid'])
			{
				$settings['channel_name']								=	isset($value['channel_name']) ? $value['channel_name']:'';
				if($value['pid'] != 0)
				{
					$settings['cpid']									=	$newcid;
				};
				$settings['channel_topic']								=	isset($value['channel_topic']) ? $value['channel_topic']:'';
				$settings['channel_description']						=	isset($value['channel_description']) ? $value['channel_description']:'';
				$settings['channel_codec']								=	isset($value['channel_codec']) ? $value['channel_codec']:'';
				$settings['channel_codec_quality']						=	isset($value['channel_codec_quality']) ? $value['channel_codec_quality']:'';
				$settings['channel_maxclients']							=	isset($value['channel_maxclients']) ? $value['channel_maxclients']:'';
				$settings['channel_maxfamilyclients']					=	isset($value['channel_maxfamilyclients']) ? $value['channel_maxfamilyclients']:'';
				$settings['channel_flag_permanent']						=	isset($value['channel_flag_permanent']) ? $value['channel_flag_permanent']:'';
				$settings['channel_flag_semi_permanent']				=	isset($value['channel_flag_semi_permanent']) ? $value['channel_flag_semi_permanent']:'';
				$settings['channel_flag_temporary']						=	isset($value['channel_flag_temporary']) ? $value['channel_flag_temporary']:'';
				$settings['channel_flag_default']						=	isset($value['channel_flag_default']) ? $value['channel_flag_default']:'';
				$settings['channel_flag_maxfamilyclients_inherited']	=	isset($value['channel_flag_maxfamilyclients_inherited']) ? $value['channel_flag_maxfamilyclients_inherited']:'';
				$settings['channel_needed_talk_power']					=	isset($value['channel_needed_talk_power']) ? $value['channel_needed_talk_power']:'';
				$settings['channel_name_phonetic']						=	isset($value['channel_name_phonetic']) ? $value['channel_name_phonetic']:'';
				$cid													=	$tsAdmin->channelCreate($settings);
				$permid													=	$tsAdmin->getElement('data', $tsAdmin->permIdGetByName(array('i_group_needed_modify_power')));
				$tsAdmin->channelAddPerm($cid['data']['cid'], $value['perms']);
				if($cid['success']===false)
				{
					echo $cid['errors'][0];
					return false;
				};
				
				channelChannelAllBackupDeploy($tsAdmin, $value['cid'], $backup, $cid['data']['cid'], 0);
			};
		};
		
		if(isset($rename_def) AND $rename_def!=0)
		{
			$tsAdmin->channelDelete($rename_def);
		};
		
		return true;
	};
	
	/*
		Function to delete all Teamspeakchannels from a spezific Teamspeakserver
	*/
	function deleteAllTeamspeakChannels($tsAdmin, $port)
	{
		$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-flags"));
		$defaultCid		=	-1;
		
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if($value['channel_flag_default'] == '1')
				{
					$defaultCid		=	$value['cid'];
				}
				else
				{
					$tsAdmin->channelDelete($value['cid']);
				};
			};
		};
		
		return $defaultCid;
	};
	
	/*
		Function to Create a Teamspeak Backup
	*/
	function createBackup($tsAdmin, $instanz, $port, $kind, $kindchannel)
	{
		global $ts3_server;
		global $language;
		
		writeInLog($_SESSION['user']['benutzer'], "Create a Backup Typ: ".$kind." Instanz: ".$instanz." Port: ".$port, true);
		
		$datum 								= 		date("Y_m_d_H_i", time());
		
		if($kind == "channel")
		{
			$channels						=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits"));
			
			if($kindchannel == "all")
			{
				foreach($channels AS $key=>$value)
				{
					$channelinfo			=	$tsAdmin->getElement('data', $tsAdmin->channelInfo($value['cid']));
					unset($channelinfo['channel_password']);
					unset($channelinfo['channel_filepath']);
					
					foreach($channelinfo AS $key2=>$value2)
					{
						if(!isset($channels[$key][$key2]))
						{
							$channels[$key][$key2]	=	$value2;
						};
					};
				};
				
				$path									=	"../../files/backups/channelnamesettings/instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_all.json";
				
				if(file_exists($path))
				{
					return $language['backup_already_exists'];
				};
				
				$handler								=	fopen($path, "a+");
				if($handler === false)
				{
					return false;
				}
				else
				{
					$count								=	1;
					$count_chans						=	count($channels);
					
					foreach($channels AS $key=>$value)
					{
						$settings							=	'';
						$count2								=	1;
						foreach($value AS $key2=>$value2)
						{
							$count_settings					=	count($value);
							$settings						.=	$key2."=".str_replace(' ', '\s',$value2);
							if($count2!=$count_settings)
							{
								$settings.=" ";
							};
							$count2++;
						};
						
						$channelperms					=	$tsAdmin->channelPermList($value['cid']);
						if($channelperms['success']===true)
						{
							$settings					.=	"<perms>";
							$count3						=	1;
							$count_perms				=	count($channelperms['data']);
							
							foreach($channelperms['data'] AS $key3=>$value3)
							{
								$count4					=	1;
								$count_permsettings		=	count($value3);
								foreach($value3 AS $key4=>$value4)
								{
									if($key4!="cid")
									{
										$settings		.=	$key4."=".$value4;
										if($count4 != $count_permsettings)
										{
											$settings	.=	" ";
										};
									};
								};
								if($count3 != $count_perms)
								{
									$settings			.=	"|";
								};
							};
							$settings					.=	"</perms>";
						};
						
						if($count != $count_chans)
						{
							$settings					.=	"||";
						};
						
						if(!fwrite($handler, $settings))
						{
							return $language['file_could_not_be_created'];
						};
						$count++;
					};
					
					fclose($handler);
				};
				
				return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_all.json";
			}
			else
			{
				$newJson					=	array();
				$path						=	"../../files/backups/channelname/instanz_" . $instanz . "_port_" . $port . "_" . $datum . ".json";
				
				foreach($channels AS $i => $channel)
				{
					$newJson[$i]			=	$channel['channel_name'];
				};
				
				if(file_exists($path))
				{
					return $language['backup_already_exists'];
				};
				
				if(file_put_contents($path, json_encode($newJson)))
				{
					return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . ".json";
				}
				else
				{
					return $language['file_could_not_be_created'];
				};
			};
		}
		else
		{
			$path							=	"../../files/backups/server/instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_server.json";
			
			if(file_exists($path))
			{
				return $language['backup_already_exists'];
			};
			
			if(file_put_contents($path, $tsAdmin->getElement('data', $tsAdmin->serverSnapshotCreate())))
			{
				return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_server.json";
			}
			else
			{
				return $language['file_could_not_be_created'];
			};
		};
	};
	
	
	
	
	
	
	
	
	
	
	
	
	/*
		JSON Decode
	*/
	//$json 									= 		$_POST['TS3_Information'];
	//$obj 									= 		json_decode($json);
	
	
	
	/*
		Aktivate the Backup Channel
	*/
	/*else if($obj[0] == 'activate_backup_channel')
	{
		// Alle Channels löschen
		$defaultCid							=		deleteAllTeamspeakChannels($obj[2], $ts3_server[$_POST['instanz']]['ip'], $ts3_server[$_POST['instanz']]['queryport'], $ts3_server[$_POST['instanz']]['user'], $ts3_server[$_POST['instanz']]['pw']);
		
		// Channel Abfragen
		$filename							=		$obj[1];
		$fileContent						= 		file($filename);
		
		foreach(json_decode($fileContent[0]) AS $channel)
		{
			createTeamspeakChannel($channel, true, $obj[2], $ts3_server[$_POST['instanz']]['ip'], $ts3_server[$_POST['instanz']]['queryport'], $ts3_server[$_POST['instanz']]['user'], $ts3_server[$_POST['instanz']]['pw']);
		};
		
		// Defaultchannel löschen
		 if(deleteTeamspeakChannel($defaultCid, $obj[2], $ts3_server[$_POST['instanz']]['ip'], $ts3_server[$_POST['instanz']]['queryport'], $ts3_server[$_POST['instanz']]['user'], $ts3_server[$_POST['instanz']]['pw']))
		 {
			 echo "done";
		 }
		 else
		 {
			echo "Ups... Something goes Wrong!";
		 };
	}*/
	
	/*
		Aktivate the Backup Channel with all Settings
	*/
	/*else if($obj[0] == 'activate_backup_channel_all')
	{
		$handler							=		file($obj[1]);
		if($handler === false)
		{
			echo "File could be not opened!";
			return;
		}
		else
		{
			$getdata						=		explode('||',$handler[0]);
			
			foreach($getdata AS $key=>$value)
			{
				$channelsettings			=		explode('<perms>',$value);
				$channelperms				=		explode('</perms>', $channelsettings[1]);
				$getsettings				=		explode(' ', $channelsettings[0]);
				$getperms					=		explode('|', $channelperms[0]);
				
				foreach($getperms AS $key2=>$value2)
				{
					$getpermsettings		=		explode(' ', $value2);
					foreach($getpermsettings AS $key3=>$value3)
					{
						$settings			=		explode('=', $value3);
						if(!empty($settings[0]))
						{
							if($settings[0] == 'permid')
							{
								$permid		=	$settings[1];
							}
							elseif($settings[0] != 'permnegated' AND $settings[0] != 'permskip')
							{
								$permissions[$key][$permid]		=		$settings[1];
							};
						};
					};
				};
				
				foreach($getsettings AS $key2=>$value2)
				{
					$equalCount 			= 		substr_count($value2, '=');
					if($equalCount > 1)
					{
						$settings 			= 		explode('=', $value2);
						for($i = 2; $i <= $equalCount; $i++) 
						{
							if(!empty($settings[$i])) 
							{
								$settings[1].= 		'='.$settings[$i];
							}
							else
							{
								$settings[1].= 		'=';
							};
						};
					}
					else
					{
						$settings			=		explode('=', $value2);
					}

					if(!empty($settings[0]))
					{
						$backup[$key][$settings[0]]		=		$settings[1];
					};
					$backup[$key]['perms']	=		$permissions[$key];
				};
			};
		};
		
		// Backup wiederherstellen
		if(channelChannelAllBackupDeploy($_POST['instanz'], $obj[2], 0, $backup, 0))
		{
			echo "done";
		}
		else
		{
			echo "Ups... Something goes Wrong!";
		};
	}*/
	
	/*
		Aktivate the Backup Channel
	*/
	/*else if($obj[0] == 'activate_backup_server')
	{
		require_once("ts3admin.class.php");
		
		// Channel Abfragen
		$filename							=		$obj[1];
		$fileContent						= 		file($filename);
		
		$tsAdmin 							= 		new ts3admin($ts3_server[$_POST['instanz']]['ip'], $ts3_server[$_POST['instanz']]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$_POST['instanz']]['user'], $ts3_server[$_POST['instanz']]['pw']);
			
			$tsServerID 					= 		$tsAdmin->serverIdGetByPort($obj[2]);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$snapshot_deploy				=		$tsAdmin->serverSnapshotDeploy($fileContent[0]);
			
			if($snapshot_deploy['success'] === false)
			{
				for($i=0; $i+1 == count($snapshot_deploy['errors']); $i++)
				{
					echo $snapshot_deploy['errors'][$i]."<br />";
				};
			}
			else
			{
				echo "done";
			};
		};
	};*/
?>