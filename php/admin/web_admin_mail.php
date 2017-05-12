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
	require_once(__DIR__."/../../config/config.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	require_once(__DIR__."/../../php/functions/functionsMail.php");
	
	/*
		Variables
	*/
	$LoggedIn		=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right				=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_mails']['key'] != $mysql_keys['right_hp_mails'])
	{
		reloadSite();
	};
	
	/*
		GetMails
	*/
	$mails					=	getMail("all");
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
			<input id="createRequestHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['create_request']['headline']); ?>"/>
			<input id="createRequestTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['create_request']['mail_subject']); ?>"/>
			<button id="createRequestCodeBttn" onClick="showMailCode('createRequest');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="createRequestCode" style="display: none;">
				<textarea class="form-control" id="createRequestBody"><?php echo htmlspecialchars($mails['create_request']['mail_body']); ?></textarea>
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
							<td><?php echo $language['slots']; ?></td>
							<td>%serverCreateSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_reservierte_slots']; ?></td>
							<td>%serverCreateReservedSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['server_password']; ?></td>
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
				<input id="createRequestTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('createRequest');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
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
			<input id="requestYesHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['request_success']['headline']); ?>"/>
			<input id="requestYesTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['request_success']['mail_subject']); ?>"/>
			<button id="requestYesCodeBttn" onClick="showMailCode('requestYes');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="requestYesCode" style="display: none;">
				<textarea class="form-control" id="requestYesBody"><?php echo htmlspecialchars($mails['request_success']['mail_body']); ?></textarea>
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
							<td><?php echo $language['server_ip']; ?></td>
							<td>%ip%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_choose_port']; ?></td>
							<td>%serverCreatePort%</td>
						</tr>
						<tr>
							<td><?php echo $language['slots']; ?></td>
							<td>%serverCreateSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_reservierte_slots']; ?></td>
							<td>%serverCreateReservedSlots%</td>
						</tr>
						<tr>
							<td><?php echo $language['server_password']; ?></td>
							<td>%serverCreatePassword%</td>
						</tr>
						<tr>
							<td><?php echo $language['ts3_welcome_message']; ?></td>
							<td>%serverCreateWelcomeMessage%</td>
						</tr>
						<tr>
							<td><?php echo $language['admintoken']; ?></td>
							<td>%token%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="requestYesTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('requestYes');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
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
			<input id="requestNoHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['request_failed']['headline']); ?>"/>
			<input id="requestNoTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['request_failed']['mail_subject']); ?>"/>
			<button id="requestNoCodeBttn" onClick="showMailCode('requestNo');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="requestNoCode" style="display: none;">
				<textarea class="form-control" id="requestNoBody"><?php echo htmlspecialchars($mails['request_failed']['mail_body']); ?></textarea>
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
				<input id="requestNoTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('requestNo');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
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
			<input id="createTicketHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['create_ticket']['headline']); ?>"/>
			<input id="createTicketTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['create_ticket']['mail_subject']); ?>"/>
			<button id="createTicketCodeBttn" onClick="showMailCode('createTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="createTicketCode" style="display: none;">
				<textarea class="form-control" id="createTicketBody"><?php echo htmlspecialchars($mails['create_ticket']['mail_body']); ?></textarea>
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
				<input id="createTicketTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('createTicket');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
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
			<input id="answerTicketHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['answer_ticket']['headline']); ?>"/>
			<input id="answerTicketTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['answer_ticket']['mail_subject']); ?>"/>
			<button id="answerTicketCodeBttn" onClick="showMailCode('answerTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="answerTicketCode" style="display: none;">
				<textarea class="form-control" id="answerTicketBody"><?php echo htmlspecialchars($mails['answer_ticket']['mail_body']); ?></textarea>
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
							<td><?php echo $language['admin']; ?></td>
							<td>%admin%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="answerTicketTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('answerTicket');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Ticket closed -->
	<div class="card alert-success">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-close"></i> <?php echo $language['ticket_closed']; ?>
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
			<input id="closeTicketHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['closed_ticket']['headline']); ?>"/>
			<input id="closeTicketTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['closed_ticket']['mail_subject']); ?>"/>
			<button id="closeTicketCodeBttn" onClick="showMailCode('closeTicket');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="closeTicketCode" style="display: none;">
				<textarea class="form-control" id="closeTicketBody"><?php echo htmlspecialchars($mails['closed_ticket']['mail_body']); ?></textarea>
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
				<input id="closeTicketTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('closeTicket');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
	<!-- Forgot password -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-close"></i> <?php echo $language['forgot_access']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="saveMail('forgotPassword');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<input id="forgotPasswordHeadline" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['headline']; ?>" value="<?php xssEcho($mails['forgot_password']['headline']); ?>"/>
			<input id="forgotPasswordTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>" value="<?php xssEcho($mails['forgot_password']['mail_subject']); ?>"/>
			<button id="forgotPasswordCodeBttn" onClick="showMailCode('forgotPassword');" class="btn btn-secondary" style="width: 100%;"><?php echo $language['show_code']; ?></button>
			<div id="forgotPasswordCode" style="display: none;">
				<textarea class="form-control" id="forgotPasswordBody"><?php echo htmlspecialchars($mails['forgot_password']['mail_body']); ?></textarea>
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
							<td><?php echo $language['new_pw']; ?></td>
							<td>%newpw%</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="input-group" style="margin-top: 10px;">
				<input id="forgotPasswordTestMail" type="text" class="form-control" placeholder="<?php echo $language['test_mail']; ?>">
				<span class="input-group-btn">
					<button onClick="sendTestMail('forgotPassword');" class="btn btn-custom" type="button"><i class="fa fa-fw fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
				</span>
			</div>
		</div>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/mail/codemirror.js"></script>
<script src="js/mail/markdown.js"></script>
<script src="js/mail/xml.js"></script>
<script>
	var CodeEditor						=	{
		createRequest: 	{ getValue:	function () { return document.getElementById("createRequestBody").value } 	},
		requestYes: 	{ getValue:	function () { return document.getElementById("requestYesBody").value } 		},
		requestNo: 		{ getValue:	function () { return document.getElementById("requestNoBody").value } 		},
		createTicket:	{ getValue:	function () { return document.getElementById("createTicketBody").value } 	},
		answerTicket:	{ getValue:	function () { return document.getElementById("answerTicketBody").value } 	},
		closeTicket:	{ getValue:	function () { return document.getElementById("closeTicketBody").value } 	},
		forgotPassword:	{ getValue:	function () { return document.getElementById("forgotPasswordBody").value } 	}
	};
	
	var options							=	{
		mode: 'markdown',
		lineNumbers: true,
		theme: "default",
		extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"}
	};
	
	function showMailCode(id)
	{
		if(document.getElementById(escapeText(id+'Code')))
		{
			document.getElementById(id+'Code').style.display 		= "inline";
		};
		
		if(document.getElementById(escapeText(id+'CodeBttn')))
		{
			document.getElementById(id+'CodeBttn').style.display 	= "none";
		};
		
		switch(escapeText(id))
		{
			case "createRequest":
				CodeEditor.createRequest = CodeMirror.fromTextArea(document.getElementById("createRequestBody"), options);
				break;
			case "requestYes":
				CodeEditor.requestYes = CodeMirror.fromTextArea(document.getElementById("requestYesBody"), options);
				break;
			case "requestNo":
				CodeEditor.requestNo = CodeMirror.fromTextArea(document.getElementById("requestNoBody"), options);
				break;
			case "createTicket":
				CodeEditor.createTicket = CodeMirror.fromTextArea(document.getElementById("createTicketBody"), options);
				break;
			case "answerTicket":
				CodeEditor.answerTicket = CodeMirror.fromTextArea(document.getElementById("answerTicketBody"), options);
				break;
			case "closeTicket":
				CodeEditor.closeTicket = CodeMirror.fromTextArea(document.getElementById("closeTicketBody"), options);
				break;
			case "forgotPassword":
				CodeEditor.forgotPassword = CodeMirror.fromTextArea(document.getElementById("forgotPasswordBody"), options);
				break;
		};
	};
</script>
<script src="js/webinterface/admin.js"></script>
<script src="js/sonstige/preloader.js"></script>