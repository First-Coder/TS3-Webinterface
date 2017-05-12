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
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
?>

<!-- Homepagerechte -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-university"></i> <?php echo $language['hp_rights']; ?></h4>
		<h6 class="card-subtitle text-muted"><?php echo $language['global_rights']; ?></h6>
	</div>
	<div class="card-block">
		<table class="table permission-table">
			<tbody>
				<tr class="<?php echo ($user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['hp_rights_edit']; ?><p class="text-muted"><?php echo $language['hp_rights_edit_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_main']['key'] == $mysql_keys['right_hp_main']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['ts3_rights_edit']; ?><p class="text-muted"><?php echo $language['ts3_rights_edit_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_ts3']['key'] == $mysql_keys['right_hp_ts3']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_hp_user_create']['key'] == $mysql_keys['right_hp_user_create']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['user_add']; ?><p class="text-muted"><?php echo $language['user_add_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_user_create']['key'] == $mysql_keys['right_hp_user_create']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['user_delete']; ?><p class="text-muted"><?php echo $language['user_delete_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_user_delete']['key'] == $mysql_keys['right_hp_user_delete']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php if($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) { echo "text-success"; } else { echo "text-danger"; } ?>">
					<td><?php echo $language['user_edit']; ?><p class="text-muted"><?php echo $language['user_edit_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_user_edit']['key'] == $mysql_keys['right_hp_user_edit']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php if($user_right['right_hp_ticket_system']['key'] == $mysql_keys['right_hp_ticket_system']) { echo "text-success"; } else { echo "text-danger"; } ?>">
					<td><?php echo $language['ticket_admin']; ?><p class="text-muted"><?php echo $language['ticket_admin_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_ticket_system']['key'] == $mysql_keys['right_hp_ticket_system']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php if($user_right['right_hp_mails']['key'] == $mysql_keys['right_hp_mails']) { echo "text-success"; } else { echo "text-danger"; } ?>">
					<td><?php echo $language['mail_settings']; ?><p class="text-muted"><?php echo $language['mail_settings_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_mails']['key'] == $mysql_keys['right_hp_mails']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr style="border-bottom: 0;" class="<?php if($user_right['right_hp_logs']['key'] == $mysql_keys['right_hp_logs']) { echo "text-success"; } else { echo "text-danger"; } ?>">
					<td><?php echo $language['logs']; ?><p class="text-muted"><?php echo $language['logs_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_hp_logs']['key'] == $mysql_keys['right_hp_logs']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- Teamspeakrechte -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-university"></i> <?php echo $language['ts_rights']; ?></h4>
		<h6 class="card-subtitle text-muted"><?php echo $language['global_rights']; ?></h6>
	</div>
	<div class="card-block">
		<table class="table permission-table">
			<tbody>
				<tr class="<?php echo ($user_right['right_web']['key'] == $mysql_keys['right_web']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['access_interface']; ?><p class="text-muted"><?php echo $language['access_interface_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_web']['key'] == $mysql_keys['right_web']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_web_global_message_poke']['key'] == $mysql_keys['right_web_global_message_poke']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['instance_msg_poke']; ?><p class="text-muted"><?php echo $language['instance_msg_poke_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_web_global_message_poke']['key'] == $mysql_keys['right_web_global_message_poke']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['create_server']; ?><p class="text-muted"><?php echo $language['create_server_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_web_server_create']['key'] == $mysql_keys['right_web_server_create']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr class="<?php echo ($user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['delete_server']; ?><p class="text-muted"><?php echo $language['delete_server_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_web_server_delete']['key'] == $mysql_keys['right_web_server_delete']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
				<tr style="border-bottom: 0;" class="<?php echo ($user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "text-success" : "text-danger"; ?>">
					<td><?php echo $language['access_to_all_server']; ?><p class="text-muted"><?php echo $language['access_to_all_server_info']; ?></p></td>
					<td class="icon"><i class="fa fa-<?php echo ($user_right['right_web_global_server']['key'] == $mysql_keys['right_web_global_server']) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- Benutzerdefinierte Rechte -->
<?php foreach($ts3_server AS $instanz => $values)
{
	if(!empty($user_right['right_web_server_view'][$instanz]))
	{
		$ports								=	array_filter(explode(",", $user_right['right_web_server_view'][$instanz]));
		foreach($ports AS $port)
		{
			$instanzname					=	($values['alias'] != '') ? xssSafe($values['alias']) : xssSafe($values['ip']); ?>
			<div class="card">
				<div onClick="slideMe('box-<?php echo $instanz; ?>-<?php echo $port; ?>', 'arrow-<?php echo $instanz; ?>-<?php echo $port; ?>');" class="card-block card-block-header" style="padding-bottom: 1.25rem;cursor: pointer;">
					<h4 class="card-title">
						<i id="arrow-<?php echo $instanz; ?>-<?php echo $port; ?>" class="fa fa-arrow-right"></i> <?php echo $language['port'].': '.$port; ?>
					</h4>
					<h6 class="card-subtitle text-muted">
						<?php echo $language['instance'].': '.$instanzname; ?>
					</h6>
				</div>
				<div id="box-<?php echo $instanz; ?>-<?php echo $port; ?>" class="card-block" style="padding-top: 0;display: none;">
					<table class="table permission-table">
						<tbody>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_view')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_view']; ?><p class="text-muted"><?php echo $language['server_view_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_view')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_edit')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_edit']; ?><p class="text-muted"><?php echo $language['server_edit_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_edit')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_start_stop')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_start_stop']; ?><p class="text-muted"><?php echo $language['server_start_stop_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_start_stop')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_message_poke')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_msg_poke']; ?><p class="text-muted"><?php echo $language['server_msg_poke_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_message_poke')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_mass_actions')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_mass_actions']; ?><p class="text-muted"><?php echo $language['server_mass_actions_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_mass_actions')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_protokoll')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_protokoll']; ?><p class="text-muted"><?php echo $language['server_protokoll_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_protokoll')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_icons')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_icons']; ?><p class="text-muted"><?php echo $language['server_icons_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_icons')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_bans')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_bans']; ?><p class="text-muted"><?php echo $language['server_bans_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_bans')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_token')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_token']; ?><p class="text-muted"><?php echo $language['server_token_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_token')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_backups')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_backups']; ?><p class="text-muted"><?php echo $language['server_backups_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_backups')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_clients')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_clients']; ?><p class="text-muted"><?php echo $language['server_clients_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_server_clients')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_file_transfer')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['server_filelist']; ?><p class="text-muted"><?php echo $language['server_filelist_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_file_transfer')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_client_actions')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['client_actions']; ?><p class="text-muted"><?php echo $language['client_actions_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_client_actions')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_client_rights')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['client_permission']; ?><p class="text-muted"><?php echo $language['client_permission_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_client_rights')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
							<tr style="border-bottom: 0;" class="<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_channel_actions')) ? "text-success" : "text-danger"; ?>">
								<td><?php echo $language['channel_actions']; ?><p class="text-muted"><?php echo $language['channel_actions_info']; ?></p></td>
								<td class="icon"><i class="fa fa-<?php echo (isPortPermission($user_right, $instanz, $port, 'right_web_channel_actions')) ? "check" : "ban"; ?>" aria-hidden="true"></i></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		<?php };
	};
}; ?>

<!-- Javascripte Laden -->
<script src="js/sonstige/preloader.js"></script>