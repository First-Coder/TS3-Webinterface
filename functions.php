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
		Installed Webinterface version
	*/
	define("INTERFACE_VERSION", "1.1.8-OPEN-BETA");
	
	/*
		Session start
	*/
	session_start();
	
	/*
		Includes
	*/
	require_once("config.php");
	require_once("lang.php");
	require_once("ts3admin.class.php");
	
	function getFilesize($byte)
	{
		if($byte < 1024)
		{
			$ergebnis 	= 	round($byte, 2). ' Byte'; 
		}
		else if($byte >= 1024 and $byte < pow(1024, 2))
		{ 
			$ergebnis 	= 	round($byte/1024, 2).' KByte'; 
		}
		else if($byte >= pow(1024, 2) and $byte < pow(1024, 3))
		{ 
			$ergebnis 	= 	round($byte/pow(1024, 2), 2).' MByte'; 
		}
		else
		{ 
			$ergebnis 	= 	round($byte/pow(1024, 3), 2).' GByte'; 
		};
		
		return $ergebnis; 
	};
	
	/*
		Get Random Keys
	*/
	function guid()
	{
		if (function_exists('com_create_guid'))
		{
			return com_create_guid();
		}
		else
		{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
			return $uuid;
		}
	}
	
	/*
		Funktion: Logfile
	*/
	function writeInLog($loglevel, $logtext, $userlog = false)
	{
		if($userlog)
		{
			$file 			= 	'logs/user';
		}
		else
		{
			$file 			= 	'logs/system';
		};
		
		if ($loglevel == 1)
		{
			$loglevel 	= 	"\tCRITICAL\t";
		}
		elseif ($loglevel == 2)
		{
			$loglevel	= 	"\tERROR\t\t";
		}
		elseif ($loglevel == 3)
		{
			$loglevel 	= 	"\tWARNING\t\t";
		}
		elseif ($loglevel == 4)
		{
			$loglevel 	=	"\tNOTICE\t\t";
		}
		elseif ($loglevel == 5)
		{
			$loglevel 	= 	"\tINFO\t\t";
		};
		
		$date			=	date("Y-m-d H:i:s");
		
		$input 		= 	$date.$loglevel.$logtext."\n";

		$loghandle = fopen($file.".log", 'a');
		fwrite($loghandle, $input);
		if (filesize($file.".log") > 10242880) // 10MB
		{
			fwrite($loghandle, $date."\tNOTICE\t\tLogfile filesie of 10 MiB reached.. Rotate logfile.\n");
			
			$zip = new ZipArchive();
			$filename = "./$file.zip";

			if ($zip->open($filename, ZipArchive::CREATE) !== TRUE)
			{
				fwrite($loghandle, $date."\tNOTICE\t\tcannot open <$filename>\n");
				fclose($loghandle);
				exit();
			}
			else
			{
				fclose($loghandle);
			};
			
			$zip->addFromString("log_".date("j_m_Y").".log", file_get_contents($file.".log"));
			$zip->close();
			
			unlink($file.".log");
		};
		
		if(file_exists($file.".zip"))
		{
			if (filesize($file.".zip") > 20485760) // 20 MB
			{
				unlink($file.".zip");
			};
		};
	};
	
	/*
		Change config settings
	*/
	function setConfigSettings($heading, $chatname, $instanz, $port, $mail)
	{
		if($instanz == "nope")
		{
			$instanz			=	"";
		};
		
		if($port == "nope")
		{
			$port				=	"";
		};
		
		// Config.php bearbeiten
		$file					=	file("config.php");
		$new_file				=	array();
		
		// Search Variabeln
		$search_chatname		=	'TS3_CHATNAME';
		$search_heading			=	'HEADING';
		$search_instanz			=	'MASTERSERVER_INSTANZ';
		$search_port			=	'MASTERSERVER_PORT';
		$search_mail			=	'MAILADRESS';
		
		// Dateiliste neu schreiben
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], $search_heading))
			{
				$new_file[$i]		=	"\tdefine(\"HEADING\", \"" . $heading . "\");\n";
			}
			elseif (strpos($file[$i], $search_chatname))
			{
				$new_file[$i]		=	"\tdefine(\"TS3_CHATNAME\", \"" . $chatname . "\");\n";
			}
			elseif (strpos($file[$i], $search_instanz))
			{
				$new_file[$i]		=	"\tdefine(\"MASTERSERVER_INSTANZ\", \"" . $instanz . "\");\n";
			}
			elseif (strpos($file[$i], $search_port))
			{
				$new_file[$i]		=	"\tdefine(\"MASTERSERVER_PORT\", \"" . $port . "\");\n";
			}
			elseif (strpos($file[$i], $search_mail))
			{
				$new_file[$i]		=	"\tdefine(\"MAILADRESS\", \"" . $mail . "\");\n";
			}
			else
			{
				$new_file[$i]		=	$file[$i];
			};
		};
		
		// Dateien übertragen
		file_put_contents("config.php", "");
		file_put_contents("config.php", $new_file);
		
		writeInLog(4, $_SESSION['user']['benutzer'].": Changed the Configfile!", true);
		
		return true;
	};
	
	function checkNewVersion($widthInfo = true)
	{
		try
		{
			$client = new SoapClient(null, array(
				'location' => 'http://wiki.first-coder.de/soap/soap_server.php',
				'uri' => 'https://wiki.first-coder.de/soap/soap_server.php'
			));
			
			return $client->getNewestVersion();
		}
		catch(Exception $e)
		{
			if($widthInfo)
			{
				return "<font style=\"color: rgb(220,0,0) !important;font-weight: bold;\">Connection to Updateserver lost!</font>";
			}
			else
			{
				return INTERFACE_VERSION;
			};
		};
	};
	
	/*
		Create News
	*/
	function createNews($title, $subtitle, $content)
	{
		$fileContent 				= 	array();
		$fileContent["title"]		=	$title;
		$fileContent["subtitle"]	=	$subtitle;
		$fileContent["content"]		=	$content;
		
		if($title == "" || $content == "")
		{
			return false;
		}
		else
		{
			writeInLog(4, $_SESSION['user']['benutzer'].": Create News \"".$title."\"!", true);
			
			return file_put_contents("news/".time().".json", json_encode($fileContent));
		};
	};
	
	/*
		Delete News
	*/
	function deleteNews($file)
	{
		writeInLog(4, $_SESSION['user']['benutzer'].": Delete News!", true);
		
		return unlink("news/".$file);
	};
	
	/*
		Profile Edit
	*/
	function updateUser($id, $content, $adminPk, $pk)
	{
		writeInLog(4, $_SESSION['user']['benutzer'].": Profil ('".$id."') will be updated!", true);
		
		include("_mysql.php");
		
		if($pk != "")
		{
			$mysql_keys			=		getKeys();
			$user_right			=		getUserRightsWithTime(getUserRights('pk', $adminPk));
			if($user_right['right_hp_user_edit'] != $mysql_keys['right_hp_user_edit'])
			{
				return false;
			};
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
		
		// Updatebefehl in MySQL umsetzen // NUR eine Sache aktualisieren
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
			return false;
		};
	};
	
	/*
		Get Userinformations
	*/
	function getUserInformations($pk)
	{
		include("_mysql.php");
		
		$informations									=	array();
		
		// Befehl für die MySQL Datenbank
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
		
		return $informations;
	};
	
	/*
		Get Username from pk
	*/
	function getUsernameFromPk($pk)
	{
		if($pk == "")
		{
			return false;
		};
		
		include("_mysql.php");
		
		// Befehl für die MySQL Datenbank
		$_sql 			= 	"SELECT benutzer FROM  main_clients WHERE pk_client='".$pk."' LIMIT 1";
		
		if (($data = $databaseConnection->query($_sql)) !== false)
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
		Get Modulinformations
	*/
	function getModuls()
	{
		include("_mysql.php");
		
		$mysql_modul									=	array();
		
		// Befehl für die MySQL Datenbank
		$_sql 			= 	"SELECT * FROM  main_modul";
		
		if (($data = $databaseConnection->query($_sql)) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 								= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($result AS $keys)
				{
					$mysql_modul[$keys['modul']]		=	$keys['active'];
				};
				
				if(MASTERSERVER_INSTANZ == "" || MASTERSERVER_PORT == "")
				{
					$mysql_modul['masterserver']		=	false;
				};
			}
			else
			{
				writeInLog(5, "getModuls: No Moduls found!");
			};
		}
		else
		{
			writeInLog(2, "getModuls (SQL Error):".$databaseConnection->errorInfo()[2]);
		};
		
		return $mysql_modul;
	};
	
	/*
		Get all Rightkeys
	*/
	function getKeys()
	{
		include("_mysql.php");
		
		$mysql_keys												=	array();
		
		// Befehl für die MySQL Datenbank
		$_sql 			= 	"SELECT * FROM  main_rights";
		
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
				writeInLog(5, "getKeys: No Keys found!");
			};
		}
		else
		{
			writeInLog(2, "getKeys (SQL Error):".$databaseConnection->errorInfo()[2]);
		};
		
		return $mysql_keys;
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
		Get all blocked edit rights (with name & keys)
	*/
	function getBlockedServerEditRights()
	{
		include("_mysql.php");
		
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
		
		return $mysql_keys;
	};
	
	/*
		Get all blocked Client edit rights (with keys)
	*/
	function getClientBlockedServerEditRights($pk, $instanz, $port)
	{
		include("_mysql.php");
		
		$_sql 																	= 	"SELECT * FROM  main_clients_rights_server_edit WHERE fk_clients='".$pk."' AND access_instanz='".$instanz."'";
		
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
		
		return $blockedServerEditRights;
	};
	
	/*
		Get Backups
	*/
	function getBackups($path)
	{
		$files	=	array();
		$i		=	0;
		
		// Ist es ein Ordner
		if(is_dir($path))
		{
			// Ordner öffnen
			if($handle = opendir($path))
			{
				// Datei lesen
				while (($file = readdir($handle)) !== false)
				{
					// Nur .json Dateien zurück geben
					if(strpos($file, '.json') !== false)
					{
						$files[$i]	=	$file;
						$i++;
					};
				};
				closedir($handle);
			};
		};
		
		return $files;
	};
	
	/*
		Set Language
	*/
	function setLanguage($lang)
	{
		writeInLog(4, $_SESSION['user']['benutzer'].": Change the Language to ".$lang."!", true);
		
		$file					=	file("config.php");
		$new_file				=	array();
		
		// Search Variabeln
		$search_lang			=	'LANGUAGE';
		
		// Dateiliste neu schreiben
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], $search_lang))
			{
				$new_file[$i]		=	"\tdefine(\"LANGUAGE\", \"" . $lang . "\");\n";
			}
			else
			{
				$new_file[$i]		=	$file[$i];
			};
		};
		
		// Dateien übertragen
		file_put_contents("config.php", "");
		file_put_contents("config.php", $new_file);
		
		echo "done";
	};
	
	/*
		Delete data
	*/
	function deleteData($link)
	{
		return unlink($link);
	};
	
	/*
		Login User
	*/
	function loginUser($_username, $password)
	{
		include("_mysql.php");
		
		$mysql_keys		=	getKeys();
		
		if(isSet($_username) && isSet($password))
		{
			$_passwort 		= 	crypt($password, $password);
			
			// Befehl für die MySQL Datenbank
			$_sql 			= 	"SELECT pk_client, benutzer, last_login, benutzer_blocked FROM main_clients  WHERE benutzer=:user AND password=:password LIMIT 1";
			$data 			= 	$databaseConnection->prepare($_sql);
			  
			if ($data->execute(array(":user"=>$_username, ":password"=>$_passwort)))
			{
				if ($data->rowCount() > 0)
				{
					$_is_logged								=	$data->rowCount();
					$result 								= 	$data->fetch(PDO::FETCH_ASSOC);
					
					// Daten in einem Array speichern um es später auszuwerten
					$_SESSION['user']['id']					=	$result['pk_client'];
					$_SESSION['user']['benutzer']			=	$result['benutzer'];
					$_SESSION['user']['last_login']			=	$result['last_login'];
					$_SESSION['user']['blocked']			=	$result['benutzer_blocked'];
					
					// $_SESSION löschen
					if($_SESSION['user']['blocked'] == 'true')
					{
						writeInLog(4, $_SESSION['user']['benutzer'].": Try to login, but is blocked!", true);
						
						$_SESSION = array();
						
						if (ini_get("session.use_cookies")) {
							$params = session_get_cookie_params();
							setcookie(session_name(), '', time() - 42000, $params["path"],
								$params["domain"], $params["secure"], $params["httponly"]
							);
						}
						
						session_destroy();
						
						// Da geblockt eine Info zurück geben
						$_is_logged							=	1337;
					}
					else
					{
						writeInLog(4, $_SESSION['user']['benutzer'].": Is logging in!", true);
						
						// In der Session merken, dass der User eingeloggt ist !
						$_SESSION['login'] 					= 	$mysql_keys['login_key'];
						
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
		
		return $_is_logged;
	};
	
	/*
		Create User
	*/
	function createUser($_username, $password, $returnPk = false, $cryptPw = false)
	{
		if($_username != '' && $password != '')
		{
			include("_mysql.php");
			
			if($cryptPw)
			{
				$_passwort 		= 	$password;
			}
			else
			{
				$_passwort 		= 	crypt($password,$password);
			};
			
			// Prüfen ob Benutzer schon existiert
			$_sql 			= 	"SELECT * FROM main_clients  WHERE benutzer=:user";
			$data 			= 	$databaseConnection->prepare($_sql);

			if ($data->execute(array(":user"=>$_username)))
			{
				if ($data->rowCount() == 0)
				{
					$newPk	=	guid();
					$status	=	true;
					$insert	= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, benutzer, password, last_login, benutzer_blocked) VALUES (\'' . $newPk . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
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
						writeInLog(4, $_SESSION['user']['benutzer'].": Create a Client called '".$_username."'!", true);
						
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
						return "";
					};
				}
				else
				{
					return "User already exists!";
				};
			}
			else
			{
				writeInLog(2, "createUser (SQL Error):".$data->errorInfo()[2]);
			};
		}
		else
		{
			return "No Username or no Password!";
		};
	};
	
	/*
		Check Username if he is already exists
	*/
	function checkUsername($name, $withPk = false)
	{
		if($name != '')
		{
			include("_mysql.php");
			
			// Prüfen ob Benutzer schon existiert
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
					}
					else
					{
						return false;
					};
				};
			}
			else
			{
				writeInLog(2, "checkUsername (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Delete all Users
	*/
	function deleteAllUser($_username, $password)
	{
		if($_username != '' && $password != '')
		{
			include("_mysql.php");
			
			$status				=	true;
			$status2			=	true;
			
			if($databaseConnection->exec('TRUNCATE TABLE main_clients;') === false)
			{
				writeInLog(2, "deleteAllUser (SQL Error):".$databaseConnection->errorInfo()[2]);
				$status			=	false;
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
					writeInLog(4, $_SESSION['user']['benutzer'].": Delete all users and create a new one called '".$_username."'!", true);
					
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
					
					$status2	=	giveUserAllRights($key);
					
					if($status && $status2)
					{
						return "done";
					}
					else if($status2 == false)
					{
						return "Rechte konnten nicht erteilt werden :/";
					};
				};
			}
			else
			{
				return "Datenbank konnte nicht geloescht werden :/";
			};
		};
	};
	
	/*
		Delete one User
	*/
	function deleteUser($pk)
	{
		if($pk != '')
		{
			include("_mysql.php");
			
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Delete a Client with the pk '".$pk."'!", true);
				
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		};
	};
	
	/*
		Give a User all Rights
	*/
	function giveUserAllRights($pk)
	{
		if($pk != '')
		{
			include("_mysql.php");
			
			$status				=	true;
			$right_key			=	array();
			
			// Wichtige Informationen abfragen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Give Global Rights to '".$pk."'!", true);
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
		Give User all Rights in a Teamspeak 3 Server
	*/
	function giveUserAllRightsTSServer($pk, $instanz, $port)
	{
		if($pk != '' && $instanz != '' && $port != '')
		{
			include("_mysql.php");
			
			// Keys abfragen
			$mysql_keys					=		getKeys();
			
			$status						=		true;
			
			foreach($mysql_keys AS $text_key => $key)
			{
				if(strpos($text_key, 'right_web_') !== false && $text_key != 'right_web_server_create' && $text_key != 'right_web_server_delete' && $text_key != 'right_web_global_message_poke')
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Give all Teamspeakrights (Instanz: '".$instanz."', Port: '".$port."') to '".$pk."'!", true);
				
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
		Get all Userrights
	*/
	// Type: 	PK:		Identnummer
	// 			Name:	Nickname
	function getUserRights($type, $key)
	{
		if(($type =='pk' || $type == 'name') && $key != '')
		{
			include("_mysql.php");
			
			if($type == 'name')
			{
				$_sql			=		"SELECT * FROM main_clients m_client INNER JOIN main_clients_rights m_client_r ON (m_client.pk_client = m_client_r.fk_clients) INNER JOIN main_rights m_rights ON (m_client_r.fk_rights = m_rights.pk_rights) WHERE m_client.benutzer='" . $key . "'";
			}
			else
			{
				$_sql			=		"SELECT * FROM main_clients m_client INNER JOIN main_clients_rights m_client_r ON (m_client.pk_client = m_client_r.fk_clients) INNER JOIN main_rights m_rights ON (m_client_r.fk_rights = m_rights.pk_rights) WHERE m_client_r.fk_clients='" . $key . "'";
			};
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$_user 																				= 	array();
					$_user['ports']																		=	array();
					$_user['time']																		=	array();
					
					$result 																			= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $row)
					{
						// Rechtenamen
						$_user[$row['rights_name']] 													= 	$row['pk_rights'];
						$_user['ports'][$row['rights_name']][$row['access_instanz']]	 				= 	$row['access_ports'];
						$_user['time'][$row['rights_name']]												=	$row['timestamp'];
					};
					
					return $_user;
				}
				else
				{
					return 0;
				};
			}
			else
			{
				writeInLog(2, "getUserRights (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		}
		else
		{
			return 0;
		};
	};
	
	/*
		Will remove that Rights they are out of date
	*/
	function getUserRightsWithTime($key)
	{
		if($key != '')
		{
			$time = time();
			foreach($key AS $key_name => $inhalt)
			{
				if(!(!is_array($inhalt) && ($key['time'][$key_name] == 0 || $key['time'][$key_name] > $time)))
				{
					if(!is_array($inhalt))
					{
						unset ($key[$key_name], $key['ports'][$key_name]);
					};
				};
			};
			return $key;
		}
		else
		{
			return 0;
		};
	};
	
	/*
		Get Information above a blocked user
	*/
	function getUserBlock($pk)
	{
		$_blocked_infos			=	array();
		$blocked				=	array();
		
		if($pk != '')
		{
			include("_mysql.php");
			
			$_sql				=	"SELECT benutzer_blocked,blocked_until FROM main_clients WHERE pk_client='" . $pk . "' LIMIT 1";
			
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
					
					return $_blocked_infos;
				}
				else
				{
					$_blocked_infos['blocked']		=	"false";
					$_blocked_infos['until']		=	0;
					
					return $_blocked_infos;
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
			$_blocked_infos['until']		=	0;
			
			return $_blocked_infos;
		};
	};
	
	/*
		Checking if user has right on a spezific instanz
	*/
	function hasUserInstanz($pk, $instanz)
	{
		if($pk != '' && $instanz != '')
		{
			include("_mysql.php");
			
			$has_instanz		=		false;
			
			$_sql 				= 		"SELECT * FROM  main_clients_rights WHERE fk_clients='" . $pk . "';";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 						= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $row)
					{
						if($row['access_instanz'] == $instanz)
						{
							$has_instanz	=	true;
						};
					};
					
					return $has_instanz;
				}
				else
				{
					return false;
				};
					
			}
			else
			{
				writeInLog(2, "hasUserInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Change the Homepage theme
	*/
	function setTheme($cssFile)
	{
		$cssFile				=	substr($cssFile, 0, -4);
		
		$file					=	file("config.php");
		$new_file				=	array();
		
		// Search Variabeln
		$search_lang			=	'STYLE';
		
		// Dateiliste neu schreiben
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], $search_lang))
			{
				$new_file[$i]		=	"\tdefine(\"STYLE\", \"" . $cssFile . "\");\n";
			}
			else
			{
				$new_file[$i]		=	$file[$i];
			};
		};
		
		// Dateien übertragen
		file_put_contents("config.php", "");
		file_put_contents("config.php", $new_file);
		
		writeInLog(4, $_SESSION['user']['benutzer'].": Change the theme to '".$cssFile."'!", true);
		
		return true;
	}
	
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
		Change Modul: Free Register
	*/
	function setModulFreeRegister($value)
	{
		if($value != '')
		{
			$status			=		setModul($value, "free_register");
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Set Modul \"Free Register\" to '".$value."'!", true);
				
				echo 'done';
			};
		}
		else
		{
			return "No Value!";
		};
	};
	
	/*
		Change Modul: Masterserver
	*/
	function setModulMasterserver($value)
	{
		if($value != '')
		{
			$status			=		setModul($value, "masterserver");
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Set Modul \"Masterserver\" to '".$value."'!", true);
				
				echo 'done';
			};
		}
		else
		{
			return "No Value!";
		};
	};
	
	/*
		Change Modul: Server Application
	*/
	function setModulFreeTS3ServerApplication($value)
	{
		if($value != '')
		{
			$status			=		setModul($value, "free_ts3_server_application");
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Set Modul \"Free Register\" to '".$value."'!", true);
				
				echo 'done';
			};
		}
		else
		{
			return "No Value!";
		};
	};
	
	/*
		Change Modul: Write News
	*/
	function setModulWriteNews($value)
	{
		if($value != '')
		{
			$status			=		setModul($value, "write_news");
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Set Modul \"Write News\" to '".$value."'!", true);
				
				echo 'done';
			};
		}
		else
		{
			return "No Value!";
		};
	};
	
	/*
		Change Modul: Webinterface
	*/
	function setModulWebinterface($value)
	{
		if($value != '')
		{
			$status			=		setModul($value, "webinterface");
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Set Modul \"Webinterface\" to '".$value."'", true);
				
				echo 'done';
			};
		}
		else
		{
			return "No Value!";
		};
	};
	
	/*
		Change Modul: Set Modul
	*/
	function setModul($value, $modul)
	{
		include("_mysql.php");
		
		if($databaseConnection->exec("UPDATE main_modul SET active='".$value."' WHERE modul='".$modul."'") === false)
		{
			writeInLog(2, "setModul (SQL Error) [Modul: ".$modul."]:".$databaseConnection->errorInfo()[2]);
			return false;
		}
		else
		{
			return true;
		};
	};
	
	/*
		Get all User Information
	*/
	function getUsers()
	{
		include("_mysql.php");
		
		$_sql		=	"SELECT benutzer, pk_client, last_login, benutzer_blocked FROM main_clients";
		
		if (($data = $databaseConnection->query($_sql)) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 	= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
				return $result;
			}
			else
			{
				return "No Users!";
			};
		}
		else
		{
			writeInLog(2, "getUsers (SQL Error):".$databaseConnection->errorInfo()[2]);
		};
	};
	
	/*
		Instanz will be edit
	*/
	function writeInstanz($instanz, $what, $content)
	{
		global $ts3_server;
		
		if($instanz != '' && $what != '' && $content != '')
		{
			$file					=	file("config_instanz.php");
			$new_file				=	array();
			
			// Search Variabeln
			$search					=	'$ts3_server[' . $instanz . '][\'' . $what . '\']';
			
			// Dateiliste neu schreiben
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
			
			// Dateien übertragen
			file_put_contents("config_instanz.php", "");
			file_put_contents("config_instanz.php", $new_file);
			
			// Check Login
			if ($what == 'ip')
			{
				$ip				=	$content;
			}
			else
			{
				$ip				=	$ts3_server[$instanz]['ip'];
			};
			
			if ($what == 'queryport')
			{
				$queryport		=	$content;
			}
			else
			{
				$queryport		=	$ts3_server[$instanz]['queryport'];
			};
			
			if ($what == 'user')
			{
				$user			=	$content;
			}
			else
			{
				$user			=	$ts3_server[$instanz]['user'];
			};
			
			if ($what == 'pw')
			{
				$pw				=	$content;
			}
			else
			{
				$pw				=	$ts3_server[$instanz]['pw'];
			};
			
			writeInLog(4, $_SESSION['user']['benutzer'].": Has Added the Instanz \"".$instanz."\"", true);
			
			$tsAdmin = new ts3admin($ip, $queryport);
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$check_login			=	$tsAdmin->login($user, $pw);
				
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
		Create a new Instanz
	*/
	function createInstanz($alias, $ip_address, $queryport, $user, $pw)
	{
		if($ip_address != '' && $queryport != '' && $user != '')
		{
			global $ts3_server;
			
			$file					=	file("config_instanz.php");
			$new_file				=	array();
			
			// Dateiliste neu schreiben
			for ($zeile = 0;$zeile < count($file);$zeile++)
			{
				$new_file[$zeile]	=	$file[$zeile];
			};
			
			// Neu Instanz anhängen
			$new_zeile				=	$zeile - 1;
			$new_instanz    		=	count($ts3_server);
			$new_file[$new_zeile]	=	"\n"; $new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['alias']\t\t= '" . htmlspecialchars($alias) . "';\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['ip']\t\t= '" . htmlspecialchars($ip_address) . "';\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['queryport']\t= " . htmlspecialchars($queryport) . ";\n"; 	$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['user']\t\t= '" . htmlspecialchars($user) . "';\n"; 			$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['pw']\t\t= '" . htmlspecialchars($pw) . "';\n"; 				$new_zeile++;
			$new_file[$new_zeile]	=	"?>";
			
			// Dateien übertragen
			file_put_contents("config_instanz.php", "");
			file_put_contents("config_instanz.php", $new_file);
			
			writeInLog(4, $_SESSION['user']['benutzer'].": Has Added the Instanz \"".$ip_address."\"", true);
			
			$tsAdmin = new ts3admin($ip_address, $queryport);
		
			if($tsAdmin->getElement('success', $tsAdmin->connect()))
			{
				$check_login			=	$tsAdmin->login($user, $pw);
				
				$tsAdmin->logout();
			};
			
			if($check_login)
			{
				return "done";
			}
			else
			{
				return "Connection was not Successful!";
			};
		}
		else
		{
			return "Not enough Parameters!";
		};
	};
	
	/*
		Delete a Instanz
	*/
	function deleteInstanz($ts3_server_del)
	{
		if($ts3_server_del != '')
		{
			global $ts3_server;
			$ts3_server_count			=	count($ts3_server)-1;
			
			// Datenverbindung herstellen
			include("_mysql.php");
			
			// Instanz aus Datenbank entfernen
			if($databaseConnection->exec('DELETE FROM main_clients_rights WHERE access_instanz!=\'\' AND access_instanz!=\'not_needed\' AND access_instanz=\'' . $ts3_server_del . '\';') === false)
			{
				writeInLog(2, "deleteInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
			
			// Instanz abändern
			if($databaseConnection->exec('UPDATE main_clients_rights SET access_instanz=' . $ts3_server_del . ' WHERE access_instanz!=\'\' AND access_instanz!=\'not_needed\' AND access_instanz=\'' . $ts3_server_count . '\';') === false)
			{
				writeInLog(2, "deleteInstanz (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			};
			
			// Datei bearbeiten
			if($ts3_server_del < $ts3_server_count)
			{
				$file					=	file("config_instanz.php");
				$new_file				=	array();
				
				// Search Variabeln
				$search_alias			=	'$ts3_server[' . $ts3_server_del . '][\'alias\']';
				$search_ip				=	'$ts3_server[' . $ts3_server_del . '][\'ip\']';
				$search_queryport		=	'$ts3_server[' . $ts3_server_del . '][\'queryport\']';
				$search_user			=	'$ts3_server[' . $ts3_server_del . '][\'user\']';
				$search_pw				=	'$ts3_server[' . $ts3_server_del . '][\'pw\']';
				
				// Dateiliste neu schreiben
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
				
				// Die letzten Zeichen löschen lassen
				for ($i = 0; $i < 7; $i++)
				{
					array_pop($new_file);
				};
				array_push($new_file, "?>");
				
				// Dateien übertragen
				file_put_contents("config_instanz.php", "");
				file_put_contents("config_instanz.php", $new_file);
				
				writeInLog(4, $_SESSION['user']['benutzer'].": Has deleted the Instanz \"".$ts3_server_del."\"", true);
				
				return true;
			}
			else
			{
				$file					=	file("config_instanz.php");
				$new_file				=	array();
				
				// Search Variabeln
				$search_alias			=	'$ts3_server[' . $ts3_server_del . '][\'alias\']';
				$search_ip				=	'$ts3_server[' . $ts3_server_del . '][\'ip\']';
				$search_queryport		=	'$ts3_server[' . $ts3_server_del . '][\'queryport\']';
				$search_user			=	'$ts3_server[' . $ts3_server_del . '][\'user\']';
				$search_pw				=	'$ts3_server[' . $ts3_server_del . '][\'pw\']';
				
				// Dateiliste neu schreiben
				for ($i = 0; $i < count($file); $i++)
				{
					// Leerzeichen löschen lassen...
					if(strpos($file[$i], $search_alias))
					{
						unset($new_file[$i-1]);
						$new_file 			= 	array_values($new_file);  
					};
					
					// Text schreiben wenn nicht...
					if(!(strpos($file[$i], $search_alias) || strpos($file[$i], $search_ip) || strpos($file[$i], $search_queryport) || strpos($file[$i], $search_user) || strpos($file[$i], $search_pw)))
					{
						$new_file[$i]		=	$file[$i];
					};
				};
				
				// Dateien übertragen
				file_put_contents("config_instanz.php", "");
				file_put_contents("config_instanz.php", $new_file);
				
				writeInLog(4, $_SESSION['user']['benutzer'].": Has deleted the Instanz \"".$ts3_server_del."\"", true);
				
				return true;
			};
		}
		else
		{
			return false;
		};
	};
	
	/*
		Delete a Teamspeakserver in your Database
	*/
	function deletePort($port, $instanz)
	{
		if($port != '' && $instanz != '')
		{
			// Datenverbindung herstellen
			include("_mysql.php");
			
			$status				=	true;
			
			// Befehl für die MySQL Datenbank
			$_sql 				= 		"SELECT * FROM main_clients_rights  WHERE access_instanz='" . $instanz . "'";
			
			if (($data = $databaseConnection->query($_sql)) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$result 	= 	$data->fetchAll(PDO::FETCH_ASSOC);
					
					foreach($result AS $row)
					{
						$_ports			=		$row['access_ports'];
						
						$_rem_ports		=		$port . ",";
						$_new_ports		=		str_replace($_rem_ports, "", $_ports);
						
						if($status)
						{
							if($databaseConnection->exec('UPDATE main_clients_rights SET access_ports=\'' . $_new_ports . '\' WHERE fk_clients=\'' . $row['fk_clients'] . '\' AND fk_rights=\'' . $row['fk_rights'] . '\' AND access_instanz=\'' . $instanz . '\'') === false)
							{
								writeInLog(2, "deletePort (SQL Error):".$databaseConnection->errorInfo()[2]);
								$status		=		false;
							};
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
			
			if($status == true)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Has deleted a Teamspeakport \"".$port."\" \"".$instanz."\"", true);
				
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
		Save Global rights from a user
	*/
	function userEdit($pk, $right, $checkbox, $time)
	{
		if($pk != '' && $right != '' && $checkbox != '' && $time != '')
		{
			// Rechte des Clienten abfragen
			$user_right			=		getUserRights('pk', $pk);
			
			// Keys abfragen
			$mysql_keys			=		getKeys();
			
			$status				=		true;
			
			// Datenverbindung herstellen
			include("_mysql.php");
			
			// Rechte Keys einfügen / entfernen
			if($right != 'benutzer_blocked')
			{
				if($checkbox == 'true')
				{
					if($user_right[$right] != $mysql_keys[$right])
					{
						if($status)
						{
							if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp) VALUES (\'' . $pk . '\', \'' . $mysql_keys[$right] . '\', \'' . $time . '\')') === false)
							{
								writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
								$status		=	false;
							};
						};
					}
					else
					{
						if($status)
						{
							if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=' . $time . ' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys[$right] . '\'') === false)
							{
								writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
								$status		=	false;
							};
						};
					};
				}
				else
				{
					if($user_right[$right] == $mysql_keys[$right])
					{
						if($status)
						{
							if($databaseConnection->exec("DELETE FROM main_clients_rights WHERE fk_clients='" . $pk . "' AND fk_rights='" . $mysql_keys[$right] . "'") === false)
							{
								writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
								$status		=	false;
							};
						};
					};
				};
			}
			else
			{
				if($status)
				{
					if($databaseConnection->exec("UPDATE main_clients SET benutzer_blocked='" . $checkbox . "', blocked_until='" . $time . "' WHERE pk_client='" . $pk . "'") === false)
					{
						writeInLog(2, "userEdit (SQL Error):".$databaseConnection->errorInfo()[2]);
						$status		=	false;
					};
				};
			};
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Edit a Users Global Permissions (".$pk.")", true);
				
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
		Save Teamspeakserver Server Edit rights for a spezfic user
	*/
	function userServerEdit($pk, $port, $instanz, $adminCheckboxRightServerEditPort, $adminCheckboxRightServerEditSlots, $adminCheckboxRightServerEditAutostart, $adminCheckboxRightServerEditMinClientVersion
		, $adminCheckboxRightServerEditMainSettings, $adminCheckboxRightServerEditDefaultServerGroups, $adminCheckboxRightServerEditHostSettings, $adminCheckboxRightServerEditComplaintSettings
		, $adminCheckboxRightServerEditAntiFloodSettings, $adminCheckboxRightServerEditTransferSettings, $adminCheckboxRightServerEditProtokollSettings)
	{
		if($pk != '' && $port != '' && $instanz != '' && $adminCheckboxRightServerEditPort != '' && $adminCheckboxRightServerEditSlots != '' && $adminCheckboxRightServerEditAutostart != ''
			&& $adminCheckboxRightServerEditMinClientVersion != '' && $adminCheckboxRightServerEditMainSettings != '' && $adminCheckboxRightServerEditDefaultServerGroups != '' && $adminCheckboxRightServerEditHostSettings != ''
			&& $adminCheckboxRightServerEditComplaintSettings != '' && $adminCheckboxRightServerEditAntiFloodSettings != '' && $adminCheckboxRightServerEditTransferSettings != '' && $adminCheckboxRightServerEditProtokollSettings != '')
		{
			// Rechte des Clienten abfragen
			$user_right			=		getClientBlockedServerEditRights($pk, $instanz, $port);
			
			// Keys abfragen
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
				writeInLog(4, $_SESSION['user']['benutzer'].": Edit a Users Teamspeak Server Edit spezific Permissions (".$pk.")", true);
				
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
		// Datenverbindung herstellen
		include("_mysql.php");
		
		if($permission != 'true')
		{
			if($user_right === false)
			{
				// Befehl für die MySQL Datenbank
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
		
		return true;
	};
	
	/*
		Save Teamspeakserver rights for a spezfic user
	*/
	function userEditPorts($pk, $server_view, $time_server_view, $teamspeak_port, $teamspeak_instanz, $server_edit, $server_start_stop, $server_msg_poke, $server_mass_actions, $server_protokoll
		, $server_icons, $server_bans, $server_token, $server_filelist, $server_backups, $server_clients, $client_actions, $client_rights, $channel_actions, $time_server_edit, $time_server_start_stop
		, $time_server_msg_poke, $time_server_mass_actions, $time_server_protokoll, $time_server_icons, $time_server_bans, $time_server_token, $time_server_filelist, $time_server_backups, $time_server_clients
		, $time_client_actions, $time_client_rights, $time_channel_actions)
	{
		if($pk != ''   && $teamspeak_port != '' && $teamspeak_instanz != ''
			&& $server_view != '' && $server_edit != '' && $server_start_stop != '' && $server_msg_poke != '' && $server_mass_actions != '' && $server_protokoll != '' && $server_icons != '' && $server_bans != ''
			&& $server_token != '' && $server_filelist != '' && $server_backups != '' && $server_clients != ''
			&& $client_actions != '' && $client_rights != ''
			&& $channel_actions != ''
			&& $time_server_view != '' && $time_server_edit != '' && $time_server_start_stop != '' && $time_server_msg_poke != '' && $time_server_mass_actions != '' && $time_server_protokoll != '' 
			&& $time_server_icons != ''  && $time_server_bans != ''  && $time_server_token != '' && $time_server_filelist != '' && $time_server_backups != ''  && $time_server_clients != '' && $time_client_actions != '' 
			&& $time_client_rights != '' && $time_channel_actions != '')
		{
			// Rechte des Clienten abfragen
			$user_right			=		getUserRights('pk', $pk);
			
			// Keys abfragen
			$mysql_keys			=		getKeys();
			
			$status				=		true;
			
			$status				=		addPortRight($server_view, strpos($user_right['ports']["right_web_server_view"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_view"], $teamspeak_instanz, $teamspeak_port, $time_server_view, $pk);
			
			if($status)
			{
				$status			=		addPortRight($server_edit, strpos($user_right['ports']["right_web_server_edit"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_edit"], $teamspeak_instanz, $teamspeak_port, $time_server_edit, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_start_stop, strpos($user_right['ports']["right_web_server_start_stop"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_start_stop"], $teamspeak_instanz, $teamspeak_port, $time_server_start_stop, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_msg_poke, strpos($user_right['ports']["right_web_server_message_poke"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_message_poke"], $teamspeak_instanz, $teamspeak_port, $time_server_msg_poke, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_mass_actions, strpos($user_right['ports']["right_web_server_mass_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_mass_actions"], $teamspeak_instanz, $teamspeak_port, $time_server_mass_actions, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_protokoll, strpos($user_right['ports']["right_web_server_protokoll"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_protokoll"], $teamspeak_instanz, $teamspeak_port, $time_server_protokoll, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_icons, strpos($user_right['ports']["right_web_server_icons"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_icons"], $teamspeak_instanz, $teamspeak_port, $time_server_icons, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_bans, strpos($user_right['ports']["right_web_server_bans"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_bans"], $teamspeak_instanz, $teamspeak_port, $time_server_bans, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_token, strpos($user_right['ports']["right_web_server_token"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_token"], $teamspeak_instanz, $teamspeak_port, $time_server_icons, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_filelist, strpos($user_right['ports']["right_web_file_transfer"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_file_transfer"], $teamspeak_instanz, $teamspeak_port, $time_server_filelist, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_backups, strpos($user_right['ports']["right_web_server_backups"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_backups"], $teamspeak_instanz, $teamspeak_port, $time_server_backups, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($server_clients, strpos($user_right['ports']["right_web_server_clients"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_server_clients"], $teamspeak_instanz, $teamspeak_port, $time_server_clients, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($client_actions, strpos($user_right['ports']["right_web_client_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_client_actions"], $teamspeak_instanz, $teamspeak_port, $time_client_actions, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($client_rights, strpos($user_right['ports']["right_web_client_rights"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_client_rights"], $teamspeak_instanz, $teamspeak_port, $time_client_rights, $pk);
			};
			if($status)
			{
				$status			=		addPortRight($channel_actions, strpos($user_right['ports']["right_web_channel_actions"][$teamspeak_instanz], $teamspeak_port), $mysql_keys["right_web_channel_actions"], $teamspeak_instanz, $teamspeak_port, $time_channel_actions, $pk);
			};
			
			if($status)
			{
				writeInLog(4, $_SESSION['user']['benutzer'].": Edit a Users Teamspeak Permissions (".$pk.")", true);
				
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
	
	function addPortRight($permission, $user_right, $mysql_keys, $teamspeak_instanz, $teamspeak_port, $permission_time, $pk)
	{
		// Datenverbindung herstellen
		include("_mysql.php");
		
		if($permission == 'true')
		{
			if($user_right === false)
			{
				// Befehl für die MySQL Datenbank
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
						
						if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=\'' . $permission_time . '\', access_ports=\'' . $_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $teamspeak_instanz . '\'') === false)
						{
							writeInLog(2, "userEditPorts (SQL Error):".$databaseConnection->errorInfo()[2]);
							return false;
						};
					}
					else
					{
						if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp, access_instanz, access_ports) VALUES (\'' . $pk . '\', \'' . $mysql_keys . '\', \'' . $permission_time . '\', \'' . $teamspeak_instanz . '\', \'' . $teamspeak_port . ',\')') === false)
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
			// Befehl für die MySQL Datenbank
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
					
					if($databaseConnection->exec('UPDATE main_clients_rights SET timestamp=\'' . $permission_time . '\', access_ports=\'' . $_new_ports . '\' WHERE fk_clients=\'' . $pk . '\' AND fk_rights=\'' . $mysql_keys . '\' AND access_instanz=\'' . $teamspeak_instanz . '\'') === false)
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
?>