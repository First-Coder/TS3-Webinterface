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
		Check if Teamspeakport exists
	*/
	if($_POST['action'] == 'checkTeamspeakPort' && $user_logged && $user_right['right_web_server_create'] == $mysql_keys['right_web_server_create'])
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			if(checkTeamspeakPort($_POST['instanz'], $_POST['port']))
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
			echo 'error';
		};
	};
	
	/*
		Get Teamspeakslots
	*/
	if($_POST['action'] == 'getTeamspeakslots' && $user_logged)
	{
		if(isSet($_POST['instanz']))
		{
			echo json_encode(getTeamspeakslots($_POST['instanz'], $_POST['force']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Reset a Teamspeakserver
	*/
	if($_POST['action'] == 'resetServer' && $user_logged && (strpos($user_right['ports']['right_web_server_backups'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			echo resetServer($_POST['instanz'], $_POST['port']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Create a Teamspeak Backup
	*/
	if($_POST['action'] == 'createBackup' && $user_logged && (strpos($user_right['ports']['right_web_server_backups'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']) && isSet($_POST['kind']))
		{
			echo createBackup($_POST['instanz'], $_POST['port'], $_POST['kind'], $_POST['kindChannel']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Write in Instanz console
	*/
	if($_POST['action'] == 'commandQueryConsole' && $user_logged && $user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3'])
	{
		if(isSet($_POST['instanz']) && isSet($_POST['command']))
		{
			if(isSet($_POST['server']))
			{
				echo ExecServerQueryCommand($_POST['instanz'], $_POST['command'], $_POST['server']);
			}
			else
			{
				echo ExecServerQueryCommand($_POST['instanz'], $_POST['command']);
			};
		}
		else
		{
			echo "Some parameters are wrong :/";
		};
	};
	
	/*
		Stop the Teamspeakserver
	*/
	if($_POST['action'] == 'serverStop' && $user_logged &&
		(strpos($user_right['ports']['right_web_server_start_stop'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['serverid']))
		{
			echo stopTeamspeakServer($_POST['serverid'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Start the Teamspeakserver
	*/
	if($_POST['action'] == 'serverStart' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_start_stop'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['serverid']))
		{
			echo startTeamspeakServer($_POST['serverid'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get Serverinfromations above a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'serverInfo' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_start_stop'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['serverid']))
		{
			echo json_encode(TeamspeakServerInformations($_POST['serverid'], $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Send Servermessage
	*/
	if($_POST['action'] == 'serverMessage' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_message_poke'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['message']) && (isSet($_POST['port']) || isSet($_POST['serverid'])) && isSet($_POST['mode']))
		{
			print_r(TeamspeakServerMessage($_POST['mode'], $_POST['port'], $_POST['serverid'], $_POST['message'], $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Send Serverpoke
	*/
	if($_POST['action'] == 'serverPoke' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_message_poke'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['message']) && (isSet($_POST['port']) || isSet($_POST['serverid'])))
		{
			echo TeamspeakServerServerPoke($_POST['message'], $_POST['port'], $_POST['serverid'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Send Message to a whole Instanz
	*/
	if($_POST['action'] == 'instanzMsgPoke' && $user_logged && $user_right['right_web_global_message_poke'] == $mysql_keys['right_web_global_message_poke'])
	{
		if(isSet($_POST['instanz']) && isSet($_POST['message']))
		{
			echo TeamspeakServerInstanzMessagePoke($_POST['message'], $_POST['mode'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Edit a Teamspeakserver
	*/
	if($_POST['action'] == 'serverEdit' && $user_logged && (strpos($user_right['ports']['right_web_server_edit'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['right']) && isSet($_POST['value']) && isSet($_POST['instanz']) && isSet($_POST['serverid']) && isSet($_POST['port']))
		{
			print_r(TeamspeakServerEdit($_POST['right'], $_POST['value'], $_POST['instanz'], $_POST['serverid'], $_POST['port']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete a Teamspeakchannel
	*/
	if($_POST['action'] == 'deleteChannel' && $user_logged && (strpos($user_right['ports']['right_web_server_edit'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['cid']) && isSet($_POST['instanz']) && isSet($_POST['serverid']) && isSet($_POST['port']))
		{
			print_r(TeamspeakDeleteChannel($_POST['cid'], $_POST['instanz'], $_POST['serverid']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get all Teamspeakchannels
	*/
	if($_POST['action'] == 'getTeamspeakChannels' && $user_logged && (strpos($user_right['ports']['right_web_server_backups'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			echo json_encode(getTeamspeakChannels($_POST['instanz'], $_POST['port']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete Teamspeak Icons
	*/
	if($_POST['action'] == 'deleteIcon' && $user_logged && (strpos($user_right['ports']['right_web_server_icons'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['serverId']) && isSet($_POST['id']) && isSet($_POST['port']))
		{
			if(deleteIcon($_POST['id'], $_POST['instanz'], $_POST['serverId'], $_POST['port']))
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
			echo 'error';
		};
	};
	
	/*
		Create a Token on a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'addToken' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_token'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['serverid']) && isSet($_POST['type']) && isSet($_POST['tokenid1']) && isSet($_POST['tokenid2']) && isSet($_POST['number']) && isSet($_POST['description']))
		{
			echo json_encode(addToken($_POST['type'], $_POST['tokenid1'], $_POST['tokenid2'], $_POST['number'], $_POST['description'], $_POST['serverid'], $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete a Token on a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'deleteToken' && $user_logged && $user_logged && 
		(strpos($user_right['ports']['right_web_server_token'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['serverid']) && isSet($_POST['token']))
		{
			print_r(deleteToken($_POST['token'], $_POST['serverid'], $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get Teamspeakclients with spezifications
	*/
	if($_POST['action'] == 'getUsers' && $user_logged && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['ts3_server']], $_POST['ts3_port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['ts3_port']) && isSet($_POST['mass_action']))
		{
			echo json_encode(getUsersMassActions($_POST['group'], $_POST['channel'], $_POST['who'], $_POST['ID'], $_POST['mass_action'], $_POST['ts3_port'], $_POST['ts3_server']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Poke
	*/
	if($_POST['action'] == 'clientPoke' && $user_logged && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['instanz']], $_POST['port']) !== false
		||  strpos($user_right['ports']['right_web_client_actions'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['message']))
		{
			echo TeamspeakServerClientPoke($_POST['message'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Message
	*/
	if($_POST['action'] == 'clientMsg' && $user_logged && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['instanz']], $_POST['port']) !== false
		||  strpos($user_right['ports']['right_web_client_actions'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['message']))
		{
			echo TeamspeakServerClientMessage($_POST['message'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Move
	*/
	if($_POST['action'] == 'clientMove' && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['instanz']], $_POST['port']) !== false
		||  strpos($user_right['ports']['right_web_client_actions'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['cid']))
		{
			echo TeamspeakServerClientMove($_POST['cid'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Kick
	*/
	if($_POST['action'] == 'clientKick' && $user_logged && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['instanz']], $_POST['port']) !== false
		||  strpos($user_right['ports']['right_web_client_actions'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['mode']))
		{
			echo TeamspeakServerClientKick($_POST['message'], $_POST['mode'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Ban
	*/
	if($_POST['action'] == 'clientBan' && $user_logged && (strpos($user_right['ports']['right_web_server_mass_actions'][$_POST['instanz']], $_POST['port']) !== false
		||  strpos($user_right['ports']['right_web_client_actions'][$_POST['instanz']], $_POST['port'] !== false) || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['time']))
		{
			echo TeamspeakServerClientBan($_POST['message'], $_POST['time'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Ban Manuell
	*/
	if($_POST['action'] == 'clientBanManuell' && $user_logged && (strpos($user_right['ports']['right_web_server_bans'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['bantype']) && isSet($_POST['input']) && isSet($_POST['time']))
		{
			echo TeamspeakServerClientBanManuell($_POST['bantype'], $_POST['input'], $_POST['time'], $_POST['reason'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Unban
	*/
	if($_POST['action'] == 'clientUnban' && $user_logged && (strpos($user_right['ports']['right_web_server_bans'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['banid']) && isSet($_POST['port']) && isSet($_POST['instanz']))
		{
			echo TeamspeakServerClientUnban($_POST['banid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Give a Client a Servergroup
	*/
	if($_POST['action'] == 'clientAddServerGroup' && $user_logged && (strpos($user_right['ports']['right_web_client_rights'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['sgid']))
		{
			echo TeamspeakServerClientAddServerGroup($_POST['sgid'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Remove a Client a Servergroup
	*/
	if($_POST['action'] == 'clientRemoveServerGroup' && $user_logged && (strpos($user_right['ports']['right_web_client_rights'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['sgid']))
		{
			echo TeamspeakServerClientRemoveServerGroup($_POST['sgid'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Change Client Channelgroup
	*/
	if($_POST['action'] == 'clientChangeChannelGroup' && $user_logged && (strpos($user_right['ports']['right_web_client_rights'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['cgid']) && isSet($_POST['cid']))
		{
			echo TeamspeakServerClientChangeChannelGroup($_POST['cgid'], $_POST['cid'], $_POST['clid'], $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete a Teamspeakserver
	*/
	if($_POST['action'] == 'serverDelete' && $user_logged && 
		(strpos($user_right['ports']['right_web_server_delete'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['serverid']) && isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			if(delTeamspeakserver($_POST['serverid'], $_POST['instanz']))
			{
				// Server in MySQL Datenbank austragen
				deletePort($_POST['port'], $_POST['instanz']);
				
				echo 'done';
			}
			else
			{
				echo 'error';
			};
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get Teamspeakports of a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'getTeamspeakPorts' && $user_logged && $user_right['right_web_server_create'] == $mysql_keys['right_web_server_create'])
	{
		if(!isSet($_POST['client_right']))
		{
			if(isSet($_POST['instanz']))
			{
				echo json_encode(getTeamspeakPorts($_POST['instanz']));
			}
			else
			{
				echo 'error';
			};
		}
		else
		{
			if(isSet($_POST['instanz']))
			{
				echo json_encode(getTeamspeakClientPorts($_POST['instanz']));
			}
			else
			{
				echo 'error';
			};
		};
	};
	
	/*
		Get the whole Teamspeaktree
	*/
	if($_POST['action'] == 'getTeamspeakBaum')// && $user_logged && strpos($user_right['ports']['right_web_server_view'][$_POST['instanz']], $_POST['port']) !== false)
	{
		if(isSet($_POST['port']) && isSet($_POST['instanz']))
		{
			echo json_encode(getTeamspeakBaum($_POST['instanz'], $_POST['port']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get the Teamspeak Querylogof a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'portServerQuerylog' && $user_logged && (strpos($user_right['ports']['right_web_server_protokoll'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			echo getPortQuery($_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get Teamspeak Serverinformations for all Teamspeakserver in every Teamspeakinstanz
	*/
	if($_POST['action'] == 'getTs3ServerlistInformations' && $user_logged)
	{
		echo json_encode(TeamspeakInstanzServerlistInformations());
	};
	
	/*
		Delete a Teamspeakclient in the Database
	*/
	if($_POST['action'] == 'deleteDBClient' && $user_logged && (strpos($user_right['ports']['right_web_server_clients'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		echo deleteDBClient($_POST['cldbid'], $_POST['port'], $_POST['instanz']);
	};
	
	/*
		Delete a File from a Teamspeakserver
	*/
	if($_POST['action'] == 'deleteFileFromFilelist' && $user_logged && (strpos($user_right['ports']['right_web_file_transfer'][$_POST['instanz']], $_POST['port']) !== false || $user_right['right_web_global_server'] == $mysql_keys['right_web_global_server']))
	{
		echo deleteFileFromFilelist($_POST['path'], $_POST['cid'], $_POST['port'], $_POST['instanz']);
	};
?>