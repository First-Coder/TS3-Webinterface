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
		&& $user_right['right_hp_user_delete']['key'] != $mysql_keys['right_hp_user_delete'])
	{
		reloadSite();
	};
	
	/*
		Set the selected Client to the First one in the List
	*/
	$choosedUserRight		=	getUserRights('pk', $_POST['id'], false, 'time');
	$choosedUserBlock		=	getUserBlock($_POST['id']);
	$userInformations		=	getUserInformations($_POST['id']);
	
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
	
<!-- Benutzer zeigen -->
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
		<div class="form-group">
			<label for="userLastLogin"><?php echo $language['last_login']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-info" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userLastLogin" value="<?php xssEcho($_POST['lastLogin']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userFirstname"><?php echo $language['firstname']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-address-card" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userFirstname" value="<?php xssEcho(($userInformations['vorname'] == '') ? $language['no_information'] : $userInformations['vorname']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userLastname"><?php echo $language['lastname']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-address-card" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userLastname" value="<?php xssEcho(($userInformations['nachname'] == '') ? $language['no_information'] : $userInformations['nachname']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userTelefon"><?php echo $language['telefon']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-phone" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userTelefon" value="<?php xssEcho(($userInformations['telefon'] == '') ? $language['no_information'] : $userInformations['telefon']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userHomepage"><?php echo $language['homepage']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					http://
				</span>
				<input type="text" class="form-control" id="userHomepage" value="<?php xssEcho(($userInformations['homepage'] == '') ? $language['no_information'] : $userInformations['homepage']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userSkype"><?php echo $language['skype']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-skype" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userSkype" value="<?php xssEcho(($userInformations['skype'] == '') ? $language['no_information'] : $userInformations['skype']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userSteam"><?php echo $language['steam']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-steam" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userSteam" value="<?php xssEcho(($userInformations['steam'] == '') ? $language['no_information'] : $userInformations['steam']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label for="userTwitter"><?php echo $language['twitter']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-twitter" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userTwitter" value="<?php xssEcho(($userInformations['twitter'] == '') ? $language['no_information'] : $userInformations['twitter']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label><?php echo $language['facebook']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-facebook" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userFacebook" value="<?php xssEcho(($userInformations['facebook'] == '') ? $language['no_information'] : $userInformations['facebook']); ?>" disabled>
			</div>
		</div>
		<div class="form-group">
			<label><?php echo $language['google_plus']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-google-plus" aria-hidden="true"></i>
				</span>
				<input type="text" class="form-control" id="userGoogle" value="<?php xssEcho(($userInformations['google'] == '') ? $language['no_information'] : $userInformations['google']); ?>" disabled>
			</div>
		</div>
	</div>
</div>

<?php if($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<!-- Homepage Benutzerdaten -->
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-sign-in"></i> <?php echo $language['userdata']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setGlobalPermissions('user');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['userdata_info']; ?></h6>
		</div>
		<div class="card-block">
			<div class="form-group">
				<label for="adminDatapickerBlockedHelp"><?php echo $language['user_blocked']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-ban" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerBlockedHelp" <?php echo ($choosedUserBlock['blocked'] == "true") ? "" : "disabled"; ?> id="adminDatapickerBlocked" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxBlocked" onClick="clickButton('adminCheckboxBlocked', 'adminDatapickerBlocked', 'true');" class="btn btn-<?php echo ($choosedUserBlock['blocked'] == "true") ? "danger" : "success"; ?> button-transition"><i class="fa fa-<?php echo ($choosedUserBlock['blocked'] == "true") ? "ban" : "check"; ?>" aria-hidden="true"></i> <?php echo ($choosedUserBlock['blocked'] == "true") ? $language['blocked'] : $language['unblocked']; ?></button>
				</div>
				<small id="adminDatapickerBlockedHelp" class="form-text text-muted"><?php echo $language['user_blocked_admin_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminUsername"><?php echo $language['mail']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						@
					</span>
					<input type="email" class="form-control" id="adminUsername" placeholder="<?php xssEcho($_POST['mail']); ?>" aria-describedby="adminUsernameHelp">
				</div>
				<small id="adminUsernameHelp" class="form-text text-muted"><?php echo $language['mail_help']; ?></small>
			</div>
			<div class="form-group">
				<label for="adminPassword"><?php echo $language['password']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-key"></i>
					</span>
					<input type="password" class="form-control" id="adminPassword" placeholder="******">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-refresh"></i>
					</span>
					<input type="password" class="form-control" id="adminPassword2" aria-describedby="adminPasswordHelp" placeholder="******">
				</div>
				<small id="adminPasswordHelp" class="form-text text-muted"><?php echo $language['password_help']; ?></small>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Homepage Rechte -->
<?php if($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-globe"></i> <?php echo $language['hp_rights']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setGlobalPermissions('homepage');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['global_rights']; ?></h6>
		</div>
		<div class="card-block">
			<!-- Homepageeinstellungen ändern -->
			<?php $permission		=	($choosedUserRight['right_hp_main']['time'] == '0' || $choosedUserRight['right_hp_main']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['hp_rights_edit']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsEditHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsEdit" onClick="clickButton('adminCheckboxRightsEdit', 'adminDatapickerRightsEdit', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsEditHelp" class="form-text text-muted"><?php echo $language['hp_rights_edit_info']; ?></small>
			</div>
			
			<!-- Teamspeak Instanzeinstellungen ändern -->
			<?php $permission		=	($choosedUserRight['right_hp_ts3']['time'] == '0' || $choosedUserRight['right_hp_ts3']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['ts3_rights_edit']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsTSEditHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsTSEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsTSEdit" onClick="clickButton('adminCheckboxRightsTSEdit', 'adminDatapickerRightsTSEdit', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsTSEditHelp" class="form-text text-muted"><?php echo $language['ts3_rights_edit_info']; ?></small>
			</div>
			
			<!-- Benutzer erstellen -->
			<?php $permission		=	($choosedUserRight['right_hp_user_create']['time'] == '0' || $choosedUserRight['right_hp_user_create']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['user_add']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsUserCreateHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsUserCreate" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsUserCreate" onClick="clickButton('adminCheckboxRightsUserCreate', 'adminDatapickerRightsUserCreate', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsUserCreateHelp" class="form-text text-muted"><?php echo $language['user_add_info']; ?></small>
			</div>
			
			<!-- Benutzer löschen -->
			<?php $permission		=	($choosedUserRight['right_hp_user_delete']['time'] == '0' || $choosedUserRight['right_hp_user_delete']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['user_delete']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsUserDeleteHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsUserDelete" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsUserDelete" onClick="clickButton('adminCheckboxRightsUserDelete', 'adminDatapickerRightsUserDelete', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsUserDeleteHelp" class="form-text text-muted"><?php echo $language['user_delete_info']; ?></small>
			</div>
			
			<!-- Benutzer bearbeiten -->
			<?php $permission		=	($choosedUserRight['right_hp_user_edit']['time'] == '0' || $choosedUserRight['right_hp_user_edit']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['user_edit']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsUserEditHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsUserEdit" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsUserEdit" onClick="clickButton('adminCheckboxRightsUserEdit', 'adminDatapickerRightsUserEdit', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsUserEditHelp" class="form-text text-muted"><?php echo $language['user_edit_info']; ?></small>
			</div>
			
			<!-- Ticketsystem -->
			<?php $permission		=	($choosedUserRight['right_hp_ticket_system']['time'] == '0' || $choosedUserRight['right_hp_ticket_system']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['ticket_admin']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsTicketsystemHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsTicketsystem" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsTicketsystem" onClick="clickButton('adminCheckboxRightsTicketsystem', 'adminDatapickerRightsTicketsystem', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsTicketsystemHelp" class="form-text text-muted"><?php echo $language['ticket_admin_info']; ?></small>
			</div>
			
			<!-- Mails -->
			<?php $permission		=	($choosedUserRight['right_hp_mails']['time'] == '0' || $choosedUserRight['right_hp_mails']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['mail_settings']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsMailsHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsMails" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsMails" onClick="clickButton('adminCheckboxRightsMails', 'adminDatapickerRightsMails', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsMailsHelp" class="form-text text-muted"><?php echo $language['mail_settings_info']; ?></small>
			</div>
			
			<!-- Logs -->
			<?php $permission		=	($choosedUserRight['right_hp_logs']['time'] == '0' || $choosedUserRight['right_hp_logs']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['logs']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsLogsHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsLogs" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsLogs" onClick="clickButton('adminCheckboxRightsLogs', 'adminDatapickerRightsLogs', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsLogsHelp" class="form-text text-muted"><?php echo $language['logs_info']; ?></small>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Teamspeakrechte -->
<?php if($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-globe"></i> <?php echo $language['ts_rights']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setGlobalPermissions('teamspeak');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
			<h6 class="card-subtitle text-muted"><?php echo $language['global_rights']; ?></h6>
		</div>
		<div class="card-block" style="font-size: initial;">
			<!-- Zugang zum Webinterface -->
			<?php $permission		=	($choosedUserRight['right_web']['time'] == '0' || $choosedUserRight['right_web']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['access_interface']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsWebHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsWeb" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightWeb" onClick="clickButton('adminCheckboxRightWeb', 'adminDatapickerRightsWeb', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsWebHelp" class="form-text text-muted"><?php echo $language['access_interface_info']; ?></small>
			</div>
			
			<!-- Servernachrichten / Serverpokes -->
			<?php $permission		=	($choosedUserRight['right_web_global_message_poke']['time'] == '0' || $choosedUserRight['right_web_global_message_poke']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['instance_msg_poke']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsWebGlobalMessagePokeHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsWebGlobalMessagePoke" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightWebGlobalMessagePoke" onClick="clickButton('adminCheckboxRightWebGlobalMessagePoke', 'adminDatapickerRightsWebGlobalMessagePoke', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsWebGlobalMessagePokeHelp" class="form-text text-muted"><?php echo $language['instance_msg_poke_info']; ?></small>
			</div>
			
			<!-- Server Erstellen -->
			<?php $permission		=	($choosedUserRight['right_web_server_create']['time'] == '0' || $choosedUserRight['right_web_server_create']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['create_server']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsWebServerCreateHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsWebServerCreate" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightWebServerCreate" onClick="clickButton('adminCheckboxRightWebServerCreate', 'adminDatapickerRightsWebServerCreate', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsWebServerCreateHelp" class="form-text text-muted"><?php echo $language['create_server_info']; ?></small>
			</div>
			
			<!-- Server löschen -->
			<?php $permission		=	($choosedUserRight['right_web_server_delete']['time'] == '0' || $choosedUserRight['right_web_server_delete']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['delete_server']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsWebServerDeleteHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsWebServerDelete" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightWebServerDelete" onClick="clickButton('adminCheckboxRightWebServerDelete', 'adminDatapickerRightsWebServerDelete', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsWebServerDeleteHelp" class="form-text text-muted"><?php echo $language['delete_server_info']; ?></small>
			</div>
			
			<!-- Zugang zu allen Server -->
			<?php $permission		=	($choosedUserRight['right_web_global_server']['time'] == '0' || $choosedUserRight['right_web_global_server']['time'] > time()) ? true : false; ?>
			<div class="form-group">
				<label><?php echo $language['access_to_all_server']; ?></label>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-clock-o" aria-hidden="true"></i>
					</span>
					<input type="text" aria-describedby="adminDatapickerRightsWebGlobalServerHelp" <?php echo ($permission) ? "" : "disabled"; ?> id="adminDatapickerRightsWebGlobalServer" class="form-control datetimepicker" placeholder="<?php echo $language['unlimited']; ?>">
					<button id="adminCheckboxRightsWebGlobalServer" onClick="clickButton('adminCheckboxRightsWebGlobalServer', 'adminDatapickerRightsWebGlobalServer', 'false');" class="btn btn-<?php echo ($permission) ? "success" : "danger"; ?> button-transition">
						<i class="fa fa-<?php echo ($permission) ? "check" : "ban"; ?>" aria-hidden="true"></i> <?php echo ($permission) ? $language['yes'] : $language['no']; ?>
					</button>
				</div>
				<small id="adminDatapickerRightsWebGlobalServerHelp" class="form-text text-muted"><?php echo $language['access_to_all_server_info']; ?></small>
			</div>
		</div>
	</div>
<?php }; ?>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/bootstrap/moment-with-locales.js"></script>
<script src="js/bootstrap/bootstrap-datetimepicker.min.js"></script>
<script src="js/webinterface/admin.js"></script>
<script>
	$(function () {
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
				close: 'fa fa-close',
				previous: 'fa fa-arrow-left',
				next: 'fa fa-arrow-right'
			}
		});
		
		// Datapicker values setzen
		$('#adminDatapickerBlocked').data("DateTimePicker").date(getTime(<?php echo $choosedUserBlock['until']; ?>));
		$('#adminDatapickerRightsEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_main']['time']; ?>));
		$('#adminDatapickerRightsTSEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_ts3']['time']; ?>));
		$('#adminDatapickerRightsUserCreate').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_user_create']['time']; ?>));
		$('#adminDatapickerRightsUserDelete').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_user_delete']['time']; ?>));
		$('#adminDatapickerRightsUserEdit').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_user_edit']['time']; ?>));
		$('#adminDatapickerRightsTicketsystem').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_ticket_system']['time']; ?>));
		$('#adminDatapickerRightsMails').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_mails']['time']; ?>));
		$('#adminDatapickerRightsLogs').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_hp_logs']['time']; ?>));
		$('#adminDatapickerRightsWeb').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_web']['time']; ?>));
		$('#adminDatapickerRightsWebGlobalMessagePoke').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_web_global_message_poke']['time']; ?>));
		$('#adminDatapickerRightsWebServerCreate').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_web_server_create']['time']; ?>));
		$('#adminDatapickerRightsWebServerDelete').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_web_server_delete']['time']; ?>));
		$('#adminDatapickerRightsWebGlobalServer').data("DateTimePicker").date(getTime(<?php echo $choosedUserRight['right_web_global_server']['time']; ?>));
	});
	
	function getTime(timestamp)
	{
		if(typeof(timestamp) === "undefined")
		{
			timestamp = 0;
		};
		var Zeit 			= 	new Date();  
		Zeit.setTime(timestamp * 1000);  
		
		return Zeit.toLocaleString();
	};
</script>
<script src="js/sonstige/preloader.js"></script>