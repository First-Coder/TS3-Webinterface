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
	require_once(__DIR__."/../../php/functions/functionsTicket.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
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
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	$isAdmin				=	false;
	if($user_right['right_hp_ticket_system']['key'] != $mysql_keys['right_hp_ticket_system'])
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
		<div class="card">
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-plus"></i> <?php echo $language['create_ticket']; ?></h4>
			</div>
			<div class="card-block">
				<div class="form-group">
					<label><?php echo $language['mail']; ?></label>
					<input type="text" class="form-control" value="<?php echo $_SESSION['user']['benutzer']; ?>" disabled>
				</div>
				<div class="form-group">
					<label><?php echo $language['firstname']; ?></label>
					<input type="text" class="form-control" value="<?php xssEcho($userInformations['vorname']); ?>" disabled>
				</div>
				<div class="form-group">
					<label><?php echo $language['lastname']; ?></label>
					<input type="text" class="form-control" value="<?php xssEcho($userInformations['nachname']); ?>" disabled>
				</div>
				<div class="form-group">
					<label><?php echo $language['subject']; ?></label>
					<input id="ticketBetreff" type="text" class="form-control">
				</div>
				<div class="form-group">
					<label><?php echo $language['area']; ?></label>
					<select id="ticketBereich" class="form-control c-select" style="width:100%;">
						<?php 
							$get_option 	= 	file_get_contents(TICKETAREAS_PATH);
							$exp_get_option = 	explode("\n", $get_option);
							foreach ($exp_get_option as $value)
							{
								echo '<option value="'.trim($value).'">'.xssSafe(trim($value)).'</option>';
							};
						?>
					</select>
				</div>
				<div class="form-group">
					<label><?php echo $language['message']; ?></label>
					<textarea id="ticketMessage" class="form-control" rows="5"></textarea>
				</div>
				<button onClick="createTicket();" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> Ticket <?php echo $language['create']; ?></button>
			</div>
		</div>
	<?php } else { ?>
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
							$get_option 		= 	file_get_contents("../../files/ticket/ticketareas.txt");
							$exp_get_option 	= 	explode("\n", $get_option);
							$skip_first			=	true;
							foreach ($exp_get_option as $i => $value)
							{ ?>
								<tr id="ticketEditRoleBox<?php echo $i; ?>">
									<td>
										<div class="input-group">
											<input value="<?php xssEcho(trim($value)); ?>" id="ticketEditRoleText<?php echo $i; ?>" type="text" class="form-control" oldValue="<?php echo trim($value); ?>">
											<span class="input-group-btn">
												<button onClick="editModerator('<?php echo $i; ?>');" class="btn btn-success <?php echo (!$skip_first) ? "no-border-radius" : ""; ?>" type="button"><i class="fa fa-check"></i> <?php echo $language['change']; ?></button>
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
	
	if(!empty($TicketInformations))
	{
		foreach($TicketInformations AS $text) { ?>
			<div id="MainTicket<?php echo $text['id']; ?>" class="card">
				<div class="card-block card-block-header" style="cursor:pointer;" onClick="slideMe('ticket<?php echo $text['id']; ?>', 'ticketIcon<?php echo $text['id']; ?>');">
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
							<?php echo $language['ticket_id']; ?>:
						</div>
						<div class="col-lg-5 col-md-6" style="text-align:center;">
							<b><?php echo $text['id']; ?></b>
						</div>
						<div class="col-lg-1"></div>
					</div>
					<div class="row" style="padding:.75rem;">
						<div class="col-lg-1"></div>
						<div class="col-lg-5 col-md-6">
							<?php echo $language['area']; ?>:
						</div>
						<div class="col-lg-5 col-md-6" style="text-align:center;">
							<b><?php xssEcho($text['department']); ?></b>
						</div>
						<div class="col-lg-1"></div>
					</div>
					<div class="row" style="padding:.75rem;">
						<div class="col-lg-1"></div>
						<div class="col-lg-5 col-md-6">
							<?php echo $language['create_on']; ?>:
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
								<?php xssEcho(urldecode($text['msg'])); ?>
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
									<?php xssEcho(urldecode($answer['msg'])); ?>
								</div>
							</div>
						<?php }; ?>
						
						<?php if($text['status'] == "open") { ?>
							<div id="answerbox<?php echo $text['id']; ?>" class="alert alert-warning">
								<?php echo $language['answer']; ?>:
								<div class="alert alert-warning">
									<textarea class="form-control" id="answer<?php echo $text['id']; ?>" style="width:100%;" rows="5"></textarea>
								</div>
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4" style="margin-bottom: 10px;">
										<button onClick="closeTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> <?php echo $language['close_ticket']; ?></button>
									</div>
									<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 col-md-offset-4 col-lg-offset-4" style="margin-bottom: 10px;">
										<button onClick="answerTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-success"><i class="fa fa-fw fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
									</div>
								</div>
							</div>
						<?php }; ?>
						<?php if(TICKET_CAN_BE_DELETED == "true" && $isAdmin) { ?>
							<div class="row" style="<?php if($text['status'] == "open") { echo "display: none;"; }; ?>" id="deleteTicketSection<?php echo $text['id']; ?>">
								<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
									<button onClick="deleteTicket('<?php echo $text['id']; ?>');" style="width:100%;" class="btn btn-danger"><i class="fa fa-fw fa-close"></i> <?php echo $language['delete_ticket']; ?></button>
								</div>
							</div>
						<?php }; ?>
					</div>
				</div>
			</div>
		<?php };
	}; ?>
</div>

<!-- Javascripte Laden -->
<script src="js/webinterface/ticket.js"></script>
<script src="js/sonstige/preloader.js"></script>