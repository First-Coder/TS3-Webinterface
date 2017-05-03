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
	require_once("functionsTicket.php");
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	
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
	$user_right		=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	$isAdmin				=	false;
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_ticket_system'] != $mysql_keys['right_hp_ticket_system'])
	{
		/*
			Get private information above the Client
		*/
		$userInformations	=	getUserInformations($_SESSION['user']['id']);
	}
	else
	{
		$isAdmin			=	true;
	};
?>

<div id="ticketContent">
	<?php if(!$isAdmin) { ?>
		<!-- Instanz hinzufügen -->
		<div class="card">
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-plus"></i> <?php echo $language['create_ticket']; ?></h4>
			</div>
			<div class="card-block">
				<table class="table table-condensed">
					<tbody>
						<tr>
							<td class="input-padding">
								<?php echo $language['mail']; ?>:
							</td>
							<td>
								<input type="text" class="form-control" value="<?php echo $_SESSION['user']['benutzer']; ?>" disabled>
							</td>
						</tr>
						<tr>
							<td class="input-padding">
								<?php echo $language['profile_perso_vorname']; ?>:
							</td>
							<td>
								<input type="text" class="form-control" placeholder="<?php echo $userInformations['vorname']; ?>" disabled>
							</td>
						</tr>
						<tr>
							<td class="input-padding">
								<?php echo $language['profile_perso_nachname']; ?>:
							</td>
							<td>
								<input type="text" class="form-control" placeholder="<?php echo $userInformations['nachname']; ?>" disabled>
							</td>
						</tr>
						<tr>
							<td class="input-padding">
								<?php echo $language['subject']; ?>:
							</td>
							<td>
								<input id="ticketBetreff" type="text" class="form-control">
							</td>
						</tr>
						<tr>
							<td class="input-padding">
								<?php echo $language['area']; ?>:
							</td>
							<td>
								<select id="ticketBereich" class="form-control" style="width:100%;">
									<?php 
										$get_option = file_get_contents("TicketBereich.txt");
										$exp_get_option = explode("\n", $get_option);
										foreach ($exp_get_option as $value)
										{
											echo '<option value="'.trim($value).'">'.trim($value).'</option>';
										};
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="input-padding">
								<?php echo $language['message']; ?>:
							</td>
							<td>
								<textarea id="ticketMessage" class="form-control" rows="5"></textarea>
							</td>
						</tr>
					</tbody>
				</table>
				<button onClick="createTicket('<?php echo $_SESSION['user']['id']; ?>', '<?php echo $_SESSION['user']['benutzer']; ?>', '<?php echo $userInformations['vorname']; ?>', '<?php echo $userInformations['nachname']; ?>');" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> Ticket <?php echo $language['create']; ?></button>
			</div>
		</div>
	<?php } else { ?>
		<!-- Ticketbereich hinzufügen -->
		<div class="card">
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-plus"></i> <?php echo $language['add_ticketarea']; ?></h4>
			</div>
			<div class="card-block">
				<table class="table table-condensed">
					<tbody>
						<tr>
							<td>
								<div class="input-group">
									<input id="ticketAddRoleText" type="text" class="form-control">
									<span class="input-group-btn">
										<button onClick="addModerator();" class="btn btn-success" type="button"><i class="fa fa-check"></i> <?php echo $language['add']; ?></button>
									</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-edit"></i> <?php echo $language['change_ticketarea']; ?></h4>
			</div>
			<div class="card-block">
				<table class="table table-condensed">
					<tbody>
						<?php 
							$get_option 		= 	file_get_contents("TicketBereich.txt");
							$exp_get_option 	= 	explode("\n", $get_option);
							$skip_first			=	true;
							foreach ($exp_get_option as $i => $value)
							{ ?>
								<tr id="ticketEditRoleBox<?php echo $i; ?>">
									<td>
										<div class="input-group">
											<input value="<?php echo htmlspecialchars(trim($value)); ?>" id="ticketEditRoleText<?php echo $i; ?>" type="text" class="form-control" oldValue="<?php echo trim($value); ?>">
											<span class="input-group-btn">
												<button onClick="editModerator('<?php echo $i; ?>');" class="btn btn-success" type="button"><i class="fa fa-check"></i> <?php echo strtolower($language['tree_modify']); ?></button>
											</span>
											<?php if(!$skip_first) { ?>
												<span class="input-group-btn">
													<button onClick="deleteModerator('<?php echo $i; ?>');" class="btn btn-danger" type="button"><i class="fa fa-trash"></i> <?php echo strtolower($language['delete']); ?></button>
												</span>
											<?php }; ?>
										</div>
									</td>
								</tr>
							<?php $skip_first = false;
							};
						?>
					</tbody>
				</table>
			</div>
		</div>
	<?php }; ?>
	
	<!-- Gepeicherte Tickets -->
	<?php 
	$TicketInformations			=	array();
	
	if(!$isAdmin)
	{
		$TicketInformations		=	getTicketInformations($_SESSION['user']['id']);
	}
	else
	{
		$TicketInformations		=	getTicketInformations($_SESSION['user']['id'], true);
	};
	
	foreach($TicketInformations AS $text) { ?>
		<div id="MainTicket<?php echo $text['id']; ?>" class="card">
			<div class="card-block card-block-header" style="cursor:pointer;" onClick="slideTicket('ticket<?php echo $text['id']; ?>', 'ticketIcon<?php echo $text['id']; ?>');">
				<h4 class="card-title">
					<div class="pull-xs-left">
						<i id="ticketIcon<?php echo $text['id']; ?>" class="fa fa-fw fa-arrow-right"></i> <?php echo "Ticket: ".$text['subject']; ?>
					</div>
					<div id="status<?php echo $text['id']; ?>" class="label label-<?php if($text['status'] == "open") { echo "success"; } else { echo "danger"; }?> pull-xs-right">
						<?php if($text['status'] == "open") { ?>
							<i class="fa fa-check"></i> <?php echo $language['open']; ?>
						<?php } else { ?>
							<i class="fa fa-close"></i> <?php echo $language['closed']; ?>
						<?php } ?>
					</div>
					<div style="clear:both;"></div>
				</h4>
			</div>
			<div class="card-block" style="display:none;" id="ticket<?php echo $text['id']; ?>">
				<div class="row" style="padding:.75rem;">
					<div class="col-lg-1"></div>
					<div class="col-lg-5 col-md-6">
						<?php echo $language['area']; ?>:
					</div>
					<div class="col-lg-5 col-md-6" style="text-align:center;">
						<b><?php echo htmlspecialchars($text['department']); ?></b>
					</div>
					<div class="col-lg-1"></div>
				</div>
				<div class="row" style="padding:.75rem;">
					<div class="col-lg-1"></div>
					<div class="col-lg-5 col-md-6">
						<?php echo $language['ts3_create_on']; ?>:
					</div>
					<div class="col-lg-5 col-md-6" style="text-align:center;">
						<b><?php echo changeTimestamp($text['dateAded']); ?></b>
					</div>
					<div class="col-lg-1"></div>
				</div>
				<div class="row" style="padding:.75rem;">
					<div class="col-lg-1"></div>
					<div class="col-lg-5 col-md-6">
						<?php echo $language['last_activity']; ?>:
					</div>
					<div class="col-lg-5 col-md-6" style="text-align:center;">
						<b><?php echo changeTimestamp($text['dateActivity']); ?></b>
					</div>
					<div class="col-lg-1"></div>
				</div>
				<div class="row" style="padding:.75rem;">
					<div class="col-lg-1"></div>
					<div class="col-lg-5 col-md-6">
						<?php echo $language['closed']; ?>:
					</div>
					<div id="closedDate<?php echo $text['id']; ?>" class="col-lg-5 col-md-6" style="text-align:center;font-weight:bold;">
						<?php echo changeTimestamp($text['dateClosed']); ?>
					</div>
					<div class="col-lg-1"></div>
				</div>
				
				<!-- Nachrichten -->
				<div style="margin-left:20px;margin-right:20px;">
					<div class="alert alert-<?php echo ($text['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
						<div style="float:left;">
							<?php echo $language['client']; ?>: <b><?php echo getUsernameFromPk($text['pk']); ?></b>
						</div>
						<div style="float:right;">
							<?php echo changeTimestamp($text['dateAded']); ?>
						</div>
						<div style="clear:both;" class="alert alert-<?php echo ($text['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
							<?php echo htmlspecialchars(urldecode($text['msg'])); ?>
						</div>
					</div>
					
					<?php foreach(view_answered($text['id']) AS $answer) { ?>
						<div class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
							<div style="float:left;">
								<?php echo $language['client']; ?>: <b><?php echo $answer['moderator']; ?></b>
							</div>
							<div style="float:right;">
								<?php echo changeTimestamp($answer['dateAded']); ?>
							</div>
							<div style="clear:both;" class="alert alert-<?php echo ($answer['pk'] == $_SESSION['user']['id']) ? "info" : "danger"; ?>">
								<?php echo htmlspecialchars(urldecode($answer['msg'])); ?>
							</div>
						</div>
					<?php }; ?>
					
					<?php if($text['status'] == "open") { ?>
						<div id="answerbox<?php echo $text['id']; ?>" class="alert alert-warning">
							<?php echo $language['answer']; ?>:
							<div class="alert alert-warning">
								<textarea id="answer<?php echo $text['id']; ?>" style="width:100%;" rows="5"></textarea>
							</div>
							<div class="row">
								<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
									<button onClick="closeTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> Ticket <?php echo strtolower($language['tick_close']); ?></button>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-md-offset-4 col-lg-offset-4">
									<button onClick="answerTicket('<?php echo $text['id']; ?>', '<?php echo $_SESSION['user']['id']; ?>', '<?php echo $_SESSION['user']['benutzer']; ?>');" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
								</div>
							</div>
						</div>
					<?php }; ?>
					<div class="row" style="<?php if($text['status'] == "open") { echo "display: none;"; }; ?>" id="deleteTicketSection<?php echo $text['id']; ?>">
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
							<button onClick="deleteTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> Ticket <?php echo strtolower($language['delete']); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }; ?>
</div>

<!-- Sprachdatein laden -->
<script>
	var success							=	'<?php echo $language['success']; ?>';
	var failed							=	'<?php echo $language['failed']; ?>';
	
	var ticket_add_moderator			=	'<?php echo $language['ticket_add_moderator']; ?>';
	var ticket_edit_moderator			=	'<?php echo $language['ticket_edit_moderator']; ?>';
	var ticket_delete_moderator			=	'<?php echo $language['ticket_delete_moderator']; ?>';
	var ticket_fill_all					=	'<?php echo $language['ticket_fill_all']; ?>';
	var ticket_create					=	'<?php echo $language['ticket_create']; ?>';
	var ticket_close					=	'<?php echo $language['ticket_close']; ?>';
	var ticket_answer					=	'<?php echo $language['ticket_answer']; ?>';
	var closedText						=	'<?php echo $language['closed']; ?>';
	var ticket_deleted					=	'<?php echo $language['ticket_deleted']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/ticket.js"></script>
<script src="js/sonstige/preloader.js"></script>