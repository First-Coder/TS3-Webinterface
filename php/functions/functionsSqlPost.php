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
		Get Webinterface Moduls
	*/
	if($_POST['action'] == 'getModuls')
	{
		echo json_encode($mysql_modul);
	};
	
	/*
		Get Client Block Information
	*/
	if($_POST['action'] == 'checkUserBlocked' && $LoggedIn)
	{
		echo json_encode(getUserBlock($_SESSION['user']['id']));
	};
	
	/*
		Get Global Rights
	*/
	if($_POST['action'] == 'refreshRights' && $LoggedIn)
	{
		echo json_encode(getUserRights('pk', $_SESSION['user']['id']));
	};
	
	/*
		Get Client Permissions
	*/
	if(isSet($_SESSION['user']['id']))
	{
		$user_right	=	getUserRights('pk', $_SESSION['user']['id']);
	};
	
	/*
		Forogot password
	*/
	if($_POST['action'] == 'forgotPassword')
	{
		echo forgotPassword($_POST['username']);
	};
	
	/*
		Delete all Clients
	*/
	if($_POST['action'] == 'deleteAllUsers' && $LoggedIn && $user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete'])
	{
		echo deleteAllUser($_POST['username'], urldecode($_POST['password']));
	};
	
	/*
		Save Server Edit settings from a spezific Client
	*/
	if($_POST['action'] == 'clientEditServerEdit' && $LoggedIn && $user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit'])
	{
		if(clientEditServerEdit($_POST['pk'], $_POST['port'], $_POST['instanz'], $_POST['adminCheckboxRightServerEditPort'], $_POST['adminCheckboxRightServerEditSlots'], $_POST['adminCheckboxRightServerEditAutostart'], 
			$_POST['adminCheckboxRightServerEditMinClientVersion'], $_POST['adminCheckboxRightServerEditMainSettings'], $_POST['adminCheckboxRightServerEditDefaultServerGroups'], $_POST['adminCheckboxRightServerEditHostSettings'], 
			$_POST['adminCheckboxRightServerEditComplaintSettings'], $_POST['adminCheckboxRightServerEditAntiFloodSettings'], $_POST['adminCheckboxRightServerEditTransferSettings'], $_POST['adminCheckboxRightServerEditProtokollSettings']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Save Server Edit settings from a spezific Client
	*/
	if($_POST['action'] == 'getCheckedClientServerEditRights' && $LoggedIn && $user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit'])
	{
		echo json_encode(getCheckedClientServerEditRights($_POST['pk'], $_POST['instanz'], $_POST['port']));
	};
	
	/*
		Change Client Rights (Teamspeakserver)
	*/
	if($_POST['action'] == 'clientEditPorts' && $LoggedIn && ($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit'] || $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create']))
	{
		if(clientEditPorts($_POST['pk'], $_POST['server_view'], $_POST['port'], $_POST['instanz'], $_POST['server_edit'], $_POST['server_start_stop'], $_POST['server_msg_poke'], $_POST['server_mass_actions'], 
			$_POST['server_protokoll'], $_POST['server_icons'], $_POST['server_bans'], $_POST['server_token'], $_POST['server_filelist'], $_POST['server_backups'], $_POST['server_clients'], $_POST['client_actions'], 
			$_POST['client_rights'], $_POST['channel_actions']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Login the Client
	*/
	if($_POST['action'] == 'loginUser')
	{
		if(isSet($_POST['username']) && isSet($_POST['password']))
		{
			echo loginUser($_POST['username'], $_POST['password']);
		}
		else
		{
			echo "0";
		};
	};
	
	/*
		Change Client Rights (Global)
	*/
	if($_POST['action'] == 'clientEdit' && $LoggedIn && $user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit'])
	{
		if(clientEdit($_POST['pk'], $_POST['right'], $_POST['checkbox'], $_POST['time']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Delete a Client
	*/
	if($_POST['action'] == 'deleteUser' && $LoggedIn && $user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete'])
	{
		if(deleteUser($_POST['pk']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Create a Client
	*/
	if($_POST['action'] == 'createUser' && ($LoggedIn  && $user_right['right_hp_user_create']['key'] == $mysql_keys['right_hp_user_create'] || $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'] ||  $mysql_modul['free_register'] == 'true'))
	{
		if($_POST['withPk'] == 'true')
		{
			if($_POST['crypted'] == 'true')
			{
				echo createUser($_POST['username'], $_POST['password'], true, true);
			}
			else
			{
				echo createUser($_POST['username'], $_POST['password'], true, false);
			};
		}
		else
		{
			if($_POST['crypted'] == 'true')
			{
				echo createUser($_POST['username'], $_POST['password'], false, true);
			}
			else
			{
				echo createUser($_POST['username'], $_POST['password'], false, false);
			};
		};
	};
	
	/*
		Change the Modul
	*/
	if($_POST['action'] == 'deleteInstanz' && $LoggedIn && $user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3'])
	{
		if(isSet($_POST['instanz']))
		{
			if(deleteInstanz($_POST['instanz']))
			{
				echo "done";
			}
			else
			{
				echo "error";
			};
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change the Modul
	*/
	if($_POST['action'] == 'setModul' && $LoggedIn && $user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['id']) && isSet($_POST['value']))
		{
			if(setModul($_POST['id'], $_POST['value']))
			{
				echo "done";
			}
			else
			{
				echo "error";
			};
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change a Clientprofile
	*/
	if($_POST['action'] == 'updateUser' && $LoggedIn)
	{
		if(isSet($_POST['id']) && isSet($_POST['content']))
		{
			if(updateUser($_POST['id'], $_POST['content'], $_POST['pk']))
			{
				echo "done";
			}
			else
			{
				echo "error";
			};
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Check if Client already exist
	*/
	if($_POST['action'] == 'checkUser' && ($user_logged || $mysql_modul['free_ts3_server_application'] == 'true'))
	{
		if(checkUsername($_POST['name']))
		{
			echo "error";
		}
		else
		{
			echo "done";
		};
	};