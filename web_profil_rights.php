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
	//require_once("lang.php");
	require_once("ts3admin.class.php");
	require_once("functions.php");
	
	/*
		Start the PHP Session
	*/
	session_start();
	
	/*
		Get the Modul Keys
	*/
	$mysql_keys			=	getKeys();
	
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
	$user_right		=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
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
		};
	};
?>

<!-- Homepagerechte -->
<div class="card">
	<div class="card-block card-block-header">
		<div style="float:left;">
			<h4 class="card-title"><i class="fa fa-university"></i> <?php echo $language['hp_rights']; ?></h4>
		</div>
		<div style="float:right;margin-top:4px;margin-right:10px;">
			<i onmouseleave="$(this).toggleClass('fa-spin');" onmouseover="$(this).toggleClass('fa-spin');" data-toggle="tooltip" data-placement="left" title="<?php echo $language['refresh']; ?>" onClick="rightsUpdate('refresh_global_hp_rights');" class="fa fa-fw fa-refresh"></i>
		</div>
		<div style="clear:both;"></div>
		<h6 class="card-subtitle text-muted" style="margin-top:0px;"><?php echo $language['hp_global_rights']; ?></h6>
	</div>
	<div class="card-block">
		<div id="global_hp_rights_box">
			<table class="table" style="cursor:default;">
				<tbody>
					<tr id="right_hp_main" class="<?php if($user_right['right_hp_main'] == $mysql_keys['right_hp_main']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['hp_rights_edit']; ?></td>
						<td id="right_hp_main_text"><?php if($user_right['right_hp_main'] == $mysql_keys['right_hp_main']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_hp_ts3" class="<?php if($user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['hp_rights_ts3_edit']; ?></td>
						<td id="right_hp_ts3_text"><?php if($user_right['right_hp_ts3'] == $mysql_keys['right_hp_ts3']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_hp_user_create" class="<?php if($user_right['right_hp_user_create'] == $mysql_keys['right_hp_user_create']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['hp_rights_user_add']; ?></td>
						<td id="right_hp_user_create_text"><?php if($user_right['right_hp_user_create'] == $mysql_keys['right_hp_user_create']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_hp_user_delete" class="<?php if($user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['hp_rights_user_del']; ?></td>
						<td id="right_hp_user_delete_text"><?php if($user_right['right_hp_user_delete'] == $mysql_keys['right_hp_user_delete']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_hp_user_edit" class="<?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['hp_rights_user_edit']; ?></td>
						<td id="right_hp_user_edit_text"><?php if($user_right['right_hp_user_edit'] == $mysql_keys['right_hp_user_edit']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Teamspeakrechte -->
<div class="card">
	<div class="card-block card-block-header">
		<div style="float:left;">
			<h4 class="card-title"><i class="fa fa-university"></i> <?php echo $language['ts_rights']; ?></h4>
		</div>
		<div style="float:right;margin-top:4px;margin-right:10px;">
			<i onmouseleave="$(this).toggleClass('fa-spin');" onmouseover="$(this).toggleClass('fa-spin');" data-toggle="tooltip" data-placement="left" title="<?php echo $language['refresh']; ?>" onClick="rightsUpdate('refresh_global_ts_rights');" class="fa fa-fw fa-refresh"></i>
		</div>
		<div style="clear:both;"></div>
		<h6 class="card-subtitle text-muted" style="margin-top:0px;"><?php echo $language['hp_global_rights']; ?></h6>
	</div>
	<div class="card-block">
		<div id="global_ts_rights_box">
			<table class="table" style="cursor:default;">
				<tbody>
					<tr id="right_web" class="<?php if($user_right['right_web'] == $mysql_keys['right_web']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['ts_rights_access']; ?></td>
						<td id="right_web_text"><?php if($user_right['right_web'] == $mysql_keys['right_web']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_web_global_message_poke" class="<?php if($user_right['right_web_global_message_poke'] == $mysql_keys['right_web_global_message_poke']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['ts_rights_global_msg_poke']; ?></td>
						<td id="right_web_global_message_poke_text"><?php if($user_right['right_web_global_message_poke'] == $mysql_keys['right_web_global_message_poke']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_web_server_create" class="<?php if($user_right['right_web_server_create'] == $mysql_keys['right_web_server_create']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['ts_rights_create_server']; ?></td>
						<td id="right_web_server_create_text"><?php if($user_right['right_web_server_create'] == $mysql_keys['right_web_server_create']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
					<tr id="right_web_server_delete" class="<?php if($user_right['right_web_server_delete'] == $mysql_keys['right_web_server_delete']) { echo "table-success"; } else { echo "table-danger"; } ?>">
						<td><?php echo $language['ts_rights_del_server']; ?></td>
						<td id="right_web_server_delete_text"><?php if($user_right['right_web_server_delete'] == $mysql_keys['right_web_server_delete']) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Benutzerdefinierte Rechte -->
<?php foreach($ts3_server AS $instanz => $values) { ?>
		<?php if(hasUserInstanz($_SESSION['user']['id'], "$instanz")) {?>
			<?php foreach($ts3_servers[$instanz]['data'] AS $port) {
				if(strpos($user_right['ports']['right_web_server_view'][$instanz], $port['virtualserver_port']) !== false)
				{ ?>
					<div id="instanz_rights_box_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>">
						<div class="card">
							<div class="card-block card-block-header">
								<div style="float:left;">
									<h4 class="card-title">
										<i class="fa fa-university"></i> Instanz: 
										<?php if($values['alias'] != '')
										{
											echo $values['alias'];
										}
										else
										{
											echo $values['ip'];
										}; ?>
										<i class="fa fa-fw fa-long-arrow-right"></i> 
										Port: <?php echo $port['virtualserver_port']; ?>
									</h4>
								</div>
								<div style="float:right;margin-top:4px;margin-right:10px;">
									<i onmouseleave="$(this).toggleClass('fa-spin');" onmouseover="$(this).toggleClass('fa-spin');" data-toggle="tooltip" data-placement="left" title="<?php echo $language['refresh']; ?>" onClick="rightsUpdate('refresh_instanz_rights', '<?php echo $instanz; ?>', '<?php echo $port['virtualserver_port']; ?>');" class="fa fa-fw fa-refresh"></i>
								</div>
								<div style="clear:both;"></div>
							</div>
							<div class="card-block">
								<table class="table" style="cursor:default;">
									<tbody>
										<tr id="right_web_server_edit_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_edit'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_edit']; ?></td>
											<td id="right_web_server_edit_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_edit'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_start_stop_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_start_stop']; ?></td>
											<td id="right_web_server_start_stop_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_start_stop'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_message_poke_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_message_poke'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_msg_poke']; ?></td>
											<td id="right_web_server_message_poke_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_message_poke'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_mass_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_mass_actions'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_mass_actions']; ?></td>
											<td id="right_web_server_mass_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_mass_actions'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										
										<tr id="right_web_server_protokoll_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_protokoll'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_protokoll']; ?></td>
											<td id="right_web_server_protokoll_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_protokoll'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_icons_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_icons'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_icons']; ?></td>
											<td id="right_web_server_icons_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_icons'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_bans_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_bans'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_bans']; ?></td>
											<td id="right_web_server_bans_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_bans'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_token_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_token'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_token']; ?></td>
											<td id="right_web_server_token_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_token'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_backups_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_backups'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_backups']; ?></td>
											<td id="right_web_server_backups_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_backups'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_server_clients_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_server_clients'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_server_clients']; ?></td>
											<td id="right_web_server_clients_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_server_clients'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_client_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_client_actions'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_client_actions']; ?></td>
											<td id="right_web_client_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_client_actions'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_client_rights_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_client_rights'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_client_rights']; ?></td>
											<td id="right_web_client_rights_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_client_rights'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
										<tr id="right_web_channel_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>" class="<?php if(strpos($user_right['ports']['right_web_channel_actions'][$instanz], $port['virtualserver_port']) !== false) { echo "table-success"; } else { echo "table-danger"; } ?>">
											<td><?php echo $language['ts_rights_channel_actions']; ?></td>
											<td id="right_web_channel_actions_<?php echo $instanz; ?>_<?php echo $port['virtualserver_port']; ?>_text"><?php if(strpos($user_right['ports']['right_web_channel_actions'][$instanz], $port['virtualserver_port']) !== false) { echo $language['hp_right_yes']; } else { echo $language['hp_right_no']; } ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				<?php }
			} 
		}
	} ?>
</div>

<!-- Sprachdatein laden -->
<script>
	var user_session_id								=	'<?php echo $_SESSION['user']['id']; ?>';
	
	var hp_right_no									=	'<?php echo $language['hp_right_no']; ?>';
	var hp_right_yes								=	'<?php echo $language['hp_right_yes']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/profile.js"></script>
<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	});
</script>
<script src="js/sonstige/preloader.js"></script>