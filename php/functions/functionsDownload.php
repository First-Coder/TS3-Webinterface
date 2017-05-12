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
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	
	if($_GET['action'] == 'downloadFileFromFilelist')
	{
		require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	};
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get Modul Keys
	*/
	$mysql_keys			=		getKeys();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		die("You are not logged in :/");
	};
	
	/*
		Get Client Permissions
	*/
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Download a Icon from Teamspeakserver
	*/
	if($_GET['action'] == 'downloadIconFromIconlist' && $LoggedIn && (strpos($user_right['right_web_server_icons'][$_GET['instanz']], $_GET['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo downloadIconFromIconlist($_GET['filename'], $_GET['port'], $_GET['instanz']);
	}
	else if($_GET['action'] == 'downloadIconFromIconlist')
	{
		die("No Access!");
	};
	
	/*
		Download a File from a Teamspeakserver
	*/
	if($_GET['action'] == 'downloadFileFromFilelist' && $LoggedIn && (strpos($user_right['right_web_file_transfer'][$_GET['instanz']], $_GET['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo downloadFileFromFilelist($_GET['path'], $_GET['filename'], $_GET['cid'], $_GET['port'], $_GET['instanz']);
	}
	else if($_GET['action'] == 'downloadFileFromFilelist')
	{
		die("No Access!");
	};
	
	/*
		Download a Backup from a Teamspeakserver
	*/
	if($_GET['action'] == 'downloadBackup' && $LoggedIn && (strpos($user_right['right_web_server_backups'][$_GET['instanz']], $_GET['port']) !== false || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo downloadBackup($_GET['type'], $_GET['name']);
	}
	else if($_GET['action'] == 'downloadBackup')
	{
		die("No Access!");
	};
	
	/*
		Function to Download a Icon from Server
	*/
	function downloadIconFromIconlist($id, $port, $instanz)
	{
		global $ts3_server;
		
		$filename		=	'../../images/ts_icons/'.$ts3_server[$instanz]['ip'].'-'.$port.'/icon_'.$id;
		
		header('Content-Disposition: attachment; filename="'.$id.'.png"');
		header('Content-Type: x-type/subtype');
		
		return fread(fopen($filename, "r"), filesize($filename));
	};
	
	/*
		Function to Download a Backup from Server
	*/
	function downloadBackup($type, $name)
	{
		$dir			=	"../../files/backups/".$type."/";
		
		header("Content-Type: application/json");
		header("Content-Disposition: attachment; filename=\"$name\"");
		
		return fread(fopen($dir.$name, "r"), filesize($dir.$name));
	};
?>