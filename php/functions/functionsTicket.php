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
	
	/*
		Definitions
	*/
	define("TICKETAREAS_PATH", __DIR__."/../../files/ticket/ticketareas.txt");
	
	/*
		Session start
	*/
	checkSession();
	
	/*
		Add Moderator
	*/
	function addModerator($value)
	{
		if(trim($value) != "")
		{
			$moderators = file_get_contents(TICKETAREAS_PATH);
			$moderators .= "\n".$value;
			file_put_contents(TICKETAREAS_PATH,$moderators);
		};
		
		return true;
	};
	
	/*
		Edit Moderator
	*/
	function editModerator($value, $oldValue)
	{
		$moderators 	= 	file_get_contents(TICKETAREAS_PATH);
		$moderators 	= 	str_replace($oldValue, $value, $moderators);
		file_put_contents(TICKETAREAS_PATH,$moderators);
		
		return true;
	};
	
	/*
		Delete Moderator
	*/
	function deleteModerator($oldValue)
	{
		$all 	= 	file_get_contents(TICKETAREAS_PATH);
		$all 	= 	explode("\n", $all);
		$save	=	"";
		foreach($all AS $moderator)
		{
			if(trim($moderator) != $oldValue)
			{
				$save	.=	$moderator."\n";
			};
		};
		file_put_contents(TICKETAREAS_PATH, trim($save));
		
		return true;
	};
	
	/*
		Ticket informations
	*/
	function getTicketInformations($pk, $admin = false)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if(!$admin)
			{
				if (($data = $databaseConnection->query("SELECT * FROM ticket_tickets WHERE pk='$pk'")) !== false)
				{
					if ($data->rowCount() > 0)
					{
						return $data->fetchAll(PDO::FETCH_ASSOC);
					};
				};
			}
			else
			{
				if (($data = $databaseConnection->query("SELECT * FROM ticket_tickets")) !== false)
				{
					if ($data->rowCount() > 0)
					{
						return $data->fetchAll(PDO::FETCH_ASSOC);
					};
				};
			};
		};
	};
	
	/*
		Get Answered Posts
	*/
	function view_answered($ticketId)
	{
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if (($data = $databaseConnection->query("SELECT * FROM ticket_answer WHERE ticketId='$ticketId'")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					return $data->fetchAll(PDO::FETCH_ASSOC);
				};
			};
		};
	};
	
	/*
		Add Ticket
	*/
	function addTicket($subject, $message, $department)
	{
		global $language;
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$pk			=	$_SESSION['user']['id'];
			$date 		= 	date('YmdHis');
			$data 		= 	$databaseConnection->prepare("SELECT status FROM ticket_tickets WHERE pk=:pk AND subject=:subject");
			
			if ($data->execute(array(":subject"=>$subject, ":pk"=>$pk)))
			{
				if ($data->rowCount() > 0)
				{
					return $language['ticket_subject_exists'];
				}
				else
				{
					$insert		= 	$databaseConnection->prepare('INSERT INTO ticket_tickets (pk,subject,msg,department,status,dateAded,dateActivity) VALUES (:pk, :subject, :message, :department, \'open\', \'' . $date . '\', \'' . $date . '\')');
					if(!$insert->execute(array(":pk"=>$pk, ":subject"=>$subject, ":message"=>$message, ":department"=>$department)))
					{
						writeInLog(2, "addTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
						return $databaseConnection->errorInfo()[2];
					}
					else
					{
						if(USE_MAILS == "true")
						{
							include_once("./functionsMail.php");
							
							$mailContent								=		array();
							$mailContent								=		getMail("create_ticket");
							
							$mailContent								=		str_replace("%heading%", 					HEADING, 									$mailContent);
							$mailContent								=		str_replace("%client%", 					$_SESSION['user']['benutzer'], 				$mailContent);
							
							writeMail($mailContent["headline"], $mailContent["mail_subject"], $_SESSION['user']['benutzer'], $mailContent["mail_body"]);
						};
						
						return $language['ticket_create'];
					};
				};
			};
		};
	};
	
	/*
		Answer Ticket
	*/
	function answerTicket($id, $message)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$date 		= 	date('YmdHis');
			$insert		= 	$databaseConnection->prepare('INSERT INTO ticket_answer (ticketId,pk,msg,moderator,dateAded) VALUES (:ticketId, :pk, :message, :moderator, \'' . $date . '\')');
			if(!$insert->execute(array(":ticketId"=>$id, ":pk"=>$_SESSION['user']['id'], ":message"=>$message, ":moderator"=>$_SESSION['user']['benutzer'])))
			{
				writeInLog(2, "addTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			}
			else
			{
				$databaseConnection->exec("UPDATE ticket_tickets SET dateActivity='".$date."' WHERE id='".$id."'");
				
				if(USE_MAILS == "true")
				{
					include_once("./functionsMail.php");
					
					$mailCreator								=		getMailCreator($databaseConnection, $id);
					
					if($mailCreator != $_SESSION['user']['benutzer'])
					{
						$mailContent							=		array();
						$mailContent							=		getMail("answer_ticket");
						
						$mailContent							=		str_replace("%heading%", 					HEADING, 									$mailContent);
						$mailContent							=		str_replace("%client%", 					$mailCreator, 								$mailContent);
						$mailContent							=		str_replace("%admin%", 						$_SESSION['user']['benutzer'], 				$mailContent);
						
						writeMail($mailContent["headline"], $mailContent["mail_subject"], $mailCreator, $mailContent["mail_body"]);
					};
				};
				
				return true;
			};
		};
		
		return false;
	};
	
	/*
		Close Ticket
	*/
	function closeTicket($id)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$date 		= 	date('YmdHis');
			
			if($databaseConnection->exec("UPDATE ticket_tickets SET status='closed', dateClosed='".$date."' WHERE id='".$id."'") === false)
			{
				writeInLog(2, "closeTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			}
			else
			{
				if(USE_MAILS == "true")
				{
					include_once("./functionsMail.php");
					
					$mailCreator								=		getMailCreator($databaseConnection, $id);
					
					if($mailCreator != $_SESSION['user']['benutzer'])
					{
						$mailContent							=		array();
						$mailContent							=		getMail("closed_ticket");
						
						$mailContent							=		str_replace("%heading%", 					HEADING, 									$mailContent);
						$mailContent							=		str_replace("%client%", 					$mailCreator, 								$mailContent);
						$mailContent							=		str_replace("%admin%", 						$_SESSION['user']['benutzer'], 				$mailContent);
						
						writeMail($mailContent["headline"], $mailContent["mail_subject"], $mailCreator, $mailContent["mail_body"]);
					};
				};
				
				return true;
			};
		};
		
		return false;
	};
	
	function getMailCreator($databaseConnection, $id)
	{
		if (($data = $databaseConnection->query("SELECT pk FROM  ticket_tickets WHERE id='".$id."' LIMIT 1")) !== false)
		{
			if ($data->rowCount() > 0)
			{
				$result 								= 	$data->fetch(PDO::FETCH_ASSOC);
				if (($mail = $databaseConnection->query("SELECT benutzer FROM  main_clients WHERE pk_client='".$result['pk']."' LIMIT 1")) !== false)
				{
					if ($mail->rowCount() > 0)
					{
						return $mail->fetch(PDO::FETCH_ASSOC)['benutzer'];
					};
				}
				else
				{
					writeInLog(2, "writeTicketMail (SQL Error):".$databaseConnection->errorInfo()[2]);
				};
			};
		}
		else
		{
			writeInLog(2, "writeTicketMail (SQL Error):".$databaseConnection->errorInfo()[2]);
		};
	};
	
	/*
		Delete Ticket
	*/
	function deleteTicket($id)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			if($databaseConnection->exec('DELETE FROM ticket_tickets WHERE id=\'' . $id . '\';') === false)
			{
				writeInLog(2, "deleteTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			}
			else
			{
				if($databaseConnection->exec('DELETE FROM ticket_answer WHERE ticketId=\'' . $id . '\';') === false)
				{
					writeInLog(2, "deleteTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
				}
				else
				{
					return true;
				};
			};
		};
		
		return false;
	};
	
	/*
		Ticket Timechanger
	*/
	function changeTimestamp($date)
	{
		$_date = $date;
		$new_date = date("Y-m-d H:i:s");
		$date = date_parse($date);
		
		if(!$date['year'] && !$date['month'] && !$date['day'] && !$date['hour'] && !$date['minute'] && !$date['second'])
		{
			return "Not closed";
		};
		
		$new_date = date_parse($new_date);
		
		$years_ago = $new_date["year"] - $date["year"];
		if($years_ago != 0)
		{
			if($years_ago == 1)
			{
				return $years_ago." year ago";
				exit();
			}
			else 
			{
				return $years_ago." years ago";
				exit();
			};
		};
		
		if($new_date["month"] == $date["month"] and $new_date["day"] == $date["day"] and $new_date["hour"] == $date["hour"] and $new_date["minute"] <= ($date["minute"] + 1))
		{
			return "Just now";
			exit();
		};
		
		$min_ago = $new_date["minute"] - $date["minute"];
		if($new_date["month"] == $date["month"] and $new_date["day"] == $date["day"] and $new_date["hour"] == $date["hour"])
		{
			return $min_ago." min ago";
			exit();
		};
		
		$hour_ago = $new_date["hour"] - $date["hour"];
		if($new_date["month"] == $date["month"] and $new_date["day"] == $date["day"])
		{
			if($hour_ago == 1)
			{
				return $hour_ago." hr ago";
				exit();
			}
			else 
			{
				return $hour_ago." hrs ago";
				exit();
			};
		};
		
		$day_ago = $new_date["day"] - $date["day"];
		if($new_date["month"] == $date["month"] and $day_ago <= 10)
		{
			if($day_ago == 1)
			{
				return $day_ago." day ago";
				exit();
			}
			else
			{
				return $day_ago." days ago";
				exit(); 
			};
		};
		
	     $dateModified = strtotime($_date);
		 $dateModified = date("M j, Y", $dateModified);
		 return $dateModified;
		 exit();
	};
?>