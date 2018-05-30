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
	require_once(__DIR__."/../../config/instance.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../classes/ts3admin.class.php");
	
	/*
		Session start
	*/
	checkSession();
	
	/*
		Delete a Teamspeak Server
	*/
	function delTeamspeakserver($serverid, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$server_del		=	$tsAdmin->serverDelete($serverid);
			
			$tsAdmin->logout();
			
			if($server_del['success'] !== false)
			{
				writeInLog($_SESSION['user']['benutzer'], "Delete Teamspeak Server Instanz: ".$instanz." Sid: ".$serverid, true);
				
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Check if port is avalible
	*/
	function checkTeamspeakPort($instanz, $port)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$serverlist		=	$tsAdmin->getElement('data', $tsAdmin->serverList());
			
			foreach($serverlist AS $server)
			{
				if($server['virtualserver_port'] == $port)
				{
					return true;
				};
			};
		};
		
		return false;
	};
	
	/*
		Create a Teamspeakserver
	*/
	function TeamspeakCreateServer($serverdata, $instanz, $copyInstanz, $copyPort, $isRequest, $requestName, $requestPw, $filename)
	{
		global $ts3_server;
		
		include_once(__dir__."/functionsSql.php");
		include_once(__dir__."/functionsMail.php");
		include_once(__dir__."/../../lang/lang.php");
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$token							=	$tsAdmin->serverCreate($serverdata);
			$status['success']				=	'1';
			$status['error']				=	'';
			
			if($token['success'] === false)
			{
				$status['success']			=		'0';
				for($i = 0; $i + 1 == count($token['errors']); $i++)
				{
					$status['error']		.=		$token['errors'][$i].'<br/>';
				};
			}
			else
			{
				$status['port']				=		$token['data']['virtualserver_port'];
				$status['serverid']			=		$token['data']['sid'];
				$status['token']			=		$token['data']['token'];
				
				if($copyInstanz != "nope" && $copyPort != "nope")
				{
					$copyTsAdmin = new ts3admin($ts3_server[$copyInstanz]['ip'], $ts3_server[$copyInstanz]['queryport']);
					
					if($copyTsAdmin->getElement('success', $copyTsAdmin->connect()))
					{
						$copyTsAdmin->login($ts3_server[$copyInstanz]['user'], $ts3_server[$copyInstanz]['pw']);
						$copyTsAdmin->selectServer($copyPort, 'port', true);
						$tsAdmin->selectServer($token['data']['virtualserver_port'], 'port', true);
						
						$channelList		=		$copyTsAdmin->channelList();
						
						if($channelList['success'] !== false)
						{
							foreach($channelList['data'] AS $channelCounter=>$channel)
							{
								createTeamspeakChannel($tsAdmin, $channel['channel_name'], false, $token['data']['virtualserver_port'], $instanz);
							};
						};
					};
				};
				
				if(giveUserAllRightsTSServer($_SESSION['user']['id'], $instanz, $token['data']['virtualserver_port']) && $isRequest == "true")
				{
					$clientPk		=	"";
					if($requestPw != "")
					{
						$clientPk	=	createUser($requestName, $requestPw, true, true);
					};
					
					if($requestPw == "" || $clienPk == false || $client)
					{
						$clientPk	=	checkUsername($requestName, true);
					};
					
					if(giveUserAllRightsTSServer($clientPk, $instanz, $token['data']['virtualserver_port'], false) && clientEdit($clientPk, 'right_web', 'true', '0'))
					{
						$mailContent			=		array();
						$mailContent			=		getMail("request_success");
						
						$mailContent			=		str_replace("%heading%", 					HEADING, 											$mailContent);
						$mailContent			=		str_replace("%client%", 					$requestName, 										$mailContent);
						$mailContent			=		str_replace("%ip%", 						$ts3_server[$instanz]['ip'], 						$mailContent);
						$mailContent			=		str_replace("%serverCreateServername%", 	$serverdata->virtualserver_name, 					$mailContent);
						$mailContent			=		str_replace("%serverCreatePort%", 			$serverdata->virtualserver_port, 					$mailContent);
						$mailContent			=		str_replace("%serverCreateSlots%", 			$serverdata->virtualserver_maxclients, 				$mailContent);
						$mailContent			=		str_replace("%serverCreateReservedSlots%", 	$serverdata->virtualserver_reserved_slots, 			$mailContent);
						$mailContent			=		str_replace("%serverCreatePassword%", 		$serverdata->virtualserver_password, 				$mailContent);
						$mailContent			=		str_replace("%serverCreateWelcomeMessage%", $serverdata->virtualserver_welcomemessage, 			$mailContent);
						$mailContent			=		str_replace("%token%", 						$token['data']['token'], 							$mailContent);
						
						if(USE_MAILS == "true")
						{
							if(writeMail($mailContent["headline"], $mailContent["mail_subject"], $requestName, $mailContent["mail_body"]) != "done")
							{
								$status['success']				=		'0';
								$status['error']				=		'Mail could not be send to client!';
							}
							else
							{
								if(!unlink(__dir__."/../../files/wantServer/".$filename))
								{
									$status['success']			=		'0';
									$status['error']			=		'Server Request file could be not deleted';
								};
							};
						};
					}
					else
					{
						$status['success']					=		'0';
						$status['error']					=		'Request client could not get permission to the server';
					};
				};
			};
		}
		else
		{
			$status['success']					=		'0';
			$status['error']					=		'Connection to Teamspeakserver failed';
		};
		
		return json_encode($status);
	};
	
	/*
		Create a Teamspeakchannel
	*/
	function TeamspeakCreateChannel($channeldata, $instanz, $port)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($port, 'port', true);
			
			$createChannel 	=	$tsAdmin->channelCreate($channeldata);
			if($createChannel['success']===false)
			{
				for($i=0; $i+1==count($createChannel['errors']); $i++)
				{
					$status	.= 	$createChannel['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Create a Teamspeakchannel (".$cid.") Instanz: ".$instanz." Port: ".$port."", true);
				
				$status		=	"done";
			};
			
			return $status;
		};
	};
	
	/*
		Delete a Teamspeakchannel
	*/
	function TeamspeakDeleteChannel($cid, $instanz, $sid)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($sid, 'serverId', true);
			
			$deleteChannel 	=	$tsAdmin->channelDelete($cid, 1);
			if($deleteChannel['success']===false)
			{
				for($i=0; $i+1==count($deleteChannel['errors']); $i++)
				{
					$status	.= 	$deleteChannel['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Delete a Teamspeakchannel (".$cid.") Instanz: ".$instanz." Sid: ".$sid."", true);
				
				$status		=	"done";
			};
			
			return $status;
		};
	};
	
	/*
		Change the Client Channelgroup
	*/
	function TeamspeakServerClientChangeChannelGroup($cgid, $cid, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($port, 'port', true);
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_cgroup = $tsAdmin->setClientChannelGroup($cgid, $cid, $clid);
			if($client_cgroup['success'] === false)
			{
				for($i = 0; $i+1 == count($client_cgroup['errors']); $i++)
				{
					$status .= $client_cgroup['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Change Channelgroup Sgroup: ".$cgid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status = "done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Remove a Client Servergroup
	*/
	function TeamspeakServerClientRemoveServerGroup($sgid, $clid, $port, $instanz, $permission)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($port, 'port', true);
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_sgroup = ($permission == "true") ? $tsAdmin->serverGroupAddClient($sgid, $clid) : $tsAdmin->serverGroupDeleteClient($sgid, $clid);
			if($client_sgroup['success'] === false)
			{
				for($i = 0; $i+1 == count($client_sgroup['errors']); $i++)
				{
					$status .= $client_sgroup['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Change Servergroup Sgroup: ".$sgid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port." Permission: ".$permission, true);
				
				$status = "done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Edit Teamspeakserver
	*/
	function TeamspeakServerEdit($right, $value, $instanz, $serverId, $port)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			$status				=	'';
			$server_edit 		=	$tsAdmin->serverEdit(array($right=>$value));
			
			if($server_edit['success'] === false)
			{
				for($i=0; $i+1==count($server_edit['errors']); $i++)
				{
					$status	.= 	$server_edit['errors'][$i]."<br />";
				};
			}
			else
			{
				if($right == 'virtualserver_port')
				{
					$status2					=		TeamspeakServerEditMySQLChange($port, $instanz, $value);
				}
				else
				{
					$status2					=		true;
				};	
				
				if($status == '')
				{
					if($status2)
					{
						writeInLog($_SESSION['user']['benutzer'], "Edit a Teamspeakserver (".$value.") Instanz: ".$instanz." Sid: ".$serverId."", true);
						
						$status					=		'done';
					}
					else
					{
						$status					=		'Serverport changed! But cant change the Port in the Database!';
					};
				};
			};
		}
		else
		{
			$status 						= 		'No Teamspeak Connection';
		};
		
		return $status;
	}
	
	/*
		Teamspeak Serverpoke
	*/
	function TeamspeakServerServerPoke($message, $port, $serverid, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			if($serverid == '')
			{
				$tsServerID = $tsAdmin->serverIdGetByPort($port);
				$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			}
			else
			{
				$tsAdmin->selectServer($serverid, 'serverId', true);
			};
			
			$tsAdmin->setName(TS3_CHATNAME);
			
			$clientlist	=	$tsAdmin->clientList("-groups");
			
			$status		= "";
			foreach($clientlist['data'] AS $key=>$value)
			{
				if($value['client_type'] != 1)
				{
					$client_poke = $tsAdmin->clientPoke($value['clid'], $message);
					
					if($client_poke['success'] === false)
					{
						for($i = 0; $i+1 == count($client_poke['errors']); $i++)
						{
							$status .= $client_poke['errors'][$i]."<br />";
						};
					};
				};
			};
		
			$tsAdmin->logout();
			
			if($status != '')
			{
				return $status;
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Serverpoke Nachricht: ".$message." Instanz: ".$instanz." Port: ".$port, true);
				
				return "done";
			};
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Teamspeak Servermessage
	*/
	function TeamspeakServerMessage($mode, $id, $serverid, $message, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			if($serverid == '')
			{
				$tsServerID = $tsAdmin->serverIdGetByPort($id);
				$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			}
			else
			{
				$tsAdmin->selectServer($serverid, 'serverId', true);
			};
			
			$tsAdmin->setName(TS3_CHATNAME);
			
			$message			=	str_replace("\\", "\\\\", $message);
			
			if($mode == 1)
			{
				$send_message	=	$tsAdmin->sendMessage(1, $id, $message);
			};
			
			if($mode == 3)
			{
				$send_message	=	$tsAdmin->sendMessage(3, $id, $message);
			};
			
			if($send_message['success'] == false)
			{
				for($i = 0; $i+1 == count($send_message['errors']); $i++)
				{
					$status 	.= 	$send_message['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Servermessage Message: ".$message." Instanz: ".$instanz, true);
				
				$status 		.= 	"done";
			};
		
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Delete just one Teamspeak Channel from a spezific Teamspeakserver
	*/
	function deleteTeamspeakChannel($cid, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin 			= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($port, 'port', true);
			$tsAdmin->channelDelete($cid);
			
			return true;
		}
		else
		{
			return false;
		};
	};
	
	/*
		Create a Teamspeak Channel in a spezific Teamspeakserver
	*/
	function createTeamspeakChannel($tsAdmin, $name, $default, $port, $instanz, $justData = false, $ownData)
	{
		$channelInfo								=	array();
		
		if(!$justData)
		{
			$data 									= 	array();
			$data['channel_name'] 					= 	$name;
			$data['channel_flag_permanent'] 		= 	'1';
			if($default)
			{
				$data['channel_flag_default'] 		= 	'1';
			};
			
			$channelInfo							=	$tsAdmin->channelCreate($data);
		}
		else
		{
			$channelInfo							=	$tsAdmin->channelCreate($ownData);
		};
		
		writeInLog($_SESSION['user']['benutzer'], "Create a Teamspeak Channel Name: ".$name." Port: ".$port, true);
		
		return $channelInfo['cid'];
	};
	
	/*
		Reset a Teamspeakserver
	*/
	function resetServer($instanz, $port)
	{
		writeInLog($_SESSION['user']['benutzer'], "Reset a Teamspeakserver Instanz: ".$instanz." Port: ".$port, true);
		
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			return $tsAdmin->permReset()['data']['token'];
		}
		else
		{
			return false;
		};
	};
	
	/*
		Delete a file from a Teamspeakserver
	*/
	function deleteFileFromFilelist($path, $cid, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 		= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$delfiles[]			=	$path;
			$file_delete		=	$tsAdmin->ftDeleteFile($cid, "", $delfiles);
			
			if($file_delete['success'] !== false)
			{
				writeInLog($_SESSION['user']['benutzer'], "Delete File From Server: ChannelId: ".$cid." ; Path: ".$path." ;  Instanz: ".$instanz." Port: ".$port, true);
				
				return "done";
			}
			else
			{
				$returnText		=	'';
				for($i=0; $i+1 == count($file_delete['errors']); $i++)
				{
					$returnText	.=	$file_delete['errors'][$i]."<br />";
				};
				
				return $returnText;
			};
		}
		else
		{
			return "Teamspeakserver Connection failed";
		};
	};
	
	/*
		Create a new Token on a Teamspeakserver
	*/
	function addToken($type, $tokenid1, $tokenid2, $number, $description, $serverId, $instanz)
	{
		global $ts3_server;
		global $language;
		
		$token			=	array();
		
		$tsAdmin 		= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			if($type == 0 AND $tokenid2 != 0)
			{
				$tokenid2				=	0;
			};
			
			if($type == 1 AND $tokenid2 == 0)
			{
				$token['done'] 			= 	"false";
				$token['error']			= 	"No Channel picked";
			}
			else
			{
				for($i = 0; $i < $number; $i++)
				{
					$token_add			=	$tsAdmin->tokenAdd($type, intval($tokenid1), $tokenid2, $description);
					
					if($token_add['success']!==false)
					{
						$token['done']	=	"true";
						$token[$i]		=	array();
						$token[$i]['token']			=	$token_add['data']['token'];
						$token[$i]['description']	=	$description;
						if($tokenid1 == 0)
						{
							$token[$i]['type']		=	$language['sgroup'];
							$token[$i]['channel']	=	"-";
						}
						else
						{
							$token[$i]['type']		=	$language['cgroup'];
							$token[$i]['channel']	=	"Channel ID: ".$tokenid2;
						};
						
						writeInLog($_SESSION['user']['benutzer'], "Create Token (".$token[$i]['token'].") Instanz: ".$instanz." Sid: ".$serverId, true);
					}
					else
					{
						$token['done'] 	= "false";
						
						for($i=0; $i+1==count($token_add['errors']); $i++)
						{
							$token['error']		.=	$token_add['errors'][$i]."<br />";
						};
						if(!empty($status))
						{
							break;
						};
					};
				};
			};
			
			$tsAdmin->logout();
		}
		else
		{
			$token['done'] = "false";
		};
		
		return $token;
	};
	
	/*
		Download a file from a Teamspeakserver
	*/
	function downloadFileFromFilelist($path, $filename, $cid, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 		= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$ft					=	$tsAdmin->ftInitDownload($path.$filename, $cid);
			
			if($ft['success'] === true or empty($ft['data']['port']))
			{
				$con_ft			=	@fsockopen($ts3_server[$instanz]['ip'], $ft['data']['port'], $errnum, $errstr, 10);
				if($con_ft)
				{
					fputs($con_ft, $ft['data']['ftkey']);
					$data		=	'';
					while (!feof($con_ft)) 
					{
						$data	.= 	fgets($con_ft, 4096);
					};
					
					header('Content-Disposition: attachment; filename="'.$filename.'"');
					header('Content-Type: x-type/subtype');
					
					return $data;
				}
				else
				{
					writeInLog(2, $_SESSION['user']['benutzer'].": Could not init the Downloadconnection ; File: ".$filename." ;  Instanz: ".$instanz." Port: ".$port, true);
				};
			}
			else
			{
				writeInLog(2, $_SESSION['user']['benutzer'].": Could not init the Download ; File: ".$filename." ;  Instanz: ".$instanz." Port: ".$port, true);
			};
		}
		else
		{
			return "Teamspeakserver Connection failed";
		};
	};
	
	/*
		Delete a Token from a Teamspeakserver
	*/
	function deleteToken($token, $serverId, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			$token_delete	=	$tsAdmin->tokenDelete($token);
			
			if($token_delete['success'] !== false)
			{
				writeInLog($_SESSION['user']['benutzer'], "Delete Token (".$token.") Instanz: ".$instanz." Sid: ".$serverId, true);
				
				$status		=	"done";
			}
			else
			{
				for($i = 0; $i+1 == count($token_delete['errors']); $i++)
				{
					$status .= 	$token_delete['errors'][$i]."<br />";
				};
			};
		}
		else
		{
			$status			=	"No Serverconnection";
		};
		
		return $status;
	};
	
	/*
		Teamspeakserver Clientban Manuell
	*/
	function TeamspeakServerClientBanManuell($bantype, $input, $time, $reason, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			$tsAdmin->setName(TS3_CHATNAME);
			
			if($bantype == "uid")
			{
				$client_ban = $tsAdmin->banAddByUid($input, $time*60, $reason);
				if($client_ban['success'] === false)
				{
					for($i = 0; $i+1 == count($client_ban['errors']); $i++)
					{
						$status .= $client_ban['errors'][$i]."<br />";
					};
				}
				else
				{
					writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Ban Uid Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status = "done";
				};
			}
			else if($bantype == "ip")
			{
				$client_ban = $tsAdmin->banAddByIp($input, $time*60, $reason);
				if($client_ban['success'] === false)
				{
					for($i = 0; $i+1 == count($client_ban['errors']); $i++)
					{
						$status .= $client_ban['errors'][$i]."<br />";
					};
				}
				else
				{
					writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Ban IP Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status = "done";
				};
			}
			else
			{
				$client_ban = $tsAdmin->banAddByName($input, $time*60, $reason);
				if($client_ban['success'] === false)
				{
					for($i = 0; $i+1 == count($client_ban['errors']); $i++)
					{
						$status .= $client_ban['errors'][$i]."<br />";
					};
				}
				else
				{
					writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Ban Name Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status = "done";
				};
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Teamspeakserver Client Unban
	*/
	function TeamspeakServerClientUnban($banid, $port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_unban = $tsAdmin->banDelete($banid);
			if($client_unban['success'] === false)
			{
				for($i = 0; $i+1 == count($client_unban['errors']); $i++)
				{
					$status .= $client_unban['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Unban Banid: ".$banid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status = "done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Delete a Teamspeakclient in the Teamspeak Database
	*/
	function deleteDBClient($cldbid, $time, $port, $instanz)
	{
		global $ts3_server;
		
		$returnArray			=	array();
		$returnArray['ids']		=	array();
		$tsAdmin 				= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 					= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			if(!empty($cldbid))
			{
				$client_delete				=	$tsAdmin->clientDbDelete($cldbid);
				
				if($client_delete['success'] !== false)
				{
					writeInLog($_SESSION['user']['benutzer'], "Delete DB Client ID: ".$cldbid." Channel Instanz: ".$instanz." Port: ".$port, true);
					
					return "done";
				}
				else
				{
					$returnText				=	'';
					for($i=0; $i+1 == count($client_delete['errors']); $i++)
					{
						$returnText			.=	$client_delete['errors'][$i]."<br />";
					};
					
					return $returnText;
				};
			}
			else
			{
				$clientList					=	$tsAdmin->clientDbList(0, GET_DB_CLIENTS);
				
				foreach($clientList['data'] AS $client)
				{
					if($client['client_unique_identifier'] != 'ServerQuery' && $client['client_lastconnected'] <= $time)
					{
						$client_delete		=	$tsAdmin->clientDbDelete($client['cldbid']);
						
						if($client_delete['success'] !== false)
						{
							$returnArray['ids'][]		=	$client['cldbid'];
							writeInLog($_SESSION['user']['benutzer'], "Delete DB Client ID: ".$client['cldbid']." Channel Instanz: ".$instanz." Port: ".$port, true);
						}
						else
						{
							$returnArray['success']		=	0;
							$returnArray['error']		=	'';
							for($i=0; $i+1 == count($client_delete['errors']); $i++)
							{
								$returnArray['error']	.=	$client_delete['errors'][$i]."<br />";
							};
							
							return json_encode($returnArray);
						};
					};
				};
				
				$returnArray['success']					=	1;
				return json_encode($returnArray);
			};
		}
		else
		{
			if(!empty($cldbid))
			{
				return "Teamspeakserver Connection failed";
			}
			else
			{
				$returnArray['success']		=	0;
				$returnArray['error']		=	"Teamspeakserver Connection failed";
				return json_encode($returnArray);
			};
		};
	};
	
	/*
		Delete Teamspeak Icons
	*/
	function deleteIcon($id, $instanz, $serverId, $port)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			$tsAdmin->selectServer($serverId, 'serverId', true);
			$tsAdmin->ftDeleteFile(0, '', array("/icon_".$id));
			
			writeInLog($_SESSION['user']['benutzer'], "Delete a Teamspeakicon (".$id.") Instanz: ".$instanz." Sid: ".$serverId."", true);
			
			return unlink('../../images/ts_icons/'.$ts3_server[$instanz]['ip'].'-'.$port.'/'."icon_".$id);
		}
		else
		{
			return false;
		};
	};
	
	/*
		Get the Teamspeak Icons and Download them to your Webspace
	*/
	function getTeamspeakIcons($tsAdmin, $port, $ip, $queryport, $username, $password)
	{
		$ft				=	$tsAdmin->ftGetFileList(0, '', '/icons');
		$handler		=	null;
		
		if(is_dir('../../images/ts_icons/'.$ip.'-'.$port.'/'))
		{
			$handler	=	@opendir('../../images/ts_icons/'.$ip.'-'.$port.'/');
		}
		else
		{
			if(@mkdir('../../images/ts_icons/'.$ip.'-'.$port.'/', 0777))
			{
				$handler		=	@opendir('../../images/ts_icons/'.$ip.'-'.$port.'/');
			}
			else
			{
				writeInLog(2, "getTeamspeakIcons: Could not Create Folder ".$ip."-".$port);
			};
		};
		
		if($handler != null)
		{
			while($datei = readdir($handler))
			{
				$icon_arr[]	=	$datei;
			};
		};
		
		$noIcon 	=	0;
		if(!empty($ft['data']))
		{
			foreach($ft['data'] AS $key2=>$value2)
			{
				$foundIcons[]	=	$value2['name'];
			};
		};
		
		if(!empty($icon_arr))
		{
			foreach($icon_arr AS $key=>$value)
			{
				if(!empty($ft['data']))
				{
					if($value!="." AND $value!=".." AND in_array($value, $foundIcons))
					{
						$noIcon = 1;
						break;
					};
					if($noIcon == 0)
					{
						@unlink('../../images/ts_icons/'.$ip.'-'.$port.'/'.$value);
					};
				}
				elseif(strpos($ft['errors'][0], 'ErrorID: 2568 | Message: insufficient client permissions failed_permid')===false)
				{
					if($value!="." AND $value!="..")
					{
						@unlink('../../images/ts_icons/'.$ip.'-'.$port.'/'.$value);
					};
				};
			};
		};
		
		if(!empty($ft['data']))
		{
			foreach($ft['data'] AS $key=>$value)
			{
				if(substr($value['name'], 0, 5) == 'icon_' && !empty($icon_arr))
				{
					if(!in_array($value['name'], $icon_arr))
					{
						$ft2				=	$tsAdmin->ftInitDownload("/".$value['name'], 0);
						
						if($ft2['success'] !== false AND !empty($ft2['data']['port']))
						{
							$con_ft			=	@fsockopen($ip, $ft2['data']['port'], $errnum, $errstr, 10);
							if($con_ft)
							{
								fputs($con_ft, $ft2['data']['ftkey']);
								$data		=	'';
								while (!feof($con_ft)) 
								{
									$data	.= 	fgets($con_ft, 4096);
								}
								$handler2	=	@fopen('../../images/ts_icons/'.$ip.'-'.$port.'/'.$value['name'], "w+");
								if($handler2 !== false)
								{
									fwrite($handler2, $data);
									fclose($handler2);
								};
							};
						};
					};
				};
			};
		};
	};
	
	/*
		Teamspeakserver Clientactions
	*/
	function TeamspeakServerClientAction($action, $message, $clid, $cid, $port, $instanz, $kickmode, $time)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 				= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			$tsAdmin->setName(TS3_CHATNAME);
			
			switch($action)
			{
				case "clientMsg":
					$actionStatus 		= 	$tsAdmin->sendMessage(1, $clid, $message);
					break;
				case "clientPoke":
					$actionStatus 		= 	$tsAdmin->clientPoke($clid, $message);
					break;
				case "clientMove":
					$actionStatus 		= 	$tsAdmin->clientMove($clid, $cid);
					break;
				case "clientKick":
					$actionStatus		=	$tsAdmin->clientKick($clid, $kickmode, $message);
					break;
				case "clientBan":
					$actionStatus		=	$tsAdmin->banClient($clid, $time*60, $message);
					break;
			};
			
			if($actionStatus['success'] === false)
			{
				for($i = 0; $i+1 == count($actionStatus['errors']); $i++)
				{
					$status 			.= 	$actionStatus['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Teamspeak Client Message Message: ".$message." Instanz: ".$instanz." Port: ".$port, true);
				
				$status 				= 	"done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Massactions: Get Teamspeakclients
	*/
	function getUsersMassActions($group, $channel, $who, $index = false, $action, $port, $instanz)
	{
		global $ts3_server;
		
		$returnArray		=	array();
		$returnArray[0]		=	$action;
		$tsAdmin 			= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$i						=	1;
			$teamspeak_clients		=	$tsAdmin->getElement('data', $tsAdmin->clientList("-uid -away -voice -times -groups -info -icon -country"));
			
			switch($who)
			{
				case "all":
					foreach($teamspeak_clients AS $client)
					{
						if((($channel == '' || $channel == 'none') || $client['client_channel_group_inherited_channel_id'] == $channel) && $client['client_type'] == 0)
						{
							$returnArray[$i++]				=	($index) ? $client['clid'] : xssSafe($client['client_nickname']);
						};
					};
					break;
				case "cgroup":
					foreach($teamspeak_clients AS $client)
					{
						if((($channel == '' || $channel == 'none') || $client['client_channel_group_inherited_channel_id'] == $channel) && $client['client_type'] == 0
							&& $client['client_channel_group_id'] == $group)
						{
							$returnArray[$i++]				=	($index) ? $client['clid'] : xssSafe($client['client_nickname']);
						};
					};
					break;
				case "sgroup":
					foreach($teamspeak_clients AS $client)
					{
						if((($channel == '' || $channel == 'none') || $client['client_channel_group_inherited_channel_id'] == $channel) && $client['client_type'] == 0)
						{
							$servergroups 			= 	explode(",", $client['client_servergroups']);
							for ($a = 0; $a < count($servergroups); $a++)
							{
								if($servergroups[$a] == $group)
								{
									$returnArray[$i++]		=	($index) ? $client['clid'] : xssSafe($client['client_nickname']);
								};
							};
						};
					};
					break;
			};
			
			return $returnArray;
		}
		else
		{
			return "error";
		};
	};
	
	/*
		Get Channeltree
	*/
	function getChannelTree($channels, $subChannels = false)
	{
		$channelTree	=	"";
		
		if(!empty($channels))
		{
			foreach($channels AS $key=>$value)
			{
				if (($value['pid'] == 0 && !$subChannels) || ($value['pid'] != 0 && $subChannels))
				{
					if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid'] == 0)
					{
						$getspacer		=	explode($treffer[0], $value['channel_name']);
						$checkspacer	=	$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
						if($treffer[1] == "*" or strlen($getspacer[1]) == 3 AND $checkspacer==$getspacer[1])
						{
							$spacer='';
							for($i=0; $i<=50; $i++)
							{
								if(strlen($spacer)<50)
								{
									$spacer	.=	$getspacer[1];
								}
								else
								{
									break;
								};
							};
							$channelTree 	.= 	"<option value='".$value['cid']."'>".xssSafe($spacer)."</option>";
						}
						else
						{
							$spacer			=	explode($treffer[0], $value['channel_name']);
							$channelTree 	.= 	"<option value='".$value['cid']."'>".xssSafe($spacer[1])."</option>";
						};
					}
					else
					{
						$channelTree 		.= 	"<option value='".$value['cid']."'>".xssSafe($value['channel_name'])."</option>";
					};
				};
			};
		};
		
		return $channelTree;
	};
	
	/*
		Get Grouptree
	*/
	function getGroupTree($groups, $cgroup = false)
	{
		$group			= 	"";
		$groupType		=	($cgroup) ? "cgroup" : "sgroup";
		
		foreach($groups AS $key => $value)
		{
			$valueId	=	($cgroup) ? $value['cgid'] : $value['sgid'];
			if ($value['type'] != '2' AND $value['type'] != '0')
			{
				$group .= "<option group='" . $groupType . "' value='" . $valueId . "'>" . xssSafe($value['name']) . "</option>";
			};
		};
		
		return $group;
	};
	
	/*
		Checking Teamspeak instanz (Just if IP and Port is avalible)
	*/
	function checkTS3Connection($ip, $queryport, $user, $pw)
	{
		$tsAdmin 		= 	new ts3admin($ip, $queryport);
		$ergebnis 		= 	$tsAdmin->connect();
		
		return $ergebnis['success'] ? true : false;
	};
	
	/*
		Get the Teamspeak Querylog from a spezific instanz and port
	*/
	function getPortQuery($port, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 	= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$getlog			=	$tsAdmin->execOwnCommand('2', 'logview lines=100 reverse=1 instance=0');
			
			if(!empty($getlog['data']))
			{
				foreach($getlog['data'] AS $key=>$value)
				{
					$log[]	=	explode('|', $value['l'], 5);
				};
			};
			
			$dataString					=		'';
			foreach($log AS $key => $value)
			{
				$dataString				.=		'<tr>';
				$dataString				.=			'<td style="text-align:center;">' . $value['0'] . '</td>';
				$dataString				.=			'<td style="text-align:center;" class="hidden-sm-down">' . $value['1'] . '</td>';
				$dataString				.=			'<td style="text-align:center;" class="hidden-lg-down">' . $value['2'] . '</td>';
				$dataString				.=			'<td style="text-align:center;" class="hidden-lg-down">' . $value['3'] . '</td>';
				$dataString				.=			'<td style="text-align:center;">' . xssSafe($value['4']) . '</td>';
				$dataString				.=		'</tr>';
			};
			
			$tsAdmin->logout();
			
			return $dataString;
		}
		else
		{
			return "error";
		};
	};
	
	/*
		Stop a Teamspeakserver
	*/
	function stopTeamspeakServer($serverid, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$server_stop	=	$tsAdmin->serverStop($serverid);
			if($server_stop['success'] === false)
			{
				for($i = 0; $i+1 == count($server_stop['errors']); $i++)
				{
					$status .= $server_stop['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Stop a Teamspeakserver Instanz: ".$instanz." Sid: ".$serverid, true);
				
				$status = "done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Start a Teamspeakserver
	*/
	function startTeamspeakServer($serverid, $instanz)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$server_stop	=	$tsAdmin->serverStart($serverid);
			if($server_stop['success']===false)
			{
				for($i = 0; $i+1 == count($server_stop['errors']); $i++)
				{
					$status .= $server_stop['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Start a Teamspeakserver Instanz: ".$instanz." Sid: ".$serverid, true);
				
				$status = "done";
			};
			
			$tsAdmin->logout();
			
			return $status;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Get all Informations above a Teamspeakserver
	*/
	function TeamspeakInstanzServerlistInformations()
	{
		global $ts3_server;
		
		$informations	=	array();
		
		foreach($ts3_server AS $instanz => $values)
		{
			$tsAdmin 								= 	new ts3admin($values['ip'], $values['queryport']);
			
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$tsAdmin->login($values['user'], $values['pw']);
				
				$informations[$instanz]		=	array();
				
				$servers 					= 	$tsAdmin->serverList();
				
				foreach($servers['data'] as $server)
				{
					$informations[$instanz][$server['virtualserver_id']]['uptime']			=	(isset($server['virtualserver_uptime'])) ? true : false;
					$informations[$instanz][$server['virtualserver_id']]['online']			=	($server['virtualserver_status'] == 'online') ? true : false;
					$informations[$instanz][$server['virtualserver_id']]['clients']			=	(isSet($server['virtualserver_clientsonline'])) ? $server['virtualserver_clientsonline'] : '';
					$informations[$instanz][$server['virtualserver_id']]['maxclients']		=	(isSet($server['virtualserver_maxclients'])) ? $server['virtualserver_maxclients'] : '';
				};
				
				$tsAdmin->logout();
			};
		};
		
		return $informations;
	};
	
	/*
		Teamspeak Server / Instanz Messages / Pokes
	*/
	function TeamspeakServerInstanzMessagePoke($message, $mode, $instanz)
	{
		global $ts3_server;
		
		if($instanz == 'all')
		{
			foreach($ts3_server AS $instanz => $values)
			{
				$tsAdmin 				= 	new ts3admin($values['ip'], $values['queryport']);
				
				if($tsAdmin->getElement('success', $tsAdmin->connect()))
				{
					$tsAdmin->login($values['user'], $values['pw']);
					
					if($mode == 'true') // Message
					{
						$message		=	str_replace("\\", "\\\\", $message);
						$send_message	=	$tsAdmin->gm($message);
						$tsAdmin->logout();
						
						if($send_message['success'] == false)
						{
							for($i=0; $i+1 == count($send_message['errors']); $i++)
							{
								$status .= $send_message['errors'][$i]."<br />";
							};
						}
						else
						{
							writeInLog($_SESSION['user']['benutzer'], "Teamspeak Server Instanz Messages ".$instanz, true);
							
							$status 	= "done";
						};
						
						return $status;
					}
					else
					{
						$servers 	= 	$tsAdmin->serverList();
						$i			=	0;
						
						foreach($servers['data'] as $server)
						{
							$tsServerID = $tsAdmin->serverIdGetByPort($server['virtualserver_port']);
							$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
							
							$tsAdmin->setName(TS3_CHATNAME);
							
							$clientlist	=	$tsAdmin->clientList("-groups");
							
							if($clientlist['success']!=false)
							{
								$i++;
								foreach($clientlist['data'] AS $key=>$value)
								{
									if($value['client_type'] != 1) // Keine Queryclienten
									{
										$client_poke = $tsAdmin->clientPoke($value['clid'], $message);
									};
								};
							};
						};
						$tsAdmin->logout();
						
						if(count($servers['data']) == $i)
						{
							writeInLog($_SESSION['user']['benutzer'], "Teamspeak Server Instanz Poke ".$instanz, true);
							
							return "done";
						}
						else
						{
							return "Not all Clients got the Poke :/";
						};
					};
				};
			};
		}
		else
		{
			$tsAdmin 				= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
			
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
				
				if($mode == 'true') // Message
				{
					$message		=	str_replace("\\", "\\\\", $message);
					$send_message	=	$tsAdmin->gm($message);
					$tsAdmin->logout();
					
					if($send_message['success'] == false)
					{
						for($i=0; $i+1 == count($send_message['errors']); $i++)
						{
							$status .= $send_message['errors'][$i]."<br />";
						};
					}
					else
					{
						writeInLog($_SESSION['user']['benutzer'], "Teamspeak Server Instanz Messages ".$instanz, true);
						
						$status 	= "done";
					};
					
					return $status;
				}
				else
				{
					$servers 	= 	$tsAdmin->serverList();
					$i			=	0;
					
					foreach($servers['data'] as $server)
					{
						$tsServerID = $tsAdmin->serverIdGetByPort($server['virtualserver_port']);
						$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
						
						$tsAdmin->setName(TS3_CHATNAME);
						
						$clientlist	=	$tsAdmin->clientList("-groups");
						
						if($clientlist['success']!=false)
						{
							$i++;
							foreach($clientlist['data'] AS $key=>$value)
							{
								if($value['client_type'] != 1) // Keine Queryclienten
								{
									$client_poke = $tsAdmin->clientPoke($value['clid'], $message);
								};
							};
						};
					};
					$tsAdmin->logout();
					
					if(count($servers['data']) == $i)
					{
						writeInLog($_SESSION['user']['benutzer'], "Teamspeak Server Instanz Poke ".$instanz, true);
						
						return "done";
					}
					else
					{
						return "Not all Clients got the Poke :/";
					};
				};
			}
			else
			{
				return "No Connection to the Instanz";
			};
		};
	};
	
	/*
		Query Console Command
	*/
	function ExecServerQueryCommand($instanz, $command, $selectedserver = -1)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			if($selectedserver != -1)
			{
				$tsAdmin->selectServer($selectedserver, 'serverId', true);
			};
			
			writeInLog($_SESSION['user']['benutzer'], "Exec a Querycommand: ".$command." to: ".$instanz."", true);
			
			$returnData 	=	$tsAdmin->execOwnCommand(3, $command);
			if($returnData['success'] === false)
			{
				$returnDataErrors		=	"";
				for($i=0; $i+1==count($returnData['errors']); $i++)
				{
					$returnDataErrors	.= 	$returnData['errors'][$i]."<br />";
				};
				return $returnDataErrors;
			}
			else
			{
				return $returnData['data']."<br />";
			};
		};
	};
	
	/*
		Edit a Teamspeakinstance and return if the connection was successfull
	*/
	function editInstance($instanz, $what, $content)
	{
		global $ts3_server;
		
		if($instanz != '' && $what != '' && $content != '')
		{
			$new_file				=	array();
			$instanceFile			=	"../../config/instance.php";
			$file					=	file($instanceFile);
			$search					=	'$ts3_server[' . $instanz . '][\'' . $what . '\']';
			
			for ($i = 0; $i < count($file); $i++)
			{
				if(strpos($file[$i], $search) && $what != 'queryport')
				{
					$new_file[$i]		=	"\t" . $search . "\t\t= '" . $content . "';\n";
				}
				elseif (strpos($file[$i], $search))
				{
					$new_file[$i]		=	"\t" . $search . "\t= " . $content . ";\n";
				}
				else
				{
					$new_file[$i]		=	$file[$i];
				};
			};
			
			file_put_contents($instanceFile, "");
			file_put_contents($instanceFile, $new_file);
			
			switch($what)
			{
				case "ip":
					$ts3_server[$instanz]['ip']			=	$content;
					break;
				case "queryport":
					$ts3_server[$instanz]['queryport']	=	$content;
					break;
				case "user":
					$ts3_server[$instanz]['user']		=	$content;
					break;
				case "pw":
					$ts3_server[$instanz]['pw']			=	$content;
					break;
			};
			
			writeInLog($_SESSION['user']['benutzer'], "Has Edit the Instanz \"".$instanz."\"", true);
			
			$tsAdmin 					= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$check_login			=	$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
				
				$tsAdmin->logout();
				
				if($check_login['success']!==false)
				{
					return 'done';
				}
				else
				{
					for($i=0; $i+1==count($check_login['errors']); $i++)
					{
						$returnValue		.=	$check_login['errors'][$i]."<br />";
					};
					return $returnValue;
				};
			}
			else
			{
				return "Connection failed!";
			};
		}
		else
		{
			return 'Invalid Parameter';
		};
	};
	
	/*
		Get all Serverports from a spezific instanz
	*/
	function getTeamspeakPorts($instanz)
	{
		global $ts3_server;
		
		$tsAdmin 			= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		$server_ports 		= 	array();
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$servers 		= 	$tsAdmin->serverList();
			
			foreach($servers['data'] as $i=>$server)
			{
				$server_ports[$i]	=	$server['virtualserver_port'];
			};
			
			$tsAdmin->logout();
		};
		
		return $server_ports;
	};
	
	/*
		Get Teamspeakslots and Teamspeak active slots from an Instanz
	*/
	function getTeamspeakslots($instanz)
	{
		global $ts3_server;
		
		$returnArray	=	array();
		$tsAdmin 		= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$returnArray	=	getTeamspeakslotsArray($tsAdmin->getElement('data', $tsAdmin->serverList()), $instanz, false);
		};
		
		return $returnArray;
	};
	
	function getTeamspeakslotsArray($serverList, $instanz, $forceInfo = false)
	{
		global $ts3_server;
		global $mysql_keys;
		
		$user_right													=	getUserRights('pk', $_SESSION['user']['id']);
		
		if(count($serverList) > 3 && !$forceInfo)
		{
			$instanzName											=	$ts3_server[$instanz]['alias'];
			
			if($ts3_server[$instanz]['alias'] == "")
			{
				$instanzName										=	$ts3_server[$instanz]['ip'];
			};
			
			$returnArray											=	array();
			$returnArray[0]											=	array();
			$returnArray[0]['virtualserver_clientsonline']			=	0;
			$returnArray[0]['virtualserver_maxclients']				=	0;
			$returnArray[0]['instanz_name']							=	$instanzName;
			
			foreach($serverList AS $server)
			{
				if(isSet($server['virtualserver_clientsonline']) && isSet($server['virtualserver_queryclientsonline']) && isSet($server['virtualserver_maxclients']))
				{
					if(isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_view') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'])
					{
						$returnArray[0]['virtualserver_clientsonline']	=	$returnArray[0]['virtualserver_clientsonline'] + ($server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline']);
						$returnArray[0]['virtualserver_maxclients']		=	$returnArray[0]['virtualserver_maxclients'] + $server['virtualserver_maxclients'];
					};
				};
			};
			
			return $returnArray;
		}
		else
		{
			$newServerList											=	array();
			foreach($serverList AS $server)
			{
				if(isPortPermission($user_right, $instanz, $server['virtualserver_port'], 'right_web_server_view') || $user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server'])
				{
					$server['virtualserver_clientsonline']			=	$server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline'];
					$newServerList[]								=	$server;
				};
			};
			return $newServerList;
		};
	};
	
	/*
		Returns the Serverid from a Serverport
	*/
	function getServerIdByPort($instanz, $port)
	{
		global $ts3_server;
		
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			return $tsAdmin->serverIdGetByPort($port)['data']['server_id'];
		};
		
		return -1;
	};
	
	/*
		Get the whole Teamspeaktree of a spezific Teamspeakserver
	*/
	function getTeamspeakBaum($instanz, $port)
	{
		global $language;
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin 	= 	new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Array mit allen Teamspeak Variabeln
			$alldata				=	array();
			$alldata['server']		=	$tsAdmin->getElement('data', $tsAdmin->serverInfo());
			$alldata['channel']		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits -icon"));
			$alldata['clients']		=	$tsAdmin->getElement('data', $tsAdmin->clientList("-uid -away -voice -times -groups -info -icon -country"));
			$alldata['sgroups']		=	$tsAdmin->getElement('data', $tsAdmin->serverGroupList());
			$alldata['cgroups']		=	$tsAdmin->getElement('data', $tsAdmin->channelGroupList());
			
			 // Standertwerte
			$tree					=	array();
			$tree['channels']		=	array();
			$tree['subChannels']	=	array();
			$tree['clients']		=	array();
			$counter				=	0;
			$counter_subchannels	=	0;
			$counter_clients		=	0;
			
			// Servericon wird gespeichert und als unsigned Int gespeichert
			if($alldata['server']['virtualserver_icon_id']<0)
			{
				$alldata['server']['virtualserver_icon_id']		=		sprintf('%u', $alldata['server']['virtualserver_icon_id'] & 0xffffffff);
			};
			
			// Icon abfragen
			$icon_src					= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$alldata['server']['virtualserver_icon_id'], $alldata['server']['virtualserver_port']);
			
			// Serverstatus zurückgeben
			$tree['serverstatus']		=	($alldata['server']['virtualserver_status'] == "online") ? "<font style=\"color:green;\">".$language['online']."</font>" : "<font style=\"color:#900;\">".$language['offline']."</font>";
			$tree['serverstatus2']		=	$alldata['server']['virtualserver_status'];
			
			// Serverzeit zurückgeben
			$Tage						= 	$alldata['server']['virtualserver_uptime'] / 86400;
			$Stunden					=	($alldata['server']['virtualserver_uptime'] - (floor($Tage) * 86400)) / 3600;
			$Minuten					=	($alldata['server']['virtualserver_uptime'] - (floor($Tage) * 86400) - (floor($Stunden) * 3600)) / 60;
			$tree['servertimeup']		=	floor($Tage) . " " . $language['days'] . " " . floor($Stunden) . " " . $language['hours'] . " " . floor($Minuten) . " " . $language['minutes'];
			
			// Clienten zurückgeben
			$tree['servermaxclients']	=	$alldata['server']['virtualserver_maxclients'];
			$tree['serverclients']		=	$alldata['server']['virtualserver_clientsonline'];
			$tree['serverqclients']		=	$alldata['server']['virtualserver_queryclientsonline'];
			
			// Serverchannels zurückgeben
			$tree['serverchannels']		=	$alldata['server']['virtualserver_channelsonline'];
			
			// Serverpassword zurückgeben
			$tree['serverpassword']		=	($alldata['server']['virtualserver_flag_password']) ? $language['yes'] : $language['no'];
			
			// Headerinformationen speichern
			$tree['globalHeader']		=	xssSafe($alldata['server']['virtualserver_name']);
			$tree['header']				=	'<i class="fa fa-server"></i>&nbsp;&nbsp;' . xssSafe($alldata['server']['virtualserver_name']);
			$tree['headerimg']			=	'<img width="18px" height="18px" src="' . $icon_src . '" />';
			$tree['headerimg_exist'] 	=	($icon_src != "") ? "1" : "0";
			
			// Channel abfragen
			if(!empty($alldata['channel']))
			{
				foreach($alldata['channel'] AS $key=>$value)
				{
					if ($value['pid'] == 0) // Hauptchannels
					{
						$tree['channels'][$counter]['cid']								=	$value['cid'];
						$tree['channels'][$counter]['pid']								=	$value['pid'];
						
						if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0 AND $value['channel_flag_permanent']==1)
						{
							$getspacer													=	explode($treffer[0], $value['channel_name']);
							$checkspacer												=	$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
							
							// Channel mit unendlich langen Spacer
							if($treffer[1]=="*" or strlen($getspacer[1]) == 3 AND $checkspacer == $getspacer[1])
							{
								$spacer	=	'';
								for($i=0; $i<=500; $i++)
								{
									if(strlen($spacer) < 500)
									{
										$spacer	.=	$getspacer[1];
									}
									else
									{
										break;
									};
								};
								$tree['channels'][$counter]['channelname']				=	$spacer;
								$tree['channels'][$counter]['align']					=	'left';
								$tree['channels'][$counter]['spacer']					=	'1';
							}
							elseif($treffer[1]=="c") // Channel mit center Spacer
							{
								$spacer								=	explode($treffer[0], xssSafe($value['channel_name']));
								$tree['channels'][$counter]['channelname']				=	$spacer[1];
								$tree['channels'][$counter]['align']					=	'center';
								$tree['channels'][$counter]['spacer']					=	'1';
							}
							elseif($treffer[1]=="r") // Channel mit rechtem Spacer
							{
								$spacer								=	explode($treffer[0], xssSafe($value['channel_name']));
								$tree['channels'][$counter]['channelname']				=	$spacer[1];
								$tree['channels'][$counter]['align']					=	'right';
								$tree['channels'][$counter]['spacer']					=	'1';
							}
							else // Ganz normaler Channel [Aber mit Spacer]
							{
								$spacer								=	explode($treffer[0], xssSafe($value['channel_name']));
								$tree['channels'][$counter]['channelname']				=	$spacer[1];
								$tree['channels'][$counter]['align']					=	'left';
								$tree['channels'][$counter]['spacer']					=	'1';
							};
						}
						else // Ganz normaler Channel
						{
							$tree['channels'][$counter]['spacer']						=	'0';
							
							$chanmaxclient							=	($value['channel_maxclients']=="-1" ? $alldata['server']['virtualserver_maxclients'] : $value['channel_maxclients']);
							
							$tree['channels'][$counter]['channel_flag_password']		=	$value['channel_flag_password'];
							$tree['channels'][$counter]['channel_flag_default']			=	$value['channel_flag_default'];
							$tree['channels'][$counter]['channel_codec']				=	$value['channel_codec'];
							$tree['channels'][$counter]['channel_needed_talk_power']	=	$value['channel_needed_talk_power'];
							
							if($value['channel_icon_id'] != 0)
							{
								
								if($value['channel_icon_id'] < 0)
								{
									$value['channel_icon_id']							=	sprintf('%u', $value['channel_icon_id'] & 0xffffffff);
								};
								
								$icon_src 												= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$value['channel_icon_id'], $alldata['server']['virtualserver_port']);
								if($icon_src != "")
								{
									$tree['channels'][$counter]['channel_icon_id']		=	"<img class=\"right-img\" style=\"height:16px;width:16px;\" src='$icon_src' alt=\"\" />";
								}
								else
								{
									$tree['channels'][$counter]['channel_icon_id']		=	'';
								};
							}
							else
							{
								$tree['channels'][$counter]['channel_icon_id']			=	'';
							};
							
							if($chanmaxclient <= $value['total_clients'])
							{
								$tree['channels'][$counter]['channelname']				=	xssSafe($value['channel_name']);
								$tree['channels'][$counter]['img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/channel_red.png'></img>";
							}
							elseif($value['channel_flag_password'] == 1)
							{
								$tree['channels'][$counter]['channelname']				=	xssSafe($value['channel_name']);
								$tree['channels'][$counter]['img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/pwchannel.png'></img>";
							}
							else
							{
								$tree['channels'][$counter]['channelname']				=	xssSafe($value['channel_name']);
								$tree['channels'][$counter]['img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/channel.png'></img>";
							};
						};
						
						$counter++;
					}
					else // Subchannels
					{
						$tree['subChannels'][$counter_subchannels]['sub_pid']						=	$value['pid'];
						$tree['subChannels'][$counter_subchannels]['sub_cid']						=	$value['cid'];
						$chanmaxclient																=	($value['channel_maxclients']=="-1" ? $alldata['server']['virtualserver_maxclients'] : $value['channel_maxclients']);
						
						$tree['subChannels'][$counter_subchannels]['sub_channel_flag_password']		=	$value['channel_flag_password'];
						$tree['subChannels'][$counter_subchannels]['sub_channel_flag_default']		=	$value['channel_flag_default'];
						$tree['subChannels'][$counter_subchannels]['sub_channel_codec']				=	$value['channel_codec'];
						$tree['subChannels'][$counter_subchannels]['sub_channel_needed_talk_power']	=	$value['channel_needed_talk_power'];
						
						if($value['channel_icon_id'] != 0)
						{
							
							if($value['channel_icon_id'] < 0)
							{
								$value['channel_icon_id']											=	sprintf('%u', $value['channel_icon_id'] & 0xffffffff);
							};
							
							$icon_src 																= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$value['channel_icon_id'], $alldata['server']['virtualserver_port']);
							if($icon_src != "")
							{
								$tree['subChannels'][$counter_subchannels]['sub_channel_icon_id']	=	"<img class=\"right-img\" style=\"height:16px;width:16px;\" src='$icon_src' alt=\"\" />";
							}
							else
							{
								$tree['subChannels'][$counter_subchannels]['sub_channel_icon_id']	=	'';
							};
						}
						else
						{
							$tree['subChannels'][$counter_subchannels]['sub_channel_icon_id']		=	'';
						};
						
						if($chanmaxclient <= $value['total_clients'])
						{
							$tree['subChannels'][$counter_subchannels]['channelname']				=	xssSafe($value['channel_name']);
							$tree['subChannels'][$counter_subchannels]['sub_img_before']			=	"<img width='16px' height='16px' src='images/ts_viewer/channel_red.png'></img>";
						}
						elseif($value['channel_flag_password'] == 1)
						{
							$tree['subChannels'][$counter_subchannels]['channelname']				=	xssSafe($value['channel_name']);
							$tree['subChannels'][$counter_subchannels]['sub_img_before']			=	"<img width='16px' height='16px' src='images/ts_viewer/pwchannel.png'></img>";
						}
						else
						{
							$tree['subChannels'][$counter_subchannels]['channelname']				=	xssSafe($value['channel_name']);
							$tree['subChannels'][$counter_subchannels]['sub_img_before']			=	"<img width='16px' height='16px' src='images/ts_viewer/channel.png'></img>";
						};
						
						$counter_subchannels++;
					};
					if($value['total_clients'] >= 1 && !empty($alldata['clients'])) // Wenn Clienten auf dem Server sind
					{
						foreach($alldata['clients'] AS $u_key=>$u_value)
						{
							$blocked_sgroups														=	explode(",", TEAMSPEAKTREE_HIDE_SGROUPS);
							$client_sgroups															=	explode(",", $u_value['client_servergroups']);
							$isBlocked																=	false;
							
							foreach($blocked_sgroups AS $sgroup)
							{
								foreach($client_sgroups AS $csgroup)
								{
									if(trim($sgroup) == trim($csgroup))
									{
										$isBlocked													=	true;
										break;
									};
								};
							};
							
							if($value['cid'] == $u_value['cid'] && !$isBlocked) // Ob Client auch im Channel ist
							{
								// Kein Query Client
								if($u_value['client_type'] != "1")
								{
									$tree['clients'][$counter_clients]['nick_away_message']			=	'';
									
									// Teamspeak Status abfragen
									if($u_value['client_away'] == "1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'away';
										if(!empty($u_value['client_away_message']))	
										{
											$tree['clients'][$counter_clients]['nick_away_message'] =	'(' . $u_value['client_away_message'] . ')';
										};
									}
									elseif($u_value['client_output_hardware'] == "0")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'hwhead';
									}
									elseif($u_value['client_input_hardware'] == "0")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'hwmic';
									}
									elseif($u_value['client_output_muted'] == "1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'head';
									}
									elseif($u_value['client_input_muted']=="1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'mic';
									}
									elseif($u_value['client_flag_talking'] == "0" AND $u_value['client_is_channel_commander'] == "1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'player_command';
									}
									elseif($u_value['client_flag_talking'] == "1" AND $u_value['client_is_channel_commander'] == "1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'player_command_on';
									}
									elseif($u_value['client_flag_talking']=="1")
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'player_on';
									}
									else
									{
										$tree['clients'][$counter_clients]['nick_status']			=	'player';
									};
									
									// Teamspeak Landimage anzeigen
									if(!empty($u_value['client_country']))
									{
										$tree['clients'][$counter_clients]['nick_country']			=	strtolower($u_value['client_country']);
									}
									else
									{
										$tree['clients'][$counter_clients]['nick_country']			=	'';
									};
									
									// Servergruppen
									$anzahlServergruppen								=	0;
									$getsgroups											=	explode(',', trim($u_value['client_servergroups']));
									if(!empty($alldata['sgroups']))
									{
										foreach($alldata['sgroups'] AS $key=>$sg_value)
										{
											if(in_array($sg_value['sgid'], $getsgroups))
											{
												$iconid									=	$sg_value['iconid'];
												if($iconid < 0) // Standertservergruppe
												{
													$iconid								=	sprintf('%u', $iconid & 0xffffffff);
												};
												if($iconid != 0) // Hochgeladene Servergruppe
												{
													$icon_src 							= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$iconid, $alldata['server']['virtualserver_port']);
													if($icon_src != "")
													{
														$tree['clients'][$counter_clients]['sgroup'][$anzahlServergruppen] 	= 	$icon_src;
														$anzahlServergruppen++;
													};
												};
											};
										};
									};
									
									$anzahlChannelgruppen								=	0;
									// Channelgruppen
									if(!empty($alldata['cgroups']))
									{
										foreach($alldata['cgroups'] AS $key=>$cg_value)
										{
											if($cg_value['cgid'] == $u_value['client_channel_group_id'])
											{
												$iconid									=	$cg_value['iconid'];
												
												if($iconid < 0) // Standertservergruppe
												{
													$iconid								=	sprintf('%u', $iconid & 0xffffffff);
												}
												if($iconid != 0) // Hochgeladene Servergruppe
												{
													if($icon_src != "")
													{
														$tree['clients'][$counter_clients]['cgroup'][$anzahlChannelgruppen] 	= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$iconid, $alldata['server']['virtualserver_port']);
														$anzahlChannelgruppen++;
													};
												};
											};
										};
									};
									
									$tree['clients'][$counter_clients]['nick_cid']					=	$u_value['cid'];
									$tree['clients'][$counter_clients]['nick_clid']					=	$u_value['clid'];
									$tree['clients'][$counter_clients]['nick_pid']					=	$value['pid'];
									$tree['clients'][$counter_clients]['nickname']					=	xssSafe($u_value['client_nickname']);
									
									$counter_clients++;
								};
							};
						};
					};
				};
			};
			
			return $tree;
		}
		else
		{
			return "error";
		};
	};
	
	// Icon Link zurueckgeben
	function get_icon ($ip, $icon_id, $port)
	{
		$name 		= 	str_replace("\\","",$icon_id);
		$name 		= 	str_replace("/","",$name);
		if(str_replace('icon_', '', $name) == 100 OR str_replace('icon_', '', $name) == 200 OR str_replace('icon_', '', $name) == 300 OR str_replace('icon_', '', $name) == 500 OR str_replace('icon_', '', $name) == 600)
		{
			$path	=	'../../images/ts_icons/';
		}
		else
		{
			$path	=	'../../images/ts_icons/'.$ip.'-'.$port.'/';
		};
		
		if(file_exists($path.$name))
		{
			return base64_encode_image($path.$name, "png");
		}
		else
		{
			return "";
		};
	};
	
	function base64_encode_image($filename, $filetype)
	{
		if ($filename)
		{
			$imgbinary = fread(fopen($filename, "r"), filesize($filename));
			return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
		};
	};
?>