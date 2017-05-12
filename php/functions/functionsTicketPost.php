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
	require_once("../../lang/lang.php");
	require_once("./functions.php");
	require_once("./functionsSql.php");
	require_once("./functionsTicket.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Check Session
	*/
	$LoggedIn		=	(checkSession($mysql_keys['login_key'])) ? true : false;
	
	/*
		Get Client Permissions
	*/
	if(isSet($_SESSION['user']['id']))
	{
		$user_right	=	getUserRights('pk', $_SESSION['user']['id'], true, 'global');
	};
	
	/*
		Add Moderator
	*/
	if($_POST['action'] == 'addModerator' && $LoggedIn && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['value']))
		{
			echo (addModerator(urldecode($_POST['value']))) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Edit Moderator
	*/
	if($_POST['action'] == 'editModerator' && $LoggedIn && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['value']) && isSet($_POST['oldValue']))
		{
			echo (editModerator(urldecode($_POST['value']), urldecode($_POST['oldValue']))) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Delete Moderator
	*/
	if($_POST['action'] == 'deleteModerator' && $LoggedIn && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['oldValue']))
		{
			echo (deleteModerator(urldecode($_POST['oldValue']))) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Create Ticket
	*/
	if($_POST['action'] == 'addTicket' && $LoggedIn)
	{
		if(isSet($_POST['subject']) && isSet($_POST['message']) && isSet($_POST['department']))
		{
			echo addTicket(urldecode($_POST['subject']), $_POST['message'], urldecode($_POST['department']));
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Anwer Ticket
	*/
	if($_POST['action'] == 'answerTicket' && $LoggedIn)
	{
		if(isSet($_POST['id']) && isSet($_POST['msg']))
		{
			echo (answerTicket($_POST['id'], $_POST['msg'])) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Close Ticket
	*/
	if($_POST['action'] == 'closeTicket' && $LoggedIn)
	{
		if(isSet($_POST['id']))
		{
			echo (closeTicket($_POST['id'])) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Delete Ticket
	*/
	if($_POST['action'] == 'deleteTicket' && $LoggedIn && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['id']))
		{
			echo (deleteTicket($_POST['id'])) ? "done" : "Something goes wrong :/";
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
?>