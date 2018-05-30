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
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("\?", $_SERVER['HTTP_REFERER'], -1);
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
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_ts3'] != $mysql_keys['right_hp_ts3'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
?>

<div id="adminContent">
	<!-- Modal: Instanz Shell -->
	<div id="modalShellInstanz" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalShellInstanzLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-info">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalShellInstanzLabel"><?php echo $language['instance']; ?> Shell</h4>
				</div>
				<div class="modal-body">
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							SSH Port:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="shellPort" type="number" class="form-control" value="22">
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['client']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="shellClient" type="text" class="form-control" placeholder="root">
						</div>
					</div>
					<div class="row user-einrueckung">
						<div class="col-lg-3 col-md-12 input-padding">
							<?php echo $language['folder_path']; ?>:
						</div>
						<div class="col-lg-9 col-md-12" style="text-align:center;">
							<input id="shellPath" type="text" class="form-control" placeholder="/home/teamspeak/">
						</div>
					</div>
					<div class="list-group">
						<a id="command-start" href="#" onClick="$(this).parent().children().removeClass('active-shell');$(this).addClass('active-shell');return false;" class="list-group-item list-group-item-action list-group-item-success active-shell">
							<h5 class="list-group-item-heading"><?php echo $language['shell_start_instanz']; ?></h5>
							<p class="list-group-item-text"><?php echo $language['shell_start_instanz_info']; ?></p>
						</a>
						<a id="command-stop" href="#" onClick="$(this).parent().children().removeClass('active-shell');$(this).addClass('active-shell');return false;" class="list-group-item list-group-item-action list-group-item-danger">
							<h5 class="list-group-item-heading"><?php echo $language['shell_stop_instanz']; ?></h5>
							<p class="list-group-item-text"><?php echo $language['shell_stop_instanz_info']; ?></p>
						</a>
						<a href="#" onClick="$(this).parent().children().removeClass('active-shell');$(this).addClass('active-shell');return false;" class="list-group-item list-group-item-action list-group-item-warning">
							<h5 class="list-group-item-heading"><?php echo $language['shell_restart_instanz']; ?></h5>
							<p class="list-group-item-text"><?php echo $language['shell_restart_instanz_info']; ?></p>
						</a>
					</div>
					<p style="text-align: center;margin-top: 10px;">
						<i class="fa fa-warning" aria-hidden="true"></i> <?php echo $language['shell_instanz_key_warning']; ?> <i class="fa fa-warning" aria-hidden="true"></i></p>
					</p>
					<div id="instanzShellConsoleBox" style="margin-top: 10px;display: none;" class="alert alert-info">
						<i class="fa fa-info"></i> Query <?php echo $language['console']; ?>
						<div id="instanzShellConsole" style="margin-top: 10px;"></div>
					</div>
					<button id="shellCommand" class="btn btn-success" style="width: 100%;margin-top: 20px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Instanz löschen -->
	<div id="modalDeleteInstanz" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalDeleteInstanzLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-danger">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalDeleteInstanzLabel"><?php echo $language['delete_instanz']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 modal-info">
							<p><?php echo $language['are_you_sure']; ?></p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['no']; ?></button>
					<button id="deleteInstanzBttn" type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-fw fa-check"></i> <?php echo $language['yes']; ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Instanz hinzufügen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-plus"></i> <?php echo $language['add_instanz']; ?></h4>
		</div>
		<div class="card-block">
			<p style="text-align:center;"><?php echo $language['instanz_info']; ?></p>
			<div class="row" style="padding:.75rem;">
				<div class="col-md-6 col-xs-6 instanz-padding">
					Alias <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="right" title="<?php echo $language['instanz_title_alias']; ?>"></i> :
				</div>
				<div class="col-md-6 col-xs-6" style="text-align:center;">
					<input id="adminCreateInstanzAlias" type="text" class="form-control">
				</div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-md-6 col-xs-6 instanz-padding">
					<?php echo $language['ip_adress']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="right" title="<?php echo $language['instanz_title_ip']; ?>"></i> :
				</div>
				<div class="col-md-6 col-xs-6" style="text-align:center;">
					<input id="adminCreateInstanzIp" type="text" class="form-control">
				</div>
			</div>

			<div class="row" style="padding:.75rem;">
				<div class="col-md-6 col-xs-6 instanz-padding">
					<?php echo $language['queryport']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="right" title="<?php echo $language['instanz_title_queryport']; ?>"></i> :
				</div>
				<div class="col-md-6 col-xs-6" style="text-align:center;">
					<input id="adminCreateInstanzQueryport" type="number" class="form-control">
				</div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-md-6 col-xs-6 instanz-padding">
					<?php echo $language['client']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="right" title="<?php echo $language['instanz_title_user']; ?>"></i> :
				</div>
				<div class="col-md-6 col-xs-6" style="text-align:center;">
					<input id="adminCreateInstanzClient" type="text" class="form-control">
				</div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-md-6 col-xs-6 instanz-padding">
					<?php echo $language['password']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="right" title="<?php echo $language['instanz_title_pw']; ?>"></i> :
				</div>
				<div class="col-md-6 col-xs-6" style="text-align:center;">
					<input id="adminCreateInstanzPassword" type="password" class="form-control">
				</div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-md-12 col-xs-12" style="text-align:center;">
					<button style="width:100%;" onClick="createInstanz()" class="btn btn-success" type="button"><i class="fa fa-save"></i> <?php echo $language['save']; ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Instanzen -->
	<?php foreach($ts3_server AS $instanz => $server)
	{ 
		$connection		=	checkTS3Connection($server['ip'], $server['queryport'], $server['user'], $server['pw']); ?>
		<div id="instanzMain<?php echo $instanz; ?>">
			<div class="card">
				<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideInstanz('<?php echo $instanz; ?>');">
					<h4 class="card-title">
						<div class="pull-xs-left">
							<i id="instanzIcon<?php echo $instanz; ?>" class="fa fa-arrow-right"></i> 
							<?php echo $language['instance']; ?>: 
							<?php 
								if($server['alias'] != '')
								{
									echo htmlspecialchars($server['alias']);
								}
								else
								{
									echo htmlspecialchars($server['ip']);
								};
							?>
						</div>
						<div class="label label-<?php if($connection) { echo "success"; } else { echo "danger"; }?> pull-xs-right" id="instanzTextBox<?php echo $instanz; ?>">
							<?php if($connection) { ?>
								<i class="fa fa-check"></i> <?php echo $language['success']; ?>
							<?php } else { ?>
								<i class="fa fa-close"></i> <?php echo $language['failed']; ?>
							<?php } ?>
						</div>
						<div style="clear:both;"></div>
					</h4>
				</div>
				<div class="card-block" id="instanzBox<?php echo $instanz; ?>" style="display:none;">
					<div class="row" style="padding:.75rem;">
						<div class="col-md-6 col-xs-6 instanz-padding">
							<?php echo $language['ip_adress']; ?>:
						</div>
						<div class="col-md-6 col-xs-6" style="text-align:center;">
							<div class="input-group">
								<input id="instanzIp<?php echo $instanz; ?>" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($server['ip']); ?>">
								<span class="input-group-btn">
									<button onClick="changeInstanz('ip', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="row" style="padding:.75rem;">
						<div class="col-md-6 col-xs-6 instanz-padding">
							<?php echo $language['queryport']; ?>:
						</div>
						<div class="col-md-6 col-xs-6" style="text-align:center;">
							<div class="input-group">
								<input id="instanzQueryport<?php echo $instanz; ?>" type="number" class="form-control" placeholder="<?php echo htmlspecialchars($server['queryport']); ?>">
								<span class="input-group-btn">
									<button onClick="changeInstanz('queryport', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="row" style="padding:.75rem;">
						<div class="col-md-6 col-xs-6 instanz-padding">
							<?php echo $language['client']; ?>:
						</div>
						<div class="col-md-6 col-xs-6" style="text-align:center;">
							<div class="input-group">
								<input id="instanzUser<?php echo $instanz; ?>" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($server['user']); ?>">
								<span class="input-group-btn">
									<button onClick="changeInstanz('user', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
								</span>
							</div>
						</div>
					</div>
					<div class="row" style="padding:.75rem;">
						<div class="col-md-6 col-xs-6 instanz-padding">
							<?php echo $language['password']; ?>:
						</div>
						<div class="col-md-6 col-xs-6" style="text-align:center;">
							<div class="input-group">
								<input id="instanzPassword<?php echo $instanz; ?>" type="password" class="form-control" placeholder="">
								<span class="input-group-btn">
									<button onClick="changeInstanz('pw', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
								</span>
							</div>
						</div>
					</div>
					<?php if($connection) { ?>
						<div class="row" style="padding:.75rem;">
							<div class="alert alert-info">
								<i class="fa fa-info"></i> Query <?php echo $language['console']; ?>
								
								<textarea id="commandInput<?php echo $instanz; ?>" class="form-control" style="width: 100%;" placeholder="Eingabe..."></textarea>
								<button instanz="<?php echo $instanz; ?>" class="btn btn-success commandQueryConsole" style="width: 100%;"><i class="fa fa-paper-plane" aria-hidden="true"></i> <?php echo $language['senden']; ?></button>
								
								<div style="margin-top: 10px;">
									<div class="pull-xs-left">
										<i class="fa fa-history" aria-hidden="true"></i> <?php echo $language['last_entrys']; ?>
									</div>
									<div class="pull-xs-right">
										<a remove-id="commandInputHistory<?php echo $instanz; ?>" href="#" class="clearConsole alert-link"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $language['clear']; ?></a>
									</div>
									<div style="clear:both;"></div>
								</div>
								<div id="commandInputHistory<?php echo $instanz; ?>" class="alert alert-info"></div>
								
								<div  style="margin-top: 10px;">
									<div class="pull-xs-left">
										<i class="fa fa-terminal" aria-hidden="true"></i> <?php echo $language['console']; ?>
									</div>
									<div class="pull-xs-right">
										<a remove-id="commandOutput<?php echo $instanz; ?>" href="#" class="clearConsole alert-link"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo $language['clear']; ?></a>
									</div>
									<div style="clear:both;"></div>
								</div>
								<div id="commandOutput<?php echo $instanz; ?>" class="alert alert-info" style="margin-top: 10px;"></div>
							</div>
						</div>
					<?php }; ?>
					<div class="row" style="padding:.75rem;">
						<div class="col-md-12 col-xs-12" style="text-align:center;">
							<button data-toggle="modal" data-target="#modalShellInstanz" data-ip="<?php echo $ts3_server[$instanz]['ip']; ?>" class="btn btn-info" type="button"><i class="fa fa-terminal"></i> Shell</button>
							<button data-toggle="modal" data-target="#modalDeleteInstanz" data-instanz="<?php echo $instanz; ?>" class="btn btn-danger" type="button"><i class="fa fa-trash"></i> <?php echo $language['delete_instanz']; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<!-- Sprachdatein laden -->
<script>
	var admin_instanz_change_done		=	'<?php echo $language['admin_instanz_change_done']; ?>';
	var admin_instanz_create_error		=	'<?php echo $language['admin_instanz_create_error']; ?>';
	var admin_instanz_exec_success		=	'<?php echo $language['admin_instanz_exec_success']; ?>';
	var admin_instanz_no_root			=	'<?php echo $language['admin_instanz_no_root']; ?>';
	
	var success							=	'<?php echo $language['success']; ?>';
	var failed							=	'<?php echo $language['failed']; ?>';
	
	queryConsoleSelected 				= 	new Array();
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/admin.js"></script>
<script>
	$(function () {
		$('[data-tooltip="tooltip"]').tooltip();
	});
	
	$('#modalDeleteInstanz').on('show.bs.modal', function (event)
	{
		var button 		=	$(event.relatedTarget);
		var instanz 	= 	button.data('instanz');
		
		$(this).find('.btn-danger').attr("data-instanz", instanz);
		
		document.getElementById('deleteInstanzBttn').addEventListener("click", function(){
			var dataString 		= 	'action=deleteInstanz&ts3_server='+$(this).attr('data-instanz');
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				dataTyp: "json",
				cache: false,
				success: function(data) {
					if(data == "error")
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
							icon: 'fa fa-warning'
						},{
							type: 'danger',
							allow_dismiss: true,
							placement:
							{
								from: 'bottom',
								align: 'right'
							}
						});
					}
					else
					{
						$("#instanzMain"+data).remove();
					};
				}
			});
		});
	});
	
	$('#modalShellInstanz').on('show.bs.modal', function (event)
	{
		var button 		=	$(event.relatedTarget);
		var ip		 	= 	button.data('ip');
		
		document.getElementById('shellCommand').addEventListener("click", function(){
			var command		=	"restart";
			
			if($("#command-start").hasClass("active-shell"))
			{
				command		=	"start";
			}
			else if($("#command-stop").hasClass("active-shell"))
			{
				command		=	"stop";
			};
			
			if($('#shellClient').val() != "" && $('#shellPort').val() != "")
			{
				if($('#shellClient').val() != 'root')
				{
					var dataString 		= 	'action=instanzShell&ip='+ip+'&port='+$('#shellPort').val()+'&username='+$('#shellClient').val()+'&command='+command+'&path='+$('#shellPath').val();
					$.ajax({
							type: "POST",
							url: "functionsShell.php",
							data: dataString,
							dataTyp: "json",
							cache: false,
							success: function(data) {
								document.getElementById("instanzShellConsole").innerHTML	=	data;
								$("#instanzShellConsoleBox").slideDown("slow");
							}
					});
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: admin_instanz_no_root,
						icon: 'fa fa-warning'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			};
		});
	});
</script>
<script src="js/sonstige/preloader.js"></script>