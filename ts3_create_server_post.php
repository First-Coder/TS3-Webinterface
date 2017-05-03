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
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
	if($user_right['right_web_server_create'] != $mysql_keys['right_web_server_create'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		JSON Decode
	*/
	$json 								= 		$_POST['TS3_Information'];
	$obj 								= 		json_decode($json);
	$ts3_port_copy						= 		$obj[31];
	$ts3_server_copy					= 		$obj[36];
	
	// Login Daten speichern
	$ts3_login							= 		array();
	$ts3_login['server']				= 		$obj[0];
	$ts3_login['ip']					= 		$ts3_server[$obj[0]]['ip'];
	$ts3_login['queryport']				= 		$ts3_server[$obj[0]]['queryport'];
	$ts3_login['user']					= 		$ts3_server[$obj[0]]['user'];
	$ts3_login['pw']					= 		$ts3_server[$obj[0]]['pw'];
	
	// $ts3_login['ip']					= 		$obj[1];
	// $ts3_login['queryport']				= 		$obj[2];
	// $ts3_login['user']					= 		$obj[3];
	// $ts3_login['pw']					= 		$obj[4];
	
	$status								=		array();
	
	/*
		Create Teamspeakserver
	*/
	$ts3_information 															= 		array();
	$ts3_information['virtualserver_reserved_slots']							= 		$obj[5];
	$ts3_information['virtualserver_hostmessage']								= 		$obj[6];
	$ts3_information['virtualserver_hostmessage_mode']							= 		$obj[7];
	$ts3_information['virtualserver_hostbanner_url']							= 		$obj[8];
	$ts3_information['virtualserver_hostbanner_gfx_url']						= 		$obj[9];
	$ts3_information['virtualserver_hostbanner_gfx_interval']					= 		$obj[10];
	$ts3_information['virtualserver_hostbutton_gfx_url']						= 		$obj[11];
	$ts3_information['virtualserver_hostbutton_tooltip']						= 		$obj[12];
	$ts3_information['virtualserver_hostbutton_url']							= 		$obj[13];
	$ts3_information['virtualserver_complain_autoban_count']					= 		$obj[14];
	$ts3_information['virtualserver_complain_autoban_time']						= 		$obj[15];
	$ts3_information['virtualserver_complain_remove_time']						= 		$obj[16];
	$ts3_information['virtualserver_antiflood_points_tick_reduce']				= 		$obj[17];
	$ts3_information['virtualserver_antiflood_points_needed_command_block']		= 		$obj[18];
	$ts3_information['virtualserver_antiflood_points_needed_ip_block']			= 		$obj[19];
	$ts3_information['virtualserver_max_upload_total_bandwidth']				= 		$obj[20];
	$ts3_information['virtualserver_upload_quota']								= 		$obj[21];
	$ts3_information['virtualserver_max_download_total_bandwidth']				= 		$obj[22];
	$ts3_information['virtualserver_download_quota']							= 		$obj[23];
	$ts3_information['virtualserver_log_client']								= 		$obj[24];
	$ts3_information['virtualserver_log_query']									= 		$obj[25];
	$ts3_information['virtualserver_log_channel']								= 		$obj[26];
	$ts3_information['virtualserver_log_permissions']							= 		$obj[27];
	$ts3_information['virtualserver_log_server']								= 		$obj[28];
	$ts3_information['virtualserver_log_filetransfer']							= 		$obj[29];
	$ts3_information['virtualserver_name']										= 		$obj[30];
	$ts3_information['virtualserver_maxclients']								= 		$obj[33];
	$ts3_information['virtualserver_password']									= 		$obj[34];
	$ts3_information['virtualserver_welcomemessage']							= 		$obj[35];
	
	if($obj[32] != "")
	{
		$ts3_information['virtualserver_port']									= 		$obj[32];
	};
	
	// Teamspeak Daten eingeben
	$tsAdmin = new ts3admin($ts3_login['ip'], $ts3_login['queryport']);
	
	// Verbindung erfolgreich
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		// Im Teamspeak Einloggen
		$tsAdmin->login($ts3_login['user'], $ts3_login['pw']);
		
		$token							=		$tsAdmin->serverCreate($ts3_information);
		if($token['success'] === false)
		{
			$status['success']				=		'0';
			for($i = 0; $i + 1 == count($token['errors']); $i++)
			{
				$status['error']		.=		$token['errors'][$i].'<br/>';
			};
		}
		else
		{
			$status['success']			=		'1';
			$status['port']				=		$token['data']['virtualserver_port'];
			$status['serverid']			=		$token['data']['sid'];
			$status['token']			=		$token['data']['token'];
			
			if($ts3_server_copy != 'none')
			{
				// Teamspeak Daten eingeben
				$copyTsAdmin = new ts3admin($ts3_server[$obj[36]]['ip'], $ts3_server[$obj[36]]['queryport']);
				
				// Verbindung erfolgreich
				if($copyTsAdmin->getElement('success', $copyTsAdmin->connect()))
				{
					// Im Teamspeak Einloggen
					$copyTsAdmin->login($ts3_server[$obj[36]]['user'], $ts3_server[$obj[36]]['pw']);
					
					$tsServerID 		= 	($copyTsAdmin->serverIdGetByPort($ts3_port_copy));
					$copyTsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
					$channelList		=		$copyTsAdmin->channelList();
					
					if($channelList['success'] !== false)
					{
						foreach($channelList['data'] AS $channelCounter=>$channel)
						{
							createTeamspeakChannel($channel['channel_name'], false, $token['data']['virtualserver_port'], $ts3_login['ip'], $ts3_login['queryport'], $ts3_login['user'], $ts3_login['pw']);
						};
					};
				};
			};
			
			if(giveUserAllRightsTSServer($_SESSION['user']['id'], $ts3_login['server'], $token['data']['virtualserver_port']) && $obj[1] == "true")
			{
				require_once("functionsMail.php");
				
				$mailContent			=		array();
				$mailContent			=		getMail("request_success");
				
				$mailContent			=		str_replace("%heading%", 					HEADING, 											$mailContent);
				$mailContent			=		str_replace("%client%", 					$obj[2], 											$mailContent);
				$mailContent			=		str_replace("%client%", 					$ts3_login['ip'], 									$mailContent);
				$mailContent			=		str_replace("%serverCreateServername%", 	$ts3_information['virtualserver_name'], 			$mailContent);
				$mailContent			=		str_replace("%serverCreatePort%", 			$ts3_information['virtualserver_port'], 			$mailContent);
				$mailContent			=		str_replace("%serverCreateSlots%", 			$ts3_information['virtualserver_maxclients'], 		$mailContent);
				$mailContent			=		str_replace("%serverCreateReservedSlots%", 	$ts3_information['virtualserver_reserved_slots'], 	$mailContent);
				$mailContent			=		str_replace("%serverCreatePassword%", 		$ts3_information['virtualserver_password'], 		$mailContent);
				$mailContent			=		str_replace("%serverCreateWelcomeMessage%", $ts3_information['virtualserver_welcomemessage'], 	$mailContent);
				$mailContent			=		str_replace("%token%", 						$token['data']['token'], 							$mailContent);
				
				if(writeMail($mailContent["headline"], $mailContent["mail_subject"], $obj[2], $mailContent["mail_body"]) != "done")
				{
					$status['success']				=		'0';
					$status['error']				=		'Mail could not be send to client!';
				}
				else
				{
					if(!unlink("wantServer/".$obj[3]))
					{
						$status['success']			=		'0';
						$status['error']			=		'Request could be not deleted';
					};
				};
			};
		};
	}
	else
	{
		$status['success']				=		'0';
		$status['error']				=		'No Teamspeak Connection';
	};
	
	// Rueckgabe
	echo json_encode($status);
?>