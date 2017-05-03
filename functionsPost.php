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
	require_once("functionsMail.php");
	
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
		Change the Configfile
	*/
	if($_POST['action'] == 'setConfigSettings' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['heading']) && isSet($_POST['chatname']) && isSet($_POST['selInstanz']) && isSet($_POST['selPort']) && isSet($_POST['mailadress']))
		{
			if(setConfigSettings($_POST['heading'], $_POST['chatname'], $_POST['selInstanz'], $_POST['selPort'], $_POST['mailadress']))
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
			"error";
		};
	};
	
	/*
		Change a Clientprofile
	*/
	if($_POST['action'] == 'updateUser' && $user_logged)
	{
		if(isSet($_POST['id']) && isSet($_POST['content']))
		{
			if(updateUser($_POST['id'], $_POST['content'], $_POST['adminpk'], $_POST['pk']))
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
			"error";
		};
	};
	
	/*
		Get Global Rights
	*/
	if($_POST['action'] == 'refreshRights' && $user_logged)
	{
		if(isSet($_POST['id']))
		{
			echo json_encode(getUserRightsWithTime(getUserRights('pk', $_POST['id'])));
			
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Get ClientRights (without a Timelimit)
	*/
	if($_POST['action'] == 'getBenutzerRights' && $user_logged && $user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'])
	{
		if(isset($_POST['pk']))
		{
			echo json_encode(getUserRights('pk', $_POST['pk']));
		}
		else
		{
			echo "0";
		};
	};
	
	/*
		Change Modul: Webinterface
	*/
	if($_POST['action'] == 'setModulWebinterface' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['value']))
		{
			print_r(setModulWebinterface($_POST['value']));
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Modul: Server Application
	*/
	if($_POST['action'] == 'setModulServerAntrag' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['value']))
		{
			print_r(setModulFreeTS3ServerApplication($_POST['value']));
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Modul: Write News
	*/
	if($_POST['action'] == 'setModulWriteNews' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['value']))
		{
			print_r(setModulWriteNews($_POST['value']));
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Modul: Free Register
	*/
	if($_POST['action'] == 'setModulFreeRegister' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['value']))
		{
			print_r(setModulFreeRegister($_POST['value']));
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Modul: Masterserver
	*/
	if($_POST['action'] == 'setModulMasterserver' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['value']))
		{
			print_r(setModulMasterserver($_POST['value']));
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Client Rights (Global)
	*/
	if($_POST['action'] == 'clientEdit' && $user_logged && $user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'])
	{
		if(userEdit($_POST['pk'], $_POST['right'], $_POST['checkbox'], $_POST['time']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Client Rights (Teamspeakserver)
	*/
	if($_POST['action'] == 'clientEditPorts' && $user_logged && ($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'] || $user_right['right_web_server_create'] == $mysql_keys['right_web_server_create']))
	{
		if(userEditPorts($_POST['pk'], $_POST['server_view'], $_POST['time_server_view'], $_POST['teamspeak_port'], $_POST['teamspeak_instanz'], $_POST['server_edit'], $_POST['server_start_stop'], 
			$_POST['server_msg_poke'], $_POST['server_mass_actions'], $_POST['server_protokoll'], $_POST['server_icons'], $_POST['server_bans'], $_POST['server_token'], $_POST['server_filelist'], $_POST['server_backups'], 
			$_POST['server_clients'], $_POST['client_actions'], $_POST['client_rights'], $_POST['channel_actions'], $_POST['time_server_edit'], $_POST['time_server_start_stop'], 
			$_POST['time_server_msg_poke'], $_POST['time_server_mass_actions'], $_POST['time_server_protokoll'], $_POST['time_server_icons'], $_POST['time_server_bans'], $_POST['time_server_token'], 
			$_POST['time_server_filelist'], $_POST['time_server_backups'], $_POST['time_server_clients'], $_POST['time_client_actions'], $_POST['time_client_rights'], $_POST['time_channel_actions']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Create a Instanz
	*/
	if($_POST['action'] == 'createInstanz' && $user_logged && $user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3'])
	{
		echo createInstanz($_POST['alias'], $_POST['ip'], $_POST['queryport'], $_POST['client'], $_POST['passwort']);
	};
	
	/*
		Edit a Instanz
	*/
	if($_POST['action'] == 'writeInstanz' && $user_logged && $user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3'])
	{
		print_r(writeInstanz($_POST['instanz'], $_POST['what'], $_POST['content']));
	};
	
	/*
		Delete a Instanz
	*/
	if($_POST['action'] == 'deleteInstanz' && $user_logged && $user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3'])
	{
		if(deleteInstanz($_POST['ts3_server']))
		{
			echo $_POST['ts3_server'];
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Create a Client
	*/
	if($_POST['action'] == 'createUser' && ($user_logged  && $user_right['right_hp_user_create'] == $mysql_keys['right_hp_user_create'] || $user_right['right_web_server_create'] == $mysql_keys['right_web_server_create'] ||  $mysql_modul['free_register'] == 'true'))
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
		Delete a Client
	*/
	if($_POST['action'] == 'deleteUser' && $user_logged && $user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete'])
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
		Delete all Clients
	*/
	if($_POST['action'] == 'deleteAllUsers' && $user_logged && $user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete'])
	{
		echo deleteAllUser($_POST['username'], $_POST['password']);
	};
	
	/*
		Get Client Block Information
	*/
	if($_POST['action'] == 'checkUserBlocked' && $user_logged)
	{
		if(isSet($_POST['id']))
		{
			echo json_encode(getUserBlock($_POST['id']));
			
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Change Webinterface language
	*/
	if($_POST['action'] == 'setLanguage' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(isSet($_POST['lang']))
		{
			echo setLanguage($_POST['lang']);
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Get Webinterface Moduls
	*/
	if($_POST['action'] == 'getModuls')
	{
		echo json_encode($mysql_modul);
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
	
	/*
		Get Cleint Pk
	*/
	if($_POST['action'] == 'getPk' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		echo checkUsername($_POST['name'], true);
	};
	
	/*
		Read a other Dokument
	*/
	if($_POST['action'] == 'deleteData' && $user_logged && $user_right['right_web_server_create'] == $mysql_keys['right_web_server_create'])
	{
		if(!file_exists("wantServer/".$_POST['link']))
		{
			echo "error";
		}
		else
		{
			if(deleteData("wantServer/".$_POST['link']))
			{
				$fileContent								=		explode("_", $_POST['link']);
				
				$mailContent								=		array();
				$mailContent								=		getMail("request_failed");
				
				$mailContent								=		str_replace("%heading%", 					HEADING, 									$mailContent);
				$mailContent								=		str_replace("%client%", 					$fileContent[0], 							$mailContent);
				
				echo writeMail($mailContent["headline"], $mailContent["mail_subject"], $fileContent[0], $mailContent["mail_body"]);
			}
			else
			{
				echo "error";
			};
		};
	};
	
	/*
		Change the Homepagetheme
	*/
	if($_POST['action'] == 'setTheme' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(setTheme($_POST['file']))
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
	if($_POST['action'] == 'clientEditServerEdit' && $user_logged && $user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'])
	{
		if(userServerEdit($_POST['pk'], $_POST['port'], $_POST['instanz'], $_POST['adminCheckboxRightServerEditPort'], $_POST['adminCheckboxRightServerEditSlots'], $_POST['adminCheckboxRightServerEditAutostart'], 
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
	if($_POST['action'] == 'clientEditServerEditInformations' && $user_logged && $user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'])
	{
		echo json_encode(getCheckedClientServerEditRights($_POST['pk'], $_POST['instanz'], $_POST['port']));
	};
	
	/*
		Get Userinformations
	*/
	if($_POST['action'] == 'getUserInformations' && $user_logged && $user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit'])
	{
		echo json_encode(getUserInformations($_POST['pk']));
	};
	
	/*
		Create News
	*/
	if($_POST['action'] == 'createNews' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(createNews($_POST['title'], $_POST['subtitle'], $_POST['content']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
	
	/*
		Delete News
	*/
	if($_POST['action'] == 'deleteNews' && $user_logged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'])
	{
		if(deleteNews($_POST['file']))
		{
			echo "done";
		}
		else
		{
			echo "error";
		};
	};
?>