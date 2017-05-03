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
		PHP Session
	*/
	session_start();
	
	/*
		Includes
	*/
	require_once("functions.php");
	require_once("functionsTicket.php");
	
	/*
		Got Webinterface Moduls / Rightkeys
	*/
	$mysql_modul		=		getModuls();
	$mysql_keys			=		getKeys();
	
	/*
		Get User rights
	*/
	$user_right			=		getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Is user logged
	*/
	$user_logged		=		false;
	if($_SESSION['login'] == $mysql_keys['login_key'])
	{
		$user_logged	=		true;
	};
	
	/*
		Add Moderator
	*/
	if($_POST['action'] == 'addModerator' && $user_logged && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['value']))
		{
			if(addModerator($_POST['value']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Edit Moderator
	*/
	if($_POST['action'] == 'editModerator' && $user_logged && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['value']) && isSet($_POST['oldValue']))
		{
			if(editModerator($_POST['value'], $_POST['oldValue']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Delete Moderator
	*/
	if($_POST['action'] == 'deleteModerator' && $user_logged && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['oldValue']))
		{
			if(deleteModerator($_POST['oldValue']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Create Ticket
	*/
	if($_POST['action'] == 'addTicket' && $user_logged)
	{
		if(isSet($_POST['pk']) && isSet($_POST['subject']) && isSet($_POST['message']) && isSet($_POST['department']))
		{
			if(addTicket($_POST['pk'], $_POST['subject'], $_POST['message'], $_POST['department']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Close Ticket
	*/
	if($_POST['action'] == 'closeTicket' && $user_logged)
	{
		if(isSet($_POST['id']))
		{
			if(closeTicket($_POST['id']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Delete Ticket
	*/
	if($_POST['action'] == 'deleteTicket' && $user_logged && $user_right['right_hp_ticket_system'] == $mysql_keys['right_hp_ticket_system'])
	{
		if(isSet($_POST['id']))
		{
			if(deleteTicket($_POST['id']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
	
	/*
		Anwer Ticket
	*/
	if($_POST['action'] == 'answerTicket' && $user_logged)
	{
		if(isSet($_POST['id']) && isSet($_POST['pk']) && isSet($_POST['msg']) && isSet($_POST['moderator']))
		{
			if(answerTicket($_POST['id'], $_POST['pk'], $_POST['msg'], $_POST['moderator']))
			{
				echo "done";
			}
			else
			{
				echo "Something goes wrong :/";
			};
		}
		else
		{
			echo "Parameter are not successfull!";
		};
	};
?>