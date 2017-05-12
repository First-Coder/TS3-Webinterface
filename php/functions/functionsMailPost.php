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
	require_once("./functionsMail.php");
	
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
		Write a Mail
	*/
	if($_POST['action'] == 'writeMail' && $LoggedIn && $user_right['right_hp_mails']['key'] == $mysql_keys['right_hp_mails'])
	{
		if(isSet($_POST['mail']) && isSet($_POST['body']))
		{
			echo writeMail(urldecode($_POST['headline']), urldecode($_POST['title']), $_POST['mail'], urldecode($_POST['body']));
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Save a Mail
	*/
	if($_POST['action'] == 'saveMail' && $LoggedIn && $user_right['right_hp_mails']['key'] == $mysql_keys['right_hp_mails'])
	{
		if(isSet($_POST['request']) && isSet($_POST['body']))
		{
			echo saveMail(urldecode($_POST['headline']), urldecode($_POST['title']), urldecode($_POST['request']), urldecode($_POST['body']));
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
?>