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
	require_once("ts3admin.class.php");
	
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
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
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
	if($user_right['right_hp_user_edit'] != $mysql_keys['right_hp_user_edit'] && $user_right['right_hp_user_create'] != $mysql_keys['right_hp_user_create']
		&& $user_right['right_hp_user_delete'] != $mysql_keys['right_hp_user_delete'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Set the selected Client to the First one in the List
	*/
	$choosedUserRight		=	getUserRights('pk', $_POST['id']);
	$choosedUserBlock		=	getUserBlock($_POST['id']);
	$userInformations		=	getUserInformations($_POST['id']);
	
	/*
		Get all Clients
	*/
	$users					=	getUsers();
	
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
	
	/*
		Set language for the Datapicker
	*/
	if(LANGUAGE == 'german')
	{
		$languageDataPicker	=	'de';
	}
	else
	{
		$languageDataPicker	=	'en';
	};
?>

<div id="adminContent">
	<!-- Modal: Benutzer löschen -->
	<div id="modalDeleteUser" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalDeleteUserLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-danger">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalDeleteUserLabel"><?php echo $language['admin_user_del']; ?></h4>
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
					<button id="deleteUserBttn" onClick="deleteUser()" type="button" class="btn btn-danger"><i class="fa fa-fw fa-check"></i> <?php echo $language['yes']; ?></button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Server bearbeiten -->
	<div id="modalServerEdit" class="modal fade" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header alert-info">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modalLabel"><?php echo $language['ts_rights_server_edit']; ?></h4>
				</div>
				<div class="modal-body">
					<div id="editServerEditLoading" class="row">
						<div class="col-lg-12 col-md-12" style="text-align:center;">
							<i style="font-size:100px;" class="fa fa-cogs fa-spin"></i>
						</div>
					</div>
					<div id="editServerEditContent" class="row" style="display:none;">
						<div class="row">
							<div class="col-md-12 modal-info">
								<p><?php echo $language['server_edit_settings_info']; ?></p>
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_change_port']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditPort" right="right_server_edit_port" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_change_slots']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditSlots" right="right_server_edit_slots" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_change_autostart']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditAutostart" right="right_server_edit_autostart" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_change_min_client']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditMinClientVersion" right="right_server_edit_min_client_version" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_main_settings']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditMainSettings" right="right_server_edit_main_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_default_group']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditDefaultServerGroups" right="right_server_edit_default_servergroups" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_hostsettings']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditHostSettings" right="right_server_edit_host_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_complainsetting']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditComplaintSettings" right="right_server_edit_complain_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_afloodsetting']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditAntiFloodSettings" right="right_server_edit_antiflood_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_transfersett']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditTransferSettings" right="right_server_edit_transfer_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
						</div>
						<div class="row user-einrueckung">
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 input-padding">
								<?php echo $language['server_edit_settings_ch_protokolsett']; ?>:
							</div>
							<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" style="text-align:right;">
								<input id="adminCheckboxRightServerEditProtokollSettings" right="right_server_edit_protokoll_settings" class="serverEditChangeClass"
									type="checkbox" data-toggle="toggle" data-onstyle="success" data-width="40%" data-offstyle="danger" data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>">
							</div>
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
	
<!-- Benutzer zeigen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left" id="mailOverview" pk="<?php echo $_POST['id']; ?>">
				<i class="fa fa-user"></i> <?php echo htmlspecialchars($_POST['mail']); ?>
			</div>
			<div class="pull-xs-right">
				<div onClick="adminUserInit();" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['back']; ?>" class="pull-xs-right btn btn-secondary user-header-icons">
					<i class="fa fa-fw fa-arrow-left"></i>
				</div>
				<?php if($user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete'] && count($users) > 1) { ?>
					<div id="deleteUser" data-id="<?php echo $_POST['id']; ?>" data-toggle="modal" data-target="#modalDeleteUser" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['admin_user_delete']; ?>" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-user-times"></i>
					</div>
				<?php } ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tr>
				<td>
					<?php echo $language['admin_user_last_login']; ?>:
				</td>
				<td>
					<?php echo $_POST['lastLogin']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_perso_vorname']; ?>:
				</td>
				<td>
					<?php if($userInformations['vorname'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['vorname']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_perso_nachname']; ?>:
				</td>
				<td>
					<?php if($userInformations['nachname'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['nachname']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_perso_telefon']; ?>:
				</td>
				<td>
					<?php if($userInformations['telefon'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['telefon']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_homepage']; ?>:
				</td>
				<td>
					<?php if($userInformations['homepage'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['homepage']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_skype']; ?>:
				</td>
				<td>
					<?php if($userInformations['skype'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['skype']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_steam']; ?>:
				</td>
				<td>
					<?php if($userInformations['steam'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['steam']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_twitter']; ?>:
				</td>
				<td>
					<?php if($userInformations['twitter'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['twitter']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_facebook']; ?>:
				</td>
				<td>
					<?php if($userInformations['facebook'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['facebook']); }; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $language['profile_kontakt_google']; ?>:
				</td>
				<td>
					<?php if($userInformations['google'] == '') { echo "<i>".$language['keine_angabe']."</i>"; } else { echo htmlspecialchars($userInformations['google']); }; ?>
				</td>
			</tr>
		</table>
	</div>
</div>

<?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<!-- Homepage Benutzerdaten -->
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-info"></i> <?php echo $language['admin_user_data']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setBenutzerdaten('<?php echo $_SESSION['user']['id']; ?>');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block" style="font-size: initial;">
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['admin_user_blocked']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_user_block']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserBlock['blocked'] == "true") ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerBlocked" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxBlocked" onClick="clickButton('adminCheckboxBlocked', 'adminDatapickerBlocked', 'true');" class="btn btn-<?php echo ($choosedUserBlock['blocked'] == "true") ? "danger" : "success"; ?> button-transition" style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserBlock['blocked'] == "true") ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserBlock['blocked'] == "true") ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo htmlspecialchars($language['mail']); ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_username']; ?>"></i>
				</div>
				<div class="col-lg-7">
					<input id="adminUsername" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($_POST['mail']); ?>">
				</div>
			</div>
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['password']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_pw_user']; ?>"></i>
				</div>
				<div class="col-lg-7">
					<input id="adminPassword" type="password" class="form-control" placeholder="">
				</div>
			</div>
			<div class="row">
				<div class="col-lg-offset-5 col-lg-7">
					<input id="adminPassword2" type="password" class="form-control" placeholder="">
				</div>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Homepage Rechte -->
<?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-globe"></i> <?php echo $language['hp_rights']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setHomepagerechte('<?php echo $_SESSION['user']['id']; ?>');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['hp_global_rights']; ?></h6>
		</div>
		<div class="card-block" style="font-size: initial;">
			<!-- Homepageeinstellungen ändern -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['hp_rights_edit']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_main']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_main'] == '0' || $choosedUserRight['time']['right_hp_main'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsEdit" onClick="clickButton('adminCheckboxRightsEdit', 'adminDatapickerRightsEdit', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_main'] == '0' || $choosedUserRight['time']['right_hp_main'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_main'] == '0' || $choosedUserRight['time']['right_hp_main'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_main'] == '0' || $choosedUserRight['time']['right_hp_main'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Teamspeak Instanzeinstellungen ändern -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['hp_rights_ts3_edit']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_ts3']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_ts3'] == '0' || $choosedUserRight['time']['right_hp_ts3'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsTSEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsTSEdit" onClick="clickButton('adminCheckboxRightsTSEdit', 'adminDatapickerRightsTSEdit', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_ts3'] == '0' || $choosedUserRight['time']['right_hp_ts3'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_ts3'] == '0' || $choosedUserRight['time']['right_hp_ts3'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_ts3'] == '0' || $choosedUserRight['time']['right_hp_ts3'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Benutzer erstellen -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['hp_rights_user_add']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_user_create']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_user_create'] == '0' || $choosedUserRight['time']['right_hp_user_create'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsUserCreate" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsUserCreate" onClick="clickButton('adminCheckboxRightsUserCreate', 'adminDatapickerRightsUserCreate', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_user_create'] == '0' || $choosedUserRight['time']['right_hp_user_create'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_user_create'] == '0' || $choosedUserRight['time']['right_hp_user_create'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_user_create'] == '0' || $choosedUserRight['time']['right_hp_user_create'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Benutzer löschen -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['hp_rights_user_del']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_user_delete']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_user_delete'] == '0' || $choosedUserRight['time']['right_hp_user_delete'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsUserDelete" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsUserDelete" onClick="clickButton('adminCheckboxRightsUserDelete', 'adminDatapickerRightsUserDelete', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_user_delete'] == '0' || $choosedUserRight['time']['right_hp_user_delete'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_user_delete'] == '0' || $choosedUserRight['time']['right_hp_user_delete'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_user_delete'] == '0' || $choosedUserRight['time']['right_hp_user_delete'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Benutzer bearbeiten -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['hp_rights_user_edit']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_user_edit']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_user_edit'] == '0' || $choosedUserRight['time']['right_hp_user_edit'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsUserEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsUserEdit" onClick="clickButton('adminCheckboxRightsUserEdit', 'adminDatapickerRightsUserEdit', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_user_edit'] == '0' || $choosedUserRight['time']['right_hp_user_edit'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_user_edit'] == '0' || $choosedUserRight['time']['right_hp_user_edit'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_user_edit'] == '0' || $choosedUserRight['time']['right_hp_user_edit'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Ticketsystem -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					Ticketsystem Admin <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_ticket_admin']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_ticket_system'] == '0' || $choosedUserRight['time']['right_hp_ticket_system'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsTicketsystem" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsTicketsystem" onClick="clickButton('adminCheckboxRightsTicketsystem', 'adminDatapickerRightsTicketsystem', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_ticket_system'] == '0' || $choosedUserRight['time']['right_hp_ticket_system'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_ticket_system'] == '0' || $choosedUserRight['time']['right_hp_ticket_system'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_ticket_system'] == '0' || $choosedUserRight['time']['right_hp_ticket_system'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Mails -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['mail_settings']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_hp_mail_settings']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_hp_mails'] == '0' || $choosedUserRight['time']['right_hp_mails'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsMails" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsMails" onClick="clickButton('adminCheckboxRightsMails', 'adminDatapickerRightsMails', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_hp_mails'] == '0' || $choosedUserRight['time']['right_hp_mails'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_hp_mails'] == '0' || $choosedUserRight['time']['right_hp_mails'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_hp_mails'] == '0' || $choosedUserRight['time']['right_hp_mails'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Teamspeakrechte -->
<?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-globe"></i> <?php echo $language['ts_rights']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setTeamspeakrechte('<?php echo $_SESSION['user']['id']; ?>');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['hp_global_rights']; ?></h6>
		</div>
		<div class="card-block" style="font-size: initial;">
			<!-- Zugang zum Webinterface -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['ts_rights_access']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_web_access']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_web'] == '0' || $choosedUserRight['time']['right_web'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsWeb" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightWeb" onClick="clickButton('adminCheckboxRightWeb', 'adminDatapickerRightsWeb', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_web'] == '0' || $choosedUserRight['time']['right_web'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_web'] == '0' || $choosedUserRight['time']['right_web'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_web'] == '0' || $choosedUserRight['time']['right_web'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Servernachrichten / Serverpokes -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['ts_rights_global_msg_poke']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_web_global_msg_poke']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_web_global_message_poke'] == '0' || $choosedUserRight['time']['right_web_global_message_poke'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsWebGlobalMessagePoke" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightWebGlobalMessagePoke" onClick="clickButton('adminCheckboxRightWebGlobalMessagePoke', 'adminDatapickerRightsWebGlobalMessagePoke', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_web_global_message_poke'] == '0' || $choosedUserRight['time']['right_web_global_message_poke'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_web_global_message_poke'] == '0' || $choosedUserRight['time']['right_web_global_message_poke'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_web_global_message_poke'] == '0' || $choosedUserRight['time']['right_web_global_message_poke'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Server Erstellen -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['ts_rights_create_server']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_web_server_create']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_web_server_create'] == '0' || $choosedUserRight['time']['right_web_server_create'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsWebServerCreate" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightWebServerCreate" onClick="clickButton('adminCheckboxRightWebServerCreate', 'adminDatapickerRightsWebServerCreate', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_web_server_create'] == '0' || $choosedUserRight['time']['right_web_server_create'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_web_server_create'] == '0' || $choosedUserRight['time']['right_web_server_create'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_web_server_create'] == '0' || $choosedUserRight['time']['right_web_server_create'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Server löschen -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['ts_rights_del_server']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_web_server_delete']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_web_server_delete'] == '0' || $choosedUserRight['time']['right_web_server_delete'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsWebServerDelete" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightWebServerDelete" onClick="clickButton('adminCheckboxRightWebServerDelete', 'adminDatapickerRightsWebServerDelete', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_web_server_delete'] == '0' || $choosedUserRight['time']['right_web_server_delete'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_web_server_delete'] == '0' || $choosedUserRight['time']['right_web_server_delete'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_web_server_delete'] == '0' || $choosedUserRight['time']['right_web_server_delete'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
			
			<!-- Zugang zu allen Server -->
			<div class="row">
				<div class="col-lg-5 input-padding">
					<?php echo $language['ts_rights_access_all_server']; ?> <i class="fa fa-fw fa-question-circle" data-tooltip="tooltip" data-placement="top" title="<?php echo $language['hp_title_web_access_all_server']; ?>"></i>
				</div>
				<div class="col-lg-4">
					<input type="text" style="display: <?php echo ($choosedUserRight['time']['right_web_global_server'] == '0' || $choosedUserRight['time']['right_web_global_server'] > time()) ? "inline" : "none"; ?>; width: 100%;" id="adminDatapickerRightsWebGlobalServer" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
				</div>
				<div class="col-lg-3">
					<button id="adminCheckboxRightsWebGlobalServer" onClick="clickButton('adminCheckboxRightsWebGlobalServer', 'adminDatapickerRightsWebGlobalServer', 'false');" class="btn btn-<?php echo ($choosedUserRight['time']['right_web_global_server'] == '0' || $choosedUserRight['time']['right_web_global_server'] > time()) ? "success" : "danger"; ?> button-transition" 
						style="width: 100%;margin-bottom: 10px;"><i class="fa fa-<?php echo ($choosedUserRight['time']['right_web_global_server'] == '0' || $choosedUserRight['time']['right_web_global_server'] > time()) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserRight['time']['right_web_global_server'] == '0' || $choosedUserRight['time']['right_web_global_server'] > time()) ? $language['yes'] : $language['no']; ?></button>
				</div>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Teamspeakserver -->
<?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) {
	if(sizeof($ts3_servers) > 0)
	{ ?>
		<div class="card">
			<!-- Instanzrechte -->
			<?php foreach($ts3_server AS $instanz => $values)
			{
				foreach($ts3_servers[$instanz]['data'] AS $number => $port)
				{ ?>
					<!-- Beispiel:
					$ts3_servers[0]['data'][0]['virtualserver_port'] <==> $ts3_servers[$SERVERINSTANZ$]['data'][$INSTANZSERVERID$]['virtualserver_port'] -->
					<div style="cursor:pointer;" class="card-block card-block-header" onClick="slidePermissionbox('permissionbox_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>', 'permissionboxicon_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>');">
						<h4 class="card-title">
							<div id="colorText_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>" class="pull-xs-left <?php echo (strpos($choosedUserRight['ports']['right_web_server_view'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "text-success" : ""; ?>">
								<i id="permissionboxicon_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="fa fa-fw fa-arrow-right"></i>
								Port: <?php echo $port['virtualserver_port']; ?>
							</div>
							<div class="pull-xs-right">
								<div style="margin-top:0px;padding: .175rem 1rem;" class="pull-xs-right btn btn-secondary user-header-icons disabled"
									id="saveButton_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>">
									<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
								</div>
							</div>
							<div style="clear:both;"></div>
						</h4>
						<h6 class="card-subtitle text-muted">Instanz: <?php echo ($values['alias'] != '') ? $values['alias'] : $values['ip']; ?></h6>
					</div>
					<div class="card-block" style="font-size: initial;">
						<div id="permissionbox_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" style="display:none;">
							<!-- Server sehen -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['hp_server_view']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_view" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_view'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerView_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerView_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_view'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_view'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Clients -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_clients']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_clients" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_clients'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerClients_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClients_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_clients'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_clients'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Start / Stop -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_start_stop']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_start_stop" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_start_stop'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerStartStop_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerStartStop_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_start_stop'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_start_stop'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Servermsg / Serverpoke -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_msg_poke']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_message_poke" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_message_poke'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerMessagePoke_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerMessagePoke_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_message_poke'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_message_poke'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Massenaktionen -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_mass_actions']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_mass_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_mass_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerMassActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerMassActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_mass_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_mass_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Protokoll -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_protokoll']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_protokoll" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_protokoll'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerProtokoll_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerProtokoll_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_protokoll'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_protokoll'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Icons -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_icons']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_icons" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_icons'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerIcons_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerIcons_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_icons'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_icons'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Bans -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_bans']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_bans" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_bans'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerBans_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerBans_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_bans'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_bans'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Token -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_token']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_token" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_token'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerToken_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerToken_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_token'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_token'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Filelist -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_filelist']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_file_transfer" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_file_transfer'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerFilelist_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerFilelist_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_file_transfer'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_file_transfer'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Backups -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_backups']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_server_backups" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_backups'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerBackups_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerBackups_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_backups'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_backups'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server bearbeiten -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_server_edit']; ?>
								</div>
								<div class="col-lg-offset-1 col-lg-3">
									<button class="btn btn-primary serverEditBlockIcon" style="width: 100%;margin-bottom: 10px;" data-id="<?php echo $_POST['id']; ?>" data-instanz="<?php echo $instanz; ?>" data-port="<?php echo $port['virtualserver_port']; ?>">
										<i class="fa fa-cog" aria-hidden="true"></i> <?php echo $language['more']; ?>
									</button>
								</div>
								<div class="col-lg-3">
									<button right="right_web_server_edit" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_server_edit'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerEdit_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerEdit_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_server_edit'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_server_edit'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Client Aktionen -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_client_actions']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_client_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_client_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerClientActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClientActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_client_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_client_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Client Rechte -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_client_rights']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_client_rights" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_client_rights'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerClientRights_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerClientRights_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_client_rights'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_client_rights'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
							
							<!-- Server Channel Aktionen -->
							<div class="row">
								<div class="col-lg-5 input-padding">
									<?php echo $language['ts_rights_channel_actions']; ?>
								</div>
								<div class="col-lg-offset-4 col-lg-3">
									<button right="right_web_channel_actions" instanz="<?php echo $instanz; ?>" port="<?php echo $port['virtualserver_port']; ?>" class="changedUserPorts btn btn-<?php echo (strpos($choosedUserRight['ports']['right_web_channel_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "success" : "danger"; ?> button-transition" id="adminCheckboxRightWebServerChannelActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>"
										onClick="showSaveButton('<?php echo $port['virtualserver_port']; ?>', '<?php echo $instanz; ?>');clickButton('adminCheckboxRightWebServerChannelActions_<?php echo $port['virtualserver_port']; ?>_<?php echo $instanz; ?>', '', 'ports');" style="width: 100%;margin-bottom: 10px;">
										<i class="fa fa-<?php echo (strpos($choosedUserRight['ports']['right_web_channel_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo (strpos($choosedUserRight['ports']['right_web_channel_actions'][$instanz], $ts3_servers[$instanz]['data'][$number]['virtualserver_port']) !== false) ? $language['yes'] : $language['no']; ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				<?php };
			}; ?>
		</div>
	<?php };
}; ?>

<!-- Sprachdatein laden -->
<script>
	var hp_user_edit_done			=	'<?php echo $language['hp_user_edit_done']; ?>';
	var hp_user_edit_failed			=	'<?php echo $language['hp_user_edit_failed']; ?>';
	
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var hp_user						=	'<?php echo $language['client']; ?>';
	var password					=	'<?php echo $language['password']; ?>';
	
	var textYes						=	'<?php echo $language['yes']; ?>';
	var textNo						=	'<?php echo $language['no']; ?>';
	
	var keine_angabe				=	'<?php echo $language['keine_angabe']; ?>';
	
	var username_needs				=	'<?php echo $language['username_needs']; ?>';
	var password_needs				=	'<?php echo $language['password_needs']; ?>';
</script>

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
		$('.datetimepicker').datetimepicker({
			locale: '<?php echo $languageDataPicker; ?>',
			calendarWeeks: false,
			showClear: true,
			showClose: true,
			minDate: "moment",
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-arrow-up",
				down: "fa fa-arrow-down",
				clear: 'fa fa-trash',
				close: 'fa fa-close'
			}
		});
		
		// Datapicker values setzen
		$('#adminDatapickerBlocked').data("DateTimePicker").date(getTime(<?php echo $choosedUserBlock['until']; ?>));
		$('#adminDatapickerRightsEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_main']; ?>));
		$('#adminDatapickerRightsTSEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_ts3']; ?>));
		$('#adminDatapickerRightsUserCreate').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_user_create']; ?>));
		$('#adminDatapickerRightsUserDelete').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_user_delete']; ?>));
		$('#adminDatapickerRightsUserEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_user_edit']; ?>));
		$('#adminDatapickerRightsTicketsystem').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_ticket_system']; ?>));
		$('#adminDatapickerRightsMails').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_hp_mails']; ?>));
		$('#adminDatapickerRightsWeb').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_web']; ?>));
		$('#adminDatapickerRightsWebGlobalMessagePoke').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_web_global_message_poke']; ?>));
		$('#adminDatapickerRightsWebServerCreate').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_web_server_create']; ?>));
		$('#adminDatapickerRightsWebServerDelete').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_web_server_delete']; ?>));
		$('#adminDatapickerRightsWebGlobalServer').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['time']['right_web_global_server']; ?>));
	});
	
	function getTime(timestamp)
	{
		var Zeit 			= 	new Date();  
		Zeit.setTime(timestamp * 1000);  
		
		return Zeit.toLocaleString();
	};
	
	$('#modalDeleteUser').on('show.bs.modal', function (event)
	{
		var id 			= 	$('#deleteUser').attr('data-id');
		
		$(this).find('#deleteUserBttn').attr( { pk:id } );
	});
	
	$(".serverEditBlockIcon").click(function() {
		var id 			= 	$(this).attr('data-id');
		var instanz 	= 	$(this).attr('data-instanz');
		var port	 	= 	$(this).attr('data-port');
		
		$('#modalServerEdit').find('#saveServerEditSettingsBttn').attr( { pk:id, instanz:instanz, port:port } );
		
		var dataString	=	'action=clientEditServerEditInformations';
		dataString		+=	'&pk='+id;
		dataString		+=	'&port='+port;
		dataString		+=	'&instanz='+instanz;
		$.ajax({
			type: "POST",
			url: "functionsPost.php",
			data: dataString,
			dataTyp: "json",
			cache: false,
			success: function(data)
			{
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