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
	$urlData				=	explode("?", $_SERVER['HTTP_REFERER']);
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
		Get saved informations
	*/
	$information		=	json_decode(file_get_contents("wantServer/".$_POST['file']));
?>

<!-- Haupteinstellungen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i class="fa fa-user"></i> <?php echo htmlspecialchars($information->username); ?>
			</div>
			<?php if($information->creationTimestamp != "") { ?>
				<div class="pull-xs-right" style="font-size: 15px;margin-top: 3px;">
					<?php echo date("Y-m-d H:i:s", $information->creationTimestamp); ?>
				</div>
			<?php }; ?>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block">
		<div class="row margin-row-server-request">
			<div class="col-lg-5 col-sm-5 col-xs-12  input-padding">
				<?php echo $language['server_request_cause']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<textarea style="width: 100%;" class="form-control" disabled><?php echo htmlspecialchars($information->serverCreateCause); ?></textarea>
			</div>
		</div>
		<div class="row margin-row-server-request">
			<div class="col-lg-5 col-sm-5 col-xs-12  input-padding">
				<?php echo $language['server_request_why']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<textarea style="width: 100%;" class="form-control" disabled><?php echo htmlspecialchars($information->serverCreateWhy); ?></textarea>
			</div>
		</div>
		<div class="row margin-row-server-request">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				<?php echo $language['server_request_clients']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="" type="number" class="form-control" value="<?php echo htmlspecialchars($information->serverCreateNeededSlots); ?>" disabled>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12" style="padding-top:3px;margin-bottom: 18px;">
				<?php echo "In Instanz"; ?>:
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12" style="text-align:center;">
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
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				<?php echo $language['teamspeak_name']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="serverCreateServername" type="text" class="form-control" value="<?php echo htmlspecialchars($information->serverCreateServername); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				Port
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="serverCreatePort" type="number" class="form-control" value="<?php echo htmlspecialchars($information->serverCreatePort); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				Slots
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="serverCreateSlots" type="number" class="form-control" value="<?php echo htmlspecialchars($information->serverCreateSlots); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				<?php echo $language['ts3_reservierte_slots']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="serverCreateReservedSlots" type="number" class="form-control" value="<?php echo htmlspecialchars($information->serverCreateReservedSlots); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				<?php echo "Server".strToLower($language['password']); ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<input id="teamspeak_password" type="text" class="form-control" value="<?php echo htmlspecialchars($information->serverCreatePassword); ?>">
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 col-sm-5 col-xs-12 input-padding">
				<?php echo $language['ts3_welcome_message']; ?>
			</div>
			<div class="col-lg-offset-1 col-lg-6 col-md-offset-1 col-md-6 col-sm-7 col-xs-12">
				<textarea id="serverCreateWelcomeMessage" class="form-control"><?php echo htmlspecialchars($information->serverCreateWelcomeMessage); ?></textarea>
			</div>
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
				<button id="createServer" onClick="createServer('<?php echo htmlspecialchars($information->username); ?>', '<?php echo htmlspecialchars($information->password); ?>', '<?php echo $_POST['file']; ?>')" class="btn btn-success" type="button"><i class="fa fa-fw fa-check"></i> <?php echo $language['create_server']; ?></button>
				<button onClick="deleteWantServer('<?php echo $_POST['file']; ?>');" class="btn btn-danger" type="button"><i class="fa fa-fw fa-trash"></i> <?php echo $language['delete_request']; ?></button>
				<button onClick="abortServerRequest();" class="btn btn-secondary" type="button"><i class="fa fa-fw fa-arrow-left"></i> <?php echo $language['back']; ?></button>
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