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
		Session start
	*/
	session_start();
	
	/*
		Includes
	*/
	require_once("functions.php");
	require_once("functionsTeamspeak.php");
	
	/*
		Get Modul Keys
	*/
	$mysql_keys			=		getKeys();
	
	/*
		Is Client logged in ?
	*/
	$user_logged		=		false;
	if($_SESSION['login'] == $mysql_keys['login_key'])
	{
		$user_logged	=		true;
	};
	
	/*
		Get all Client Permissions
	*/
	$user_right			=		getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Download a File from a Teamspeakserver
	*/
	if($_GET['action'] == 'downloadFileFromFilelist' && $user_logged && (strpos($user_right['ports']['right_web_file_transfer'][$_GET['instanz']], $_GET['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		echo downloadFileFromFilelist($_GET['path'], $_GET['filename'], $_GET['cid'], $_GET['port'], $_GET['instanz']);
	};
?>