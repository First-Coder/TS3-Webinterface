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
	require_once("config.php");
	require_once("lang.php");
	require_once("functions.php");
	require_once("ts3admin.class.php");
	
	/*
		Check if port is avalible
	*/
	function checkTeamspeakPort($instanz, $port)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$serverlist		=	$tsAdmin->getElement('data', $tsAdmin->serverList());
			
			foreach($serverlist AS $server)
			{
				if($server['virtualserver_port'] == $port)
				{
					return true;
				};
			};
			
			return false;
		};
	};
	
	/*
		Deploy a Teamspeakbackup
	*/
	function channelChannelAllBackupDeploy($instanz, $port, $pid, $backup, $newcid, $firstrun = 1)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 					= 		$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			if($firstrun == 1)
			{
				$channellist										=	$tsAdmin->getElement('data', $tsAdmin->channelList());
				$rename_def											=	0;
				foreach($channellist AS $key => $value)
				{
					if($rename_def == 0)
					{
						$newsettings['channel_name']				=	'Auto delete after backup';
						$newsettings['channel_flag_permanent']		=	'1';
						$newsettings['channel_flag_semi_permanent']	=	'0';
						$newsettings['channel_flag_default']		=	'1';
						$tsAdmin->channelEdit($value['cid'], $newsettings);
						$rename_def									=	$value['cid'];
					}
					else
					{
						$tsAdmin->channelDelete($value['cid']);
					};
				};
			};
			
			foreach($backup AS $key=>$value)
			{
				if ($pid==$value['pid'])
				{
					$settings['channel_name']								=	isset($value['channel_name']) ? $value['channel_name']:'';
					if($value['pid'] != 0)
					{
						$settings['cpid']									=	$newcid;
					};
					$settings['channel_topic']								=	isset($value['channel_topic']) ? $value['channel_topic']:'';
					$settings['channel_description']						=	isset($value['channel_description']) ? $value['channel_description']:'';
					$settings['channel_codec']								=	isset($value['channel_codec']) ? $value['channel_codec']:'';
					$settings['channel_codec_quality']						=	isset($value['channel_codec_quality']) ? $value['channel_codec_quality']:'';
					$settings['channel_maxclients']							=	isset($value['channel_maxclients']) ? $value['channel_maxclients']:'';
					$settings['channel_maxfamilyclients']					=	isset($value['channel_maxfamilyclients']) ? $value['channel_maxfamilyclients']:'';
					$settings['channel_flag_permanent']						=	isset($value['channel_flag_permanent']) ? $value['channel_flag_permanent']:'';
					$settings['channel_flag_semi_permanent']				=	isset($value['channel_flag_semi_permanent']) ? $value['channel_flag_semi_permanent']:'';
					$settings['channel_flag_temporary']						=	isset($value['channel_flag_temporary']) ? $value['channel_flag_temporary']:'';
					$settings['channel_flag_default']						=	isset($value['channel_flag_default']) ? $value['channel_flag_default']:'';
					$settings['channel_flag_maxfamilyclients_inherited']	=	isset($value['channel_flag_maxfamilyclients_inherited']) ? $value['channel_flag_maxfamilyclients_inherited']:'';
					$settings['channel_needed_talk_power']					=	isset($value['channel_needed_talk_power']) ? $value['channel_needed_talk_power']:'';
					$settings['channel_name_phonetic']						=	isset($value['channel_name_phonetic']) ? $value['channel_name_phonetic']:'';
					$cid													=	$tsAdmin->channelCreate($settings);
					$permid													=	$tsAdmin->getElement('data', $tsAdmin->permIdGetByName(array('i_group_needed_modify_power')));
					$tsAdmin->channelAddPerm($cid['data']['cid'], $value['perms']);
					if($cid['success']===false)
					{
						return false;
					};
					channelChannelAllBackupDeploy($instanz, $port, $value['cid'], $backup, $cid['data']['cid'], 0);
				};
			};
			
			if(isset($rename_def) AND $rename_def!=0)
			{
				$tsAdmin->channelDelete($rename_def);
			};
			
			return true;
		};
	};
	
	/*
		Get Teamspeakslots and Teamspeak active slots from an Instanz
	*/
	function getTeamspeakslots($instanz, $forceInfo = false)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$serverList													=	$tsAdmin->getElement('data', $tsAdmin->serverList());
			
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
					$returnArray[0]['virtualserver_clientsonline']		=	$returnArray[0]['virtualserver_clientsonline'] + ($server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline']);
					$returnArray[0]['virtualserver_maxclients']			=	$returnArray[0]['virtualserver_maxclients'] + $server['virtualserver_maxclients'];
				};
				
				return $returnArray;
			}
			else
			{
				$newServerList											=	array();
				foreach($serverList AS $server)
				{
					$server['virtualserver_clientsonline']				=	$server['virtualserver_clientsonline'] - $server['virtualserver_queryclientsonline'];
					
					$newServerList[]									=	$server;
				};
				return $newServerList;
			};
		};
	};
	
	
	/*
		Query Console Command
	*/
	function ExecServerQueryCommand($instanz, $command, $selectedserver = -1)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			if($selectedserver != -1)
			{
				$tsAdmin->selectServer($selectedserver, 'serverId', true);
			};
			
			writeInLog(4, $_SESSION['user']['benutzer'].": Exec a Querycommand: ".$command." to: ".$instanz."", true);
			
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
		Delete a Teamspeakchannel
	*/
	function TeamspeakDeleteChannel($cid, $instanz, $sid)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete a Teamspeakchannel (".$cid.") Instanz: ".$instanz." Sid: ".$sid."", true);
				
				$status		=	"done";
			};
			
			echo $status;
		};
	};
	
	/*
		Edit Teamspeakserver
	*/
	function TeamspeakServerEdit($right, $value, $instanz, $serverId, $port)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			$status				=	'';
			$server_edit 		=	$tsAdmin->serverEdit(array($right=>$value));
			if($server_edit['success']===false)
			{
				for($i=0; $i+1==count($server_edit['errors']); $i++)
				{
					$status	.= 	$server_edit['errors'][$i]."<br />";
				};
			};
			
			// Datenbankänderung
			if($right == 'virtualserver_port')
			{
				$status2					=		TeamspeakServerEditMySQLChange($port, $instanz, $value);
				$status3					=		TeamspeakServerEditEditMySQLChange($port, $instanz, $value);
			}
			else
			{
				$status2					=		true;
				$status3					=		true;
			};	
			
			if($status == '')
			{
				if($status2 && $status3)
				{
					writeInLog(4, $_SESSION['user']['benutzer'].": Edit a Teamspeakserver (".$value.") Instanz: ".$instanz." Sid: ".$serverId."", true);
					
					$status					=		'done';
				}
				else
				{
					$status					=		'Serverport changed! But cant change the Port in the Database!';
				};
			};
		}
		else
		{
			$status 						= 		'No Teamspeak Connection';
		};
		
		// Rueckgabe
		return $status;
	}
	
	/*
		Change Virtualserverport in Database (Just this time here I will use the MySQL Connection)
	*/
	function TeamspeakServerEditMySQLChange($port, $instanz, $newPort)
	{
		include("_mysql.php");
		
		$_sql 			= 	"SELECT fk_clients, fk_rights, access_ports FROM  `main_clients_rights` WHERE access_instanz='".$instanz."'";
		$status			=	true;
		
		if (($data = $databaseConnection->query($_sql)) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 																			= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($result AS $row)
				{
					if(strpos($row['access_ports'], $port) !== false)
					{
						$newPorts 		=	str_replace($port, $newPort, $row['access_ports']);
						
						if($databaseConnection->exec('UPDATE main_clients_rights SET access_ports="' . $newPorts . '" WHERE fk_clients="' . $row['fk_clients'] . '" AND fk_rights="' . $row['fk_rights'] . '" AND access_instanz="' . $instanz . '"') === false)
						{
							$status		=	false;
						};
					};
				};
				
				return $status;
			}
			else
			{
				return true;
			};
		}
		else
		{
			return false;
		};
	};
	
	function TeamspeakServerEditEditMySQLChange($port, $instanz, $newPort)
	{
		include("_mysql.php");
		
		$_sql 			= 	"SELECT fk_clients, fk_rights, access_ports FROM  `main_clients_rights_server_edit` WHERE access_instanz='".$instanz."'";
		$status			=	true;
		
		if (($data = $databaseConnection->query($_sql)) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 																			= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($result AS $row)
				{
					if(strpos($row['access_ports'], $port) !== false)
					{
						$newPorts 		=	str_replace($port, $newPort, $row['access_ports']);
						
						if($databaseConnection->exec('UPDATE main_clients_rights_server_edit SET access_ports="' . $newPorts . '" WHERE fk_clients="' . $row['fk_clients'] . '" AND fk_rights="' . $row['fk_rights'] . '" AND access_instanz="' . $instanz . '"') === false)
						{
							$status		=	false;
						};
					};
				};
				
				return $status;
			}
			else
			{
				return true;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Delete Teamspeak Icons
	*/
	function deleteIcon($id, $instanz, $serverId, $port)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsAdmin->selectServer($serverId, 'serverId', true);
			$tsAdmin->ftDeleteFile(0, '', array("/icon_".$id));
			
			unlink('images/ts_icons/'.$ts3_server[$instanz]['ip'].'-'.$port.'/'."icon_".$id);
			
			writeInLog(4, $_SESSION['user']['benutzer'].": Delete a Teamspeakicon (".$id.") Instanz: ".$instanz." Sid: ".$serverId."", true);
			
			return true;
		}
		else
		{
			return false;
		};
	};
	
	/*
		Create a new Token on a Teamspeakserver
	*/
	function addToken($type, $tokenid1, $tokenid2, $number, $description, $serverId, $instanz)
	{
		global $ts3_server;
		
		$token	=	array();
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
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
				for($i = 1; $i <= $number; $i++)
				{
					$token_add			=	$tsAdmin->tokenAdd($type, intval($tokenid1), $tokenid2, $description);
					
					if($token_add['success']!==false)
					{
						writeInLog(4, $_SESSION['user']['benutzer'].": Create Token (".$token[$i]['token'].") Instanz: ".$instanz." Sid: ".$serverId, true);
						
						$token['done']	=	"true";
						$token[$i]		=	array();
						$token[$i]['token']			=	$token_add['data']['token'];
						$token[$i]['description']	=	$description;
						if($tokenid1 == 0)
						{
							$token[$i]['type']		=	$language['ts_sgroup'];
							$token[$i]['channel']	=	"-";
						}
						else
						{
							$token[$i]['type']		=	$language['ts_cgroup'];
							$token[$i]['channel']	=	"Channel ID: ".$tokenid2;
						};
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
			
			return $token;
		}
		else
		{
			$token['done'] = "false";
			return $token;
		};
	};
	
	/*
		Delete a Token from a Teamspeakserver
	*/
	function deleteToken($token, $serverId, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsAdmin->selectServer($serverId, 'serverId', true);
			
			// Token loeschen
			$token_delete	=	$tsAdmin->tokenDelete($token);
			
			if($token_delete['success'] !== false)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete Token (".$token.") Instanz: ".$instanz." Sid: ".$serverId, true);
				
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
		Stop a Teamspeakserver
	*/
	function stopTeamspeakServer($serverid, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server stoppen lassen
			$server_stop	=	$tsAdmin->serverStop($serverid);
			if($server_stop['success']===false)
			{
				for($i=0; $i+1==count($server_stop['errors']); $i++)
				{
					$status .= $server_stop['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Stop a Teamspeakserver Instanz: ".$instanz." Sid: ".$serverid, true);
				
				$status .= "done";
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
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server stoppen lassen
			$server_stop	=	$tsAdmin->serverStart($serverid);
			if($server_stop['success']===false)
			{
				for($i=0; $i+1==count($server_stop['errors']); $i++)
				{
					$status .= $server_stop['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Start a Teamspeakserver Instanz: ".$instanz." Sid: ".$serverid, true);
				
				$status .= "done";
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
	function TeamspeakServerInformations($serverid, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Teamspeakserver selectieren
			$tsAdmin->selectServer($serverid, 'serverId', true);
			
			// Server Infos abfragen
			$server 		= 	$tsAdmin->serverInfo();
			
			$tsAdmin->logout();
			
			return $server;
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
		// Variable global deklarieren
		global $language;
		global $ts3_server;
		
		$informations	=	array();
		
		foreach($ts3_server AS $instanz => $values)
		{
			// Teamspeak Daten eingeben
			$tsAdmin 			= 	new ts3admin($values['ip'], $values['queryport']);
			
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				// Im Teamspeak Einloggen
				$tsAdmin->login($values['user'], $values['pw']);
				
				$informations[$instanz]		=	array();
				
				// Server Infos abfragen
				$servers 					= 	$tsAdmin->serverList();
				
				// Für jeden Server...
				foreach($servers['data'] as $server)
				{
					// Server Uptime
					if(isset($server['virtualserver_uptime']))
					{
						$uptime 			= 	$tsAdmin->convertSecondsToStrTime($server['virtualserver_uptime']);
					}
					else
					{
						$uptime 			= 	'-';
					};
					
					// Server status
					if($server['virtualserver_status'] == 'online')
					{
						$status				=	$language['online'];
					}
					else
					{
						$status				=	$language['offline'];
					};
					
					// Clienten
					$clients_online			=	$server['virtualserver_clientsonline'];
					$query_online			=	$server['virtualserver_queryclientsonline'];
					$clients				=	$clients_online - $query_online;
					
					// Maxclients
					$maxclients				=	$server['virtualserver_maxclients'];
					
					// Variabeln im Jason übergeben
					$informations[$instanz][$server['virtualserver_id']]['virtualserver_uptime']					=	$uptime;
					$informations[$instanz][$server['virtualserver_id']]['virtualserver_status']					=	$status;
					$informations[$instanz][$server['virtualserver_id']]['virtualserver_clientsonline']				=	$clients;
					if($query_online == null)
					{
						$informations[$instanz][$server['virtualserver_id']]['virtualserver_queryclientsonline']	=	'-';
					}
					else
					{
						$informations[$instanz][$server['virtualserver_id']]['virtualserver_queryclientsonline']	=	$query_online;
					};
					$informations[$instanz][$server['virtualserver_id']]['virtualserver_maxclients']				=	$maxclients;
					if($server['virtualserver_status'] == 'online')
					{
						$informations[$instanz][$server['virtualserver_id']]['virtualserver_clientmaxclients']		=	$clients . " / " . $maxclients;
					}
					else
					{
						$informations[$instanz][$server['virtualserver_id']]['virtualserver_clientmaxclients']		= 	"-";
					};
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
				// Teamspeak Daten eingeben
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
							writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Server Instanz Messages ".$instanz, true);
							
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
							// Server Select
							$tsServerID = $tsAdmin->serverIdGetByPort($server['virtualserver_port']);
							$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
							
							// Server Name setzen
							$tsAdmin->setName(TS3_CHATNAME);
							
							// Clientliste abfragen
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
							writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Server Instanz Poke ".$instanz, true);
							
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
			// Teamspeak Daten eingeben
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
						writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Server Instanz Messages ".$instanz, true);
						
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
						// Server Select
						$tsServerID = $tsAdmin->serverIdGetByPort($server['virtualserver_port']);
						$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
						
						// Server Name setzen
						$tsAdmin->setName(TS3_CHATNAME);
						
						// Clientliste abfragen
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
						writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Server Instanz Poke ".$instanz, true);
						
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
		Teamspeakserver Clientpoke
	*/
	function TeamspeakServerClientPoke($message, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_poke = $tsAdmin->clientPoke($clid, $message);
			
			if($client_poke['success'] === false)
			{
				for($i = 0; $i+1 == count($client_poke['errors']); $i++)
				{
					$status .= $client_poke['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Poke Message: ".$message." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeakserver Clientmessage
	*/
	function TeamspeakServerClientMessage($message, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_msg = $tsAdmin->sendMessage(1, $clid, $message);
			
			if($client_msg['success'] === false)
			{
				for($i = 0; $i+1 == count($client_msg['errors']); $i++)
				{
					$status .= $client_msg['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Message Message: ".$message." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeakserver Clientmove
	*/
	function TeamspeakServerClientMove($cid, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_move = $tsAdmin->clientMove($clid, $cid);
			
			if($client_move['success'] === false)
			{
				for($i = 0; $i+1 == count($client_move['errors']); $i++)
				{
					$status .= $client_move['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Move ChannelId: ".$cid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeakserver Clientkick
	*/
	function TeamspeakServerClientKick($message, $mode, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_kick = $tsAdmin->clientKick($clid, $mode, $message);
			if($client_kick['success'] === false)
			{
				for($i = 0; $i+1 == count($client_kick['errors']); $i++)
				{
					$status .= $client_kick['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Kick Message: ".$message." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeakserver Clientban
	*/
	function TeamspeakServerClientBan($message, $time, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_ban = $tsAdmin->banClient($clid, $time*60, $message);
			if($client_ban['success'] === false)
			{
				for($i = 0; $i+1 == count($client_ban['errors']); $i++)
				{
					$status .= $client_ban['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Ban Message: ".$message." Client: ".$clid." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeakserver Clientban Manuell
	*/
	function TeamspeakServerClientBanManuell($bantype, $input, $time, $reason, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
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
					writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Ban Uid Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status .= "done";
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
					writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Ban IP Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status .= "done";
				};
			}
			else // name
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
					writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Ban Name Message: ".$reason." Client: ".$input." Time: ".$time." Instanz: ".$instanz." Port: ".$port, true);
					
					$status .= "done";
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
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Unban Banid: ".$banid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Give a Client a Servergroup
	*/
	function TeamspeakServerClientAddServerGroup($sgid, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_sgroup = $tsAdmin->serverGroupAddClient($sgid, $clid);
			if($client_sgroup['success'] === false)
			{
				for($i = 0; $i+1 == count($client_sgroup['errors']); $i++)
				{
					$status .= $client_sgroup['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Add Servergroup Sgroup: ".$sgid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
	function TeamspeakServerClientRemoveServerGroup($sgid, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$client_sgroup = $tsAdmin->serverGroupDeleteClient($sgid, $clid);
			if($client_sgroup['success'] === false)
			{
				for($i = 0; $i+1 == count($client_sgroup['errors']); $i++)
				{
					$status .= $client_sgroup['errors'][$i]."<br />";
				};
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Remove Servergroup Sgroup: ".$sgid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Change the Client Channelgroup
	*/
	function TeamspeakServerClientChangeChannelGroup($cgid, $cid, $clid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			$tsServerID = $tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Server Name setzen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Client Change Channelgroup Sgroup: ".$cgid." Client: ".$clid." Instanz: ".$instanz." Port: ".$port, true);
				
				$status .= "done";
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
		Teamspeak Serverpoke
	*/
	function TeamspeakServerServerPoke($message, $port, $serverid, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			if($serverid == '')
			{
				$tsServerID = $tsAdmin->serverIdGetByPort($port);
				$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			}
			else
			{
				$tsAdmin->selectServer($serverid, 'serverId', true);
			};
			
			// Server Name setzen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Serverpoke Nachricht: ".$message." Instanz: ".$instanz." Port: ".$port, true);
				
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
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server Select
			if($serverid == '')
			{
				$tsServerID = $tsAdmin->serverIdGetByPort($id);
				$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			}
			else
			{
				$tsAdmin->selectServer($serverid, 'serverId', true);
			};
			
			// Server Name setzen
			$tsAdmin->setName(TS3_CHATNAME);
			
			$message	=	str_replace("\\", "\\\\", $message);
			
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Teamspeak Servermessage Message: ".$message." Instanz: ".$instanz, true);
				
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
		Get all Serverports from a spezific instanz
	*/
	function getTeamspeakPorts($instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$informations	=	array();
			
			// Server Infos abfragen
			$servers 		= 	$tsAdmin->serverList();
			
			$i				=	0;
			$server_ports 	= 	array();
			
			foreach($servers['data'] as $server)
			{
				$server_ports[$i]	=	$server['virtualserver_port'];
				$i++;
			};
			
			$tsAdmin->logout();
			
			return $server_ports;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Get all Serverports from a spezific instanz (with permissions)
	*/
	function getTeamspeakClientPorts($instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$informations	=	array();
			
			// Server Infos abfragen
			$servers 		= 	$tsAdmin->serverList();
			
			$i				=	0;
			$server_ports 	= 	array();
			
			foreach($servers['data'] as $server)
			{
				if(strpos($user_right['ports']['right_web_server_protokoll'][$instanz], $server['virtualserver_port']) !== false)
				{
					$server_ports[$i]	=	$server['virtualserver_port'];
					$i++;
				};
			};
			
			$tsAdmin->logout();
			
			return $server_ports;
		}
		else
		{
			return "No Connection";
		};
	};
	
	/*
		Delete a Teamspeak Server
	*/
	function delTeamspeakserver($serverid, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			// Server löschen
			$server_del		=	$tsAdmin->serverDelete($serverid);
			
			$tsAdmin->logout();
			
			if($server_del['success'] === false)
			{
				return false;
			}
			else
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete Teamspeak Server Instanz: ".$instanz." Sid: ".$serverid, true);
				
				return true;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Get the Teamspeak Querylog from a spezific instanz and port
	*/
	function getPortQuery($port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
		
			$begin_pos		=	' begin_pos=0';
			$instance		=	' instance=0';
			
			$getlog			=	$tsAdmin->execOwnCommand('2', 'logview lines=100 reverse=1'.$instance);
			
			if(!empty($getlog['data']))
			{
				foreach($getlog['data'] AS $key=>$value)
				{
					$log[]	=	explode('|', $value['l'], 5);
				};
			};
			
			foreach($log AS $key => $value)
			{
				$dataString				.=		'<tr>';
				$dataString				.=		'<td style="text-align:center;">' . $value['0'] . '</td>';
				$dataString				.=		'<td style="text-align:center;" class="hidden-sm-down">' . $value['1'] . '</td>';
				$dataString				.=		'<td style="text-align:center;" class="hidden-lg-down">' . $value['2'] . '</td>';
				$dataString				.=		'<td style="text-align:center;" class="hidden-lg-down">' . $value['3'] . '</td>';
				$dataString				.=		'<td style="text-align:center;">' . htmlspecialchars($value['4']) . '</td>';
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
		Massactions: Get Teamspeakclients
	*/
	function getUsersMassActions($group, $channel, $who, $index, $action, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Vaiabeln deklarieren
			$clients 				= 	array();
			$i						=	1;
			$ID						=	false;
			$teamspeak_clients		=	$tsAdmin->getElement('data', $tsAdmin->clientList("-uid -away -voice -times -groups -info -icon -country"));
			// Action uebergeben
			$clients[0]				=	$action;
			
			// Wird die ID gefordert ?
			if($index == 1)
			{
				$ID					=	true;
			};
			
			if($who == 'all')
			{
				foreach($teamspeak_clients AS $client)
				{
					if($channel == '' || $channel == 'none')
					{
						if($client['client_type'] == 0)
						{
							if($ID)
							{
								$clients[$i]	=	$client['clid'];
							}
							else
							{
								$clients[$i]	=	htmlspecialchars($client['client_nickname']);
							};
							$i++;
						};
					}
					else
					{
						if($client['client_channel_group_inherited_channel_id'] == $channel && $client['client_type'] == 0)
						{
							if($ID)
							{
								$clients[$i]	=	$client['clid'];
							}
							else
							{
								$clients[$i]	=	htmlspecialchars($client['client_nickname']);
							};
							$i++;
						};
					};
				};
			};
			
			if($who == 'cgroup')
			{
				foreach($teamspeak_clients AS $client)
				{
					if($channel == '' || $channel == 'none')
					{
						if($client['client_type'] == 0 && $client['client_channel_group_id'] == $group)
						{
							if($ID)
							{
								$clients[$i]	=	$client['clid'];
							}
							else
							{
								$clients[$i]	=	$client['client_nickname'];
							};
							$i++;
						};
					}
					else
					{
						if($client['client_type'] == 0 && $client['client_channel_group_id'] == $group && $client['client_channel_group_inherited_channel_id'] == $channel)
						{
							if($ID)
							{
								$clients[$i]	=	$client['clid'];
							}
							else
							{
								$clients[$i]	=	$client['client_nickname'];
							};
							$i++;
						};
					};
				};
			};
			
			if($who == 'sgroup')
			{
				foreach($teamspeak_clients AS $client)
				{
					if($channel == '' || $channel == 'none')
					{
						if($client['client_type'] == 0)
						{
							$servergroups = explode(",", $client['client_servergroups']);
							
							for ($a = 0; $a < count($servergroups); $a++)
							{
								if($servergroups[$a] == $group)
								{
									if($ID)
									{
										$clients[$i]	=	$client['clid'];
									}
									else
									{
										$clients[$i]	=	$client['client_nickname'];
									};
									$i++;
								};
							};
						};
					}
					else
					{
						if ($client['client_channel_group_inherited_channel_id'] == $channel && $client['client_type'] == 0)
						{
							$servergroups = explode(",", $client['client_servergroups']);
							
							for ($a = 0; $a < count($servergroups); $a++)
							{
								if($servergroups[$a] == $group)
								{
									if($ID)
									{
										$clients[$i]	=	$client['clid'];
									}
									else
									{
										$clients[$i]	=	$client['client_nickname'];
									};
									$i++;
								};
							};
						};
					};
				};
			};
			return $clients;
		}
		else
		{
			return "error";
		};
	};
	
	/*
		Reset a Teamspeakserver
	*/
	function resetServer($instanz, $port)
	{
		writeInLog(4, $_SESSION['user']['benutzer'].": Reset a Teamspeakserver Instanz: ".$instanz." Port: ".$port, true);
		
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
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
		Create a Teamspeak Backup
	*/
	function createBackup($instanz, $port, $kind, $kindchannel)
	{
		writeInLog(4, $_SESSION['user']['benutzer'].": Create a Backup Typ: ".$kind." Instanz: ".$instanz." Port: ".$port, true);
		
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$datum 								= 		date("Y_m_d_H_i", time());
			
			if($kind == "channel")
			{
				$channels						=	$tsAdmin->getElement('data', $tsAdmin->channelList("-topic -flags -voice -limits"));
				
				if($kindchannel == "all")
				{
					foreach($channels AS $key=>$value)
					{
						$channelinfo			=	$tsAdmin->getElement('data', $tsAdmin->channelInfo($value['cid']));
						unset($channelinfo['channel_password']);
						unset($channelinfo['channel_filepath']);
						
						foreach($channelinfo AS $key2=>$value2)
						{
							if(!isset($channels[$key][$key2]))
							{
								$channels[$key][$key2]	=	$value2;
							};
						};
					};
					
					$path									=	"backup/channel/instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_all.json";
					$handler								=	fopen($path, "a+");
					if($handler === false)
					{
						return false;
					}
					else
					{
						$count								=	1;
						$count_chans						=	count($channels);
						
						foreach($channels AS $key=>$value)
						{
							$settings							=	'';
							$count2								=	1;
							foreach($value AS $key2=>$value2)
							{
								$count_settings					=	count($value);
								$settings						.=	$key2."=".str_replace(' ', '\s',$value2);
								if($count2!=$count_settings)
								{
									$settings.=" ";
								};
								$count2++;
							};
							
							$channelperms					=	$tsAdmin->channelPermList($value['cid']);
							if($channelperms['success']===true)
							{
								$settings					.=	"<perms>";
								$count3						=	1;
								$count_perms				=	count($channelperms['data']);
								
								foreach($channelperms['data'] AS $key3=>$value3)
								{
									$count4					=	1;
									$count_permsettings		=	count($value3);
									foreach($value3 AS $key4=>$value4)
									{
										if($key4!="cid")
										{
											$settings		.=	$key4."=".$value4;
											if($count4 != $count_permsettings)
											{
												$settings	.=	" ";
											};
										};
									};
									if($count3 != $count_perms)
									{
										$settings			.=	"|";
									};
								};
								$settings					.=	"</perms>";
							};
							
							if($count != $count_chans)
							{
								$settings					.=	"||";
							};
							
							if(!fwrite($handler, $settings))
							{
								return false;
							};
							$count++;
						};
						
						fclose($handler);
					};
					
					return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_all.json";
				}
				else
				{
					$newJson					=	array();
					
					foreach($channels AS $i => $channel)
					{
						$newJson[$i]			=	$channel['channel_name'];
					};
					
					if(file_put_contents("backup/channel/instanz_" . $instanz . "_port_" . $port . "_" . $datum . ".json", json_encode($newJson)))
					{
						return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . ".json";
					}
					else
					{
						return false;
					};
				};
			}
			else
			{
				if(file_put_contents("backup/server/instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_server.json", $tsAdmin->getElement('data', $tsAdmin->serverSnapshotCreate())))
				{
					return "instanz_" . $instanz . "_port_" . $port . "_" . $datum . "_server.json";
				}
				else
				{
					return false;
				};
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Get all Teamspeakchannels from a Spezific Teamspeakserver
	*/
	function getTeamspeakChannels($instanz, $port)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID = ($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList());
			
			return $channels;
		}
		else
		{
			return "error";
		};
	};
	
	/*
		Delete all Teamspeakchannels from a spezific Teamspeakserver
	*/
	function deleteAllTeamspeakChannels($port, $ip, $queryport, $username, $password)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	= 	($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$channels		=	$tsAdmin->getElement('data', $tsAdmin->channelList("-flags"));
			$defaultCid		=	-1;
			
			if(!empty($channels))
			{
				foreach($channels AS $key=>$value)
				{
					if($value['channel_flag_default'] == '1')
					{
						$defaultCid		=	$value['cid'];
					}
					else
					{
						$tsAdmin->channelDelete($value['cid']);
					};
				};
			};
			
			return $defaultCid;
		}
		else
		{
			return -1;
		};
	};
	
	/*
		Delete just one Teamspeak Channel from a spezific Teamspeakserver
	*/
	function deleteTeamspeakChannel($cid, $port, $ip, $queryport, $username, $password)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	= 	($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
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
	function createTeamspeakChannel($name, $default, $port, $ip, $queryport, $username, $password, $justData = false, $ownData)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	= 	($tsAdmin->serverIdGetByPort($port));
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$channelInfo							=	array();
			
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
			
			writeInLog(4, $_SESSION['user']['benutzer'].": Create a Teamspeak Channel Name: ".$name." Port: ".$port, true);
			
			return $channelInfo['cid'];
		}
		else
		{
			return -1;
		};
	};
	
	/*
		Get the Information above a Teamspeakchannel
	*/
	function getChannelInformations($channelid, $port, $ip, $queryport, $username, $password)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Channel abfragen
			return $tsAdmin->channelInfo($channelid);
		}
		else
		{
			return 'error';
		};
	};
	
	/*
		Get the Information above a Teamspeakclient
	*/
	function getClientInformations($clientid, $port, $ip, $queryport, $username, $password)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Channel abfragen
			return $tsAdmin->clientInfo($clientid);
		}
		else
		{
			return 'error';
		};
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
			$tree['serverstatus']		=	$alldata['server']['virtualserver_status'];
			
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
			$tree['globalHeader']		=	htmlspecialchars($alldata['server']['virtualserver_name']);
			$tree['header']				=	'<i class="fa fa-server"></i>&nbsp;&nbsp;' . htmlspecialchars($alldata['server']['virtualserver_name']);
			$tree['headerimg']			=	'<img width="18px" height="18px" src="' . $icon_src . '" />';
			$tree['headerimg_exist'] 	=	(file_exists($icon_src)) ? "1" : "0";
			
			// Channel abfragen
			if(!empty($alldata['channel']))
			{
				foreach($alldata['channel'] AS $key=>$value)
				{
					if ($value['pid'] == 0) // Hauptchannels
					{
						$tree[$counter]['cid']				=	$value['cid'];
						$tree[$counter]['pid']				=	$value['pid'];
						
						if(preg_match("^\[(.*)spacer([\w\p{L}\d]+)?\]^u", $value['channel_name'], $treffer) AND $value['pid']==0 AND $value['channel_flag_permanent']==1)
						{
							$getspacer						=	explode($treffer[0], $value['channel_name']);
							$checkspacer					=	$getspacer[1][0].$getspacer[1][0].$getspacer[1][0];
							
							// Channel mit unendlich langen Spacer
							if($treffer[1]=="*" or strlen($getspacer[1])==3 AND $checkspacer==$getspacer[1])
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
								$tree['channels'][$counter]			=	$spacer;
								$tree[$counter]['align']			=	'left';
								$tree[$counter]['spacer']			=	'1';
							}
							elseif($treffer[1]=="c") // Channel mit center Spacer
							{
								$spacer								=	explode($treffer[0], htmlspecialchars($value['channel_name']));
								$tree['channels'][$counter]			=	$spacer[1];
								$tree[$counter]['align']			=	'center';
								$tree[$counter]['spacer']			=	'1';
							}
							elseif($treffer[1]=="r") // Channel mit rechtem Spacer
							{
								$spacer								=	explode($treffer[0], htmlspecialchars($value['channel_name']));
								$tree['channels'][$counter]			=	$spacer[1];
								$tree[$counter]['align']			=	'right';
								$tree[$counter]['spacer']			=	'1';
							}
							else // Ganz normaler Channel [Aber mit Spacer]
							{
								$spacer								=	explode($treffer[0], htmlspecialchars($value['channel_name']));
								$tree['channels'][$counter]			=	$spacer[1];
								$tree[$counter]['align']			=	'left';
								$tree[$counter]['spacer']			=	'1';
							};
						}
						else // Ganz normaler Channel
						{
							$tree[$counter]['spacer']				=	'0';
							
							$chanmaxclient							=	($value['channel_maxclients']=="-1" ? $alldata['server']['virtualserver_maxclients'] : $value['channel_maxclients']);
							
							$tree[$counter]['channel_flag_password']		=	$value['channel_flag_password'];
							$tree[$counter]['channel_flag_default']			=	$value['channel_flag_default'];
							$tree[$counter]['channel_codec']				=	$value['channel_codec'];
							$tree[$counter]['channel_needed_talk_power']	=	$value['channel_needed_talk_power'];
							
							if($value['channel_icon_id'] != 0)
							{
								
								if($value['channel_icon_id'] < 0)
								{
									$value['channel_icon_id']		=	sprintf('%u', $value['channel_icon_id'] & 0xffffffff);
								};
								
								$icon_src 							= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$value['channel_icon_id'], $alldata['server']['virtualserver_port']);
								if($icon_src != "")
								{
									$tree[$counter]['channel_icon_id']	=	"<img class=\"right-img\" style=\"height:16px;width:16px;\" src='$icon_src' alt=\"\" />";
								}
								else
								{
									$tree[$counter]['channel_icon_id']	=	'';
								};
							}
							else
							{
								$tree[$counter]['channel_icon_id']	=	'';
							};
							
							if($chanmaxclient <= $value['total_clients'])
							{
								$tree['channels'][$counter]			=	htmlspecialchars($value['channel_name']);
								$tree[$counter]['img_before']		=	"<img width='16px' height='16px' src='images/ts_viewer/channel_red.png'></img>";
							}
							elseif($value['channel_flag_password'] == 1)
							{
								$tree['channels'][$counter]			=	htmlspecialchars($value['channel_name']);
								$tree[$counter]['img_before']		=	"<img width='16px' height='16px' src='images/ts_viewer/pwchannel.png'></img>";
							}
							else
							{
								$tree['channels'][$counter]			=	htmlspecialchars($value['channel_name']);
								$tree[$counter]['img_before']		=	"<img width='16px' height='16px' src='images/ts_viewer/channel.png'></img>";
							};
						};
						
						$counter++;
					}
					else // Subchannels
					{
						$tree[$counter_subchannels]['sub_pid']							=	$value['pid'];
						$tree[$counter_subchannels]['sub_cid']							=	$value['cid'];
						$chanmaxclient													=	($value['channel_maxclients']=="-1" ? $alldata['server']['virtualserver_maxclients'] : $value['channel_maxclients']);
						
						$tree[$counter_subchannels]['sub_channel_flag_password']		=	$value['channel_flag_password'];
						$tree[$counter_subchannels]['sub_channel_flag_default']			=	$value['channel_flag_default'];
						$tree[$counter_subchannels]['sub_channel_codec']				=	$value['channel_codec'];
						$tree[$counter_subchannels]['sub_channel_needed_talk_power']	=	$value['channel_needed_talk_power'];
						
						if($value['channel_icon_id'] != 0)
						{
							
							if($value['channel_icon_id'] < 0)
							{
								$value['channel_icon_id']								=	sprintf('%u', $value['channel_icon_id'] & 0xffffffff);
							};
							
							$icon_src 													= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$value['channel_icon_id'], $alldata['server']['virtualserver_port']);
							if($icon_src != "")
							{
								$tree[$counter_subchannels]['sub_channel_icon_id']		=	"<img class=\"right-img\" style=\"height:16px;width:16px;\" src='$icon_src' alt=\"\" />";
							}
							else
							{
								$tree[$counter_subchannels]['sub_channel_icon_id']		=	'';
							};
						}
						else
						{
							$tree[$counter_subchannels]['sub_channel_icon_id']			=	'';
						};
						
						if($chanmaxclient <= $value['total_clients'])
						{
							$tree['sub_channels'][$counter_subchannels]					=	htmlspecialchars($value['channel_name']);
							$tree[$counter_subchannels]['sub_img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/channel_red.png'></img>";
						}
						elseif($value['channel_flag_password'] == 1)
						{
							$tree['sub_channels'][$counter_subchannels]					=	htmlspecialchars($value['channel_name']);
							$tree[$counter_subchannels]['sub_img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/pwchannel.png'></img>";
						}
						else
						{
							$tree['sub_channels'][$counter_subchannels]					=	htmlspecialchars($value['channel_name']);
							$tree[$counter_subchannels]['sub_img_before']				=	"<img width='16px' height='16px' src='images/ts_viewer/channel.png'></img>";
						};
						
						$counter_subchannels++;
					};
					if($value['total_clients'] >= 1 && !empty($alldata['clients'])) // Wenn Clienten auf dem Server sind
					{
						foreach($alldata['clients'] AS $u_key=>$u_value)
						{
							if($value['cid'] == $u_value['cid']) // Ob Client auch im Channel ist
							{
								// Kein Query Client
								if($u_value['client_type'] != "1")
								{
									$tree[$counter_clients]['nick_away_message']		=	'';
									
									// Teamspeak Status abfragen
									if($u_value['client_away'] == "1")
									{
										$tree[$counter_clients]['nick_status']			=	'away';
										if(!empty($u_value['client_away_message']))	
										{
											$tree[$counter_clients]['nick_away_message'] =	'(' . $u_value['client_away_message'] . ')';
										};
									}
									elseif($u_value['client_output_hardware'] == "0")
									{
										$tree[$counter_clients]['nick_status']			=	'hwhead';
									}
									elseif($u_value['client_input_hardware'] == "0")
									{
										$tree[$counter_clients]['nick_status']			=	'hwmic';
									}
									elseif($u_value['client_output_muted'] == "1")
									{
										$tree[$counter_clients]['nick_status']			=	'head';
									}
									elseif($u_value['client_input_muted']=="1")
									{
										$tree[$counter_clients]['nick_status']			=	'mic';
									}
									elseif($u_value['client_flag_talking'] == "0" AND $u_value['client_is_channel_commander'] == "1")
									{
										$tree[$counter_clients]['nick_status']			=	'player_command';
									}
									elseif($u_value['client_flag_talking'] == "1" AND $u_value['client_is_channel_commander'] == "1")
									{
										$tree[$counter_clients]['nick_status']			=	'player_command_on';
									}
									elseif($u_value['client_flag_talking']=="1")
									{
										$tree[$counter_clients]['nick_status']			=	'player_on';
									}
									else
									{
										$tree[$counter_clients]['nick_status']			=	'player';
									};
									
									// Teamspeak Landimage anzeigen
									if(!empty($u_value['client_country']))
									{
										$tree[$counter_clients]['nick_country']			=	strtolower($u_value['client_country']);
									}
									else
									{
										$tree[$counter_clients]['nick_country']			=	'';
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
														$tree[$counter_clients]['sgroup'][$anzahlServergruppen] 	= 	$icon_src;
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
														$tree[$counter_clients]['cgroup'][$anzahlChannelgruppen] 	= 	get_icon($ts3_server[$instanz]['ip'], "icon_".$iconid, $alldata['server']['virtualserver_port']);
														$anzahlChannelgruppen++;
													};
												};
											};
										};
									};
									
									$tree[$counter_clients]['nick_cid']					=	$u_value['cid'];
									$tree[$counter_clients]['nick_clid']				=	$u_value['clid'];
									$tree[$counter_clients]['nick_pid']					=	$value['pid'];
									$tree['nickname'][$counter_clients]					=	htmlspecialchars($u_value['client_nickname']);
									
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
			$path	=	'images/ts_icons/';
		}
		else
		{
			$path	=	'images/ts_icons/'.$ip.'-'.$port.'/';
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
	
	/*
		Get the Teamspeak Icons and Download them to your Webspace
	*/
	function getTeamspeakIcons($port, $ip, $queryport, $username, $password)
	{
		// Teamspeak Daten eingeben
		$tsAdmin 			= 	new ts3admin($ip, $queryport);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($username, $password);
			
			$tsServerID 	=	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			// Dateiliste für Icons abfragen
			$ft				=	$tsAdmin->ftGetFileList(0, '', '/icons');
			
			// Existiert der Ordner, wenn nicht, erstellen!
			if(is_dir('images/ts_icons/'.$ip.'-'.$port.'/'))
			{
				$handler	=	@opendir('images/ts_icons/'.$ip.'-'.$port.'/');
			}
			else
			{
				// Ordner erstellen
				if(@mkdir('images/ts_icons/'.$ip.'-'.$port.'/', 0777))
				{
					$handler		=	@opendir('images/ts_icons/'.$ip.'-'.$port.'/');
				};
			};
			
			// Lokale Datei lesen und in Array speichern
			while($datei = readdir($handler))
			{
				$icon_arr[]	=	$datei;
			};
			
			// Gefundene Icons in Array speichern
			$noIcon 	=	0;
			if(!empty($ft['data']))
			{
				foreach($ft['data'] AS $key2=>$value2)
				{
					$foundIcons[]	=	$value2['name'];
				};
			};
			
			// Alle nicht vorhandenen Icons vom Server löschen
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
						@unlink('images/ts_icons/'.$ip.'-'.$port.'/'.$value);
					};
				}
				elseif(strpos($ft['errors'][0], 'ErrorID: 2568 | Message: insufficient client permissions failed_permid')===false)
				{
					if($value!="." AND $value!="..")
					{
						@unlink('images/ts_icons/'.$ip.'-'.$port.'/'.$value);
					};
				};
			};
			
			if(!empty($ft['data']))
			{
				foreach($ft['data'] AS $key=>$value)
				{
					if(substr($value['name'], 0, 5) == 'icon_')
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
									$handler2	=	@fopen('images/ts_icons/'.$ip.'-'.$port.'/'.$value['name'], "w+");
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
	};
	
	/*
		Delete a Teamspeakclient in the Teamspeak Database
	*/
	function deleteDBClient($cldbid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 		= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$client_delete		=	$tsAdmin->clientDbDelete($cldbid);
			
			if($client_delete['succes']!==false)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete DB Client ID: ".$cldbid." Channel Instanz: ".$instanz." Port: ".$port, true);
				
				return "done";
			}
			else
			{
				$returnText		=	'';
				for($i=0; $i+1 == count($client_delete['errors']); $i++)
				{
					$returnText	.=	$client_delete['errors'][$i]."<br />";
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
		Delete a file from a Teamspeakserver
	*/
	function deleteFileFromFilelist($path, $cid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($ts3_server[$instanz]['user'], $ts3_server[$instanz]['pw']);
			
			$tsServerID 		= 	$tsAdmin->serverIdGetByPort($port);
			$tsAdmin->selectServer($tsServerID['data']['server_id'], 'serverId', true);
			
			$delfiles[]			=	$path;
			$file_delete		=	$tsAdmin->ftDeleteFile($cid, "", $delfiles);
			
			if($file_delete['succes'] !== false)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete File From Server: ChannelId: ".$cid." ; Path: ".$path." ;  Instanz: ".$instanz." Port: ".$port, true);
				
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
		Download a file from a Teamspeakserver
	*/
	function downloadFileFromFilelist($path, $filename, $cid, $port, $instanz)
	{
		global $ts3_server;
		
		// Teamspeak Daten eingeben
		$tsAdmin = new ts3admin($ts3_server[$instanz]['ip'], $ts3_server[$instanz]['queryport']);
		
		// Verbindung erfolgreich
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
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
?>