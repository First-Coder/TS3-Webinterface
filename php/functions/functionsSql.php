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
		Create Sql Connection
	*/
	function getSqlConnection($withError = true)
	{
		$string						=	SQL_Mode.':host='.SQL_Hostname.';port='.SQL_Port.';dbname='.SQL_Datenbank.'';
		if(SQL_SSL != "0")
		{
			$string					.=	';sslmode=require';
		};
		
		try
		{
			$databaseConnection 	= 	new PDO($string, SQL_Username, SQL_Password);
			
			return $databaseConnection;
		}
		catch (PDOException $e)
		{
			return ($withError) ? "Failed to get DB handle: " . $e->getMessage() . "\n" : false;
		};
	};
	
	/*
		Forgot password
	*/
	function forgotPassword($username)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if (($data = $databaseConnection->query("SELECT pk_client FROM main_clients WHERE benutzer='".$username."' LIMIT 1")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 								= 	$data->fetch(PDO::FETCH_ASSOC);
					$newPw									=	randomString(8);
					
					if($databaseConnection->exec('UPDATE main_clients SET password=\''.crypt($newPw, $newPw).'\' WHERE pk_client=\'' . $result['pk_client'] . '\'') === false)
					{
						writeInLog(2, "forgotPassword (SQL Error):".$databaseConnection->errorInfo()[2]);
						return "Database Error...";
					}
					else
					{
						include_once("./functionsMail.php");
						
						$mailContent							=		array();
						$mailContent							=		getMail("forgot_password");
						
						$mailContent							=		str_replace("%heading%", 					HEADING, 									$mailContent);
						$mailContent							=		str_replace("%newpw%", 						$newPw, 									$mailContent);
						
						return writeMail($mailContent["headline"], $mailContent["mail_subject"], $username, $mailContent["mail_body"]);
					};
				}
				else
				{
					return $language['user_does_not_exist'];
				};
			};
		};
	};
	
	/*
		Delete all Users
	*/
	function deleteAllUser($_username, $password)
	{
		if($_username != '' && $password != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$status					=	true;
			$status2				=	true;
			
			if($databaseConnection->exec('TRUNCATE TABLE main_clients;') === false)
			{
				writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
				$status				=	false;
			};
			
			if($status)
			{
				if($databaseConnection->exec('TRUNCATE TABLE main_clients_rights;') === false)
				{
					writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($databaseConnection->exec('TRUNCATE TABLE main_clients_infos;') === false)
				{
					writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($databaseConnection->exec('TRUNCATE TABLE main_clients_rights_server_edit;') === false)
				{
					writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($databaseConnection->exec('TRUNCATE TABLE ticket_tickets;') === false)
				{
					writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($databaseConnection->exec('TRUNCATE TABLE ticket_answer;') === false)
				{
					writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($status)
				{
					writeInLog($_SESSION['user']['benutzer'], "Delete all users and create a new one called '".$_username."'!", true);
					
					$key		=	guid();
					
					$_passwort 	= 	crypt($password, $password);
					
					$insert		= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, benutzer, password, last_login, benutzer_blocked) VALUES (\'' . $key . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
					if(!$insert->execute(array(":user"=>$_username, ":password"=>$_passwort)))
					{
						writeInLog(2, "deleteAllUser (SQL Error):".$insert->errorInfo()[2]);
						$status			=	false;
					};
					
					if($databaseConnection->exec('INSERT INTO main_clients_infos (fk_clients) VALUES (\'' . $key . '\')') === false)
					{
						writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status			=	false;
					};
					
					$status2	=	giveUserAllRights($databaseConnection, $key);
					
					if($status && $status2)
					{
						return "done";
					}
					else if($status2 == false)
					{
						return $language['permissions_couldnt_set'];
					};
				};
			}
			else
			{
				return $language['database_couldnt_be_deleted'];
			};
		};
	};
	
	/*
		Give a User all Rights
	*/
	function giveUserAllRights($databaseConnection, $pk)
	{
		if($pk != '')
		{
			$status				=	true;
			$right_key			=	array();
			
			$_sql 				= 	"SELECT * FROM  main_rights";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $keys)
					{
						if(strpos($keys['rights_name'], "web") === false || $keys['rights_name'] == "right_web_global_server" || $keys['rights_name'] == "right_web_server_create" || $keys['rights_name'] == "right_web"
							 || $keys['rights_name'] == "right_web_global_message_poke" || $keys['rights_name'] == "right_web_server_create" || $keys['rights_name'] == "right_web_server_delete")
						{
							$right_key[$keys['rights_name']]			=	$keys['pk_rights'];
						};
					};
				}
				else
				{
					writeInLog(5, "giveUserAllRights: No Keys found!");
				};
			}
			else
			{
				writeInLog(2, "giveUserAllRights (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
			
			foreach($right_key as $key)
			{
				if($status == true)
				{
					if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp) VALUES (\'' . $pk . '\', \'' . $key . '\', \'0\')') === false)
					{
						writeInLog(2, "giveUserAllRights (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status 			= 	false;
					};
				};
			};
			
			if($status)
			{
				writeInLog($_SESSION['user']['benutzer'], "Give Global Rights to '".$pk."'!", true);
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Give User all Rights in a Teamspeak 3 Server
	*/
	function giveUserAllRightsTSServer($pk, $instanz, $port, $withServerEdit = true)
	{
		if($pk != '' && $instanz != '' && $port != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$mysql_keys					=		getKeys();
			$status						=		true;
			
			foreach($mysql_keys AS $text_key => $key)
			{
				if(strpos($text_key, 'right_web_') !== false && $text_key != 'right_web_server_create' && $text_key != 'right_web_server_delete' && $text_key != 'right_web_global_message_poke'
					&& $text_key != 'right_web_global_server' && (!$withServerEdit && $text_key != 'right_web_server_edit'))
				{
					$_sql 				= 	"SELECT * FROM main_clients_rights  WHERE fk_clients='" . $pk . "' AND fk_rights='" . $key . "' AND access_instanz='" . $instanz . "'";
					
					if (($data = $databaseConnection->query($_sql)) !== false)
					{
						if ($data->rowCount() > 0)
						{
							$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
							
							foreach($result AS $row)
							{
								$_ports			=		$row['access_ports'];
								$_ports			=		$_ports . $port . ",";
								
								if($status)
								{
									if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=\'0\', access_ports=\'' . $_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $key . '\' AND access_instanz=\'' . $instanz . '\'') === false)
									{
										writeInLog(2, "giveUserAllRightsTSServer (SQL Error):".$databaseConnection->errorInfo()[2]);
										$status		=		false;
									};
								};
							};
						}
						else
						{
							if($status)
							{
								if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp, access_instanz, access_ports) VALUES (\'' . $pk . '\', \'' . $key . '\', \'0\', \'' . $instanz . '\', \'' . $port . ',\')') === false)
								{
									writeInLog(2, "giveUserAllRightsTSServer (SQL Error):".$databaseConnection->errorInfo()[2]);
									$status 			= 	false;
								};
							};
						};
					}
					else
					{
						writeInLog(2, "giveUserAllRightsTSServer (SQL Error):".$databaseConnection->errorInfo()[2]);
					};
				};
			};
			
			if($status)
			{
				writeInLog($_SESSION['user']['benutzer'], "Give all Teamspeakrights (Instanz: '".$instanz."', Port: '".$port."') to '".$pk."'!", true);
				
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Get Username from pk
	*/
	function getUsernameFromPk($pk)
	{
		if($pk == "" || ($databaseConnection = getSqlConnection(false)) === false)
		{
			return false;
		};
		
		if (($data = $databaseConnection->query("SELECT benutzer FROM  main_clients WHERE pk_client='".$pk."' LIMIT 1")) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 								= 	$data->fetch(PDO::FETCH_ASSOC);
				return $result["benutzer"];
			}
			else
			{
				writeInLog(5, "getUserInformations: User not found!");
				return false;
			};
		}
		else
		{
			writeInLog(2, "getUserInformations (SQL Error):".$databaseConnection->errorInfo()[2]);
			return false;
		};
	};
	
	/*
		Delete a Teamspeakserver in your Database
	*/
	function deletePort($port, $instanz)
	{
		if($port != '' && $instanz != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$status				=	true;
			
			if (($data = $databaseConnection->query("SELECT * FROM main_clients_rights  WHERE access_instanz='" . $instanz . "'")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 	= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $row)
					{
						$_ports			=		$row['access_ports'];
						
						$_rem_ports		=		$port . ",";
						$_new_ports		=		str_replace($_rem_ports, "", $_ports);
						
						if($databaseConnection->exec('UPDATE main_clients_rights SET access_ports=\'' . $_new_ports . '\' WHERE fk_clients=\'' . $row['fk_clients'] . '\' AND fk_rights=\'' . $row['fk_rights'] . '\' AND access_instanz=\'' . $instanz . '\'') === false)
						{
							writeInLog(2, "deletePort (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status		=		false;
						};
					};
				}
				else
				{
					writeInLog(5, "deletePort: Server do not exists!");
				};
			}
			else
			{
				writeInLog(2, "deletePort (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
			
			writeInLog($_SESSION['user']['benutzer'], "Has deleted a Teamspeakport \"".$port."\" \"".$instanz."\"", true);
			
			return $status;
		}
		else
		{
			return false;
		};
	};
	
	/*
		Change Virtualserverport in Database (Just this time here I will use the MySQL Connection)
	*/
	function TeamspeakServerEditMySQLChange($port, $instanz, $newPort)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql 			= 	"SELECT fk_clients, fk_rights, access_ports FROM  `main_clients_rights` WHERE access_instanz='".$instanz."'";
			$status			=	true;
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 				= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
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
				};
			}
			else
			{
				$status		=	false;
			};
			
			if(!$status)
			{
				$_sql 		= 	"SELECT fk_clients, fk_rights, access_ports FROM  `main_clients_rights_server_edit` WHERE access_instanz='".$instanz."'";
				
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
					};
				}
				else
				{
					$status	=	false;
				};
			};
			
			return $status;
		}
		else
		{
			return false;
		};
	};
	
	/*
		Save Teamspeakserver Server Edit rights for a spezfic user
	*/
	function clientEditServerEdit($pk, $port, $instanz, $adminCheckboxRightServerEditPort, $adminCheckboxRightServerEditSlots, $adminCheckboxRightServerEditAutostart, $adminCheckboxRightServerEditMinClientVersion
		, $adminCheckboxRightServerEditMainSettings, $adminCheckboxRightServerEditDefaultServerGroups, $adminCheckboxRightServerEditHostSettings, $adminCheckboxRightServerEditComplaintSettings
		, $adminCheckboxRightServerEditAntiFloodSettings, $adminCheckboxRightServerEditTransferSettings, $adminCheckboxRightServerEditProtokollSettings)
	{
		if($pk != '' && $port != '' && $instanz != '' && $adminCheckboxRightServerEditPort != '' && $adminCheckboxRightServerEditSlots != '' && $adminCheckboxRightServerEditAutostart != ''
			&& $adminCheckboxRightServerEditMinClientVersion != '' && $adminCheckboxRightServerEditMainSettings != '' && $adminCheckboxRightServerEditDefaultServerGroups != '' && $adminCheckboxRightServerEditHostSettings != ''
			&& $adminCheckboxRightServerEditComplaintSettings != '' && $adminCheckboxRightServerEditAntiFloodSettings != '' && $adminCheckboxRightServerEditTransferSettings != '' && $adminCheckboxRightServerEditProtokollSettings != '')
		{
			$user_right			=		getClientBlockedServerEditRights($pk, $instanz, $port);
			$mysql_keys			=		getBlockedServerEditRights();
			$status				=		true;
			
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditPort, strpos($user_right['right_server_edit_port'][$instanz], $port)
											, $mysql_keys['right_server_edit_port'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditSlots, strpos($user_right['right_server_edit_slots'][$instanz], $port)
											, $mysql_keys['right_server_edit_slots'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditAutostart, strpos($user_right['right_server_edit_autostart'][$instanz], $port)
											, $mysql_keys['right_server_edit_autostart'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditMinClientVersion, strpos($user_right['right_server_edit_min_client_version'][$instanz], $port)
											, $mysql_keys['right_server_edit_min_client_version'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditMainSettings, strpos($user_right['right_server_edit_main_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_main_settings'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditDefaultServerGroups, strpos($user_right['right_server_edit_default_servergroups'][$instanz], $port)
											, $mysql_keys['right_server_edit_default_servergroups'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditHostSettings, strpos($user_right['right_server_edit_host_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_host_settings'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditComplaintSettings, strpos($user_right['right_server_edit_complain_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_complain_settings'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditAntiFloodSettings, strpos($user_right['right_server_edit_antiflood_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_antiflood_settings'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditTransferSettings, strpos($user_right['right_server_edit_transfer_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_transfer_settings'], $instanz, $port, $pk);
			};
			if($status)
			{
				$status			=		addPortRightServerEdit($adminCheckboxRightServerEditProtokollSettings, strpos($user_right['right_server_edit_protokoll_settings'][$instanz], $port)
											, $mysql_keys['right_server_edit_protokoll_settings'], $instanz, $port, $pk);
			};
			
			if($status)
			{
				writeInLog($_SESSION['user']['benutzer'], "Edit a Users Teamspeak Server Edit spezific Permissions (".$pk.")", true);
				
				return true;
			}
			else
			{
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	function addPortRightServerEdit($permission, $user_right, $mysql_keys, $instanz, $port, $pk)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if($permission != 'true')
			{
				if($user_right === false)
				{
					$_sql 				= 		"SELECT * FROM main_clients_rights_server_edit  WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys . "' AND access_instanz='" . $instanz . "'";
					
					if (($data = $databaseConnection->query($_sql)) !== false)
					{
						if ($data->rowCount() > 0)
						{
							foreach($result AS $row)
							{
								$_ports		=		$row['access_ports'];
							};
							
							$_ports			=		$_ports . $port . ",";
							
							if($databaseConnection->exec('UPDATE main_clients_rights_server_edit SET access_ports=\'' . $_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $instanz . '\'') === false)
							{
								writeInLog(2, "addPortRightServerEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
								return false;
							};
						}
						else
						{
							if($databaseConnection->exec('INSERT INTO main_clients_rights_server_edit (fk_clients, fk_rights, access_instanz, access_ports) VALUES (\'' . $pk . '\', \'' . $mysql_keys . '\', \'' . $instanz . '\', \'' . $port . ',\')') === false)
							{
								writeInLog(2, "addPortRightServerEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
								return false;
							};
						};
					}
					else
					{
						writeInLog(2, "addPortRightServerEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
						return false;
					};
				};
			}
			else
			{
				$_sql 				= 		"SELECT * FROM main_clients_rights_server_edit  WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys . "' AND access_instanz='" . $instanz . "'";
				
				if (($data = $databaseConnection->query($_sql)) !== false)
				{
					if ($data->rowCount() > 0)
					{
						foreach($result AS $row)
						{
							$_ports		=		$row['access_ports'];
						};
						
						$_rem_ports		=		$port . ",";
						$_new_ports		=		str_replace($_rem_ports, "", $_ports);
						
						if($databaseConnection->exec('UPDATE main_clients_rights_server_edit SET access_ports=\'' . $_new_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $instanz . '\'') === false)
						{
							writeInLog(2, "addPortRightServerEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
							return false;
						};
					};
				}
				else
				{
					writeInLog(2, "addPortRightServerEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
					return false;
				};
			};
		}
		else
		{
			return false;
		};
		
		return true;
	};
	
	/*
		Get all Client edit rights (with name)
	*/
	function getCheckedClientServerEditRights($pk, $instanz, $port)
	{
		if($pk != '' && $instanz != '' && $port != '')
		{
			$databaseKeys	=	getBlockedServerEditRights();
			$clientKeys		=	getClientBlockedServerEditRights($pk, $instanz, $port);
			$returnKeys		=	$databaseKeys;
			
			foreach($databaseKeys AS $keyname => $key)
			{
				if(!empty($clientKeys))
				{
					if(!in_array($key, $clientKeys) && strpos($clientKeys[$key][$instanz], $port) !== false)
					{
						unset($returnKeys[$keyname]);
					};
				}
				else
				{
					unset($returnKeys[$keyname]);
				};
			};
			
			return $returnKeys;
		}
		else
		{
			return "parameter Error";
		};
	};
	
	/*
		Get all blocked Client edit rights (with keys)
	*/
	function getClientBlockedServerEditRights($pk, $instanz, $port)
	{
		$blockedServerEditRights													=	array();
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql 																	= 	"SELECT fk_rights,access_instanz,access_ports FROM  main_clients_rights_server_edit WHERE fk_clients='".$pk."' AND access_instanz='".$instanz."'";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 														= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $keys)
					{
						$blockedServerEditRights[$keys['fk_rights']][$keys['access_instanz']]	 			= 	$keys['access_ports'];
					};
				};
			}
			else
			{
				writeInLog(2, "getClientBlockedServerEditRights (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return $blockedServerEditRights;
	};
	
	/*
		Save Teamspeakserver rights for a spezfic user
	*/
	function clientEditPorts($pk, $server_view, $teamspeak_port, $teamspeak_instanz, $server_edit, $server_start_stop, $server_msg_poke, $server_mass_actions, $server_protokoll
		, $server_icons, $server_bans, $server_token, $server_filelist, $server_backups, $server_clients, $client_actions, $client_rights, $channel_actions)
	{
		global $mysql_keys;
		
		if($pk != ''   && $teamspeak_port != '' && $teamspeak_instanz != ''
			&& $server_view != '' && $server_edit != '' && $server_start_stop != '' && $server_msg_poke != '' && $server_mass_actions != '' && $server_protokoll != '' && $server_icons != '' && $server_bans != ''
			&& $server_token != '' && $server_filelist != '' && $server_backups != '' && $server_clients != ''
			&& $client_actions != '' && $client_rights != ''
			&& $channel_actions != ''
			&& ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$user_right			=		getUserRights('pk', $pk, true, 'ports');
			$status				=		addPortRight($server_view, strpos($user_right["right_web_server_view"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_view"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			
			if($status)
			{
				$status			=		addPortRight($server_edit, strpos($user_right["right_web_server_edit"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_edit"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_start_stop, strpos($user_right["right_web_server_start_stop"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_start_stop"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_msg_poke, strpos($user_right["right_web_server_message_poke"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_message_poke"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_mass_actions, strpos($user_right["right_web_server_mass_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_mass_actions"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_protokoll, strpos($user_right["right_web_server_protokoll"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_protokoll"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_icons, strpos($user_right["right_web_server_icons"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_icons"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_bans, strpos($user_right["right_web_server_bans"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_bans"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_token, strpos($user_right["right_web_server_token"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_token"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_filelist, strpos($user_right["right_web_file_transfer"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_file_transfer"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_backups, strpos($user_right["right_web_server_backups"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_backups"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($server_clients, strpos($user_right["right_web_server_clients"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_clients"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($client_actions, strpos($user_right["right_web_client_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_client_actions"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($client_rights, strpos($user_right["right_web_client_rights"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_client_rights"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			if($status)
			{
				$status			=		addPortRight($channel_actions, strpos($user_right["right_web_channel_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_channel_actions"], $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection);
			};
			
			if($status)
			{
				writeInLog($_SESSION['user']['benutzer'], "Edit a Users Teamspeak Permissions (".$pk.")", true);
				
				return true;
			}
			else
			{
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	function addPortRight($permission, $user_right, $mysql_keys, $teamspeak_instanz, $teamspeak_port, $pk, $databaseConnection)
	{
		if($permission == 'true')
		{
			if($user_right === false)
			{
				$_sql 				= 		"SELECT * FROM main_clients_rights  WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys . "' AND access_instanz='" . $teamspeak_instanz . "'";
				
				if (($data = $databaseConnection->query($_sql)) !== false)
				{
					$result 								= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					if ($data->rowCount() > 0)
					{
						foreach($result AS $row)
						{
							$_ports		=		$row['access_ports'];
						};
						
						$_ports			=		$_ports . $teamspeak_port . ",";
						
						if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=\'0\', access_ports=\'' . $_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $teamspeak_instanz . '\'') === false)
						{
							writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
							return false;
						};
					}
					else
					{
						if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp, access_instanz, access_ports) VALUES (\'' . $pk . '\', \'' . $mysql_keys . '\', \'0\', \'' . $teamspeak_instanz . '\', \'' . $teamspeak_port . ',\')') === false)
						{
							writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
							return false;
						};
					};
				}
				else
				{
					writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
					return false;
				};
			};
		}
		else
		{
			$_sql 				= 		"SELECT * FROM main_clients_rights  WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys . "' AND access_instanz='" . $teamspeak_instanz . "'";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 								= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $row)
					{
						$_ports		=		$row['access_ports'];
					};
					
					$_rem_ports		=		$teamspeak_port . ",";
					$_new_ports		=		str_replace($_rem_ports, "", $_ports);
					
					if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=\'0\', access_ports=\'' . $_new_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $teamspeak_instanz . '\'') === false)
					{
						writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
						return false;
					};
				};
			}
			else
			{
				writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
		};
		
		return true;
	};
	
	/*
		Get Information above a blocked user
	*/
	function getUserBlock($pk)
	{
		$_blocked_infos				=	array();
		$_blocked_infos['until']	=	0;
		
		if($pk != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql					=	"SELECT benutzer_blocked,blocked_until FROM main_clients WHERE pk_client='" . $pk . "' LIMIT 1";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 						= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
					foreach($result AS $row)
					{
						$blocked 					= 	$row;
					};
					
					$_blocked_infos['blocked']		=	$blocked['benutzer_blocked'];
					$_blocked_infos['until']		=	$blocked['blocked_until'];
				}
				else
				{
					$_blocked_infos['blocked']		=	"false";
				};
			}
			else
			{
				writeInLog(2, "getUserBlock (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		}
		else
		{
			$_blocked_infos['blocked']		=	"true";
		};
		
		return $_blocked_infos;
	};
	
	/*
		Save Global rights from a user
	*/
	function clientEdit($pk, $right, $checkbox, $time)
	{
		global $mysql_keys;
		
		if($pk != '' && $right != '' && $checkbox != '' && $time != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$user_right			=		getUserRights('pk', $pk, false, 'global');
			$status				=		true;
			
			if($right != 'benutzer_blocked')
			{
				if($checkbox == 'true')
				{
					if($user_right[$right] != $mysql_keys[$right])
					{
						if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp) VALUES (\'' . $pk . '\', \'' . $mysql_keys[$right] . '\', \'' . $time . '\')') === false)
						{
							writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status		=	false;
						};
					}
					else
					{
						if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=' . $time . ' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys[$right] . '\'') === false)
						{
							writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status		=	false;
						};
					};
				}
				else
				{
					if($user_right[$right] == $mysql_keys[$right])
					{
						if($databaseConnection->exec("DELETE FROM main_clients_rights WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys[$right] . "'") === false)
						{
							writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status		=	false;
						};
					};
				};
			}
			else
			{
				if($databaseConnection->exec("UPDATE main_clients SET benutzer_blocked='" . $checkbox . "', blocked_until='" . $time . "' WHERE pk_client='" . $pk . "'") === false)
				{
					writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status		=	false;
				};
			};
			
			if($status)
			{
				writeInLog($_SESSION['user']['benutzer'], "Edit a Users Global Permissions (".$pk.")", true);
				
				return true;
			}
			else
			{
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Delete one User
	*/
	function deleteUser($pk)
	{
		if($pk != '')
		{
			if(($databaseConnection = getSqlConnection(false)) !== false)
			{
				$status 			= 	true;
				
				if($databaseConnection->exec('DELETE FROM main_clients WHERE pk_client=\'' . $pk . '\';') === false)
				{
					writeInLog(2, "deleteUser (SQL Error):".$databaseConnection->errorInfo()[2]);
					$status			=	false;
				};
				
				if($status)
				{
					if($databaseConnection->exec('DELETE FROM main_clients_rights WHERE fk_clients=\'' . $pk . '\';') === false)
					{
						writeInLog(2, "deleteUser (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status			=	false;
					};
				};
				
				if($status)
				{
					if($databaseConnection->exec('DELETE FROM main_clients_infos WHERE fk_clients=\'' . $pk . '\';') === false)
					{
						writeInLog(2, "deleteUser (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status			=	false;
					};
				};
				
				if($status)
				{
					if($databaseConnection->exec('DELETE FROM main_clients_rights_server_edit WHERE fk_clients=\'' . $pk . '\';') === false)
					{
						writeInLog(2, "deleteUser (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status			=	false;
					};
				};
				
				if($status)
				{
					writeInLog($_SESSION['user']['benutzer'], "Delete a Client with the pk '".$pk."'!", true);
					
					return true;
				}
				else
				{
					return false;
				};
			}
			else
			{
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Create User
	*/
	function createUser($_username, $password, $returnPk = false, $cryptPw = false)
	{
		global $language;
		
		if($_username != '' && $password != '')
		{
			if(($databaseConnection = getSqlConnection(false)) !== false)
			{
				if($cryptPw)
				{
					$_passwort 		= 	$password;
				}
				else
				{
					$_passwort 		= 	crypt($password,$password);
				};
				
				$_sql 				= 	"SELECT * FROM main_clients  WHERE benutzer=:user";
				$data 				= 	$databaseConnection->prepare($_sql);

				if ($data->execute(array(":user"=>$_username)))
				{
					if ($data->rowCount() == 0)
					{
						$newPk		=	guid();
						$status		=	true;
						$insert		= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, benutzer, password, last_login, benutzer_blocked) VALUES (\'' . $newPk . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
						if(!$insert->execute(array(":user"=>$_username, ":password"=>$_passwort)))
						{
							writeInLog(2, "createUser (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status 	=	 false;
						};
						
						if($databaseConnection->exec('INSERT INTO main_clients_infos (fk_clients) VALUES (\'' . $newPk . '\')') === false)
						{
							writeInLog(2, "createUser (SQL Error):".$databaseConnection->errorInfo()[2]);
							$status 	=	 false;
						};
						
						if($status)
						{
							writeInLog($_SESSION['user']['benutzer'], "Create a Client called '".$_username."'!", true);
							
							if($returnPk)
							{
								return $newPk;
							}
							else
							{
								return "done";
							};
						}
						else
						{
							return $language['user_could_not_created'];
						};
					}
					else
					{
						return $language['user_already_exists'];
					};
				}
				else
				{
					writeInLog(2, "createUser (SQL Error):".$data->errorInfo()[2]);
				};
			}
			else
			{
				return "Databaseconnection error";
			};
		}
		else
		{
			return "No Username or no Password!";
		};
	};
	
	/*
		Get all User Information
	*/
	function getUsers()
	{
		$result			=	array();
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql		=	"SELECT benutzer, pk_client, last_login, benutzer_blocked FROM main_clients";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 	= 	$data->fetchAll(PDO::FETCH_ASSOC);
				};
			}
			else
			{
				writeInLog(2, "getUsers (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		return $result;
	};
	
	/*
		Delete Instance
	*/
	function deleteInstanz($instance)
	{
		global $ts3_server;
		
		if($instance != '' && ($databaseConnection = getSqlConnection(false)) !== false)
		{
			$ts3_server_count			=	count($ts3_server)-1;
			
			if($databaseConnection->exec('DELETE FROM main_clients_rights WHERE access_instanz!=\'\' AND access_instanz!=\'not_needed\' AND access_instanz=\'' . $instance . '\';') === false)
			{
				writeInLog(2, "deleteInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
			
			if($databaseConnection->exec('UPDATE main_clients_rights SET access_instanz=' . $instance . ' WHERE access_instanz!=\'\' AND access_instanz!=\'not_needed\' AND access_instanz=\'' . $ts3_server_count . '\';') === false)
			{
				writeInLog(2, "deleteInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
			
			$instanceFile				=	"../../config/instance.php";
			$file						=	file($instanceFile);
			$new_file					=	array();
			$search_alias				=	'$ts3_server[' . $instance . '][\'alias\']';
			$search_ip					=	'$ts3_server[' . $instance . '][\'ip\']';
			$search_queryport			=	'$ts3_server[' . $instance . '][\'queryport\']';
			$search_user				=	'$ts3_server[' . $instance . '][\'user\']';
			$search_pw					=	'$ts3_server[' . $instance . '][\'pw\']';
			
			if($instance < $ts3_server_count)
			{
				for ($i = 0; $i < count($file); $i++)
				{
					if(strpos($file[$i], $search_alias))
					{
						$new_file[$i]		=	"\t" . $search_alias . "\t\t= '" . $ts3_server[$ts3_server_count]['alias'] . "';\n";
					}
					elseif (strpos($file[$i], $search_ip))
					{
						$new_file[$i]		=	"\t" . $search_ip . "\t\t= '" . $ts3_server[$ts3_server_count]['ip'] . "';\n";
					}
					elseif (strpos($file[$i], $search_queryport))
					{
						$new_file[$i]		=	"\t" . $search_queryport . "\t= " . $ts3_server[$ts3_server_count]['queryport'] . ";\n";
					}
					elseif (strpos($file[$i], $search_user))
					{
						$new_file[$i]		=	"\t" . $search_user . "\t\t= '" . $ts3_server[$ts3_server_count]['user'] . "';\n";
					}
					elseif (strpos($file[$i], $search_pw))
					{
						$new_file[$i]		=	"\t" . $search_pw . "\t\t= '" . $ts3_server[$ts3_server_count]['pw'] . "';\n";
					}
					else
					{
						$new_file[$i]		=	$file[$i];
					};
				};
				
				for ($i = 0; $i < 7; $i++)
				{
					array_pop($new_file);
				};
				array_push($new_file, "?>");
			}
			else
			{
				for ($i = 0; $i < count($file); $i++)
				{
					if(strpos($file[$i], $search_alias))
					{
						unset($new_file[$i-1]);
						$new_file 			= 	array_values($new_file);  
					};
					
					if(!(strpos($file[$i], $search_alias) || strpos($file[$i], $search_ip) || strpos($file[$i], $search_queryport) || strpos($file[$i], $search_user) || strpos($file[$i], $search_pw)))
					{
						$new_file[$i]		=	$file[$i];
					};
				};
			};
			
			file_put_contents($instanceFile, "");
			file_put_contents($instanceFile, $new_file);
			
			writeInLog($_SESSION['user']['benutzer'], "Has deleted the Instanz \"".$instance."\"", true);
			
			return true;
		}
		else
		{
			return false;
		};
	};
	
	/*
		Set Modul
	*/
	function setModul($id, $value)
	{
		$modul			=	"";
		switch($id)
		{
			case "setModulFreeRegister":
				$modul	=	"free_register";
				break;
			case "setModulMasterserver":
				$modul	=	"masterserver";
				break;
			case "setModulServerAntrag":
				$modul	=	"free_ts3_server_application";
				break;
			case "setModulWriteNews":
				$modul	=	"write_news";
				break;
			case "setModulWebinterface":
				$modul	=	"webinterface";
				break;
		};
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if($databaseConnection->exec("UPDATE main_modul SET active='".$value."' WHERE modul='".$modul."'") === false)
			{
				writeInLog(2, "setModul (SQL Error) [Modul: ".$modul."]:".$databaseConnection->errorInfo()[2]);
				return false;
			}
			else
			{
				writeInLog($_SESSION['user']['benutzer'], "Set Modul \"".$id."\" to '".$value."'!", true);
				return true;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Profile Edit
	*/
	function updateUser($id, $content, $pk)
	{
		global $mysql_keys;
		global $user_right;
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			writeInLog($_SESSION['user']['benutzer'], "Profil ('".$id."') will be updated!", true);
			
			if($pk != "" && $user_right['right_hp_user_edit']['key'] != $mysql_keys['right_hp_user_edit'])
			{
				return false;
			};
			
			if($pk == "")
			{
				$_User_Pk 			= 		$_SESSION['user']['id'];
			}
			else
			{
				$_User_Pk			=		$pk;
			};
			
			if($id == "adminPassword" || $id == "profilePassword")
			{
				$sqlContent			=		crypt($content, $content);
			}
			else
			{
				$sqlContent			=		$content;
			};
			
			switch($id)
			{
				case 'profileUser':			$_sql 				= 		"UPDATE main_clients SET benutzer=:content WHERE pk_client=:pk" ; 						break;
				case 'adminUsername':		$_sql 				= 		"UPDATE main_clients SET benutzer=:content WHERE pk_client=:pk" ; 						break;
				case 'profilePassword':		$_sql 				= 		"UPDATE main_clients SET password=:content WHERE pk_client=:pk" ; 						break;
				case 'adminPassword':		$_sql 				= 		"UPDATE main_clients SET password=:content WHERE pk_client=:pk" ; 						break;
				case 'profileVorname':		$_sql 				= 		"UPDATE main_clients_infos SET vorname=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileNachname':		$_sql 				= 		"UPDATE main_clients_infos SET nachname=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileTelefon':		$_sql 				= 		"UPDATE main_clients_infos SET telefon=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileHomepage':		$_sql 				= 		"UPDATE main_clients_infos SET homepage=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileSkype':		$_sql 				= 		"UPDATE main_clients_infos SET skype=:content WHERE fk_clients=:pk" ; 					break;
				case 'profileSteam':		$_sql 				= 		"UPDATE main_clients_infos SET steam=:content WHERE fk_clients=:pk" ; 					break;
				case 'profileTwitter':		$_sql 				= 		"UPDATE main_clients_infos SET twitter=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileFacebook':		$_sql 				= 		"UPDATE main_clients_infos SET facebook=:content WHERE fk_clients=:pk" ; 				break;
				case 'profileGoogle':		$_sql 				= 		"UPDATE main_clients_infos SET google=:content WHERE fk_clients=:pk" ; 					break;
			};
			
			$data 					= 		$databaseConnection->prepare($_sql);
			
			if ($data->execute(array(":content"=>$sqlContent, ":pk"=>$_User_Pk)))
			{
				if($id == 'profileUser' || ($id == 'adminUsername' && $_SESSION['user']['id'] == $pk))
				{
					$_SESSION['user']['benutzer'] = $content;
				};
				return true;
			}
			else
			{
				writeInLog(2, "updateUser (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return false;
	};
	
	/*
		Get Userinformations
	*/
	function getUserInformations($pk)
	{
		$informations										=	array();
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql 			= 	"SELECT vorname,nachname,telefon,homepage,skype,steam,twitter,facebook,google FROM  main_clients_infos WHERE fk_clients='".$pk."' LIMIT 1";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 								= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $infos)
					{
						$informations['vorname']			=	$infos['vorname'];
						$informations['nachname']			=	$infos['nachname'];
						$informations['telefon']			=	$infos['telefon'];
						$informations['homepage']			=	$infos['homepage'];
						$informations['skype']				=	$infos['skype'];
						$informations['steam']				=	$infos['steam'];
						$informations['twitter']			=	$infos['twitter'];
						$informations['facebook']			=	$infos['facebook'];
						$informations['google']				=	$infos['google'];
					};
				}
				else
				{
					writeInLog(5, "getUserInformations: User not found!");
				};
			}
			else
			{
				writeInLog(2, "getUserInformations (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return $informations;
	};
	
	/*
		Checking if user has right on a spezific instanz
	*/
	function hasUserInstanz($pk, $instanz)
	{
		if($pk != '' && $instanz != '')
		{
			if(($databaseConnection = getSqlConnection(false)) !== false)
			{
				$_sql 				= 		"SELECT access_instanz FROM  main_clients_rights WHERE fk_clients='".$pk."' AND access_instanz='0' LIMIT 1";
				
				if (($data = $databaseConnection->query($_sql)) !== false)
				{
					if ($data->rowCount() > 0)
					{
						return true;
					};
				}
				else
				{
					writeInLog(2, "hasUserInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				};
			};
		};
		
		return false;
	};
	
	/*
		Login User
	*/
	function loginUser($_username, $password)
	{
		$returnValue											=	0;
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$mysql_keys		=	getKeys();
			
			if(isSet($_username) && isSet($password))
			{
				$_passwort 		= 	crypt($password, $password);
				$data 			= 	$databaseConnection->prepare("SELECT pk_client, benutzer, last_login, benutzer_blocked FROM main_clients  WHERE benutzer=:user AND password=:password LIMIT 1");
				
				if ($data->execute(array(":user"=>$_username, ":password"=>$_passwort)))
				{
					if ($data->rowCount() > 0)
					{
						$returnValue							=	$data->rowCount();
						$result 								= 	$data->fetch(PDO::FETCH_ASSOC);
						$_SESSION['user']['id']					=	$result['pk_client'];
						$_SESSION['user']['benutzer']			=	$result['benutzer'];
						$_SESSION['user']['last_login']			=	$result['last_login'];
						$_SESSION['user']['blocked']			=	$result['benutzer_blocked'];
						
						if($_SESSION['user']['blocked'] == 'true')
						{
							writeInLog($_SESSION['user']['benutzer'], "Try to login, but is blocked!", true);
							
							$_SESSION = array();
							
							if (ini_get("session.use_cookies")) {
								$params = session_get_cookie_params();
								setcookie(session_name(), '', time() - 42000, $params["path"],
									$params["domain"], $params["secure"], $params["httponly"]
								);
							}
							
							session_destroy();
							
							$returnValue						=	1337;
						}
						else
						{
							writeInLog($_SESSION['user']['benutzer'], "Is logging in!", true);
							
							$_SESSION['login'] 					= 	$mysql_keys['login_key'];
							$_SESSION['agent']					=	md5($_SERVER['HTTP_USER_AGENT']);
							
							if($databaseConnection->exec("UPDATE main_clients SET last_login='". date("d.m.Y - H:i", time()) . "' WHERE pk_client='" . $_SESSION['user']['id'] . "'") === false)
							{
								writeInLog(2, "loginUser (SQL Error):".$databaseConnection->errorInfo()[2]);
							};
						};
					};
				}
				else
				{
					writeInLog(2, "loginUser (SQL Error):".$data->errorInfo()[2]);
				};
			};
		};
		
		return $returnValue;
	};
	
	/*
		Check Username if he is already exists
	*/
	function checkUsername($name, $withPk = false)
	{
		if($name != '')
		{
			if(($databaseConnection = getSqlConnection(false)) !== false)
			{
				$_sql 			= 	"SELECT * FROM main_clients  WHERE benutzer='$name'";
				
				if (($data = $databaseConnection->query($_sql)) !== false)
				{
					if ($data->rowCount() > 0)
					{
						if($withPk)
						{
							$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
							
							foreach($result AS $data)
							{
								return $data["pk_client"];
							};
						}
						else
						{
							return true;
						};
					}
					else
					{
						if($withPk)
						{
							return "User does not exist!";
						};
					};
				}
				else
				{
					writeInLog(2, "checkUsername (SQL Error):".$databaseConnection->errorInfo()[2]);
				};
			};
		};
		
		return false;
	};
	
	/*
		Get all Rightkeys
	*/
	function getKeys()
	{
		$returnData													=	array();
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			
			if (($data = $databaseConnection->query("SELECT * FROM  main_rights")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $keys)
					{
						$returnData[$keys['rights_name']]			=	$keys['pk_rights'];
					};
					
					return $returnData;
				}
				else
				{
					writeInLog(5, "getKeys: No Keys found!");
					return $returnData;
				};
			}
			else
			{
				writeInLog(2, "getKeys (SQL Error):".$databaseConnection->errorInfo()[2]);
				return $returnData;
			};
		};
	};
	
	/*
		Get all blocked edit rights (with name & keys)
	*/
	function getBlockedServerEditRights()
	{
		$mysql_keys				=	array();
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_sql 				= 	"SELECT * FROM  main_rights_server_edit";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $keys)
					{
						$mysql_keys[$keys['rights_name']]			=	$keys['pk_rights'];
					};
				}
				else
				{
					writeInLog(5, "getBlockedServerEditRights: No Keys found!");
				};
			}
			else
			{
				writeInLog(2, "getBlockedServerEditRights (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return $mysql_keys;
	};
	
	/*
		Get Modulinformations
	*/
	function getModuls($withInstanzCheck = true)
	{
		$returnData													=	array();
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if (($data = $databaseConnection->query("SELECT * FROM  main_modul")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $keys)
					{
						$returnData[$keys['modul']]					=	$keys['active'];
					};
					
					if((MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "") && $withInstanzCheck)
					{
						$returnData['masterserver']					=	"false";
					};
					
					return $returnData;
				}
				else
				{
					writeInLog(5, "getKeys: No Keys found!");
					return $returnData;
				};
			}
			else
			{
				writeInLog(2, "getKeys (SQL Error):".$databaseConnection->errorInfo()[2]);
				return $returnData;
			};
		};
	};
	
	/*
		Get all Userrights
	*/
	function getUserRights($type, $key, $withTime = true, $just = "all")
	{
		$returnData						=		array();
		if(($type =='pk' || $type == 'name') && $key != '')
		{
			if(($databaseConnection = getSqlConnection(false)) !== false)
			{
				if($type == 'name')
				{
					$_sql				=		"SELECT rights_name,pk_rights,access_instanz,access_ports,timestamp FROM main_clients m_client INNER JOIN main_clients_rights m_client_r ON (m_client.pk_client = m_client_r.fk_clients) INNER JOIN main_rights m_rights ON (m_client_r.fk_rights = m_rights.pk_rights) WHERE m_client.benutzer='" . $key . "'";
				}
				else
				{
					$_sql				=		"SELECT rights_name,pk_rights,access_instanz,access_ports,timestamp FROM main_clients m_client INNER JOIN main_clients_rights m_client_r ON (m_client.pk_client = m_client_r.fk_clients) INNER JOIN main_rights m_rights ON (m_client_r.fk_rights = m_rights.pk_rights) WHERE m_client_r.fk_clients='" . $key . "'";
				};
				
				if (($data = $databaseConnection->query($_sql)) !== false)
				{
					if ($data->rowCount() > 0)
					{
						$result 		= 		$data->fetchAll(PDO::FETCH_ASSOC);
						$time 			= 		time();
						
						switch($just)
						{
							case "ports":
								foreach($result AS $row)
								{
									if($row['access_instanz'] != "not_needed")
									{
										$returnData[$row['rights_name']]['key']							=	$row['pk_rights'];
										$returnData[$row['rights_name']][$row['access_instanz']]		= 	$row['access_ports'];
									};
								};
								break;
							case "global":
								foreach($result AS $row)
								{
									if($row['access_instanz'] == "not_needed")
									{
										if($withTime && ($row['timestamp'] > $time || $row['timestamp'] == "0"))
										{
											$returnData[$row['rights_name']]						=	$row['pk_rights'];
										}
										else if(!$withTime)
										{
											$returnData[$row['rights_name']]						=	$row['pk_rights'];
										};
									};
								};
								break;
							case "time":
								foreach($result AS $row)
								{
									if($row['access_instanz'] == "not_needed")
									{
										$returnData[$row['rights_name']]['key']							=	$row['pk_rights'];
										$returnData[$row['rights_name']]['time']						= 	$row['timestamp'];
									};
								};
								break;
							default:
								foreach($result AS $row)
								{
									if($withTime && ($row['timestamp'] > $time || $row['timestamp'] == "0"))
									{
										$returnData[$row['rights_name']]['key']						=	$row['pk_rights'];
										$returnData[$row['rights_name']][$row['access_instanz']]	=	$row['access_ports'];
									}
									else if(!$withTime)
									{
										$returnData[$row['rights_name']]['key']						=	$row['pk_rights'];
										$returnData[$row['rights_name']][$row['access_instanz']]	=	$row['access_ports'];
									};
								};
								break;
						};
						
						return $returnData;
					}
					else
					{
						return $returnData;
					};
				}
				else
				{
					writeInLog(2, "getUserRights (SQL Error):".$databaseConnection->errorInfo()[2]);
					return $returnData;
				};
			}
			else
			{
				return $returnData;
			};
		}
		else
		{
			return $returnData;
		};
	};
?>