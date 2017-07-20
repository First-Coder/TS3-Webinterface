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
	require_once(__DIR__."/../classes/ts3admin.class.php");
	
	/*
		Variables
	*/
	$LoggedIn		=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
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
	if($user_right['right_hp_user_edit']['key'] != $mysql_keys['right_hp_user_edit'] && $user_right['right_hp_user_create']['key'] != $mysql_keys['right_hp_user_create']
		&& $user_right['right_hp_user_delete']['key'] != $mysql_keys['right_hp_user_delete'] && isSet($_POST['id']))
	{
		reloadSite();
	};
	
	/*
		Set the selected Client to the First one in the List
	*/
	$choosedUserRight		=	getUserRights('pk', $_POST['id']);
	
	/*
		Teamspeak Funktions
	*/
	$ts3_servers							=	array();
	foreach ($ts3_server AS $server_number => $server)
	{
		$tsAdmin 							= 	new ts3admin($server['ip'], $server['queryport']);
		
		if($tsAdmin->getElement('success', $tsAdmin->connect()))
		{
			// Im Teamspeak Einloggen
			$tsAdmin->login($server['user'], $server['pw']);
			
			// Daten im Array speichern
			$ts3_servers[$server_number]	=	$tsAdmin->serverList();
			
			$tsAdmin->logout();
		};
	};
?>

<div id="adminContent">
	<!-- Modal: Server bearbeiten -->
	<div id="modalServerEdit" class="modal fade" data-backdrop="true" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-info">
					<button onClick="closeModalServerEdit();" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalLabel"><?php echo $language['server_edit']; ?></h4>
				</div>
				<div class="modal-body">
					<div id="editServerEditLoading" class="row">
						<div class="col-lg-12 col-md-12" style="text-align:center;">
							<i style="font-size:100px;" class="fa fa-cogs fa-spin"></i>
						</div>
					</div>
					<div id="editServerEditContent" class="card-block" style="display:none;">
						<p style="text-align: center;"><?php echo $language['server_edit_settings_info']; ?></p>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditPort"><?php echo $language['change_server_port']; ?></label>
							<input id="adminCheckboxRightServerEditPort" right="right_server_edit_port" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditSlots"><?php echo $language['change_server_slots']; ?></label>
							<input id="adminCheckboxRightServerEditSlots" right="right_server_edit_slots" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditAutostart"><?php echo $language['change_autostart']; ?></label>
							<input id="adminCheckboxRightServerEditAutostart" right="right_server_edit_autostart" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditMinClientVersion"><?php echo $language['change_min_client']; ?></label>
							<input id="adminCheckboxRightServerEditMinClientVersion" right="right_server_edit_min_client_version" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditMainSettings"><?php echo $language['change_main_settings']; ?></label>
							<input id="adminCheckboxRightServerEditMainSettings" right="right_server_edit_main_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditDefaultServerGroups"><?php echo $language['change_default_group']; ?></label>
							<input id="adminCheckboxRightServerEditDefaultServerGroups" right="right_server_edit_default_servergroups" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditHostSettings"><?php echo $language['change_hostsettings']; ?></label>
							<input id="adminCheckboxRightServerEditHostSettings" right="right_server_edit_host_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditComplaintSettings"><?php echo $language['change_complainsettings']; ?></label>
							<input id="adminCheckboxRightServerEditComplaintSettings" right="right_server_edit_complain_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditAntiFloodSettings"><?php echo $language['change_antifloodsettings']; ?></label>
							<input id="adminCheckboxRightServerEditAntiFloodSettings" right="right_server_edit_antiflood_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditTransferSettings"><?php echo $language['change_transfersettings']; ?></label>
							<input id="adminCheckboxRightServerEditTransferSettings" right="right_server_edit_transfer_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
						<div class="form-group">
							<label for="adminCheckboxRightServerEditProtokollSettings"><?php echo $language['change_protokollsettings']; ?></label>
							<input id="adminCheckboxRightServerEditProtokollSettings" right="right_server_edit_protokoll_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="danger" data-width="100%" data-offstyle="success" data-on="<?php echo $language['blocked']; ?>" data-off="<?php echo $language['unblocked']; ?>">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button onClick="closeModalServerEdit();" type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-fw fa-close"></i> <?php echo $language['abort']; ?></button>
					<button onClick="saveServerEditSettingsBttn();closeModalServerEdit();" id="saveServerEditSettingsBttn" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-check"></i> <?php echo $language['save']; ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left" id="mailOverview" pk="<?php echo $_POST['id']; ?>">
				<i class="fa fa-user"></i> <?php xssEcho($_POST['mail']); ?>
			</div>
			<div class="pull-xs-right">
				<div onClick="adminUserInit();" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['back']; ?>" class="pull-xs-right btn btn-secondary user-header-icons">
					<i class="fa fa-fw fa-arrow-left"></i>
				</div>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block">
		<?php if($choosedUserRight['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) { ?>
			<div class="alert alert-warning">
				<b><i class="fa fa-warning" aria-hidden="true"></i> <?php echo $language['attention']; ?>!</b>
				<p><?php echo $language['attention_serverpermission']; ?></p>
			</div>
		<?php }; ?>
	</div>
	<!-- Teamspeakserver -->
	<?php if($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) {
		if(sizeof($ts3_servers) > 0)
		{
			foreach($ts3_server AS $instanz => $values)
			{
				if(!empty($ts3_servers[$instanz]['data']))
				{
					foreach($ts3_servers[$instanz]['data'] AS $number => $port)
					{
						$permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_view')) ? true : false; ?>
						<div class="card-block">
							<div id="colorBox_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="alert alert-<?php echo ($permission) ? "success" : "danger"; ?>">
								<div style="cursor:pointer;" onClick="slideMe('permissionbox_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>', 'permissionboxicon_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>');">
									<h5 class="card-title">
										<div class="pull-xs-left <?php echo (isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_view')) ? "text-success" : ""; ?>">
											<i id="permissionboxicon_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="fa fa-fw fa-arrow-right"></i>
											<?php echo $language['port']; ?>: <?php echo $port['virtualserver_port']; ?>
										</div>
										<div class="pull-xs-right">
											<div style="margin-top:0px;padding: .175rem 1rem;" class="pull-xs-right btn btn-secondary user-header-icons disabled"
												id="saveButton_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>">
												<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
											</div>
										</div>
										<div style="clear:both;"></div>
									</h5>
									<h6 class="card-subtitle  text-muted"><?php echo $language['instance']; ?>: <?php echo ($values['alias'] != '') ? $values['alias'] : $values['ip']; ?></h6>
								</div>
								<div class="card-block" style="padding: 0 1.25rem;">
									<div id="permissionbox_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" style="display:none;margin-top:20px;">
										<!-- Server sehen -->
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_view']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerViewHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_view" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerView_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerView_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerViewHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_view_info']; ?></small>
										</div>
										
										<!-- Server Banner -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_banner')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['serverbanner']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerBannerHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_banner" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerBanner_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerBanner_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerBannerHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['serverbanner_info']; ?></small>
										</div>
										
										<!-- Server Clients -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_clients')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_clients']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerClientsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_clients" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerClients_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClients_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerClientsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_clients_info']; ?></small>
										</div>
										
										<!-- Server Start / Stop -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_start_stop')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_start_stop']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerStartStopHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_start_stop" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerStartStop_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerStartStop_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerStartStopHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_start_stop_info']; ?></small>
										</div>
										
										<!-- Servermsg / Serverpoke -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_message_poke')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_msg_poke']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerMessagePokeHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_message_poke" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerMessagePoke_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerMessagePoke_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerMessagePokeHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_msg_poke_info']; ?></small>
										</div>
										
										<!-- Server Massenaktionen -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_mass_actions')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_mass_actions']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerMassActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_mass_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerMassActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerMassActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerMassActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_mass_actions_info']; ?></small>
										</div>
										
										<!-- Server Protokoll -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_protokoll')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_protokoll']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerProtokollHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_protokoll" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerProtokoll_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerProtokoll_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerProtokollHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_protokoll_info']; ?></small>
										</div>
										
										<!-- Server Icons -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_icons')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_icons']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerIconsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_icons" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerIcons_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerIcons_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerIconsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_icons_info']; ?></small>
										</div>
										
										<!-- Server Bans -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_bans')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_bans']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerBansHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_bans" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerBans_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerBans_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerBansHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_bans_info']; ?></small>
										</div>
										
										<!-- Server Token -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_token')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_token']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerTokenHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_token" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerToken_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerToken_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerTokenHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_token_info']; ?></small>
										</div>
										
										<!-- Server Filelist -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_file_transfer')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_filelist']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerFilelistHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_file_transfer" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerFilelist_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerFilelist_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerFilelistHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_filelist_info']; ?></small>
										</div>
										
										<!-- Server Backups -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_backups')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_backups']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerBackupsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_backups" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerBackups_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerBackups_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerBackupsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_backups_info']; ?></small>
										</div>
										
										<!-- Server bearbeiten -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_server_edit')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['server_edit']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerEditHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_server_edit" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerEdit_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerEdit_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<button class="btn btn-primary serverEditBlockIcon" style="width: 100%;border-radius: 0;" data-id="<?php echo $_POST['id']; ?>" data-instanz="<?php echo $instanz; ?>" data-port="<?php echo $port['virtualserver_port']; ?>">
												<i class="fa fa-eye" aria-hidden="true"></i> <?php echo $language['server_edit_subcategory']; ?>
											</button>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerEditHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['server_edit_info']; ?></small>
										</div>
										
										<!-- Server Client Aktionen -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_client_actions')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['client_actions']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerClientActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_client_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerClientActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClientActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerClientActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['client_actions_info']; ?></small>
										</div>
										
										<!-- Server Client Rechte -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_client_rights')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['client_permission']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerClientRightsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_client_rights" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerClientRights_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClientRights_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerClientRightsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['client_permission_info']; ?></small>
										</div>
										
										<!-- Server Channel Aktionen -->
										<?php $permission		=	(isPortPermission($choosedUserRight, $instanz, $port['virtualserver_port'], 'right_web_channel_actions')) ? true : false; ?>
										<div class="form-group">
											<label class="default-color"><?php echo $language['channel_actions']; ?></label>
											<div class="input-group">
												<span class="input-group-addon">
													<i class="fa fa-clock-o" aria-hidden="true"></i>
												</span>
												<input type="text" aria-describedby="adminCheckboxRightWebServerChannelActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>" disabled>
												<button right="right_web_channel_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" id="adminCheckboxRightWebServerChannelActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
													onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerChannelActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition"><i class="fa fa-<?php echo (!$permission) ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['unblocked'] : $language['blocked']; ?></button>
											</div>
											<small class="form-text text-muted" id="adminCheckboxRightWebServerChannelActionsHelp_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"><?php echo $language['channel_actions_info']; ?></small>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php };
				};
			};
		};
	}; ?>
</div>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/bootstrap/moment-with-locales.js"></script>
<script src="js/bootstrap/bootstrap-datetimepicker.min.js"></script>
<script src="js/webinterface/admin.js"></script>
<script>
	function closeModalServerEdit()
	{
		$('#editServerEditContent').slideUp("slow", function() {
			$('#editServerEditLoading').slideDown("slow");
		});
	};
	
	$(function () {
		benutzerChange	=	false;
		$('[data-tooltip="tooltip"]').tooltip();
	});
	
	$(".serverEditBlockIcon").click(function() {
		var id 			= 	$(this).attr('data-id');
		var instanz 	= 	$(this).attr('data-instanz');
		var port	 	= 	$(this).attr('data-port');
		
		$('#modalServerEdit').find('#saveServerEditSettingsBttn').attr( { pk:id, instanz:instanz, port:port } );
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsSqlPost.php",
			data: {
				action:		'getCheckedClientServerEditRights',
				pk:			escapeText(id),
				port:		escapeText(port),
				instanz:	escapeText(instanz)
			},
			success: function(data)
			{
				console.log(data);
				if(data == 'null')
				{
					$('.serverEditChangeClass').each(function() {
						$(this).bootstrapToggle('on');
					});
					
					$('#editServerEditLoading').slideUp("slow", function() {
						$('#editServerEditContent').slideDown("slow");
					});
				}
				else
				{
					var informations		= 	JSON.parse(data);
					
					$('.serverEditChangeClass').each(function() {
						if(typeof(informations[$(this).attr("right")]) != 'undefined')
						{
							$(this).bootstrapToggle('on');
						}
						else
						{
							$(this).bootstrapToggle('off');
						};
					});
					
					$('#editServerEditLoading').slideUp("slow", function() {
						$('#editServerEditContent').slideDown("slow");
					});
				};
			}
		});
		$('#modalServerEdit').modal('show');
	});
</script>
<script src="js/sonstige/preloader.js"></script>