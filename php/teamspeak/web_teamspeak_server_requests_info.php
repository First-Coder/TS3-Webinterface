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
	if($_SESSION['login'] != $mysql_keys['login_key'] || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right		=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_web']['key'] != $mysql_keys['right_web'] || $user_right['right_web_server_create']['key'] != $mysql_keys['right_web_server_create'] || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite();
	};
	
	/*
		Get saved informations
	*/
	$information		=	json_decode(file_get_contents(__DIR__."/../../files/wantServer/".$_POST['file']));
?>

<!-- Haupteinstellungen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<i class="fa fa-user"></i> <?php xssEcho($information->username); ?>
		</h4>
		<?php if($information->creationTimestamp != "") { ?>
			<h6 class="card-subtitle text-muted"><?php echo $language['create_on'].": ".date("H:i:s d.m.Y", $information->creationTimestamp); ?></h6>
		<?php }; ?>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label ><?php echo $language['server_request_cause']; ?></label>
			<textarea style="width: 100%;" class="form-control" disabled><?php xssEcho($information->serverCreateCause); ?></textarea>
		</div>
		<div class="form-group">
			<label ><?php echo $language['server_request_why']; ?></label>
			<textarea style="width: 100%;" class="form-control" disabled><?php xssEcho($information->serverCreateWhy); ?></textarea>
		</div>
		<div class="form-group">
			<label ><?php echo $language['server_request_clients']; ?></label>
			<input type="number" class="form-control" value="<?php xssEcho($information->serverCreateNeededSlots); ?>" disabled>
		</div>
		<div class="form-group">
			<label ><?php echo $language['in_instance']; ?></label>
			<select class="form-control c-select" id="serverCreateWhichInstanz" style="width:100%;">
				<?php
					foreach($ts3_server AS $instanz=>$server)
					{
						if($server['alias'] != '')
						{
							echo '<option value="' . $instanz . '">' . xssSafe($server['alias']) . '</option>';
						}
						else
						{
							echo '<option value="' . $instanz . '">' . xssSafe($server['ip']) . '</option>';
						};
					};
				?>
			</select>
			<small class="form-text text-muted"><?php echo $language['in_instance_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['teamspeak_name']; ?></label>
			<input id="serverCreateServername" type="text" class="form-control" value="<?php xssEcho($information->serverCreateServername); ?>">
			<small class="form-text text-muted"><?php echo $language['ts3_servername_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['ts3_choose_port']; ?></label>
			<input id="serverCreatePort" type="number" class="form-control" value="<?php xssEcho($information->serverCreatePort); ?>">
			<small class="form-text text-muted"><?php echo $language['ts3_choose_port_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['ts3_max_clients']; ?></label>
			<input id="serverCreateSlots" type="number" class="form-control" value="<?php xssEcho($information->serverCreateSlots); ?>">
			<small class="form-text text-muted"><?php echo $language['ts3_max_clients_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['ts3_reservierte_slots']; ?></label>
			<input id="serverCreateReservedSlots" type="number" class="form-control" value="<?php xssEcho($information->serverCreateReservedSlots); ?>">
			<small class="form-text text-muted"><?php echo $language['ts3_reservierte_slots_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['password']; ?></label>
			<input id="serverCreatePassword" type="password" class="form-control" value="<?php xssEcho($information->serverCreatePassword); ?>">
			<small class="form-text text-muted"><?php echo $language['password_info']; ?></small>
		</div>
		<div class="form-group">
			<label ><?php echo $language['ts3_welcome_message']; ?></label>
			<textarea id="serverCreateWelcomeMessage" rows="5" class="form-control"><?php xssEcho($information->serverCreateWelcomeMessage); ?></textarea>
		</div>
		<?php if(isSet($information->password))
		{
			echo "<p style=\"text-align: center;\">".$language['ts_create_user_info']."</p>";
		}; ?>
	</div>
</div>

<!-- Buttons -->
<div class="card">
	<div class="card-block">
		<div class="row">
			<div class="col-lg-12 col-md-12" style="text-align:center;">
				<button onClick="teamspeakServerRequestsInit();" class="btn btn-secondary" type="button"><i class="fa fa-fw fa-arrow-left"></i> <?php echo $language['back']; ?></button>
				<button id="createServer" onClick="createServer('<?php xssEcho($information->username); ?>', '<?php xssEcho($information->password); ?>', '<?php echo $_POST['file']; ?>', true)" class="btn btn-success" type="button"><i class="fa fa-fw fa-check"></i> <?php echo $language['create_server']; ?></button>
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
	
	var file_delete_success			=	'<?php echo $language['file_delete_success']; ?>';
	var ts_create_user_info			=	'<?php echo $language['ts_create_user_info']; ?>';
	var hp_user_edit_failed			=	'<?php echo $language['hp_user_edit_failed']; ?>';
	
	var ts3_server_create_default 	= 	<?php echo json_encode($ts3_server_create_default); ?>;
	
	var serverRequestUsername		=	'';
	var serverRequestPassword		=	'';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>