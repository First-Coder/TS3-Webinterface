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
		Get the Modulkeys
	*/
	$mysql_keys			=		getKeys();
	
	/*
		Is user logged in?
	*/
	$user_logged		=		false;
	if($_SESSION['login'] == $mysql_keys['login_key'])
	{
		$user_logged	=		true;
	};
	
	/*
		Get the Rights of the Client
	*/
	$user_right			=		getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Instanz shell
	*/
	if($_POST['action'] == 'instanzShell' && $user_logged && $user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3'])
	{
		$returnString	=	"<font>".shell_exec("shell/teamspeakCommands.sh ".$_POST['username']." ".$_POST['ip']." ".$_POST['port']." ".$_POST['path']." ".$_POST['command']);
		$returnString	=	str_replace("[33;", "</font><font ", $returnString);
		$returnString	=	str_replace("36m", "class=\"\">", $returnString);
		$returnString	=	str_replace("31m", "class=\"text-danger\">", $returnString);
		$returnString	=	str_replace("32m", "class=\"text-success\">", $returnString);
		$returnString	=	str_replace("38m", "class=\"text-warning\">", $returnString);
		
		echo $returnString;
	};
?>