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
		Start the PHP Session
	*/
	session_start();
	
	/*
		Includes
	*/
	require_once("functions.php");
	require_once("functionsTeamspeak.php");
	
	/*
		Get the Modul Keys
	*/
	$mysql_keys			=		getKeys();
	
	/*
		Get Client Permissions
	*/
	$user_right			=		getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_web_server_create'] != $mysql_keys['right_web_server_create'] && $user_right['right_web'] != $mysql_keys['right_web'])
	{
		$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		JSON Decode
	*/
	$json 									= 		$_POST['TS3_Information'];
	$obj 									= 		json_decode($json);
	
	/*
		Delete the Backup
	*/
	if($obj[0] == 'delete_backup')
	{
		if(strpos($obj[1], ".json") !== false)
		{
			if(unlink($obj[1]))
			{
				echo "done";
			}
			else
			{
				echo "Cant delete File.. Permission error!";
			};
		}
		else
		{
			echo "Unvalid filename!";
		};
	}
	
	/*
		Aktivate the Backup Channel
	*/
	else if($obj[0] == 'activate_backup_channel')
	{
		// Alle Channels löschen
		$defaultCid							=		deleteAllTeamspeakChannels($obj[2], $ts3_server[$obj[3]]['ip'], $ts3_server[$obj[3]]['queryport'], $ts3_server[$obj[3]]['user'], $ts3_server[$obj[3]]['pw']);
		
		// Channel Abfragen
		$filename							=		$obj[1];
		$fileContent						= 		file($filename);
		
		foreach(json_decode($fileContent[0]) AS $channel)
		{
			createTeamspeakChannel($channel, true, $obj[2], $ts3_server[$obj[3]]['ip'], $ts3_server[$obj[3]]['queryport'], $ts3_server[$obj[3]]['user'], $ts3_server[$obj[3]]['pw']);
		};
		
		// Defaultchannel löschen
		 if(deleteTeamspeakChannel($defaultCid, $obj[2], $ts3_server[$obj[3]]['ip'], $ts3_server[$obj[3]]['queryport'], $ts3_server[$obj[3]]['user'], $ts3_server[$obj[3]]['pw']))
		 {
			 echo "done";
		 }
		 else
		 {
			echo "Ups... Something goes Wrong!";
		 };
	}
	
	/*
		Aktivate the Backup Channel with all Settings
	*/
	else if($obj[0] == 'activate_backup_channel_all')
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
		if(channelChannelAllBackupDeploy($obj[3], $obj[2], 0, $backup, 0))
		{
			echo "done";
		}
		else
		{
			echo "Ups... Something goes Wrong!";
		};
	}
	
	/*
		Aktivate the Backup Channel
	*/
	else if($obj[0] == 'activate_backup_server')
	{
		require_once("ts3admin.class.php");
		
		// Channel Abfragen
		$filename							=		$obj[1];
		$fileContent						= 		file($filename);
		
		$tsAdmin 							= 		new ts3admin($ts3_server[$obj[3]]['ip'], $ts3_server[$obj[3]]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$obj[3]]['user'], $ts3_server[$obj[3]]['pw']);
			
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
	};
?>