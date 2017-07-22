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
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/functionsBot.php");
	
	/*
		Session start
	*/
	checkSession();
	
	/*
		Save Querybotsettings
	*/
	function saveQueryBotSettings($botid, $tablename, $data)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$newData						=	array();
			$firstElement					=	true;
			$sqlTxt							=	"UPDATE ".$tablename." SET ";
			foreach($data AS $index=>$content)
			{
				if(!$firstElement)
				{
					$sqlTxt					.=	", ";
				};
				$sqlTxt						.=	$index."=:".$index;
				$firstElement				=	false;
				$newData[':'.$index]		=	$content;
			};
			$sqlTxt							.=	" WHERE id=".$botid;
			
			$data 							= 	$databaseConnection->prepare($sqlTxt);
			if ($data->execute($newData))
			{
				return "done";
			}
			else
			{
				return $databaseConnection->errorInfo()[2];
				writeInLog(2, "saveQueryBotSettings (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return "Databaseconnection failed";
	};
	
	/*
		Get Querybots
	*/
	function getQuerybots()
	{
		$returnArray				=	array();
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if (($data = $databaseConnection->query("SELECT * FROM  bot_connections")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					$returnArray	=	$data->fetchAll(PDO::FETCH_ASSOC);
				};
			}
			else
			{
				writeInLog(2, "getQuerybots (SQL Error):".$insert->errorInfo()[2]);
			};
		};
		
		return $returnArray;
	};
	
	/*
		Get Querybotinformation
	*/
	function getAllQueryBotInformation($id)
	{
		$returnArray				=	array();
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$data 					= 	$databaseConnection->prepare("
																		SELECT  con.id, con.botname, pat.*, afk.*, messages.* 
																		FROM  bot_connections con, bot_bad_pattern pat, bot_settigns_afk afk, bot_settings_messages messages 
																		WHERE con.id=:id AND pat.id=:id AND afk.id=:id AND messages.id=:id
																	");
			
			if ($data->execute(array(":id"=>$id)))
			{
				if ($data->rowCount() != 0)
				{
					$returnArray 	=	$data->fetch(PDO::FETCH_ASSOC);
				};
			};
		};
		
		return $returnArray;
	};
	
	/*
		Create Teamspeak Querybot
	*/
	function createQueryBot($instanz, $port, $name)
	{
		$serverId		=	getServerIdByPort($instanz, $port);
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$data 				= 	$databaseConnection->prepare("SELECT id FROM bot_connections  WHERE instance=:instance AND sid=:sid LIMIT 1");
			if ($data->execute(array(":instance"=>$instanz, ":sid"=>$serverId)))
			{
				if ($data->rowCount() == 0)
				{
					$insert		= 	$databaseConnection->prepare('INSERT INTO bot_connections (instance, sid, port, botname) VALUES (:instance, :sid, :port, :name)');
					if(!$insert->execute(array(":instance"=>$instanz, ":sid"=>$serverId, ":port"=>$port, ":name"=>$name)))
					{
						writeInLog(2, "createQueryBot (SQL Error):".$insert->errorInfo()[2]);
						return "Bot konnte nicht angelegt werden!";
					}
					else
					{
						if ($data->execute(array(":instance"=>$instanz, ":sid"=>$serverId)))
						{
							if ($data->rowCount() != 0)
							{
								$id 		= 	$data->fetch(PDO::FETCH_ASSOC)['id'];
								
								if($databaseConnection->exec('INSERT INTO bot_bad_pattern (id) VALUES (\'' . $id . '\')') === false)
								{
									writeInLog(2, "createQueryBot (SQL Error):".$insert->errorInfo()[2]);
									return "Bot wurde angelegt, allerdings konnte der Prozess nicht komplett beendet werden!";
								};
								
								if($databaseConnection->exec('INSERT INTO bot_settigns_afk (id) VALUES (\'' . $id . '\')') === false)
								{
									writeInLog(2, "createQueryBot (SQL Error):".$insert->errorInfo()[2]);
									return "Bot wurde angelegt, allerdings konnte der Prozess nicht komplett beendet werden!";
								};
								
								if($databaseConnection->exec('INSERT INTO bot_settings_messages (id) VALUES (\'' . $id . '\')') === false)
								{
									writeInLog(2, "createQueryBot (SQL Error):".$insert->errorInfo()[2]);
									return "Bot wurde angelegt, allerdings konnte der Prozess nicht komplett beendet werden!";
								};
							}
							else
							{
								return "Bot wurde angelegt, allerdings konnte der Prozess nicht komplett beendet werden!";
							};
						}
						else
						{
							writeInLog(2, "createQueryBot (SQL Error):".$data->errorInfo()[2]);
						};
					};
				}
				else
				{
					return "Bot ".$port." existiert bereits!";
				};
			}
			else
			{
				writeInLog(2, "createQueryBot (SQL Error):".$data->errorInfo()[2]);
			};
			
			return "Bot wurde erfolgreich hinzugef&uuml;gt";
		};
	};
	
	/*
		Delete Teamspeak Querybot
	*/
	function deleteQueryBot($id)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$delete		= 	$databaseConnection->prepare('DELETE FROM bot_bad_pattern WHERE id=:id');
			if(!$delete->execute(array(":id"=>$id)))
			{
				writeInLog(2, "deleteQueryBot (SQL Error):".$insert->errorInfo()[2]);
				return "Bot konnte nicht oder nur teilweise gel&ouml;scht werden!";
			};
			
			$delete		= 	$databaseConnection->prepare('DELETE FROM bot_settigns_afk WHERE id=:id');
			if(!$delete->execute(array(":id"=>$id)))
			{
				writeInLog(2, "deleteQueryBot (SQL Error):".$insert->errorInfo()[2]);
				return "Bot konnte nicht oder nur teilweise gel&ouml;scht werden!";
			};
			
			$delete		= 	$databaseConnection->prepare('DELETE FROM bot_settings_messages WHERE id=:id');
			if(!$delete->execute(array(":id"=>$id)))
			{
				writeInLog(2, "deleteQueryBot (SQL Error):".$insert->errorInfo()[2]);
				return "Bot konnte nicht oder nur teilweise gel&ouml;scht werden!";
			};
			
			$delete		= 	$databaseConnection->prepare('DELETE FROM bot_connections WHERE id=:id');
			if(!$delete->execute(array(":id"=>$id)))
			{
				writeInLog(2, "deleteQueryBot (SQL Error):".$insert->errorInfo()[2]);
				return "Bot konnte nicht oder nur teilweise gel&ouml;scht werden!";
			};
		};
		
		return "Bot erfolgreich gel&ouml;scht";
	};
?>