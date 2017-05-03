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
	session_start();
	
	/*
		Includes
	*/
	require_once("config.php");
	require_once("lang.php");
	require_once("phpmailer.class.php");
	
	/*
		Save Mail
	*/
	function saveMail($Headline = "", $betreff, $id, $body)
	{
		include("_mysql.php");
		
		$sqlId				=	"";
		
		switch($id)
		{
			case "createRequest":
				$sqlId		=	"create_request";
				break;
			case "requestNo":
				$sqlId		=	"request_failed";
				break;
			case "requestYes":
				$sqlId		=	"request_success";
				break;
			case "createTicket":
				$sqlId		=	"create_ticket";
				break;
			case "answerTicket":
				$sqlId		=	"answer_ticket";
				break;
			case "closeTicket":
				$sqlId		=	"closed_ticket";
				break;
		};
		
		if($sqlId != "")
		{
			$_sql 			= 	"UPDATE main_mails SET headline=:headline, mail_subject=:subject, mail_body=:body WHERE id='".$sqlId."'";
			$data 			= 	$databaseConnection->prepare($_sql);
			$data->bindValue(':headline', $Headline);
			$data->bindValue(':subject', $betreff);
			$data->bindValue(':body', $body);
			if(!$data->execute())
			{
				writeInLog(2, "saveMail (SQL Error):".$databaseConnection->errorInfo()[2]);
				return $databaseConnection->errorInfo()[2];
			}
			else
			{
				return "done";
			};
		}
		else
		{
			writeInLog(2, "saveMail: Parameter Id is not clearly enough!");
			return "Parameter Id is not clearly enough!";
		};
	};
	
	/*
		Get Mailtemplete
	*/
	function getMail($id)
	{
		include("_mysql.php");
		
		$_sql 			= 	"SELECT * FROM main_mails WHERE id=:id LIMIT 1";
		$data 			= 	$databaseConnection->prepare($_sql);
		
		if ($data->execute(array(":id"=>$id)))
		{
			if ($data->rowCount() > 0)
			{
				$result 								= 	$data->fetchAll(PDO::FETCH_ASSOC);
				
				foreach($result AS $infos)
				{
					return $infos;
				};
			}
			else
			{
				writeInLog(5, "getMail: Selectresult is empty!");
				return false;
			};
		}
		else
		{
			writeInLog(2, "getMail (SQL Error):".$databaseConnection->errorInfo()[2]);
			return false;
		};
	};
	
	/*
		Write an Mail
	*/
	function writeMail($Headline = "", $betreff, $destinationMail, $body, $bodyAlt = "")
	{
		//Instanz von PHPMailer bilden
		$mail 					= 		new PHPMailer();
		
		if(MAIL_SMTP == "true")
		{
			require_once("phpmailer.smtp.class.php");
			
			$mail->IsSMTP();
			$mail->SMTPAuth 	= 	true;
			
			$mail->Host			= 	MAIL_SMTP_HOST;
			$mail->Port			= 	MAIL_SMTP_PORT;
			$mail->Username		= 	MAIL_SMTP_USERNAME;
			$mail->Password		= 	MAIL_SMTP_PASSWORD;
			$mail->SMTPDebug	= 	MAIL_SMTP_DEBUG;
		}
		else
		{
			// fix written by cjmwid
			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				$mail->isMail();
			}
			else
			{
				$mail->isSendmail();
			};
		};

		//Absenderadresse der Email setzen
		$mail->setFrom(MAILADRESS, HEADING.$Headline);

		//Empfängeradresse setzen
		$mail->AddAddress($destinationMail);

		//Betreff der Email setzen
		if($betreff == "")
		{
			$mail->Subject = HEADING;
		}
		else
		{
			$mail->Subject = $betreff;
		};
		
		// Mail als HTML setzen
		$mail->isHTML(true);
		
		//Text der EMail setzen
		$mail->Body    			= 		$body;
		$mail->AltBody 			= 		$bodyAlt;
		
		//EMail senden und überprüfen ob sie versandt wurde
		if(!$mail->Send())
		{
			writeInLog(2, "writeMail:".$mail->ErrorInfo);
			return "Error: " . $mail->ErrorInfo;
		}
		else
		{
			//$mail->Send() liefert TRUE zurück: Die Email ist unterwegs
			return "done";
		};
	};
?>