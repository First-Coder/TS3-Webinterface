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
	checkSession();
	
	/*
		Reload Site defines
	*/
	define("RELOAD_TO_MAIN", 0);
	define("RELOAD_TO_SERVERVIEW", 1);
	define("RELOAD_THE_SITE", 2);
	
	/*
		Installed Webinterface version
	*/
	define("INTERFACE_VERSION", "1.3.20-STABLE");
	
	/*
		Anti XSS
	*/
	function xssSafe($data, $encoding = 'UTF-8')
	{
		return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
	};
	
	/*
		Anti XSS Echo
	*/
	function xssEcho($data)
	{
		echo xssSafe(str_replace("%", " ", $data));
	};
	
	/*
		Get random password
	*/
	function randomString($laenge)
	{
		$zeichen 	= 	'0123456789';
		$zeichen 	.= 	'abcdefghijklmnopqrstuvwxyz';
		$zeichen 	.= 	'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$str 		= 	'';
		$anz 		= 	strlen($zeichen);
		
		for ($i=0; $i<$laenge; $i++)
		{
			$str 	.= 	$zeichen[rand(0,$anz-1)];
		};
		
		return $str;
	};
	
	/*
		Get Link Informations
	*/
	function getLinkInformations()
	{
		$returnData							=	array();
		if(isSet($_SERVER['REQUEST_URI']))
		{
			$urlData						=	explode("?", $_SERVER['REQUEST_URI']);
			if(count($urlData) >= 3)
			{
				$returnData['instanz']		=	$urlData[2];
				$returnData['sid']			=	$urlData[3];
			}
			else if(isSet($_SERVER['HTTP_REFERER']))
			{
				$urlData					=	explode("?", $_SERVER['HTTP_REFERER']);
				if(count($urlData) >= 3)
				{
					$returnData['instanz']	=	$urlData[2];
					$returnData['sid']		=	$urlData[3];
				};
			};
		};
		
		return $returnData;
	};
	
	/*
		Get Bool of Portpermissions
	*/
	function isPortPermission($user_right, $instanz, $port, $permissionkey)
	{
		if(!empty($user_right[$permissionkey][$instanz]))
		{
			if(strpos($user_right[$permissionkey][$instanz], $port) !== false)
			{
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Check Session
	*/
	function checkSession($key = "")
	{
		if(session_status() != PHP_SESSION_ACTIVE)
		{
			session_start();
		};
		
		if(isSet($_SESSION['login']))
		{
			if(($_SESSION['login'] == $key || $key == "") && md5($_SERVER['HTTP_USER_AGENT']) == $_SESSION['agent'])
			{
				return true;
			}
			else
			{
				$_SESSION 		= 	array();
				session_destroy();
				return false;
			};
		};
	};
	
	/*
		Reload to Mainpage
	*/
	function reloadSite($type = RELOAD_THE_SITE)
	{
		switch($type)
		{
			case RELOAD_TO_MAIN:
				echo '<script type="text/javascript">
						goBackToMain();
					</script>';
				break;
			case RELOAD_TO_SERVERVIEW:
				echo '<script type="text/javascript">
						teamspeakViewInit();
					</script>';
				break;
			case RELOAD_THE_SITE:
				if(isSet($_SERVER['HTTP_REFERER']))
				{
					$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
					echo '<script type="text/javascript">
							window.location.href="'.$urlData[0].'";
						</script>';
				};
				break;
		};
		exit();
	};
	
	/*
		Check Windowsserver
	*/
	function isWindows()
	{
		return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false;
	};
	
	/*
		Check for a new Version
	*/
	function checkNewVersion($withInfo = true)
	{
		try
		{
			$client = new SoapClient(null, array(
				'location' => 'http://wiki.first-coder.de/soap/soap_server.php',
				'uri' => 'https://wiki.first-coder.de/soap/soap_server.php'
			));
			
			return $client->getNewestVersion(INTERFACE_VERSION, DONATOR_MAIL);
		}
		catch(Exception $e)
		{
			if($withInfo)
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
		Update possible
	*/
	function isUpdatePossible()
	{
		try
		{
			$client = new SoapClient(null, array(
				'location' => 'http://wiki.first-coder.de/soap/soap_server.php',
				'uri' => 'https://wiki.first-coder.de/soap/soap_server.php'
			));
			
			return $client->isUpdatePossible(INTERFACE_VERSION, DONATOR_MAIL);
		}
		catch(Exception $e)
		{
			return false;
		};
	};
	
	/*
		Set instance config
	*/
	function setInstance($action, $alias, $ip_address, $queryport, $user, $pw)
	{
		global $ts3_server;
		
		if($ip_address != '' && $queryport != '' && $user != '')
		{
			global $ts3_server;
			$instanceFile			=	"../../config/instance.php";
			
			$file					=	file($instanceFile);
			$new_file				=	$file;
			
			$new_zeile				=	count($file) - 1;
			$new_instanz    		=	count($ts3_server);
			$new_file[$new_zeile]	=	"\n"; $new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['alias']\t\t= '" . $alias . "';\n"; 			$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['ip']\t\t= '" . $ip_address . "';\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['queryport']\t= " . $queryport . ";\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['user']\t\t= '" . $user . "';\n"; 			$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $new_instanz . "]['pw']\t\t= '" . $pw . "';\n"; 				$new_zeile++;
			$new_file[$new_zeile]	=	"?>";
			
			file_put_contents($instanceFile, "");
			file_put_contents($instanceFile, $new_file);
			
			writeInLog($_SESSION['user']['benutzer'], "Has Added the Instanz \"".$ip_address."\"", true);
			
			return "done";
		}
		else
		{
			return "Not enough Parameters!";
		};
	};
	
	/*
		Change config settings
	*/
	function setConfigSettings($data)
	{
		$settings				=	json_decode($data);
		
		if(empty($settings))
		{
			return false;
		};
		
		if(isSet($settings->MASTERSERVER_INSTANZ))
		{
			if($settings->MASTERSERVER_INSTANZ == "nope")
			{
				$settings->MASTERSERVER_INSTANZ	=	"";
			};
			
			if($settings->MASTERSERVER_PORT == "nope")
			{
				$settings->MASTERSERVER_PORT	=	"";
			};
		};
		
		$filePath							=	"../../config/config.php";
		$file								=	file($filePath);
		$new_file							=	array();
		
		for ($i = 0; $i < count($file); $i++)
		{
			$foundString					=	false;
			foreach($settings AS $search=>$content)
			{
				if(strpos($file[$i], "define(\"".$search."\""))
				{
					$foundString			=	true;
					$new_file[$i]			=	"\tdefine(\"".$search."\", \"".$content."\");\n";
					
					if(isset($_SESSION['user']['benutzer']))
					{
						writeInLog($_SESSION['user']['benutzer'], "Changed the Configfile! Set ".$search." to ".$content, true);
					}
					else
					{
						writeInLog("UNKNOWN", "Changed the Configfile! Set ".$search." to ".$content, true);
					};
				};
			};
			
			if(!$foundString)
			{
				$new_file[$i]				=	$file[$i];
			};
		};
		
		file_put_contents($filePath, "");
		file_put_contents($filePath, $new_file);
		
		return true;
	};
	
	/*
		Funktion: Logfile
	*/
	function writeInLog($loglevel, $logtext, $userlog = false)
	{
		if($userlog)
		{
			$file 			= 	'../../logs/user';
		}
		else
		{
			$file 			= 	'../../logs/system';
		};
		
		if ($loglevel == 1)
		{
			$loglevel 	= 	"\tCRITICAL\t|\t";
		}
		elseif ($loglevel == 2)
		{
			$loglevel	= 	"\tERROR\t\t|\t";
		}
		elseif ($loglevel == 3)
		{
			$loglevel 	= 	"\tWARNING\t\t|\t";
		}
		elseif ($loglevel == 4)
		{
			$loglevel 	=	"\tNOTICE\t\t|\t";
		}
		elseif ($loglevel == 5)
		{
			$loglevel 	= 	"\tINFO\t\t|\t";
		}
		else
		{
			$loglevel	=	$loglevel."\t|\t";
		};
		
		$date			=	date("Y-m-d H:i:s");
		
		$input 			= 	$date."\t|\t".$loglevel.$logtext."\n";

		if(file_exists($file.".log"))
		{
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
		Get the Filesize with prefix
	*/
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
		Delete File
	*/
	function deleteFile($file)
	{
		writeInLog($_SESSION['user']['benutzer'], "Delete File ".$file."!", true);
		
		return unlink(__dir__."/../../".$file);
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
			writeInLog($_SESSION['user']['benutzer'], "Create News \"".$title."\"!", true);
			return file_put_contents("../../files/news/".time().".json", json_encode($fileContent));
		};
	};