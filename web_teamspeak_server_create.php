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
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls();
	
	/*
		Is Client logged in?
	*/
	$urlData				=	split("\?", $_SERVER['HTTP_REFERER'], -1);
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
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
	if($user_right['right_web'] != $mysql_keys['right_web'] || $user_right['right_web_server_create'] != $mysql_keys['right_web_server_create'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Search files in Folder wantserver/
	*/
	$wantServer = scandir('wantServer/');
?>

<!-- Haupteinstellungen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-paint-brush"></i> <?php echo $language['main_settings']; ?>
		</h4>
	</div>
	<div class="card-block">
		<p style="text-align:center;text-decoration:underline;"><?php echo $language['ts3_lizenz_info']; ?></p>
		<!-- Haupteinstellungen -->
		<div class="row server-create-einrueckung" style="margin-top:10px;">
			<div class="col-lg-6 col-md-12" style="padding-top:3px;">
				<?php echo "In Instanz"; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<select class="form-control" id="serverCreateWhichInstanz" style="width:100%;">
					<?php
						foreach($ts3_server AS $instanz=>$server)
						{
							if($server['alias'] != '')
							{
								echo '<option value="' . $instanz . '">' . $server['alias'] . '</option>';
							}
							else
							{
								echo '<option value="' . $instanz . '">' . $server['ip'] . '</option>';
							};
						};
					?>
				</select>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_servername']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateServername" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['servername']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_choose_port']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreatePort" maxlength="5" type="number" class="form-control" placeholder="XXXXX">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_max_clients']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateSlots" maxlength="4" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['slots']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_reservierte_slots']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateReservedSlots" maxlength="4" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['reserved_slots']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['password']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreatePassword" type="password" class="form-control" placeholder="<?php echo $ts3_server_create_default['password']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_welcome_message']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<textarea id="serverCreateWelcomeMessage" class="form-control" rows="8"><?php echo $ts3_server_create_default['welcome_message']; ?></textarea> 
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Beschwerdeeinstellungen -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseBeschwerdesettings');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseBeschwerdesettingsIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['complaintsettings']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseBeschwerdesettings" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_autoban_count']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateAutobanCount" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['auto_ban_count']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_autoban_duration']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateAutobanDuration" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['auto_ban_time']; ?>">
				<?php echo $language['seconds']; ?>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_autoban_delete_after']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateAutobanDeleteAfter" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['remove_time']; ?>">
				<?php echo $language['seconds']; ?>
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Hostsettings -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseHostsettings');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseHostsettingsIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['hostsettings']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseHostsettings" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12" style="padding-top:3px;">
				<?php echo $language['ts3_host_message_show']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<select id="serverCreateHosttype" class="form-control" style="width:100%;">
					<option value="0" <?php if ($ts3_server_create_default['host_message_show'] == 0) {echo 'selected';}?>><?php echo $language['ts3_host_message_show_1']; ?></option>
					<option value="1" <?php if ($ts3_server_create_default['host_message_show'] == 1) {echo 'selected';}?>><?php echo $language['ts3_host_message_show_2']; ?></option>
					<option value="2" <?php if ($ts3_server_create_default['host_message_show'] == 2) {echo 'selected';}?>><?php echo $language['ts3_host_message_show_3']; ?></option>
					<option value="3" <?php if ($ts3_server_create_default['host_message_show'] == 3) {echo 'selected';}?>><?php echo $language['ts3_host_message_show_4']; ?></option>
				</select>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_url']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostUrl" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_url']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_message']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostMessage" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_message']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_banner_url']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostBannerUrl" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_banner_url']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_banner_interval']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostBannerInterval" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_banner_int']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_buttton_gfx_url']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostButtonGfxUrl" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_button_gfx']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_button_tooltip']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostButtonTooltip" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_button_tip']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_host_button_url']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateHostButtonUrl" type="text" class="form-control" placeholder="<?php echo $ts3_server_create_default['host_button_url']; ?>">
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Antiflood -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseAntiFlood');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseAntiFloodIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['anti_flood_settings']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseAntiFlood" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_reduce_points']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreateReducePoints" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['points_tick_reduce']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_points_block']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreatePointsBlock" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['points_needed_block_cmd']; ?>">
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_points_block_ip']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<input id="serverCreatePointsBlockIp" type="number" class="form-control" placeholder="<?php echo $ts3_server_create_default['points_needed_block_ip']; ?>">
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Transfersettings -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseTransfersettings');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseTransfersettingsIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['transfersettings']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseTransfersettings" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_upload_limit']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateUploadLimit" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['upload_bandwidth_limit']; ?>">
				<?php echo $language['byte_s']; ?>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_upload_kontigent']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateUploadKontigent" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['upload_quota']; ?>">
				<?php echo $language['mbyte']; ?>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_download_limit']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateDownloadLimit" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['download_bandwidth_limit']; ?>">
				<?php echo $language['byte_s']; ?>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_download_kontigent']; ?>:
			</div>
			<div class="col-lg-6 col-md-12">
				<input id="serverCreateDownloadKontigent" type="number" class="form-control" style="width:70%;display:inline;" placeholder="<?php echo $ts3_server_create_default['download_quota']; ?>">
				<?php echo $language['mbyte']; ?>
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Protokolsettings -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseProtokolsettings');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseProtokolsettingsIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['protokolsettings']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseProtokolsettings" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_client']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolClient" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_client'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_query']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolQuery" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_query'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_channel']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolChannel" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_channel'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_rights']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolRights" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_permissions'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_server']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolServer" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_server'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12 input-padding">
				<?php echo $language['ts3_protokol_transfer']; ?>:
			</div>
			<div class="col-lg-offset-2 col-lg-2 col-md-12" style="text-align:center;">
				<input id="serverCreateProtokolTransfer" type="checkbox" data-width="100%" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary"
					data-on="<?php echo $language['yes']; ?>" data-off="<?php echo $language['no']; ?>" <?php if($ts3_server_create_default['virtualserver_log_filetransfer'] == 1) { echo 'checked'; } ?>>
			</div>
		</div>
	</div>
</div>

<!-- Optionale Einstellungen: Erweiterungen -->
<div class="card">
	<div style="cursor:pointer;" class="card-block card-block-header" onClick="slideSettings('collapseExtrasettings');">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i id="collapseExtrasettingsIcon" class="fa fa-fw fa-arrow-right"></i> <?php echo $language['extensions']; ?>
			</div>
			<div class="label label-primary pull-xs-right">
				<i class="fa fa-info"></i> <?php echo $language['optional']; ?>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" id="collapseExtrasettings" style="display:none;">
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12" style="padding-top:3px;">
				<?php echo $language['ts3_servercopy']; ?>:
			</div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<select onChange="serverCreateChangePort()" id="serverCreateServerCopy" style="width:100%;" class="form-control">
					<option value="nope" selected><?php echo $language['ts3_no_copy']; ?></option>
					<?php for ($i = 0; $i < count($ts3_server); $i++) { ?>
						<option value="<?php echo $i; ?>">
							<?php if($ts3_server[$i]['alias'] != '') {
									echo $ts3_server[$i]['alias'];
								} else {
									echo $ts3_server[$i]['ip']; 
								} ?>
						</option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="row server-create-einrueckung">
			<div class="col-lg-6 col-md-12"></div>
			<div class="col-lg-6 col-md-12" style="text-align:center;">
				<select id="serverCreateServerCopyPort" style="width:100%;" class="form-control">
					<option value="nope" selected><?php echo $language['ts3_no_copy']; ?></option>
				</select>
			</div>
		</div>
	</div>
</div>

<!-- Buttons -->
<div class="card">
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12" style="text-align:center;">
				<button id="createServer" onClick="createServer()" class="btn btn-success" type="button"><i class="fa fa-fw fa-check"></i> <?php echo $language['create_server']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
	
	var ts_server_create_wrong_port	=	'<?php echo $language['ts_server_create_wrong_port']; ?>';
	var ts3_no_copy					=	'<?php echo $language['ts3_no_copy']; ?>';
	
	var hp_user_edit_failed			=	'<?php echo $language['hp_user_edit_failed']; ?>';
	
	var ts3_server_create_default 	= 	<?php echo json_encode($ts3_server_create_default); ?>;
</script>

<!-- Javascripte Laden -->
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/webinterface/teamspeak.js"></script>
<script>
	$(document).ready(function() {
		$('[data-tooltip="tooltip"]').tooltip();
		$('.dropdown-toggle').dropdown();
	});
</script>
<script src="js/sonstige/preloader.js"></script>