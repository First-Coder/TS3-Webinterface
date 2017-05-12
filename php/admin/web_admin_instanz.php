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
	require_once(__DIR__."/../../config/instance.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	
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
	if($user_right['right_hp_ts3']['key'] != $mysql_keys['right_hp_ts3'])
	{
		reloadSite();
	};
?>

<div id="adminContent">
	<!-- Modal: Instanz Shell -->
	<div id="modalShellInstanz" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalShellInstanzLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-info">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalShellInstanzLabel"><?php echo $language['instance_shell']; ?></h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="shellPort"><?php echo $language['ssh_port']; ?></label>
						<input type="number" class="form-control" id="shellPort" aria-describedby="shellPortHelp" value="22">
						<small id="shellPortHelp" class="form-text text-muted"><?php echo $language['ssh_port_info']; ?></small>
					</div>
					<div class="form-group">
						<label for="shellPath"><?php echo $language['folder_path']; ?></label>
						<input type="text" class="form-control" id="shellPath" aria-describedby="shellPathHelp" placeholder="Example: /home/teamspeak/">
						<small id="shellPathHelp" class="form-text text-muted"><?php echo $language['ssh_folder_path_info']; ?></small>
					</div>
					<hr/>
					<label for="shellPath">Login via</label>
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
							<label class="c-input c-radio">
								<input id="useShellClient" onChange="slideInstanzShell('client');" name="loginArtRadio" type="radio" checked>
								<span class="c-indicator"></span>
								<?php echo $language['client']; ?>
							</label>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
							<label class="c-input c-radio">
								<input onChange="slideInstanzShell('key');" name="loginArtRadio" type="radio">
								<span class="c-indicator"></span>
								<?php echo $language['key']; ?>
							</label>
						</div>
					</div>
					<div id="shellClientArea">
						<div class="form-group">
							<label for="shellClient"><?php echo $language['client']; ?></label>
							<input type="text" class="form-control" id="shellClient" aria-describedby="shellClientHelp" placeholder="root">
							<small id="shellClientHelp" class="form-text text-muted"><?php echo $language['ssh_client_info']; ?></small>
						</div>
						<div class="form-group">
							<label for="shellPassword"><?php echo $language['password']; ?></label>
							<input type="password" class="form-control" id="shellPassword" aria-describedby="shellClientPassword" placeholder="*****">
							<small id="shellClientPassword" class="form-text text-muted"><?php echo $language['ssh_password_info']; ?></small>
						</div>
					</div>
					<div id="shellKeyArea" style="display: none;">
						<div class="form-group">
							<label><?php echo $language['key']; ?></label>
							<select id="shellKeySelect" class="form-control c-select">
								<option disabled selected style="display: none;"><?php echo $language['choose_key']; ?></option>
								<?php
									$keys 	= 	scandir(__dir__.'/../../files/shell/');
									foreach($keys AS $key)
									{
										if($key != "." && $key != ".." && strpos($key, ".ppk") !== false)
										{
											$key		=	str_replace(".ppk", "", $key);
											if(file_exists(__dir__."/../../files/shell/".$key))
											{
												echo '<option value="'.$key.'">'.$key.'</option>';
											};
										};
									};
								?>
							</select>
							<small class="form-text text-muted"><?php echo $language['ssh_key_info']; ?></small>
						</div>
						<div class="form-group">
							<label for="shellClientKey"><?php echo $language['client']; ?></label>
							<input type="text" class="form-control" id="shellClientKey" aria-describedby="shellClientKeyHelp" placeholder="root">
							<small id="shellClientKeyHelp" class="form-text text-muted"><?php echo $language['ssh_client_info']; ?></small>
						</div>
						<div class="form-group">
							<label for="shellPassphare"><?php echo $language['passphare']; ?></label>
							<input type="password" class="form-control" id="shellPassphare" aria-describedby="shellPassphareHelp" placeholder="*****">
							<small id="shellPassphareHelp" class="form-text text-muted"><?php echo $language['ssh_passphare_info']; ?></small>
						</div>
					</div>
					<hr/>
					<label><?php echo $language['actions']; ?></label>
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
						<i class="fa fa-info"></i> <?php echo $language['query_console']; ?>
						<div id="instanzShellConsole" style="margin-top: 10px;"></div>
					</div>
					<button id="shellCommand" class="btn btn-success" style="width: 100%;margin-top: 20px;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Modal: Instanz Admin Query -->
	<div id="modalAdminQueryInstanz" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalAdminQueryInstanzLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-warning">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalAdminQueryInstanzLabel"><?php echo $language['admin_query']; ?>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p><?php echo $language['admin_query_add']; ?>:</p>
							<div style="margin-bottom: 10px;">
								<code style="float: left;">servergroupaddclient sgid=2 cldbid=MY_CLIENT_DATABASEID</code>
								<button data-dismiss="modal" id="addServerAdminQuery" style="float: right;" class="btn btn-warning"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
								<div style="clear: both;"></div>
							</div>
							<p><?php echo $language['admin_query_del']; ?></p>
							<div style="margin-bottom: 10px;">
								<code style="float: left;">servergroupdelclient sgid=2 cldbid=MY_CLIENT_DATABASEID</code>
								<button data-dismiss="modal" id="delServerAdminQuery" style="float: right;" class="btn btn-warning"><i class="fa fa-fw fa-check"></i> <?php echo $language['submit']; ?></button>
								<div style="clear: both;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Instanz hinzufügen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-plus"></i> <?php echo $language['add_instanz']; ?></h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['instanz_info']; ?></h6>
		</div>
		<div class="card-block">
			<div class="form-group">
				<label for="adminCreateInstanzAlias"><?php echo $language['alias']; ?></label>
				<input type="text" class="form-control" id="adminCreateInstanzAlias" aria-describedby="adminCreateInstanzAliasHelp">
				<small id="adminCreateInstanzAliasHelp" class="form-text text-muted"><?php echo $language['alias_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminCreateInstanzIp"><?php echo $language['ip_adress']; ?></label>
				<input type="text" class="form-control" id="adminCreateInstanzIp" aria-describedby="adminCreateInstanzIpHelp">
				<small id="adminCreateInstanzIpHelp" class="form-text text-muted"><?php echo $language['ip_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminCreateInstanzQueryport"><?php echo $language['queryport']; ?></label>
				<input type="number" class="form-control" id="adminCreateInstanzQueryport" aria-describedby="adminCreateInstanzQueryportHelp">
				<small id="adminCreateInstanzQueryportHelp" class="form-text text-muted"><?php echo $language['queryport_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminCreateInstanzClient"><?php echo $language['client']; ?></label>
				<input type="text" class="form-control" id="adminCreateInstanzClient" aria-describedby="adminCreateInstanzClientHelp">
				<small id="adminCreateInstanzClientHelp" class="form-text text-muted"><?php echo $language['client_info_instanz']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminCreateInstanzPassword"><?php echo $language['password']; ?></label>
				<input type="password" class="form-control" id="adminCreateInstanzPassword" aria-describedby="adminCreateInstanzPasswordHelp">
				<small id="adminCreateInstanzPasswordHelp" class="form-text text-muted"><?php echo $language['password_info_instanz']; ?></small>
			</div>
			<div class="row">
				<div class="col-md-12 col-xs-12" style="text-align:center;">
					<button style="width:100%;" onClick="createInstanz();" class="btn btn-success" id="addInstanz" type="button"><i class="fa fa-save"></i> <?php echo $language['save']; ?></button>
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
				<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideMe('instanzBox<?php echo $instanz; ?>', 'instanzIcon<?php echo $instanz; ?>');">
					<h4 class="card-title">
						<div class="pull-xs-left">
							<i id="instanzIcon<?php echo $instanz; ?>" class="fa fa-arrow-right"></i> 
							<?php echo $language['instance']; ?>: 
							<?php 
								if($server['alias'] != '')
								{
									xssEcho($server['alias']);
								}
								else
								{
									xssEcho($server['ip']);
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
					<div class="form-group">
						<label for="instanzIp<?php echo $instanz; ?>"><?php echo $language['ip_adress']; ?></label>
						<div class="input-group">
							<input type="text" class="form-control" id="instanzIp<?php echo $instanz; ?>" aria-describedby="instanzIp<?php echo $instanz; ?>Help" placeholder="<?php xssEcho($server['ip']); ?>">
							<span class="input-group-btn">
								<button onClick="changeInstanz('ip', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
						<small id="instanzIp<?php echo $instanz; ?>Help" class="form-text text-muted"><?php echo $language['ip_info']; ?></small>
					</div>
					<div class="form-group">
						<label for="instanzQueryport<?php echo $instanz; ?>"><?php echo $language['queryport']; ?></label>
						<div class="input-group">
							<input type="number" class="form-control" id="instanzQueryport<?php echo $instanz; ?>" aria-describedby="instanzQueryport<?php echo $instanz; ?>Help" placeholder="<?php xssEcho($server['queryport']); ?>">
							<span class="input-group-btn">
								<button onClick="changeInstanz('queryport', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
						<small id="instanzQueryport<?php echo $instanz; ?>Help" class="form-text text-muted"><?php echo $language['queryport_info']; ?></small>
					</div>
					<div class="form-group">
						<label for="instanzUser<?php echo $instanz; ?>"><?php echo $language['client']; ?></label>
						<div class="input-group">
							<input type="text" class="form-control" id="instanzUser<?php echo $instanz; ?>" aria-describedby="instanzUser<?php echo $instanz; ?>Help" placeholder="<?php xssEcho($server['user']); ?>">
							<span class="input-group-btn">
								<button onClick="changeInstanz('user', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
						<small id="instanzUser<?php echo $instanz; ?>Help" class="form-text text-muted"><?php echo $language['client_info_instanz']; ?></small>
					</div>
					<div class="form-group">
						<label for="instanzPassword<?php echo $instanz; ?>"><?php echo $language['password']; ?></label>
						<div class="input-group">
							<input type="password" class="form-control" id="instanzPassword<?php echo $instanz; ?>" aria-describedby="instanzPassword<?php echo $instanz; ?>Help" placeholder="******">
							<span class="input-group-btn">
								<button onClick="changeInstanz('pw', '<?php echo $instanz; ?>')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
						<small id="instanzPassword<?php echo $instanz; ?>Help" class="form-text text-muted"><?php echo $language['password_info_instanz']; ?></small>
					</div>
					<?php if($connection) { ?>
						<div class="row" style="padding:.75rem;">
							<div class="alert alert-info">
								<i class="fa fa-info"></i> <?php echo $language['query_console']; ?>
								
								<textarea id="commandInput<?php echo $instanz; ?>" class="form-control small-top-bottom-margin" style="width: 100%;" placeholder="<?php echo $language['input']; ?>..."></textarea>
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
							<?php if(!isWindows()) { ?>
								<button data-toggle="modal" data-target="#modalShellInstanz" data-ip="<?php echo $ts3_server[$instanz]['ip']; ?>" class="btn btn-info btn-sm" type="button"><i class="fa fa-terminal"></i> Shell</button>
							<?php }; ?>
							<?php if($connection) { ?>
								<button data-toggle="modal" data-target="#modalAdminQueryInstanz" data-instanz="<?php echo $instanz; ?>" class="btn btn-warning btn-sm" type="button"><i class="fa fa-user-secret"></i> Admin Query</button>
							<?php }; ?>
							<button onClick="AreYouSure('<?php echo $language['delete_instanz']; ?>', 'deleteInstanz(\'<?php echo $instanz; ?>\')');" class="btn btn-danger btn-sm" type="button"><i class="fa fa-trash"></i> <?php echo $language['delete_instanz']; ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<!-- Javascripte Laden -->
<script src="js/webinterface/admin.js"></script>
<script>
	queryConsoleSelected 				= 	new Array();
	
	$('#modalAdminQueryInstanz').on('show.bs.modal', function (event)
	{
		var button 		=	$(event.relatedTarget);
		var instanz 	= 	button.data('instanz');
		
		document.getElementById('addServerAdminQuery').addEventListener("click", function(){
			$('#commandInput'+instanz).val('servergroupaddclient sgid=2 cldbid=MY_CLIENT_DATABASEID');
		});
		
		document.getElementById('delServerAdminQuery').addEventListener("click", function(){
			$('#commandInput'+instanz).val('servergroupdelclient sgid=2 cldbid=MY_CLIENT_DATABASEID');
		});
	});
	
	function shellCommand(ip)
	{
		var command		=	"restart",
			useClient	=	$('#useShellClient').prop("checked");
		
		if($("#command-start").hasClass("active-shell"))
		{
			command		=	"start";
		}
		else if($("#command-stop").hasClass("active-shell"))
		{
			command		=	"stop";
		};
		
		if((($('#shellClient').val() != "" && useClient) || ($('#shellClientKey').val() != "" != "" && !useClient)) && $('#shellPort').val() != "")
		{
			if(($('#shellClient').val() != 'root' && useClient) || ($('#shellClientKey').val() != 'root' && !useClient))
			{
				if(useClient)
				{
					var postData	=	{
						action: 		'instanzShell',
						ip:				ip,
						port:			$('#shellPort').val(),
						username:		$('#shellClient').val(),
						password:		encodeURIComponent($('#shellPassword').val()),
						command:		command,
						path:			encodeURIComponent($('#shellPath').val())
					};
				}
				else
				{
					var postData	=	{
						action: 		'instanzShell',
						ip:				ip,
						port:			$('#shellPort').val(),
						username:		$('#shellClientKey').val(),
						password:		encodeURIComponent($('#shellPassphare').val()),
						command:		command,
						path:			encodeURIComponent($('#shellPath').val()),
						file:			$('#shellKeySelect').val()
					};
				};
				
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsShell.php",
					data: postData,
					success: function(data) {
						console.log(data);
						document.getElementById("instanzShellConsole").innerHTML	=	data;
						$("#instanzShellConsoleBox").slideDown("slow");
					}
				});
			}
			else
			{
				setNotifyFailed(lang.no_root);
			};
		}
		else
		{
			setNotifyFailed(lang.instanz_add_empty);
		};
	};
	
	$('#modalShellInstanz').on('show.bs.modal', function (event)
	{
		var button 		=	$(event.relatedTarget),
			ip		 	= 	button.data('ip');
		
		document.getElementById('shellCommand').addEventListener("click", function(){
			shellCommand(ip)
		});
	});
	
	/*$('#modalShellInstanz').on('hide.bs.modal', function (event)
	{
		document.getElementById('shellCommand').removeEventListener("click", function()
		{
			shellCommand();
		});
	});*/
</script>
<script src="js/sonstige/preloader.js"></script>