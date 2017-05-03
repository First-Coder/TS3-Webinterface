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
	require_once("functionsTeamspeak.php");
	
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
	$user_right		=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	
	/*
		Has the Client the Permission
	*/
	if($user_right['right_hp_main'] != $mysql_keys['right_hp_main'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Ports abfragen
	*/
	if(MASTERSERVER_INSTANZ != "" && MASTERSERVER_INSTANZ != "nope")
	{
		$tsPorts		=	getTeamspeakPorts(MASTERSERVER_INSTANZ);
	};
?>

<div id="adminContent">
	<!-- Versioninformationen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-info"></i> <?php echo $language['admin_version_info']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row" style="padding:.75rem;">
				<div class="col-lg-1"></div>
				<div class="col-lg-5 col-md-6">
					<?php echo $language['admin_installed_version']; ?>:
				</div>
				<div class="col-lg-5 col-md-6 <?php echo (INTERFACE_VERSION == checkNewVersion()) ? "text-success" : "text-danger"; ?>" style="text-align:center;">
					<?php echo INTERFACE_VERSION; ?>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-lg-1"></div>
				<div class="col-lg-5 col-md-6">
					<?php echo $language['admin_newest_version']; ?>:
				</div>
				<div class="col-lg-5 col-md-6 text-success" style="text-align:center;">
					<?php echo checkNewVersion(); ?>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<?php if(INTERFACE_VERSION != checkNewVersion(false)) { ?>
				<div class="row" style="padding:.75rem;">
					<div class="alert alert-danger">
						<b><i class="fa fa-warning"></i> <?php echo $language['attention']; ?>!</b>
						<p><?php echo $language['download_new_version']; ?></p>
					</div>
				</div>
			<?php }; ?>
		</div>
	</div>
	
	<!-- Config -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-cogs"></i> <?php echo $language['homepage_configurations']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setConfig();" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<table class="table table-condensed" style="font-size: 1em;">
				<tr>
					<td class="input-padding">
						<?php echo $language['webinterface_title']; ?>:
					</td>
					<td>
						<input id="heading" style="width:100%;" value="<?php echo htmlspecialchars(HEADING); ?>" class="form-control">
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['teamspeak_name']; ?>:
					</td>
					<td>
						<input id="chatname" style="width:100%;" value="<?php echo htmlspecialchars(TS3_CHATNAME); ?>" class="form-control">
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						Interface Mail:
					</td>
					<td>
						<input id="mailadress" style="width:100%;" value="<?php echo htmlspecialchars(MAILADRESS); ?>" class="form-control">
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $language['masterserver_instanz']; ?>:
					</td>
					<td>
						<select id="masterserverSelectInstanz" onChange="adminSettingsChangePort();" class="form-control" style="width:100%;">
							<option value="nope" <?php echo (MASTERSERVER_INSTANZ == "" || MASTERSERVER_INSTANZ == "nope") ? "selected" : ""; ?>><?php echo $language['no_masterserver']; ?></option>
							<?php for ($i = 0; $i < count($ts3_server); $i++) { ?>
								<option value="<?php echo $i; ?>" <?php echo (MASTERSERVER_INSTANZ == "$i") ? "selected" : ""; ?>>
									<?php if($ts3_server[$i]['alias'] != '') {
											echo htmlspecialchars($ts3_server[$i]['alias']);
										} else {
											echo $ts3_server[$i]['ip']; 
										}; ?>
								</option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $language['masterserver_port']; ?>:
					</td>
					<td>
						<select id="masterserverSelectPort" class="form-control" style="width:100%;">
							<option value="nope" <?php echo (MASTERSERVER_INSTANZ == "" || MASTERSERVER_INSTANZ == "nope") ? "selected" : ""; ?>><?php echo $language['no_masterserver']; ?></option>
							<?php if(MASTERSERVER_INSTANZ != "" && MASTERSERVER_INSTANZ != "nope") {
								foreach($tsPorts AS $port) { ?>
									<option value="<?php echo $port; ?>" <?php echo (MASTERSERVER_PORT == $port) ? "selected" : ""; ?>>
										<?php echo $port; ?>
									</option>
								<?php };
							}; ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Homepagemodule -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-list"></i> <?php echo $language['admin_homepage_modul']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row">
				<div class="col-lg6 col-md-12 col-sm-12 col-xs-12 margin-row">
					<div style="float:left;" class="toggle-padding-adminsettings">
						<?php echo $language['admin_free_register']; ?>:
					</div>
					<div style="float:right;">
						<input id="setModulFreeRegister" onChange="changeModul('setModulFreeRegister')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['free_register'] == 'true') { echo 'checked'; } ?>>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="col-lg6 col-md-12 col-sm-12 col-xs-12 margin-row">
					<div style="float:left;" class="toggle-padding-adminsettings">
						<?php echo $language['admin_server_antrag']; ?>:
					</div>
					<div style="float:right;">
						<input id="setModulServerAntrag" onChange="changeModul('setModulServerAntrag')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['free_ts3_server_application'] == 'true') { echo 'checked'; } ?>>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="col-lg6 col-md-12 col-sm-12 col-xs-12 margin-row">
					<div style="float:left;" class="toggle-padding-adminsettings">
						<?php echo $language['admin_write_news']; ?>:
					</div>
					<div style="float:right;" class="disabled">
						<input id="setModulWriteNews" onChange="changeModul('setModulWriteNews')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['write_news'] == 'true') { echo 'checked'; } ?>>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="col-lg6 col-md-12 col-sm-12 col-xs-12 margin-row">
					<div style="float:left;" class="toggle-padding-adminsettings">
						<?php echo $language['admin_webinterface']; ?>:
					</div>
					<div style="float:right;">
						<input id="setModulWebinterface" onChange="changeModul('setModulWebinterface')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['webinterface'] == 'true') { echo 'checked'; } ?>>
					</div>
					<div style="clear:both;"></div>
				</div>
				<div class="col-lg6 col-md-12 col-sm-12 col-xs-12 margin-row">
					<div style="float:left;" class="toggle-padding-adminsettings">
						Masterserver:
					</div>
					<div style="float:right;">
						<input id="setModulMasterserver" onChange="changeModul('setModulMasterserver')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['masterserver'] == 'true') { echo 'checked'; } ?>>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Spracheinstellungen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-flag"></i> <?php echo $language['admin_speaksettings']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row">
				<?php foreach ($installedLanguages AS $choosedLanguage => $languageLink) { ?>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
						<label class="c-input c-radio" data-tooltip="tooltip" data-placement="top" title="Translate by: <?php echo $languageLink; ?>">
							<input name="langRadio" onClick="changeLanguage('<?php echo $choosedLanguage; ?>');" type="radio" <?php if($choosedLanguage == LANGUAGE) { echo 'checked'; } ?>>
							<span class="c-indicator"></span>
							<?php echo $choosedLanguage; ?>
						</label>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
	
	<!-- Designeinstellungen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-tint"></i> Homepage Designs</h4>
		</div>
		<div class="card-block">
			<?php
				$files 			= 	scandir('css/themes/');
				$files[0]		=	"style.css";
				unset($files[1]);
				
				foreach($files AS $file)
				{ 
					if($file != "style.css")
					{
						$data 		= 	file("css/themes/$file");
					}
					else
					{
						$data 		= 	file("css/$file");
					};
					$background	=	'';
					foreach($data as $line)
					{
						if(strpos($line, "color:") !== false)
						{
							$background		=	$line;
							break;
						};
					};?>
					<div class="row" style="padding:.75rem;<?php echo $background; ?>">
						<div class="col-lg-1"></div>
						<div class="col-lg-5 col-md-6 toggle-padding">
							<?php if($file != "style.css") { ?>
								/css/themes/<?php echo $file; ?>
							<?php } else { ?>
								/css/<?php echo $file; ?>
							<?php } ?>
						</div>
						<div class="col-lg-5 col-md-6" style="text-align:center;">
							<button onClick="changeTheme('<?php echo $file; ?>');" class="btn btn-success"><i class="fa fa-fw fa-check"></i> <?php echo $language['accept']; ?></button>
						</div>
						<div class="col-lg-1"></div>
					</div>
				<?php };
			?>
		</div>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var hp_modul_settings_done		=	'<?php echo $language['admin_modul_settings_done']; ?>';
	var hp_modul_settings_failed	=	'<?php echo $language['admin_modul_settings_failed']; ?>';
	
	var field_cant_be_empty			=	'<?php echo $language['field_cant_be_empty']; ?>';
	var settings_mail_needed		=	'<?php echo $language['settings_mail_needed']; ?>';
	var no_masterserver				=	'<?php echo $language['no_masterserver']; ?>';
	var settigns_saved				=	'<?php echo $language['settigns_saved']; ?>';
	
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/jquery/jquery-2.2.0.js"></script>
<script src="js/bootstrap/tether.js"></script>
<script src="js/bootstrap/bootstrap.js"></script>
<script src="js/bootstrap/bootstrap-toggle.js"></script>
<script src="js/bootstrap/bootstrap-notify.js"></script>
<script src="js/webinterface/admin.js"></script>
<script>
	$(function () {
		$('[data-tooltip="tooltip"]').tooltip();
	});
</script>
<script src="js/sonstige/preloader.js"></script>