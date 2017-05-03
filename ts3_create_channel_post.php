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
	
	/*
		Get Client Permissions
	*/
	$user_right			=		getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Get SQL Keys
	*/
	$mysql_keys			=	getKeys();
	
	/*
		JSON Decode
	*/
	$json 								= 		$_POST['TS3_Information'];
	$obj 								= 		json_decode($json);
	$ts3_port							= 		$obj[5];
	
	// Login Daten speichern
	$ts3_login							= 		array();
	$ts3_login['server']				= 		$obj[0];
	$ts3_login['ip']					= 		$ts3_server[$obj[0]]['ip'];
	$ts3_login['queryport']				= 		$ts3_server[$obj[0]]['queryport'];
	$ts3_login['user']					= 		$ts3_server[$obj[0]]['user'];
	$ts3_login['pw']					= 		$ts3_server[$obj[0]]['pw'];
	
	/*
		Has the Client the Permission
	*/
	if(strpos($user_right['ports']['right_web_server_edit'][$ts3_login['server']], $ts3_port) === false && $user_right['right_web_global_server'] != $mysql_keys['right_web_global_server'])
	{
		$urlData						=		split("\?", $_SERVER['HTTP_REFERER'], -1);
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Check the Channeltype
	*/
	$ts3_information 															= 		array();
	if($obj[14] == 1)
	{
		$ts3_information['channel_flag_permanent']								=		0;
		$ts3_information['channel_flag_semi_permanent']							=		1;
	}
	elseif($obj[14] == 2)
	{
		$ts3_information['channel_flag_permanent']								=		1;
		$ts3_information['channel_flag_semi_permanent']							=		0;
	}
	elseif($obj[14] == 3)
	{
		$ts3_information['channel_flag_permanent']								=		1;
		$ts3_information['channel_flag_semi_permanent']							=		0;
		$ts3_information['channel_flag_permanent']								=		1;
	};
	
	/*
		Create the Teamspeakchannel
	*/
	$ts3_information['cpid']													= 		$obj[6];
	$ts3_information['channel_name']											= 		$obj[7];
	$ts3_information['channel_topic']											= 		$obj[8];
	$ts3_information['channel_description']										= 		$obj[9];
	$ts3_information['channel_codec']											= 		$obj[10];
	$ts3_information['channel_codec_quality']									= 		$obj[11];
	$ts3_information['channel_maxclients']										= 		$obj[12];
	$ts3_information['channel_maxfamilyclients']								= 		$obj[13];
	$ts3_information['channel_flag_maxfamilyclients_inherited']					= 		$obj[15];
	$ts3_information['channel_needed_talk_power']								= 		$obj[16];
	$ts3_information['channel_name_phonetic']									= 		$obj[17];
	
	// Teamspeak Daten eingeben
	$tsAdmin = new ts3admin($ts3_login['ip'], $ts3_login['queryport']);
	
	// Verbindung erfolgreich
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		// Im Teamspeak Einloggen
		$tsAdmin->login($ts3_login['user'], $ts3_login['pw']);
		
		$tsServerID 	= 	$tsAdmin->serverIdGetByPort($ts3_port);
		$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
		
		$channel_create					=		$tsAdmin->channelCreate($ts3_information);
		if($channel_create['success'] === false)
		{
			for($i = 0; $i + 1 == count($channel_create['errors']); $i++)
			{
				$status 				= 		$channel_create['errors'][$i].'<br />';
			};
		}
		else
		{
			$status						=		'done';
		};
	}
	else
	{
		$status 						= 		'No Teamspeak Connection';
	};
	
	// Rueckgabe
	print_r($status);
?>