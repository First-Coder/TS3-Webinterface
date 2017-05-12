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
	require_once("../../config/config.php");
	require_once("../../config/instance.php");
	require_once("./functions.php");
	require_once("./functionsSql.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
	/*
		Check Session
	*/
	$LoggedIn		=	(checkSession($mysql_keys['login_key'])) ? true : false;
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Change the Configfile
	*/
	if($_POST['action'] == 'setConfig' && ($LoggedIn && $user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main']) || file_exists("../../install"))
	{
		if(isSet($_POST['data']))
		{
			if(setConfigSettings($_POST['data']))
			{
				echo "done";
			}
			else
			{
				echo "error";
			};
		}
		else
		{
			"error";
		};
	};
	
	/*
		Create News
	*/
	if($_POST['action'] == 'createNews' && $LoggedIn && $user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main'])
	{
		if(createNews(urldecode($_POST['title']), urldecode($_POST['subtitle']), urldecode($_POST['content'])))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Delete Backup
	*/
	if($_POST['action'] == 'deleteBackup' && $LoggedIn && (strpos($user_right['right_web_server_backups'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(deleteFile("files/backups/".$_POST['file']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Create Server Request
	*/
	if($_POST['action'] == 'createServerRequest')
	{
		$mysql_modul		=		getModuls();
		
		if($mysql_modul['free_ts3_server_application'] == "true")
		{
			include_once("./functionsMail.php");
			
			$obj 											= 		json_decode($_POST['wantServerPost']);
			
			$fileContent 									= 		array();
			$fileContent['username']						= 		$obj[0];
			if($obj[1] != "")
			{
				$fileContent['password']					= 		crypt($obj[1], $obj[1]);
			};
			
			$fileContent['serverCreateCause']				= 		$obj[2];
			$fileContent['serverCreateWhy']					= 		$obj[3];
			$fileContent['serverCreateNeededSlots']			= 		$obj[4];
			$fileContent['serverCreateServername']			= 		$obj[5];
			$fileContent['serverCreatePort']				= 		$obj[6];
			$fileContent['serverCreateSlots']				= 		$obj[7];
			$fileContent['serverCreateReservedSlots']		= 		$obj[8];
			$fileContent['serverCreatePassword']			= 		$obj[9];
			$fileContent['serverCreateWelcomeMessage']		= 		$obj[10];
			$fileContent['creationTimestamp']				=		time();
			
			if(file_exists($obj[11].$obj[0]."_".$fileContent['serverCreatePort'].".txt"))
			{
				echo "Server Request already exist!";
			}
			else
			{
				if(file_put_contents($obj[11].$obj[0]."_".$fileContent['serverCreatePort'].".txt", json_encode($fileContent)) !== false)
				{
					$mailContent								=		array();
					$mailContent								=		getMail("create_request");
					
					$mailContent								=		str_replace("%heading%", 					HEADING, 									$mailContent);
					$mailContent								=		str_replace("%client%", 					$fileContent['username'], 					$mailContent);
					$mailContent								=		str_replace("%password%", 					$obj[1], 									$mailContent);
					$mailContent								=		str_replace("%serverCreateServername%", 	$fileContent['serverCreateServername'], 	$mailContent);
					$mailContent								=		str_replace("%serverCreatePort%", 			$fileContent['serverCreatePort'], 			$mailContent);
					$mailContent								=		str_replace("%serverCreateSlots%", 			$fileContent['serverCreateSlots'], 			$mailContent);
					$mailContent								=		str_replace("%serverCreateReservedSlots%", 	$fileContent['serverCreateReservedSlots'], 	$mailContent);
					$mailContent								=		str_replace("%serverCreatePassword%", 		$fileContent['serverCreatePassword'], 		$mailContent);
					$mailContent								=		str_replace("%serverCreateWelcomeMessage%", $fileContent['serverCreateWelcomeMessage'], $mailContent);
					
					echo writeMail($mailContent["headline"], $mailContent["mail_subject"], $fileContent['username'], $mailContent["mail_body"]);
				}
				else
				{
					echo "Could not create Server Request!";
				};
			};
		}
		else
		{
			echo "Modul is not aktivated!";
		};
	};
	
	/*
		Delete Server Request
	*/
	if($_POST['action'] == 'deleteServerRequest' && $LoggedIn && $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])
	{
		if(deleteFile("files/wantServer/".$_POST['file']))
		{
			include_once("./functionsMail.php");
			
			$fileContent								=		explode("_", $_POST['file']);
			
			$mailContent								=		array();
			$mailContent								=		getMail("request_failed");
			
			$mailContent								=		str_replace("%heading%", 					HEADING, 									$mailContent);
			$mailContent								=		str_replace("%client%", 					$fileContent[0], 							$mailContent);
			
			echo writeMail($mailContent["headline"], $mailContent["mail_subject"], $fileContent[0], $mailContent["mail_body"]);
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Delete News
	*/
	if($_POST['action'] == 'deleteNews' && $LoggedIn && $user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main'])
	{
		if(deleteFile("files/news/".$_POST['file']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Instancefile
	*/
	if($_POST['action'] == 'changeInstanz' && $LoggedIn && $user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3'])
	{
		if(isSet($_POST['subaction']))
		{
			echo setInstance($_POST['subaction'], (isSet($_POST['alias'])) ? $_POST['alias'] : -1, (isSet($_POST['ip'])) ? $_POST['ip'] : -1
				, (isSet($_POST['queryport'])) ? $_POST['queryport'] : -1, (isSet($_POST['client'])) ? $_POST['client'] : -1
				, (isSet($_POST['passwort'])) ? $_POST['passwort'] : -1);
		}
		else
		{
			"error";
		};
	};
?>