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
	
	/*
		Check the Database
	*/
	if($_POST['action'] == 'check_database')
	{
		$verbindung					=	true;
		$string						=	$_POST['mode'].':host='.urldecode($_POST['host']).';port='.$_POST['port'].';dbname='.urldecode($_POST['database']);
		if($_POST['ssl'] == "true")
		{
			$string					.=	';sslmode=require';
		};
		
		try
		{
			$databaseConnection 	= 	new PDO($string, urldecode($_POST['user']), urldecode($_POST['pw']));
		}
		catch (PDOException $e)
		{
			$verbindung				=	false;
			echo '<b>Databaseconnection failed!</b><br />' . $e->getMessage();
		};
		
		if($verbindung)
		{
			echo "done";
		};
	};
	
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
		Create the Client and give him all rights
	*/
	if($_POST['action'] == 'create_user')
	{
		require_once("../config/config.php");
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$_passwort 		= 	crypt(urldecode($_POST['pw']), urldecode($_POST['pw']));
			$newPk			=	guid();
			$status			=	true;
			$insert			= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, benutzer, password, last_login, benutzer_blocked) VALUES (\'' . $newPk . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
			
			if(!$insert->execute(array(":user"=>$_POST['user'], ":password"=>$_passwort)))
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
				if(giveUserAllRights($databaseConnection, $newPk))
				{
					echo "User created and all Permissions created!";
					setConfigSettings();
				}
				else
				{
					echo "<font class='console_error' style='color:red;'>User created but no Permissions";
				};
			}
			else
			{
				echo "User and Rights can not be created!";
			};
		}
		else
		{
			echo '<font class="console_error" style="color:red;">Databaseconnection failed!<br/>'.$databaseConnection->errorInfo()[2];
		};
	};
	
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
	
	function setConfigSettings()
	{
		$file					=	file("../config/config.php");
		$new_file				=	array();
		$search_chatname		=	'TS3_CHATNAME';
		$search_heading			=	'HEADING';
		
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], $search_heading))
			{
				$new_file[$i]		=	"\tdefine(\"HEADING\", \"" . urldecode($_POST['heading']) . "\");\n";
			}
			elseif (strpos($file[$i], $search_chatname))
			{
				$new_file[$i]		=	"\tdefine(\"TS3_CHATNAME\", \"" . urldecode($_POST['tschatname']) . "\");\n";
			}
			else
			{
				$new_file[$i]		=	$file[$i];
			};
		};
		
		file_put_contents("../config/config.php", "");
		file_put_contents("../config/config.php", $new_file);
		
		echo "<br />config.php has been written!";
	};
	
	/*
		Create the Database
	*/
	if($_POST['action'] == 'create_database')
	{
		$string					=	$_POST['mode'].':host='.$_POST['host'].';port='.$_POST['port'].';dbname='.$_POST['database'];
		$ssl					=	"0";
		if($_POST['ssl'] == "true")
		{
			$string				.=	';sslmode=require';
			$ssl				=	"1";
		};
		
		$databaseConnection 	= 	new PDO($string, $_POST['user'], $_POST['pw']);
		$status					=	true;
		
		echo "Create Table: 'main_rights'<br />";
		$_sql					=	"CREATE TABLE main_rights (
										pk_rights varchar(255) NOT NULL default '',
										rights_name varchar(255) NOT NULL default '',
										PRIMARY KEY  (pk_rights)
									)";
		
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_rights' could not be created :/</font>";
			exit();
		};
		echo setSpaces(4)."Table 'main_rights' are created ;)<br />";
		
		/*
			main_rights Einträge
		*/
		echo setSpaces(8)."Add Right 'right_hp_user_edit' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_user_edit", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_create' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_create", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_bot' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_bot", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_protokoll' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_protokoll", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_hp_user_delete' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_user_delete", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_delete' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_delete", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_edit' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_edit", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_global_message_poke' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_global_message_poke", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_mass_actions' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_mass_actions", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_channel_actions' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_channel_actions", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_start_stop' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_start_stop", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_message_poke' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_message_poke", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_client_rights' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_client_rights", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'login_key' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("login_key", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_hp_ts3' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_ts3", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_hp_main' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_main", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_view' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_view", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_hp_user_create' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_user_create", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_client_actions' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_client_actions", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_icons' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_icons", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_token' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_token", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_clients' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_clients", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_backups' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_backups", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_bans' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_server_bans", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_bans' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_ticket_system", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_server_bans' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_global_server", $databaseConnection);
		
		// New at version 1.1.2
		echo setSpaces(8)."Add Right 'right_hp_mails' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_mails", $databaseConnection);
		
		echo setSpaces(8)."Add Right 'right_web_file_transfer' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_web_file_transfer", $databaseConnection);
		
		// New at version 1.2.9
		echo setSpaces(8)."Add Right 'right_hp_logs' in 'main_rights'<br />";
		$status 				= 	createDatabaseInsertIntoMainRights("right_hp_logs", $databaseConnection);
		
		if(!$status)
		{
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font>";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			
			$databaseConnection->exec('DROP TABLE main_rights');
		};
		
		/*
			main_clients_infos Tabelle
		*/
		echo "Create Table: 'main_clients_infos'<br />";
		$_sql			=	"CREATE TABLE main_clients_infos (
								fk_clients varchar(255) NOT NULL default '',
								vorname varchar(255) NOT NULL default '',
								nachname varchar(255) NOT NULL default '',
								telefon varchar(255) NOT NULL default '',
								homepage varchar(255) NOT NULL default '',
								skype varchar(255) NOT NULL default '',
								steam varchar(255) NOT NULL default '',
								twitter varchar(255) NOT NULL default '',
								facebook varchar(255) NOT NULL default '',
								google varchar(255) NOT NULL default '',
								PRIMARY KEY  (fk_clients)
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_clients_infos' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_clients_infos' are created ;)<br />";
		};
		
		/*
			main_clients_rights Tabelle
		*/
		echo "Create Table: 'main_clients_rights'<br />";
		if($_POST['mode'] == "mysql")
		{
			$_sql			=	"CREATE TABLE main_clients_rights (
								id int(255) NOT NULL AUTO_INCREMENT,
								fk_clients varchar(255) NOT NULL default '',
								fk_rights varchar(255) NOT NULL default '',
								access_instanz varchar(255) NOT NULL default 'not_needed',
								access_ports varchar(255) NOT NULL default 'not_needed',
								timestamp varchar(255) NOT NULL default 'not_needed',
								PRIMARY KEY  (id)
							)";
		}
		else
		{
			$_sql			=	"CREATE TABLE main_clients_rights (
								id serial NOT NULL,
								fk_clients varchar(255) NOT NULL default '',
								fk_rights varchar(255) NOT NULL default '',
								access_instanz varchar(255) NOT NULL default 'not_needed',
								access_ports varchar(255) NOT NULL default 'not_needed',
								timestamp varchar(255) NOT NULL default 'not_needed',
								PRIMARY KEY  (id)
							)";
		};
		
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_clients_rights' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".print_r($databaseConnection->errorInfo()[2])."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_clients_rights' are created ;)<br />";
		};
		
		/*
			main_clients Tabelle
		*/
		echo "Create Table: 'main_clients'<br />";
		$_sql			=	"CREATE TABLE main_clients (
								pk_client varchar(255) NOT NULL default '',
								benutzer varchar(255) NOT NULL default '',
								password varchar(255) NOT NULL default '',
								last_login varchar(255) NOT NULL default '',
								benutzer_blocked varchar(255) NOT NULL default '',
								blocked_until varchar(255) NOT NULL default '0',
								PRIMARY KEY  (pk_client)
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_clients' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_clients' are created ;)<br />";
		};
		
		/*
			main_modul Tabelle
		*/
		echo "Create Table: 'main_modul'<br />";
		$_sql			=	"CREATE TABLE main_modul (
								modul varchar(255) NOT NULL default '',
								active varchar(255) NOT NULL default ''
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_modul' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_modul' are created ;)<br />";
			
			echo setSpaces(8)."Add Modul 'webinterface' in 'main_modul'<br />";
			$status			=	createDatabaseInsertIntoMainModule("webinterface", $databaseConnection, true);
			
			//echo setSpaces(8)."Add Modul 'botinterface' in 'main_modul'<br />";
			//$status			=	createDatabaseInsertIntoMainModule("botinterface", $databaseConnection, false);
			
			echo setSpaces(8)."Add Modul 'free_register' in 'main_modul'<br />";
			$status			=	createDatabaseInsertIntoMainModule("free_register", $databaseConnection, false);
			
			echo setSpaces(8)."Add Modul 'free_ts3_server_application' in 'main_modul'<br />";
			$status			=	createDatabaseInsertIntoMainModule("free_ts3_server_application", $databaseConnection, false);
			
			echo setSpaces(8)."Add Modul 'write_news' in 'main_modul'<br />";
			$status			=	createDatabaseInsertIntoMainModule("write_news", $databaseConnection, true);
			
			echo setSpaces(8)."Add Modul 'masterserver' in 'main_modul'<br />";
			$status			=	createDatabaseInsertIntoMainModule("masterserver", $databaseConnection, false);
			
			if(!$status)
			{
				echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
				echo "<font class='console_error' style='color:red;'>Error at Add Modul in 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
				echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				
				$databaseConnection->exec('DROP TABLE main_modul');
				$databaseConnection->exec('DROP TABLE main_clients');
				$databaseConnection->exec('DROP TABLE main_clients_rights');
				$databaseConnection->exec('DROP TABLE main_clients_infos');
				$databaseConnection->exec('DROP TABLE main_rights');
				exit();
			};
		};
		
		/*
			main_clients_rights_server_edit Tabelle
		*/
		echo "Create Table: 'main_clients_rights_server_edit'<br />";
		$_sql			=	"CREATE TABLE main_clients_rights_server_edit (
								fk_clients varchar(255) NOT NULL default '',
								fk_rights varchar(255) NOT NULL default '',
								access_instanz varchar(255) NOT NULL default '',
								access_ports varchar(255) NOT NULL default ''
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_clients_rights_server_edit' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_modul');
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_clients_rights_server_edit' are created ;)<br />";
		};
		
		/*
			main_rights_server_edit Tabelle
		*/
		echo "Create Table: 'main_rights_server_edit'<br />";
		$_sql			=	"CREATE TABLE main_rights_server_edit (
								rights_name varchar(255) NOT NULL default '',
								pk_rights varchar(255) NOT NULL default ''
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_rights_server_edit' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_clients_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_modul');
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		}
		else
		{
			echo setSpaces(4)."Table 'main_rights_server_edit' are created ;)<br />";
			
			echo setSpaces(8)."Add Right 'right_server_edit_port' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_port", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_slots' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_slots", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_autostart' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_autostart", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_min_client_version' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_min_client_version", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_main_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_main_settings", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_default_servergroups' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_default_servergroups", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_host_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_host_settings", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_complain_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_complain_settings", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_antiflood_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_antiflood_settings", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_transfer_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_transfer_settings", $databaseConnection);
			
			echo setSpaces(8)."Add Right 'right_server_edit_protokoll_settings' in 'main_rights_server_edit'<br />";
			$status 		=	createDatabaseInsertIntoServerEdit("right_server_edit_protokoll_settings", $databaseConnection);
			
			if(!$status)
			{
				echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
				echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
				
				$databaseConnection->exec('DROP TABLE main_rights_server_edit');
				$databaseConnection->exec('DROP TABLE main_clients_rights_server_edit');
				$databaseConnection->exec('DROP TABLE main_modul');
				$databaseConnection->exec('DROP TABLE main_clients');
				$databaseConnection->exec('DROP TABLE main_clients_rights');
				$databaseConnection->exec('DROP TABLE main_clients_infos');
				$databaseConnection->exec('DROP TABLE main_rights');
			};
		};
		
		/*
			ticket_answer Tabelle
		*/
		echo "Create Table: 'ticket_answer'<br />";
		$_sql			=	"CREATE TABLE ticket_answer (
								id int(11) NOT NULL,
								ticketId smallint(6) NOT NULL,
								pk varchar(255) NOT NULL,
								msg longtext NOT NULL,
								moderator text NOT NULL,
								dateAded datetime NOT NULL
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'ticket_answer' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE main_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_clients_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_modul');
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		};
		
		/*
			ticket_tickets Tabelle
		*/
		echo "Create Table: 'ticket_tickets'<br />";
		if($_POST['mode'] == "mysql")
		{
			$_sql			=	"CREATE TABLE ticket_tickets (
								id int(11) NOT NULL AUTO_INCREMENT,
								pk varchar(255) NOT NULL,
								subject text NOT NULL,
								msg longtext NOT NULL,
								department text NOT NULL,
								status text NOT NULL,
								dateAded datetime NOT NULL,
								dateClosed datetime NOT NULL,
								dateActivity datetime NOT NULL,
								PRIMARY KEY (id)
							)";
		}
		else
		{
			$_sql			=	"CREATE TABLE ticket_tickets (
								id serial NOT NULL AUTO_INCREMENT,
								pk varchar(255) NOT NULL,
								subject text NOT NULL,
								msg longtext NOT NULL,
								department text NOT NULL,
								status text NOT NULL,
								dateAded datetime NOT NULL,
								dateClosed datetime NOT NULL,
								dateActivity datetime NOT NULL,
								PRIMARY KEY (id)
							)";
		};
		
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'ticket_tickets' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'ticket_answer'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE ticket_answer');
			$databaseConnection->exec('DROP TABLE main_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_clients_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_modul');
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
		};
		
		/*
			main_mails Tabelle
			new in version 1.1.2
		*/
		echo "Create Table: 'main_mails'<br />";
		$_sql			=	"CREATE TABLE main_mails (
								id varchar(255) NOT NULL,
								headline varchar(255) NOT NULL,
								mail_subject varchar(255) NOT NULL,
								mail_body longtext NOT NULL,
								PRIMARY KEY  (id)
							)";
		if($databaseConnection->exec($_sql) === false)
		{
			echo setSpaces(4)."<font class='console_error' style='color:red;'>Table 'main_mails' could not be created :/</font><br />";
			echo "<font class='console_error' style='color:red;font-weight:bold;'>Error from Database: ".$databaseConnection->errorInfo()[2]."</font>";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'ticket_tickets'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'ticket_answer'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights_server_edit'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_modul'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			echo "<font class='console_error' style='color:red;'>Error in Create Table 'main_clients_infos'... delete the whole Table now that you can maybe Restart the process!</font><br /><br />";
			echo "<font class='console_error' style='color:red;'>Error at Add Rights in 'main_rights'... delete the whole Table now that you can maybe Restart the process!</font><br />";
			
			$databaseConnection->exec('DROP TABLE ticket_tickets');
			$databaseConnection->exec('DROP TABLE ticket_answer');
			$databaseConnection->exec('DROP TABLE main_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_clients_rights_server_edit');
			$databaseConnection->exec('DROP TABLE main_modul');
			$databaseConnection->exec('DROP TABLE main_clients');
			$databaseConnection->exec('DROP TABLE main_clients_rights');
			$databaseConnection->exec('DROP TABLE main_clients_infos');
			$databaseConnection->exec('DROP TABLE main_rights');
			exit();
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
			
			if(LANGUAGE == "english")
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
				
				// New in version 1.2.9
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
				
				// New in version 1.2.9
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
		};
		
		$file					=	file("../config/config.php");
		$new_file				=	array();
		
		$search_hostname		=	'SQL_Hostname';
		$search_datenbank		=	'SQL_Datenbank';
		$search_username		=	'SQL_Username';
		$search_password		=	'SQL_Password';
		$search_port			=	'SQL_Port';
		$search_mode			=	'SQL_Mode';
		$search_ssl				=	'SQL_SSL';
		
		for ($i = 0; $i < count($file); $i++)
		{
			if(strpos($file[$i], $search_hostname))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Hostname\", \"" . $_POST['host'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_datenbank))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Datenbank\", \"" . $_POST['database'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_username))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Username\", \"" . $_POST['user'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_password))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Password\", \"" . $_POST['pw'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_port))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Port\", \"" . $_POST['port'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_mode))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_Mode\", \"" . $_POST['mode'] . "\");\n";
			}
			elseif (strpos($file[$i], $search_ssl))
			{
				$new_file[$i]		=	"\tdefine(\"SQL_SSL\", \"" . $ssl . "\");\n";
			}
			else
			{
				$new_file[$i]		=	$file[$i];
			};
		};
		
		file_put_contents("../config/config.php", "");
		file_put_contents("../config/config.php", $new_file);
		
		echo "<br />config.php has been written!";
	};
	
	function createDatabaseInsertIntoMainRights($name, $databaseConnection)
	{
		if($databaseConnection->exec('INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'' . guid() . '\', \'' . $name . '\')') === false)
		{
			echo setSpaces(12)."<font class='console_error' style='color:red;'>Right '" . $name . "' could not be added :/</font><br />";
			return false;
		}
		else
		{
			echo setSpaces(12)."Right '" . $name . "' successful added<br />";
			return true;
		};
	};
	
	function createDatabaseInsertIntoMainModule($name, $databaseConnection, $active)
	{
		if($active == true)
		{
			$status		=	"true";
		}
		else
		{
			$status		=	"false";
		};
		
		if($databaseConnection->exec('INSERT INTO main_modul (modul, active) VALUES (\'' . $name . '\', \'' . $status . '\')') === false)
		{
			echo setSpaces(12)."<font class='console_error' style='color:red;'>Modul '" . $name . "' could not be added :/</font><br />";
			return false;
		}
		else
		{
			echo setSpaces(12)."Modul '" . $name . "' successful added<br />";
			return true;
		};
	};
	
	function createDatabaseInsertIntoServerEdit($name, $databaseConnection)
	{
		if($databaseConnection->exec('INSERT INTO main_rights_server_edit (pk_rights, rights_name) VALUES (\'' . guid() . '\', \'' . $name . '\')') === false)
		{
			echo setSpaces(12)."<font class='console_error' style='color:red;'>Right '" . $name . "' could not be added :/</font><br />";
			return false;
		}
		else
		{
			echo setSpaces(12)."Right '" . $name . "' successful added<br />";
			return true;
		};
	};
	
	function createDatabaseInsertIntoMainMails($id, $headline, $subject, $body, $databaseConnection)
	{
		if($databaseConnection->exec('INSERT INTO main_mails (id, headline, mail_subject, mail_body) VALUES (\'' . $id . '\', \'' . $headline . '\', \'' . $subject . '\', \'' . $body . '\')') === false)
		{
			echo setSpaces(12)."<font class='console_error' style='color:red;'>Mail with id '" . $id . "' could not be added :/</font><br />";
			return false;
		}
		else
		{
			echo setSpaces(12)."Mail with id '" . $id . "' successful added<br />";
			return true;
		};
	};
	
	function setSpaces($num)
	{
		$returnString		=	'';
		for($i = 0;$i < $num;$i++)
		{
			$returnString	.=	'&nbsp;';
		};
		
		return $returnString;
	};
?>