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
		Get data
	*/
	$login			=	array();
	if(isset($_POST['login']))
	{
		$login		=	json_decode($_POST['login']);
	};
	
	/*
		Delete the installer
	*/
	if($_POST['action'] == 'deleteInstaller')
	{
		$arrayFiles					=	array(
											"bg.png",
											"fcLogo.png",
											"interface.zip",
											"helper.php"
										);
		$handle = @opendir("./");
		while ($file = @readdir ($handle))
		{
			if(in_array($file, $arrayFiles) || (strpos($file, "temp") !== false && strpos($file, ".php") !== false))
			{
				@unlink($file);
			};
		};
		
		echo "success";
	};
	
	/*
		Unzip the Interface archive
	*/
	if($_POST['action'] == 'setConfig')
	{
		$settings							=	array();
		$settings							=	json_decode($_POST['settings']);
		$ssl								=	($login->sql_ssl == "true") ? "1" : "0";
		$filePath							=	"./config/config.php";
		$file								=	file($filePath);
		$new_file							=	array();
		
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], "define(\"SQL_Hostname\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Hostname\", \"".$login->sql_host."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_Datenbank\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Datenbank\", \"".$login->sql_database."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_Username\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Username\", \"".$login->sql_user."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_Password\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Password\", \"".$login->sql_password."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_Port\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Port\", \"".$login->sql_port."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_Mode\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_Mode\", \"".$login->sql_type."\");\n";
			}
			else if(strpos($file[$i], "define(\"SQL_SSL\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"SQL_SSL\", \"".$ssl."\");\n";
			}
			
			else if(strpos($file[$i], "define(\"HEADING\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"HEADING\", \"".$settings->settings_title."\");\n";
			}
			else if(strpos($file[$i], "define(\"TS3_CHATNAME\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"TS3_CHATNAME\", \"".$settings->settings_teamspeakname."\");\n";
			}
			else if(strpos($file[$i], "define(\"LANGUAGE\""))
			{
				$foundString			=	true;
				$new_file[$i]			=	"\tdefine(\"LANGUAGE\", \"".$settings->settings_language."\");\n";
			}
			
			else
			{
				$new_file[$i]			=	$file[$i];
			};
		};
		
		file_put_contents($filePath, "");
		file_put_contents($filePath, $new_file);
		
		echo "success";
	};
	
	/*
		Unzip the Interface archive
	*/
	if($_POST['action'] == 'unzipArchive')
	{
		$zip = new ZipArchive;
		$res = $zip->open("./interface.zip");
		
		if($res === true)
		{
			$zip->extractTo("./");
			$zip->close();
		};
		
		echo "success";
	};
	
	/*
		Check the Database
	*/
	if($_POST['action'] == 'checkDatabase')
	{
		if(empty($login))
		{
			echo "Logininformations incomplete!";
			return;
		};
		
		$return 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
						, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false, true);
		
		echo ($return === false) ? "success" : $return;
	};
	
	/*
		Sql Command
	*/
	if($_POST['action'] == 'sqlCommand')
	{
		if(empty($login))
		{
			echo "Logininformations incomplete!";
			return;
		};
		
		sleep($_POST['delay']);
		
		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo "Databaseconnection failed!";
			return;
		}
		else
		{
			if($databaseConnection->exec(str_replace('$GUID', guid(), $_POST['sqlCommand'])) === false)
			{
				if($databaseConnection->errorCode() != "42S01") // Already exists
				{
					echo $databaseConnection->errorInfo()[2];
				}
				else
				{
					echo "exists";
				};
			}
			else
			{
				echo "success";
			};
		};
	};
	
	/*
		Sql Create user
	*/
	if($_POST['action'] == 'setSqlUser')
	{
		if(empty($login))
		{
			echo "Logininformations incomplete!";
			return;
		};
		
		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo "Databaseconnection failed!";
			return;
		}
		else
		{
			$_passwort 		= 	crypt(urldecode($login->admin_password), urldecode($login->admin_password));
			$newPk			=	guid();
			$status			=	true;
			$insert			= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, benutzer, password, last_login, benutzer_blocked) VALUES (\'' . $newPk . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
			
			if(!$insert->execute(array(":user"=>$login->admin_user, ":password"=>$_passwort)))
			{
				echo $databaseConnection->errorInfo()[2];
			};
			
			if($databaseConnection->exec('INSERT INTO main_clients_infos (fk_clients) VALUES (\'' . $newPk . '\')') === false)
			{
				echo $databaseConnection->errorInfo()[2];
			};
			
			if($status)
			{
				if(giveUserAllRights($databaseConnection, $newPk))
				{
					echo "success";
				}
				else
				{
					echo "User created but no Permissions givin";
				};
			}
			else
			{
				echo "User and Rights can not be created!";
			};
		};
	};
	
	/*
		Sql Insert Mails
	*/
	if($_POST['action'] == 'sqlInsertMails')
	{
		if(empty($login))
		{
			echo "Logininformations incomplete!";
			return;
		};
		
		$settings				=	array();
		$settings				=	json_decode($_POST['settings']);
		
		if(empty($settings))
		{
			echo "Settingsinformation incomplete!";
			return;
		};
		
		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo "Databaseconnection failed!";
			return;
		}
		else
		{
			$dataContent									=	array();
			$dataContent['answer_ticket']					=	array();
			$dataContent['closed_ticket']					=	array();
			$dataContent['create_request']					=	array();
			$dataContent['create_ticket']					=	array();
			$dataContent['request_failed']					=	array();
			$dataContent['request_success']					=	array();
			
			if($settings->settings_language == "english")
			{
				$dataContent['answer_ticket']['headline']	=	": Ticket answered";
				$dataContent['answer_ticket']['subject']	=	"Ticket answered";
				$dataContent['closed_ticket']['headline']	=	": Ticket closed";
				$dataContent['closed_ticket']['subject']	=	"Ticket closed";
				$dataContent['create_request']['headline']	=	": Server Request";
				$dataContent['create_request']['subject']	=	"Server Request";
				$dataContent['create_ticket']['headline']	=	": Ticket created";
				$dataContent['create_ticket']['subject']	=	"Ticket created";
				$dataContent['request_failed']['headline']	=	": Server Request rejected";
				$dataContent['request_failed']['subject']	=	"Server Request rejected";
				$dataContent['request_success']['headline']	=	": Server Request accepted";
				$dataContent['request_success']['subject']	=	"Server Request accepted";
				$dataContent['forgot_password']['headline']	=	": Forgot password";
				$dataContent['forgot_password']['subject']	=	"Forgot password";
				
				$dataContent['answer_ticket']['body']		=	file_get_contents("tempAnswerTicketEn.php");
				$dataContent['closed_ticket']['body']		=	file_get_contents("tempClosedTicketEn.php");
				$dataContent['create_request']['body']		=	file_get_contents("tempCreateRequestEn.php");
				$dataContent['create_ticket']['body']		=	file_get_contents("tempCreateTicketEn.php");
				$dataContent['request_failed']['body']		=	file_get_contents("tempRequestFailedEn.php");
				$dataContent['request_success']['body']		=	file_get_contents("tempRequestSuccessEn.php");
				$dataContent['forgot_password']['body']		=	file_get_contents("tempForgotPasswordEn.php");
			}
			else
			{
				$dataContent['answer_ticket']['headline']	=	": Ticket beantwortet";
				$dataContent['answer_ticket']['subject']	=	"Ticket beantwortet";
				$dataContent['closed_ticket']['headline']	=	": Ticket geschlossen";
				$dataContent['closed_ticket']['subject']	=	"Ticket geschlossen";
				$dataContent['create_request']['headline']	=	": Serverantrag";
				$dataContent['create_request']['subject']	=	"Serverantrag";
				$dataContent['create_ticket']['headline']	=	": Ticket erstellt";
				$dataContent['create_ticket']['subject']	=	"Ticket erstellt";
				$dataContent['request_failed']['headline']	=	": Serverantrag abgelehnt";
				$dataContent['request_failed']['subject']	=	"Serverantrag abgelehnt";
				$dataContent['request_success']['headline']	=	": Serverantrag akzeptiert";
				$dataContent['request_success']['subject']	=	"Serverantrag akzeptiert";
				$dataContent['forgot_password']['headline']	=	": Passwort vergessen";
				$dataContent['forgot_password']['subject']	=	"Passwort vergessen";
				
				$dataContent['answer_ticket']['body']		=	file_get_contents("tempAnswerTicketDe.php");
				$dataContent['closed_ticket']['body']		=	file_get_contents("tempClosedTicketDe.php");
				$dataContent['create_request']['body']		=	file_get_contents("tempCreateRequestDe.php");
				$dataContent['create_ticket']['body']		=	file_get_contents("tempCreateTicketDe.php");
				$dataContent['request_failed']['body']		=	file_get_contents("tempRequestFailedDe.php");
				$dataContent['request_success']['body']		=	file_get_contents("tempRequestSuccessDe.php");
				$dataContent['forgot_password']['body']		=	file_get_contents("tempForgotPasswordDe.php");
			};
			
			foreach($dataContent AS $id => $content)
			{
				createDatabaseInsertIntoMainMails($id, $content['headline'], $content['subject'], $content['body'], $databaseConnection);
			};
			
			echo "success";
		};
	};
	
	function createDatabaseInsertIntoMainMails($id, $headline, $subject, $body, $databaseConnection)
	{
		$databaseConnection->exec('INSERT INTO main_mails (id, headline, mail_subject, mail_body) VALUES (\'' . $id . '\', \'' . $headline . '\', \'' . $subject . '\', \'' . $body . '\')');
	};
	
	/*
		Create Sql Connection
	*/
	function getSqlConnection($mode, $host, $port, $database, $user, $pw, $ssl = false, $returnMsg = false)
	{
		$string						=	$mode.':host='.$host.';port='.$port.';dbname='.$database.'';
		if($ssl)
		{
			$string					.=	';sslmode=require';
		};
		
		try
		{
			$databaseConnection 	= 	new PDO($string, $user, $pw);
			
			return ($returnMsg) ? false : $databaseConnection;
		}
		catch (PDOException $e)
		{
			return ($returnMsg) ? $e->getMessage() : false;
		};
	};
	
	/*
		Give a User all global permissions
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
				};
			};
			
			foreach($right_key as $key)
			{
				if($status == true)
				{
					if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights, timestamp) VALUES (\'' . $pk . '\', \'' . $key . '\', \'0\')') === false)
					{
						$status 			= 	false;
					};
				};
			};
			
			if($status)
			{
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Create Random Keys
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
		};
	};
?>