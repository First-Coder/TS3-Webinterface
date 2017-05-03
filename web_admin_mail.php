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
	require_once("lang.php");
	require_once("functions.php");
	require_once("functionsMail.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_mails'] != $mysql_keys['right_hp_mails'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		GetMails
	*/
	$create_request			=	getMail("create_request");
	$request_failed			=	getMail("request_failed");
	$request_success		=	getMail("request_success");
	
	$create_ticket			=	getMail("create_ticket");
	$answer_ticket			=	getMail("answer_ticket");
	$close_ticket			=	getMail("closed_ticket");
?>

<div id="adminContent">
	<!-- Request Create -->
	<div class="card alert-warning">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-edit"></i> <?php echo $language['server_request_created']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('createRequest');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="createRequestHeadline" class="form-control" placeholder="Headline" value="<?php echo htmlspecialchars($create_request["headline"]); ?>"/>
			<input id="createRequestTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($create_request["mail_subject"]); ?>"/>
			<button id="createRequestCodeBttn" onClick="showMailCode('createRequest');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="createRequestCode" class="display-none">
				<textarea class="form-control" id="createRequestBody"><?php echo htmlspecialchars($create_request["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
						<tr>
							<td><?php echo $language['password']; ?></td>
							<td>%password%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_servername']; ?></td>
							<td>%serverCreateServername%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_choose_port']; ?></td>
							<td>%serverCreatePort%</td>
						</tr>
						<tr>
							<td>Slots</td>
							<td>%serverCreateSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_reservierte_slots']; ?></td>
							<td>%serverCreateReservedSlots%</td>
						</tr>
						<tr>
							<td><?php echo "Server".strToLower($language['password']); ?></td>
							<td>%serverCreatePassword%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_welcome_message']; ?></td>
							<td>%serverCreateWelcomeMessage%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="createRequestTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('createRequest');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Request Succeed -->
	<div class="card alert-success">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-check"></i> <?php echo $language['server_request_success']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('requestYes');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="requestYesHeadline" class="form-control" placeholder="<?php echo "Headline"; ?>" value="<?php echo htmlspecialchars($request_success["headline"]); ?>"/>
			<input id="requestYesTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($request_success["mail_subject"]); ?>"/>
			<button id="requestYesCodeBttn" onClick="showMailCode('requestYes');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="requestYesCode" class="display-none">
				<textarea class="form-control" id="requestYesBody"><?php echo htmlspecialchars($request_success["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_servername']; ?></td>
							<td>%serverCreateServername%</td>
						</tr>
						<tr>
							<td>Server IP</td>
							<td>%ip%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_choose_port']; ?></td>
							<td>%serverCreatePort%</td>
						</tr>
						<tr>
							<td>Slots</td>
							<td>%serverCreateSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_reservierte_slots']; ?></td>
							<td>%serverCreateReservedSlots%</td>
						</tr>
						<tr>
							<td><?php echo "Server".strToLower($language['password']); ?></td>
							<td>%serverCreatePassword%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_welcome_message']; ?></td>
							<td>%serverCreateWelcomeMessage%</td>
						</tr>
						<tr>
							<td>Admintoken</td>
							<td>%token%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="requestYesTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('requestYes');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Request Failed -->
	<div class="card alert-danger">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-close"></i> <?php echo $language['server_request_failed']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('requestNo');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="requestNoHeadline" class="form-control" placeholder="<?php echo "Headline"; ?>" value="<?php echo htmlspecialchars($request_failed["headline"]); ?>"/>
			<input id="requestNoTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($request_failed["mail_subject"]); ?>"/>
			<button id="requestNoCodeBttn" onClick="showMailCode('requestNo');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="requestNoCode" class="display-none">
				<textarea class="form-control" id="requestNoBody"><?php echo htmlspecialchars($request_failed["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="requestNoTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('requestNo');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Ticket Create -->
	<div class="card alert-warning">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-ticket"></i> <?php echo $language['create_ticket']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('createTicket');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="createTicketHeadline" class="form-control" placeholder="Headline" value="<?php echo htmlspecialchars($create_ticket["headline"]); ?>"/>
			<input id="createTicketTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($create_ticket["mail_subject"]); ?>"/>
			<button id="createTicketCodeBttn" onClick="showMailCode('createTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="createTicketCode" class="display-none">
				<textarea class="form-control" id="createTicketBody"><?php echo htmlspecialchars($create_ticket["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="createTicketTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('createTicket');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Ticket answered -->
	<div class="card alert-warning">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-edit"></i> <?php echo $language['ticket_answered']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('answerTicket');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="answerTicketHeadline" class="form-control" placeholder="Headline" value="<?php echo htmlspecialchars($answer_ticket["headline"]); ?>"/>
			<input id="answerTicketTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($answer_ticket["mail_subject"]); ?>"/>
			<button id="answerTicketCodeBttn" onClick="showMailCode('answerTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="answerTicketCode" class="display-none">
				<textarea class="form-control" id="answerTicketBody"><?php echo htmlspecialchars($answer_ticket["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
						<tr>
							<td>Admin</td>
							<td>%admin%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="answerTicketTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('answerTicket');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Ticket closed -->
	<div class="card alert-success">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-close"></i> Ticket <?php echo $language['closed']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('closeTicket');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="closeTicketHeadline" class="form-control" placeholder="Headline" value="<?php echo htmlspecialchars($close_ticket["headline"]); ?>"/>
			<input id="closeTicketTitle" class="form-control" placeholder="<?php echo $language['title']; ?>" value="<?php echo htmlspecialchars($close_ticket["mail_subject"]); ?>"/>
			<button id="closeTicketCodeBttn" onClick="showMailCode('closeTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="closeTicketCode" class="display-none">
				<textarea class="form-control" id="closeTicketBody"><?php echo htmlspecialchars($close_ticket["mail_body"]); ?></textarea>
				<table style="font-size: 0.8em;" class="table">
					<thead>
						<th colspan="2"><?php echo $language['legend']; ?></th>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $language['webinterface_title']; ?></td>
							<td>%heading%</td>
						</tr>
						<tr>
							<td><?php echo $language['client']; ?></td>
							<td>%client%</td>
						</tr>
						<tr>
							<td>Admin</td>
							<td>%admin%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="closeTicketTestMail" type="text" class="form-control" placeholder="Test E-Mail">
				<span class="input-group-btn">
					<button onClick="sendTestMail('closeTicket');" class="btn btn-primary" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var username_needs				=	'<?php echo $language['username_needs']; ?>';
	var emailSended					=	'<?php echo $language['email_sended']; ?>';
	var emailSaved					=	'<?php echo $language['email_saved']; ?>';
	
	var CreateRequestEditor;
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/admin.js"></script>
<script src="js/mail/codemirror.js"></script>
<script src="js/mail/markdown.js"></script>
<script src="js/mail/xml.js"></script>
<script>
	$(document).ready(function() {
		setTimeout(function(){
			CreateRequestEditor = CodeMirror.fromTextArea(document.getElementById("createRequestBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
			RequestYesEditor = CodeMirror.fromTextArea(document.getElementById("requestYesBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
			RequestNoEditor = CodeMirror.fromTextArea(document.getElementById("requestNoBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
			CreateTicketEditor = CodeMirror.fromTextArea(document.getElementById("createTicketBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
			AnswerTicketEditor = CodeMirror.fromTextArea(document.getElementById("answerTicketBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
			CloseTicketEditor = CodeMirror.fromTextArea(document.getElementById("closeTicketBody"), {
				mode: 'markdown',
				lineNumbers: true,
				theme: "default",
				extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
			});
		}, 200);
	});
</script>
<script src="js/sonstige/preloader.js"></script>