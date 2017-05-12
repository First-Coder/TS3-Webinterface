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
		This Upgrade is for the version 1.2.9
		Includes
	*/
	file_put_contents("../config/config.php", file_get_contents("../tempConfig.php"));
	file_put_contents("../config/instance.php", file_get_contents("../tempInstance.php"));
	
	if(file_exists("../config.php"))
	{
		include_once("../config.php");
	}
	else
	{
		include_once("../config/config.php");
	};
	
	/*
		Set new Configs
	*/
	setConfigSettings(json_encode(array(
		"MASTERSERVER_INSTANZ" 		=> 	MASTERSERVER_INSTANZ,
		"MASTERSERVER_PORT" 		=> 	MASTERSERVER_PORT,
		"SQL_Hostname" 				=> 	SQL_Hostname,
		"SQL_Datenbank" 			=>	SQL_Datenbank,
		"SQL_Username" 				=> 	SQL_Username,
		"SQL_Password" 				=> 	SQL_Password,
		"SQL_Port" 					=> 	SQL_Port,
		"SQL_Mode"					=>	SQL_Mode,
		"SQL_SSL"					=>	SQL_SSL,
		"MAILADRESS"				=>	MAILADRESS,
		"MAIL_SMTP"					=>	MAIL_SMTP,
		"MAIL_SMTP_HOST"			=>	MAIL_SMTP_HOST,
		"MAIL_SMTP_PORT"			=>	MAIL_SMTP_PORT,
		"MAIL_SMTP_USERNAME"		=>	MAIL_SMTP_USERNAME,
		"MAIL_SMTP_PASSWORD"		=>	MAIL_SMTP_PASSWORD,
		"MAIL_SMTP_DEBUG"			=>	MAIL_SMTP_DEBUG,
		"HEADING"					=>	HEADING,
		"TS3_CHATNAME"				=>	TS3_CHATNAME,
		"TEAMSPEAKTREE_INTERVAL"	=>	TEAMSPEAKTREE_INTERVAL))
	);
	
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
		
		$filePath							=	"../config/config.php";
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
		Set instance config
	*/
	$linecount 		= 	0;
	$handle 		= 	fopen("../config/instance.php", "r");
	while(!feof($handle))
	{
		$line 		= 	fgets($handle);
		$linecount++;
	};
	fclose($handle);
	
	if($linecount <= 25)
	{
		foreach($ts3_server AS $instanz=>$content)
		{
			setInstance($instanz, $content['alias'], $content['ip'], $content['queryport'], $content['user'], $content['pw']);
		};
	};
	
	function setInstance($instanz, $alias, $ip_address, $queryport, $user, $pw)
	{
		if($ip_address != '' && $queryport != '' && $user != '')
		{
			$instanceFile			=	"../config/instance.php";
			$file					=	file($instanceFile);
			$new_file				=	$file;
			$new_zeile				=	count($file) - 1;
			$new_file[$new_zeile]	=	"\n"; $new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $instanz . "]['alias']\t\t= '" . $alias . "';\n"; 			$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $instanz . "]['ip']\t\t= '" . $ip_address . "';\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $instanz . "]['queryport']\t= " . $queryport . ";\n"; 		$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $instanz . "]['user']\t\t= '" . $user . "';\n"; 			$new_zeile++;
			$new_file[$new_zeile]	=	"\t\$ts3_server[" . $instanz . "]['pw']\t\t= '" . $pw . "';\n"; 				$new_zeile++;
			$new_file[$new_zeile]	=	"?>";
			
			file_put_contents($instanceFile, "");
			file_put_contents($instanceFile, $new_file);
			
			return "done";
		}
		else
		{
			return "Not enough Parameters!";
		};
	};
	
	/*
		SQL Connection
	*/
	$string						=	SQL_Mode.':host='.SQL_Hostname.';port='.SQL_Port.';dbname='.SQL_Datenbank.'';
	if(SQL_SSL != "0")
	{
		$string					.=	';sslmode=require';
	};
	
	try
	{
		$databaseConnection 	= 	new PDO($string, SQL_Username, SQL_Password);
	}
	catch (PDOException $e)
	{
		echo "Failed to get DB handle: " . $e->getMessage() . "\n";
		exit;
	};
	
	/*
		Remove all folders and files, that will not needed anymore
	*/
	if(!rrmdir("../backup") || !rrmdir("../errorpages") || !rrmdir("../news") || !rrmdir("../shell") || !rrmdir("../wantServer") || !rrmdir("../lang", true))
	{
		die("Could not delete old folders! Looks like an permission problem. Give the folders backup, css/themes, errorlogs, logs, news, shell, wantServer and lang 0777 permissions and retry the update!");
	};
	
	unlink("../css/sonstige/morris.css");
	unlink("../css/sonstige/prettify.css");
	
	unlink("../css/themes/blackwolf.css");
	unlink("../css/themes/cyan.css");
	unlink("../css/themes/ghostrider.css");
	unlink("../css/themes/minecraft.css");
	unlink("../css/themes/orange.css");
	unlink("../css/themes/violett.css");
	unlink("../css/themes/wapen.css");
	unlink("../css/themes/weihnachten.css");
	
	unlink("../images/bgBlue.jpg");
	unlink("../images/bgCyan.jpg");
	unlink("../images/bgGreen.jpg");
	unlink("../images/bgMC.png");
	unlink("../images/bgOrange.jpg");
	unlink("../images/bgRed.jpg");
	unlink("../images/bgSchnee.jpg");
	unlink("../images/bgViolett.jpg");
	unlink("../images/bgWolf.jpg");
	
	file_put_contents("../lang/lang.php", file_get_contents("../tempLang.php"));
	file_put_contents("../lang/de.php", file_get_contents("../tempDe.php"));
	file_put_contents("../lang/en.php", file_get_contents("../tempEn.php"));
	
	/*
		Get SQL Keys
	*/
	$mysql_keys												=	array();
	if (($data = $databaseConnection->query("SELECT * FROM  main_rights")) !== false)
	{
		if ($data->rowCount() > 0)
		{
			$result 										= 	$data->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($result AS $keys)
			{
				$mysql_keys[$keys['rights_name']]			=	$keys['pk_rights'];
			};
		};
	};
	
	/*
		Add Database Keys
	*/
	$keyMailsFounded					=	false;
	$keyFileTransferFounded				=	false;
	$keyLogsFounded						=	false;
	foreach($mysql_keys AS $name => $key)
	{
		switch($name)
		{
			case "right_hp_mails":
				$keyMailsFounded		=	true;
				break;
			case "right_web_file_transfer":
				$keyFileTransferFounded	=	true;
				break;
			case "right_hp_logs":
				$keyLogsFounded			=	true;
				break;
		};
	};
	
	if(!$keyMailsFounded)
	{
		addKey("right_hp_mails");
	};
	
	if(!$keyFileTransferFounded)
	{
		addKey("right_web_file_transfer");
	};
	
	if(!$keyLogsFounded)
	{
		addKey("right_hp_logs");
	};
	
	/*
		Add new Mails
	*/
	prepareMail("forgot_password");
	
	function prepareMail($id)
	{
		global $databaseConnection;
		
		if(($data = $databaseConnection->query('SELECT headline FROM main_mails WHERE id=\''.$id.'\' LIMIT 1;')) !== false)
		{
			if ($data->rowCount() <= 0)
			{
				$content					=	array();
				
				if(LANGUAGE == "english")
				{
					$content['headline']	=	": Forgot password";
					$content['subject']		=	"Forgot password";
					
					$content['body']		=	file_get_contents("../tempForgotPasswordEn.php");
				}
				else
				{
					$content['headline']	=	": Passwort vergessen";
					$content['subject']		=	"Passwort vergessen";
					
					$content['body']		=	file_get_contents("../tempForgotPasswordDe.php");
				};
				
				addMails($id, $content['headline'], $content['subject'], $content['body']);
			};
			
			if(LANGUAGE == "english")
			{
				unlink("../tempForgotPasswordEn.php");
			}
			else
			{
				unlink("../tempForgotPasswordDe.php");
			};
		};
	};
	
	/*
		Function to add Mail inserts
	*/
	function addMails($id, $headline, $subject, $body)
	{
		global $databaseConnection;
		
		if($databaseConnection->exec('INSERT INTO main_mails (id, headline, mail_subject, mail_body) VALUES (\'' . $id . '\', \'' . $headline . '\', \'' . $subject . '\', \'' . $body . '\')') === false)
		{
			echo "upgrade (SQL Error):".$databaseConnection->errorInfo()[2];
			return false;
		}
		else
		{
			return true;
		};
	};
	
	/*
		Function to add a rightkey into the database
	*/
	function addKey($name)
	{
		global $databaseConnection;
		
		if($databaseConnection->exec('INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'' . guid() . '\', \'' . $name . '\')') === false)
		{
			echo "upgrade (SQL Error):".$databaseConnection->errorInfo()[2];
			return false;
		}
		else
		{
			return true;
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
		Function to delete recrusive a folder
		written by https://gist.github.com/XzaR90/48c6b615be12fa765898
	*/
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;
	use SplFileInfo;
	
	function rrmdir($source, $removeOnlyChildren = false)
	{
		if(empty($source) || file_exists($source) === false)
		{
			return true;
		};
		
		if(is_file($source) || is_link($source))
		{
			return unlink($source);
		};
		
		$files = new RecursiveIteratorIterator
		(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		
		foreach($files as $fileinfo)
		{
			if($fileinfo->isDir())
			{
				if(rrmdir($fileinfo->getRealPath()) === false)
				{
					return false;
				};
			}
			else
			{
				if(unlink($fileinfo->getRealPath()) === false)
				{
					return false;
				};
			};
		};
		
		if($removeOnlyChildren === false)
		{
			return rmdir($source);
		};
		
		return true;
	};
	
	/*
		Delete the last files we don't need anymore
	*/
	foreach (scandir('../') as $datei)
	{
		if(!is_dir($datei) && $datei != ".htaccess" && $datei != "checkInterface.php" && $datei != "iframeServerView.php" && $datei != "index.php" && $datei != "LICENCE.txt" && $datei != "upgrade.php")
		{
			unlink("../".$datei);
		};
	};
	
	unlink("../upgrade.php");
?>