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
		Create Teamspeakbanner
	*/
	if($_POST['action'] == 'createBanner' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_banner') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['port']) && isSet($_POST['instanz']))
		{
			$packagefile = fopen('../../images/ts_banner/'.$_POST['instanz'].'_'.$_POST['port'].'_settings.json', 'w+');
			fwrite($packagefile, $_POST['data']);
			fclose($packagefile);
			
			echo "done";
		}
		else
		{
			echo "Wrong POST Parameters!";
		};
	};
	
	/*
		Delete a Teamspeakserver
	*/
	if($_POST['action'] == 'serverDelete' && $LoggedIn && $user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete'])
	{
		if(isSet($_POST['serverid']) && isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			if(delTeamspeakserver($_POST['serverid'], $_POST['instanz']))
			{
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
		Check if Teamspeakport exists
	*/
	if($_POST['action'] == 'createServer' && $LoggedIn && $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])
	{
		echo TeamspeakCreateServer(json_decode(urldecode($_POST['serverdata'])), $_POST['instanz'], $_POST['copyInstanz'], $_POST['copyPort'], $_POST['isRequest'], $_POST['requestName'], $_POST['requestPw'], $_POST['filename']);
	};
	
	/*
		Check if Teamspeakport exists
	*/
	if($_POST['action'] == 'checkTeamspeakPort' && $LoggedIn && $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])
	{
		if(isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			echo (checkTeamspeakPort($_POST['instanz'], $_POST['port'])) ? "done" : "error";
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete a Teamspeakchannel
	*/
	if($_POST['action'] == 'createChannel' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_edit') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['channeldata']) && isSet($_POST['instanz']) && isSet($_POST['port']))
		{
			echo TeamspeakCreateChannel(json_decode(urldecode($_POST['channeldata'])), $_POST['instanz'], $_POST['port']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Delete a Teamspeakchannel
	*/
	if($_POST['action'] == 'deleteChannel' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_edit') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Change Client Channelgroup
	*/
	if($_POST['action'] == 'clientChangeChannelGroup' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_client_rights') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Remove a Client a Servergroup
	*/
	if($_POST['action'] == 'clientAddRemoveServerGroup' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_client_rights') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['sgid']) && isSet($_POST['permission']))
		{
			echo TeamspeakServerClientRemoveServerGroup($_POST['sgid'], $_POST['clid'], $_POST['port'], $_POST['instanz'], $_POST['permission']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Edit a Teamspeakserver
	*/
	if($_POST['action'] == 'serverEdit' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_edit') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Send Servermessage
	*/
	if($_POST['action'] == 'serverMessage' && $LoggedIn && 
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_message_poke') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['message']) && (isSet($_POST['port']) || isSet($_POST['serverid'])) && isSet($_POST['mode']))
		{
			print_r(TeamspeakServerMessage($_POST['mode'], $_POST['port'], $_POST['serverid'], urldecode($_POST['message']), $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Send Serverpoke
	*/
	if($_POST['action'] == 'serverPoke' && $LoggedIn && 
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_message_poke') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['message']) && (isSet($_POST['port']) || isSet($_POST['serverid'])))
		{
			echo TeamspeakServerServerPoke(urldecode($_POST['message']), $_POST['port'], $_POST['serverid'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Reset a Teamspeakserver
	*/
	if($_POST['action'] == 'resetServer' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_backups') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Delete a File from a Teamspeakserver
	*/
	if($_POST['action'] == 'deleteFileFromFilelist' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_file_transfer') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo deleteFileFromFilelist(urldecode($_POST['path']), $_POST['cid'], $_POST['port'], $_POST['instanz']);
	};
	
	/*
		Create a Token on a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'addToken' && $LoggedIn && 
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_token') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
	if($_POST['action'] == 'deleteToken' && $LoggedIn && 
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_token') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['instanz']) && isSet($_POST['serverid']) && isSet($_POST['token']))
		{
			print_r(deleteToken(urldecode($_POST['token']), $_POST['serverid'], $_POST['instanz']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Ban Manuell
	*/
	if($_POST['action'] == 'clientBanManuell' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_bans') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['bantype']) && isSet($_POST['input']) && isSet($_POST['time']))
		{
			echo TeamspeakServerClientBanManuell($_POST['bantype'], urldecode($_POST['input']), $_POST['time'], urldecode($_POST['reason']), $_POST['port'], $_POST['instanz']);
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Client Unban
	*/
	if($_POST['action'] == 'clientUnban' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_bans') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Delete a Teamspeakclient in the Database
	*/
	if($_POST['action'] == 'deleteDBClient' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_clients') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		echo deleteDBClient($_POST['cldbid'], $_POST['time'], $_POST['port'], $_POST['instanz']);
	};
	
	/*
		Delete Teamspeak Icons
	*/
	if($_POST['action'] == 'deleteIcon' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_icons') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Client Message
	*/
	if(($_POST['action'] == 'clientMsg' || $_POST['action'] == 'clientPoke' || $_POST['action'] == 'clientMove' || $_POST['action'] == 'clientKick' || $_POST['action'] == 'clientBan') && $LoggedIn &&
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_mass_actions') || isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_client_actions') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['clid']) && isSet($_POST['message']))
		{
			echo TeamspeakServerClientAction($_POST['action'], urldecode($_POST['message']), $_POST['clid'], (isSet($_POST['cid'])) ? $_POST['cid'] : '', $_POST['port'], $_POST['instanz'], (isSet($_POST['kickmode'])) ? $_POST['kickmode'] : '', (isSet($_POST['bantime'])) ? $_POST['bantime'] : '');
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get Teamspeakclients with spezifications
	*/
	if($_POST['action'] == 'getUsersMassActions' && $LoggedIn && (isPortPermission($user_right, $_POST['ts3_server'], $_POST['ts3_port'], 'right_web_server_mass_actions') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
	{
		if(isSet($_POST['ts3_port']) && isSet($_POST['mass_action']))
		{
			echo json_encode(getUsersMassActions($_POST['group'], $_POST['channel'], $_POST['who'], $_POST['just_id'], $_POST['mass_action'], $_POST['ts3_port'], $_POST['ts3_server']));
		}
		else
		{
			echo 'error';
		};
	};
	
	/*
		Get the Teamspeak Querylogof a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'portServerQuerylog' && $LoggedIn && (isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_protokoll') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Stop the Teamspeakserver
	*/
	if($_POST['action'] == 'serverStop' && $LoggedIn &&
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
	if($_POST['action'] == 'serverStart' && $LoggedIn && 
		(isPortPermission($user_right, $_POST['instanz'], $_POST['port'], 'right_web_server_start_stop') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']))
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
		Get Teamspeak Serverinformations for all Teamspeakserver in every Teamspeakinstanz
	*/
	if($_POST['action'] == 'getInstanzServerlistInformations' && $LoggedIn  && $user_right['right_web']['key'] == $mysql_keys['right_web'])
	{
		echo json_encode(TeamspeakInstanzServerlistInformations());
	};
	
	/*
		Send Message to a whole Instanz
	*/
	if($_POST['action'] == 'instanzMsgPoke' && $LoggedIn && $user_right['right_web_global_message_poke']['key'] == $mysql_keys['right_web_global_message_poke'])
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
		Get Teamspeakports of a spezific Teamspeakserver
	*/
	if($_POST['action'] == 'getTeamspeakPorts' && $LoggedIn && isSet($_POST['instanz']))
	{
		if($user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main'] || $user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create'])
		{
			echo json_encode(getTeamspeakPorts($_POST['instanz']));
		}
		else
		{
			//echo json_encode(getTeamspeakClientPorts($_POST['instanz']));
		};
	};
	
	/*
		Write in Instanz console
	*/
	if($_POST['action'] == 'commandQueryConsole' && $LoggedIn && $user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3'])
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
		Edit a Teamspeakinstance and return if the connection was successfull
	*/
	if($_POST['action'] == 'editInstance' && $LoggedIn && $user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3'])
	{
		if(isSet($_POST['instanz']) && isSet($_POST['what']) && isSet($_POST['content']))
		{
			echo editInstance($_POST['instanz'], $_POST['what'], $_POST['content']);
		}
		else
		{
			echo 'Parameter are not correct!';
		};
	};
	
	/*
		Get the whole Teamspeaktree
	*/
	if($_POST['action'] == 'getTeamspeakBaum')
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
		Get Teamspeakslots
	*/
	if($_POST['action'] == 'getTeamspeakslots' && $LoggedIn)
	{
		if(isSet($_POST['instanz']))
		{
			echo json_encode(getTeamspeakslots($_POST['instanz'], (isset($_POST['force'])) ? $_POST['force'] : false));
		}
		else
		{
			echo 'error';
		};
	};