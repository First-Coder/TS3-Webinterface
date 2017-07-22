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
		Save Mail
	*/
	function saveMail($Headline = "", $betreff, $id, $body)
	{
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
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
				case "forgotPassword":
					$sqlId		=	"forgot_password";
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
		}
		else
		{
			return "SQL Connection Failed";
		};
	};
	
	/*
		Get Mailtemplete
	*/
	function getMail($id)
	{
		$infos							=	array();
		
		if(($databaseConnection = getSqlConnection(false)) !== false)
		{
			$sql						=	($id == "all") ? "SELECT * FROM main_mails" : "SELECT * FROM main_mails WHERE id=:id LIMIT 1";
			$data 						= 	$databaseConnection->prepare($sql);
			
			if ($data->execute(array(":id"=>$id)))
			{
				if ($data->rowCount() > 0)
				{
					if($id == "all")
					{
						$tmp 						= 	$data->fetchAll(PDO::FETCH_ASSOC);
						foreach($tmp AS $mail)
						{
							$infos[$mail['id']]		=	array();
							$infos[$mail['id']]		=	$mail;
						};
					}
					else
					{
						$infos 						= 	$data->fetchAll(PDO::FETCH_ASSOC)[0];
					};
				}
				else
				{
					writeInLog(5, "getMail: Selectresult is empty!");
				};
			}
			else
			{
				writeInLog(2, "getMail (SQL Error):".$databaseConnection->errorInfo()[2]);
			};
		};
		
		return $infos;
	};
	
	/*
		Write an Mail
	*/
	function writeMail($Headline = "", $betreff, $destinationMail, $body, $bodyAlt = "")
	{
		require_once(__dir__."/../classes/phpmailer.class.php");
		
		$mail 					= 		new PHPMailer();
		
		if(MAIL_SMTP == "true")
		{
			require_once(__dir__."/../classes/phpmailer.smtp.class.php");
			
			$mail->IsSMTP();
			$mail->SMTPAuth 	= 	true;
			$mail->SMTPSecure	=	MAIL_SMTP_ENCRYPTION;
			
			$mail->Host			= 	MAIL_SMTP_HOST;
			$mail->Port			= 	MAIL_SMTP_PORT;
			$mail->Username		= 	MAIL_SMTP_USERNAME;
			$mail->Password		= 	MAIL_SMTP_PASSWORD;
			$mail->SMTPDebug	= 	MAIL_SMTP_DEBUG;
		}
		else
		{
			// fix written by cjmwid
			if(isWindows())
			{
				$mail->isMail();
			}
			else
			{
				$mail->isSendmail();
			};
		};
		
		$mail->setFrom(MAILADRESS, HEADING.$Headline);
		$mail->AddAddress($destinationMail);
		
		if($betreff == "")
		{
			$mail->Subject = HEADING;
		}
		else
		{
			$mail->Subject = $betreff;
		};
		
		$mail->isHTML(true);
		$mail->Body    			= 		$body;
		$mail->AltBody 			= 		$bodyAlt;
		
		if(!$mail->Send())
		{
			writeInLog(2, "writeMail:".$mail->ErrorInfo);
			return "Error: " . $mail->ErrorInfo;
		}
		else
		{
			return "done";
		};
	};
?>