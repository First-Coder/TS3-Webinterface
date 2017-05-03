<?php 
	/*
		First-Coder Teamspeak 3 Webinterface
		
		File: funtctionsTicket.php
		
		written by L.Gmann
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
	require_once("functionsMail.php");
	
	/*
		Add Moderator
	*/
	function addModerator($value)
	{
		if(trim(htmlspecialchars(strip_tags($value))) != "")
		{
			$moderators = file_get_contents("TicketBereich.txt");
			$moderators .= "\n".htmlspecialchars(strip_tags($value));
			file_put_contents("TicketBereich.txt",$moderators);
		};
		
		return true;
	};
	
	/*
		Edit Moderator
	*/
	function editModerator($value, $oldValue)
	{
		$moderators 	= 	file_get_contents("TicketBereich.txt");
		$moderators 	= 	str_replace($oldValue, strip_tags($value), $moderators);
		file_put_contents("TicketBereich.txt",$moderators);
		
		return true;
	};
	
	/*
		Delete Moderator
	*/
	function deleteModerator($oldValue)
	{
		$all 	= 	file_get_contents("TicketBereich.txt");
		$all 	= 	explode("\n", $all);
		unlink("TicketBereich.txt");
		$fp 	= 	fopen("TicketBereich.txt", "w");
		fwrite($fp, $all[0]);
		for($j = 1; $j < count($all); $j++)
		{
			if(trim($all[$j]) != $oldValue)
			{
				fwrite($fp, "\n".$all[$j]);
			};
		};
		fclose($fp);
		
		return true;
	};
	
	/*
		Add Ticket
	*/
	function addTicket($pk, $subject, $message, $department)
	{
		include("_mysql.php");
		
		$date 		= 	date('YmdHis');
		$message 	= 	strip_tags(urlencode($message));
		$data 		= 	$databaseConnection->prepare("SELECT status FROM ticket_tickets WHERE pk=:pk AND subject=:subject");
		
		if ($data->execute(array(":subject"=>$subject, ":pk"=>$pk)))
		{
			if ($data->rowCount() > 0)
			{
				return false;
			}
			else
			{
				$insert		= 	$databaseConnection->prepare('INSERT INTO ticket_tickets (pk,subject,msg,department,status,dateAded,dateActivity) VALUES (:pk, :subject, :message, :department, \'open\', \'' . $date . '\', \'' . $date . '\')');
				if(!$insert->execute(array(":pk"=>$pk, ":subject"=>$subject, ":message"=>$message, ":department"=>$department)))
				{
					writeInLog(2, "addTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
					return false;
				}
				else
				{
					$mailContent								=		array();
					$mailContent								=		getMail("create_ticket");
					
					$mailContent								=		str_replace("%heading%", 					HEADING, 									$mailContent);
					$mailContent								=		str_replace("%client%", 					$_SESSION['user']['benutzer'], 				$mailContent);
					
					writeMail($mailContent["headline"], $mailContent["mail_subject"], $_SESSION['user']['benutzer'], $mailContent["mail_body"]);
					
					return true;
				};
			};
		};
	};
	
	/*
		Close Ticket
	*/
	function closeTicket($id)
	{
		include("_mysql.php");
		
		$date 		= 	date('YmdHis');
		
		if($databaseConnection->exec("UPDATE ticket_tickets SET status='closed', dateClosed='".$date."' WHERE id='".$id."'") === false)
		{
			writeInLog(2, "closeTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			return false;
		}
		else
		{
			$mailCreator								=		getMailCreator($id);
			
			if($mailCreator != $_SESSION['user']['benutzer'])
			{
				$mailContent							=		array();
				$mailContent							=		getMail("closed_ticket");
				
				$mailContent							=		str_replace("%heading%", 					HEADING, 									$mailContent);
				$mailContent							=		str_replace("%client%", 					$mailCreator, 								$mailContent);
				$mailContent							=		str_replace("%admin%", 						$_SESSION['user']['benutzer'], 				$mailContent);
				
				writeMail($mailContent["headline"], $mailContent["mail_subject"], $mailCreator, $mailContent["mail_body"]);
			};
			
			return true;
		};
	};
	
	/*
		Delete Ticket
	*/
	function deleteTicket($id)
	{
		include("_mysql.php");
		
		if($databaseConnection->exec('DELETE FROM ticket_tickets WHERE id=\'' . $id . '\';') === false)
		{
			writeInLog(2, "deleteTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			return false;
		}
		else
		{
			if($databaseConnection->exec('DELETE FROM ticket_answer WHERE ticketId=\'' . $id . '\';') === false)
			{
				writeInLog(2, "deleteTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
				return false;
			}
			else
			{
				return true;
			};
		};
	};
	
	/*
		Answer Ticket
	*/
	function answerTicket($id, $pk, $message, $moderator)
	{
		include("_mysql.php");
		
		// Add to the Database
		$date 		= 	date('YmdHis');
		$message 	= 	strip_tags(urlencode($message));
		
		$insert		= 	$databaseConnection->prepare('INSERT INTO ticket_answer (ticketId,pk,msg,moderator,dateAded) VALUES (:ticketId, :pk, :message, :moderator, \'' . $date . '\')');
		if(!$insert->execute(array(":ticketId"=>$id, ":pk"=>$pk, ":message"=>$message, ":moderator"=>$moderator)))
		{
			writeInLog(2, "addTicket (SQL Error):".$databaseConnection->errorInfo()[2]);
			return false;
		}
		else
		{
			$mailCreator								=		getMailCreator($id);
			
			if($mailCreator != $_SESSION['user']['benutzer'])
			{
				$mailContent							=		array();
				$mailContent							=		getMail("answer_ticket");
				
				$mailContent							=		str_replace("%heading%", 					HEADING, 									$mailContent);
				$mailContent							=		str_replace("%client%", 					$mailCreator, 								$mailContent);
				$mailContent							=		str_replace("%admin%", 						$_SESSION['user']['benutzer'], 				$mailContent);
				
				writeMail($mailContent["headline"], $mailContent["mail_subject"], $mailCreator, $mailContent["mail_body"]);
			};
			
			return true;
		};
	};
	
	function getMailCreator($id)
	{
		include("_mysql.php");
		
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
		Ticket informations
	*/
	function getTicketInformations($pk, $admin = false)
	{
		include("_mysql.php");
		
		if(!$admin)
		{
			if (($data = $databaseConnection->query("SELECT * FROM ticket_tickets WHERE pk='$pk'")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					return($data->fetchAll(PDO::FETCH_ASSOC));
				};
			};
		}
		else
		{
			if (($data = $databaseConnection->query("SELECT * FROM ticket_tickets")) !== false)
			{
				if ($data->rowCount() > 0)
				{
					return($data->fetchAll(PDO::FETCH_ASSOC));
				};
			};
		};
	};
	
	/*
		Get Answered Posts
	*/
	function view_answered($ticketId)
	{
		
		include("_mysql.php");
		
		if (($data = $databaseConnection->query("SELECT * FROM ticket_answer WHERE ticketId='$ticketId'")) !== false)
		{
			if ($data->rowCount() > 0)
			{
				return($data->fetchAll(PDO::FETCH_ASSOC));
			};
		};
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