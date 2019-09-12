<?php
	/*
		First-Coder Teamspeak 3 Webinterface for everyone
		Copyright (C) 2019 by L.Gmann

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
		if($login->sql_type === 'sqlite')
		{
			echo "success";
			return;
		};

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
		Create Sql Tables
	*/
	if($_POST['action'] == 'sqlCreateTables')
	{
		$sqlTables = array(
			'main_rights' => ' \
				CREATE TABLE main_rights ( \
					pk_rights varchar(40) NOT NULL default \'\', \
					rights_name varchar(100) NOT NULL default \'\', \
					PRIMARY KEY (pk_rights), \
					UNIQUE KEY (rights_name) \
				)',
			'main_clients_infos' => ' \
				CREATE TABLE main_clients_infos ( \
					fk_clients varchar(40) NOT NULL default \'\', \
					firstname varchar(100) NOT NULL default \'\', \
					lastname varchar(100) NOT NULL default \'\', \
					phone varchar(100) NOT NULL default \'\', \
					homepage varchar(100) NOT NULL default \'\', \
					skype varchar(100) NOT NULL default \'\', \
					steam varchar(100) NOT NULL default \'\', \
					twitter varchar(100) NOT NULL default \'\', \
					facebook varchar(100) NOT NULL default \'\', \
					google varchar(100) NOT NULL default \'\', \
					PRIMARY KEY  (fk_clients) \
				)',
			'main_clients_rights' => ' \
				CREATE TABLE main_clients_rights ( \
					id int(40) NOT NULL AUTO_INCREMENT, \
					fk_clients varchar(40) NOT NULL default \'\', \
					fk_rights varchar(40) NOT NULL default \'\', \
					access_instance varchar(10) NOT NULL default \'not_needed\', \
					access_ports varchar(100) NOT NULL default \'not_needed\', \
					PRIMARY KEY  (id) \
				)',
			'main_clients' => ' \
				CREATE TABLE main_clients ( \
					pk_client varchar(40) NOT NULL default \'\', \
					user varchar(100) NOT NULL default \'\', \
					password varchar(100) NOT NULL default \'\', \
					last_login varchar(100) NOT NULL default \'\', \
					user_blocked varchar(10) NOT NULL default \'\', \
					PRIMARY KEY  (pk_client) \
				)',
			'main_modul' => ' \
				CREATE TABLE main_modul ( \
					modul varchar(100) NOT NULL default \'\', \
					active varchar(10) NOT NULL default \'\', \
					UNIQUE KEY (modul) \
				)',
			'main_mails' => ' \
				CREATE TABLE main_mails ( \
					id varchar(100) NOT NULL default \'\', \
					mail_subject varchar(100) NOT NULL default \'\', \
					mail_preheader varchar(100) NOT NULL default \'\', \
					mail_body text NOT NULL, \
					PRIMARY KEY (id), \
					UNIQUE KEY (id) \
				)',
			'main_clients_rights_server_edit' => ' \
				CREATE TABLE main_clients_rights_server_edit ( \
					fk_clients varchar(40) NOT NULL default \'\', \
					fk_rights varchar(40) NOT NULL default \'\', \
					access_instance varchar(10) NOT NULL default \'\', \
					access_ports varchar(100) NOT NULL default \'\' \
				)',
			'main_rights_server' => ' \
				CREATE TABLE main_rights_server ( \
					rights_name varchar(100) NOT NULL default \'\', \
					pk_rights varchar(40) NOT NULL default \'\', \
					PRIMARY KEY (pk_rights), \
					UNIQUE KEY (rights_name) \
				)',
			'main_news' => ' \
				CREATE TABLE main_news ( \
					id int(100) NOT NULL AUTO_INCREMENT, \
					title varchar(60) NOT NULL, \
					sub_title varchar(60) NOT NULL, \
					text text NOT NULL, \
					created timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), \
					show_on timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), \
					PRIMARY KEY  (id) \
				)',
			'main_settings_homepage' => ' \
				CREATE TABLE main_settings_homepage ( \
					donator varchar(60) NOT NULL DEFAULT \'\', \
					title varchar(60) NOT NULL DEFAULT \'First-Coder\', \
					extern_name varchar(60) NOT NULL DEFAULT \'TS3-Servewache\', \
					operator varchar(100) NOT NULL DEFAULT \'\', \
					address varchar(100) NOT NULL DEFAULT \'\', \
					language varchar(30) NOT NULL DEFAULT \'english\', \
					custom_news int(10) NOT NULL DEFAULT \'0\', \
					custom_dashboard int(10) NOT NULL DEFAULT \'0\', \
					delete_tickets int(10) NOT NULL DEFAULT \'0\', \
					ts_tree_intervall int(10) NOT NULL DEFAULT \'4000\', \
					ts_get_db_clients int(10) NOT NULL DEFAULT \'9000000\' \
				)',
			'main_settings_own_sites' => ' \
				CREATE TABLE main_settings_own_sites ( \
					id varchar(100) NOT NULL, \
					name varchar(50) NOT NULL DEFAULT \'\', \
					value varchar(100) NOT NULL DEFAULT \'\', \
					icon varchar(100) NOT NULL DEFAULT \'\', \
					UNIQUE KEY (id) \
				)',
			'ticket_answer' => ' \
				CREATE TABLE ticket_answer ( \
					id int(11) NOT NULL, \
					ticketId smallint(6) NOT NULL, \
					pk varchar(40) NOT NULL, \
					msg longtext NOT NULL, \
					moderator text NOT NULL, \
					dateAded datetime NOT NULL \
				)',
			'ticket_areas' => ' \
				CREATE TABLE ticket_areas ( \
					id int(100) NOT NULL AUTO_INCREMENT, \
					area varchar(100) NOT NULL, \
					UNIQUE KEY (area), \
					PRIMARY KEY  (id) \
				)',
			'ticket_tickets' => ' \
				CREATE TABLE ticket_tickets ( \
					id int(11) NOT NULL AUTO_INCREMENT, \
					pk varchar(40) NOT NULL, \
					subject text NOT NULL, \
					msg longtext NOT NULL, \
					department text NOT NULL, \
					status text NOT NULL, \
					dateAded datetime NOT NULL, \
					dateClosed datetime NOT NULL, \
					dateActivity datetime NOT NULL, \
					PRIMARY KEY (id) \
				)'
		);
		
		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo json_encode(array('success' => false, "msg" => "Databaseconnection failed!"));
			return;
		}
		else
		{
			$status = true;
			$data = [];
			foreach($sqlTables AS $table => $command)
			{
				$command = str_replace(' \ ', '', preg_replace('/\s+/', ' ', $command));
				if($databaseConnection->exec($command) === false)
				{
					if($databaseConnection->errorCode() != "42S01") // Already exists
					{
						$status = false;
						$data[$table] = $databaseConnection->errorInfo()[2];
					}
					else
					{
						$data[$table] = "Table already exists!";
					};
				}
				else
				{
					$data[$table] = true;
				};
			};
			
			echo json_encode(array('success' => $status, "msg" => $data));
		};
	};

	/*
		Update Sql Homepage settings
	*/
	if($_POST['action'] == 'sqlUpdateSettings')
	{
		$data = json_decode($_POST['settings']);
		$_sql = "UPDATE main_settings_homepage
				SET
					title=:title,
					extern_name=:extern_name,
					language=:language";
		$exec = array(
			":title"=>$data->settings_title,
			":extern_name"=>$data->settings_teamspeakname,
			":language"=>$data->settings_language
		);

		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo json_encode(array('success' => false, "msg" => "Databaseconnection failed!"));
			return;
		}
		else
		{
			$update = $databaseConnection->prepare(trim(preg_replace('/\s+/', ' ', $_sql)));
			if ($update->execute($exec)) {
				echo json_encode(array("success" => true, "msg" => ""));
			}
			else {
				echo json_encode(array("success" => false, "msg" => $databaseConnection->errorInfo()[2]));
			};
		};
	};
	
	/*
		Create Sql inserts
	*/
	if($_POST['action'] == 'sqlCreateInserts')
	{
		$sqlTables = array(
			'main_rights' => [
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_main\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_lang\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_mail\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_module\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_designs\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_settings_sites\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_ts\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_ts_add\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_ts_delete\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_bot\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_bot_add\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_instances_bot_delete\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_users_add\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_users_delete\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_users_edit\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_admin_logs\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_profile_see_perm\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_profile_ticket_admin\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_profile_ticket_settings\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_teamspeak_global_msg_poke\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_teamspeak_create_server\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_teamspeak_delete_server\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_teamspeak_access_server\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_bot_create_bot\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_bot_delete_bot\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_bot_access_bot\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_main_news_create\')',
				'INSERT INTO main_rights (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_main_news_delete\')'
			],
			'main_rights_server' => [
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_start_stop\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_create_channel\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_delete_channel\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_message_poke\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_mass_actions\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_client_actions\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_client_sgroups\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_groups\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_clients\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_delete_clients\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_protocol\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_token\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_bans\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_icons\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_filelist\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_create_backups\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_use_backups\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_delete_backups\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_complain\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_host\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_antiflood\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_transfer\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_protocol\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_name\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_port\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_clients\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_password\')',
				'INSERT INTO main_rights_server (pk_rights, rights_name) VALUES (\'$GUID\', \'perm_ts_server_edit_welcome\')'
			],
			'main_modul' => [
				'INSERT INTO main_modul (modul, active) VALUES (\'free_register\', \'false\')',
				'INSERT INTO main_modul (modul, active) VALUES (\'free_ts3_server_application\', \'false\')',
				'INSERT INTO main_modul (modul, active) VALUES (\'splamy_musicbot\', \'false\')',
				'INSERT INTO main_modul (modul, active) VALUES (\'support_teamspeak\', \'false\')',
				'INSERT INTO main_modul (modul, active) VALUES (\'support_teamspeak_instance\', \'\')',
				'INSERT INTO main_modul (modul, active) VALUES (\'support_teamspeak_port\', \'\')'
			],
			'main_mails' => [
				'INSERT INTO main_mails (id, mail_subject, mail_preheader, mail_body) VALUES (\'answer_ticket\', \'Ticket answered\', \'Ticket %TICKET_NAME% has been answered\', \'<p><b>Hello %MAIL%,</b></p><p><br></p><p>Your Ticket <b>%TICKET_NAME%</b> has been answered by <b>%ANSWER%</b>. You can see the answer if you look in the ticket section.</p><p><br></p><p><b>Best greetings</b></p><p>%TITLE%</p>\')',
				'INSERT INTO main_mails (id, mail_subject, mail_preheader, mail_body) VALUES (\'closed_ticket\', \'Ticket closed\', \'Ticket %ID% closed\', \'<p><b>Hello %MAIL%,</b></p><p><br></p><p>your Ticket %ID% is closed by <b>%CLOSED_NAME%</b>. We hope that you got the wanted answer.</p><p><br></p><p>Have a nice day!</p><p><br></p><p><b>Best greetings</b></p><p>%TITLE%</p>\')',
				'INSERT INTO main_mails (id, mail_subject, mail_preheader, mail_body) VALUES (\'create_ticket\', \'Ticket created\', \'%TICKET_NAME% Ticket created\', \'<p><b>Hello %MAIL%,</b></p><p><br></p><p>you have created a ticket (%TICKET_NAME% [%DEPARTMENT%]) on our page.&nbsp; We will answer as soon as possible. We ask for patience.</p><p><br></p><p><b>Best greetings</b></p><p>%TITLE%</p>\')',
				'INSERT INTO main_mails (id, mail_subject, mail_preheader, mail_body) VALUES (\'create_ticket_admin\', \'Ticket has been created\', \'%TICKET_NAME% Ticket created\', \'<p><span style="font-weight: bolder;">Hello %MAIL%,</span></p><p><br></p><p>a member in your interface has created a ticket (%TICKET_NAME% [%DEPARTMENT%]).&nbsp; Please visit your ticket area to answer his question / problem.</p><p><br></p><p><span style="font-weight: bolder;">Best greetings</span></p><p>%TITLE%</p>\')',
				'INSERT INTO main_mails (id, mail_subject, mail_preheader, mail_body) VALUES (\'forgot_password\', \'Forget access\', \'Resetted password from %TITLE%\', \'<p><b>Hello %MAIL%,</b></p><p><br></p><p>your password is resetted. You can now login into the Interface with your new password: <b>%NEW_PASSWORD%</b></p><p><b><br></b></p><p><b>Best&nbsp;greetings</b></p><p>%TITLE%</p>\')'
			],
			'main_settings_homepage' => [
				'INSERT INTO main_settings_homepage (operator) VALUES (\'Max Mustermann\')'
			],
			'main_settings_own_sites' => [
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_01\')',
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_02\')',
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_03\')',
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_04\')',
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_dashboard\')',
				'INSERT INTO main_settings_own_sites (id) VALUES (\'custom_news\')'
			],
			'ticket_areas' => [
				'INSERT INTO ticket_areas (id, area) VALUES (1, \'Default\')'
			]
		);
		
		$databaseConnection 	= 	getSqlConnection($login->sql_type, $login->sql_host, $login->sql_port, $login->sql_database
									, $login->sql_user, $login->sql_password, ($login->sql_ssl == "true") ? true : false);
		
		if($databaseConnection === false)
		{
			echo json_encode(array('success' => false, "msg" => "Databaseconnection failed!"));
			return;
		}
		else
		{
			$status = true;
			$data = [];
			foreach($sqlTables AS $table => $commands)
			{
				foreach($commands AS $key=>$command)
				{
					if($databaseConnection->exec(str_replace('$GUID', guid(), $command)) === false)
					{
						if($databaseConnection->errorCode() != "23000") // Already exists
						{
							$status = false;
							$data[$table.'_'.$key] = $databaseConnection->errorInfo()[2];
						}
						else
						{
							$data[$table.'_'.$key] = "Insert already exists!";
						};
					}
					else
					{
						$data[$table.'_'.$key] = true;
					};
				};
			};
			
			echo json_encode(array('success' => $status, "msg" => $data));
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
			$_passwort 		= 	password_hash($login->admin_password, PASSWORD_BCRYPT, array("cost" => 10));
			$newPk			=	guid();
			$status			=	true;
			$insert			= 	$databaseConnection->prepare('INSERT INTO main_clients (pk_client, user, password, last_login, user_blocked) VALUES (\'' . $newPk . '\', :user, :password, \'' . date("d.m.Y - H:i", time()) . '\', \'false\')');
			
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
		Create Sql Connection
	*/
	function getSqlConnection($mode, $host, $port, $database, $user, $pw, $ssl = false, $returnMsg = false)
	{
		if($mode === 'sqlite')
		{
			$string						=	$mode.':sqlite.db';
		}
		else
		{
			$string						=	$mode.':host='.$host.';port='.$port.';dbname='.$database;
			if($ssl)
			{
				$string					.=	';sslmode=require';
			};
		};
		
		try
		{
			$databaseConnection = new PDO($string, $user, $pw);
			//$databaseConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
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
					foreach($data->fetchAll(PDO::FETCH_ASSOC) AS $keys)
					{
						$right_key[$keys['rights_name']] = $keys['pk_rights'];
					};
				};
			};
			
			foreach($right_key as $key)
			{
				if($status == true)
				{
					if($databaseConnection->exec('INSERT INTO main_clients_rights (fk_clients, fk_rights) VALUES (\'' . $pk . '\', \'' . $key . '\')') === false)
					{
						$status 			= 	false;
					};
				};
			};
			
			return $status;
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