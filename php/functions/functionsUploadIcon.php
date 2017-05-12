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
	require_once("functions.php");
	require_once("functionsSql.php");
	require_once("functionsTeamspeak.php");
	
	/*
		Get the Modul Keys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
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
		reloadSite();
	};
	
	/*
		Teamspeak Funktions
	*/
	$tsAdmin 			= 	new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server = $tsAdmin->serverInfo();
		
		if(((strpos($user_right['right_web_server_view'][$LinkInformations['instanz']], $server['data']['virtualserver_port']) === false || strpos($user_right['right_web_server_icons'][$LinkInformations['instanz']], $server['data']['virtualserver_port']) === false)
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite();
		}
		else
		{
			$informations	=	array();
			if (isset($_FILES['file']) && strpos($_FILES['file']['type'], 'image') !== false)
			{
				$filename		=	"icon_".mt_rand(1000000000, 2147483647);
				move_uploaded_file($_FILES['file']['tmp_name'], '../../images/ts_icons/'.$filename);
				
				$informations['status']		=	"done";
				$informations['text']		=	$filename;
				$informations['size']		=	$_FILES['file']['size'];
			}
			else
			{
				$informations['status']		=	"error";
				$informations['text']		=	"Wrong Datatype!";
			};
			
			$ft2		=	$tsAdmin->getElement('data', $tsAdmin->ftInitUpload("/".$filename, 0, $_FILES['file']['size']));
			$file		=	file_get_contents("../../images/ts_icons/".$filename);
			$con_ft		=	fsockopen($ts3_server[$LinkInformations['instanz']]['ip'], $ft2['port'], $errnum, $errstr, 10);
			fputs($con_ft, $ft2['ftkey']);
			fputs($con_ft, $file);
			unlink("../../images/ts_icons/".$filename);
			fclose($con_ft);
			
			getTeamspeakIcons($tsAdmin, $server['data']['virtualserver_port'], $ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport'], $ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		};
	}
	else
	{
		reloadSite();
	};
?>