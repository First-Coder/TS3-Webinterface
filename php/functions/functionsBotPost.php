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
	require_once("./functions.php");
	require_once("./functionsSql.php");
	require_once("./functionsTeamspeak.php");
	require_once("./functionsBot.php");
	
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
	if(isSet($_SESSION['user']['id']))
	{
		$user_right	=	getUserRights('pk', $_SESSION['user']['id']);
	};
	
	/*
		Create Teamspeak Querybot
	*/
	if($_POST['action'] == 'saveBotSettings' && $LoggedIn && $user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete'])
	{
		echo saveQueryBotSettings($_POST['botid'], $_POST['table'], json_decode($_POST['data']));
	};
	
	/*
		Create Teamspeak Querybot
	*/
	if($_POST['action'] == 'createQueryBot' && $LoggedIn && $user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete'])
	{
		echo createQueryBot($_POST['instanz'], $_POST['port'], urldecode($_POST['name']));
	};
	
	/*
		Delete Teamspeak Querybot
	*/
	if($_POST['action'] == 'deleteQueryBot' && $LoggedIn && $user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete'])
	{
		echo deleteQueryBot($_POST['id']);
	};
?>