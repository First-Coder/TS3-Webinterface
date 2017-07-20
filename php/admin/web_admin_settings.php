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
	require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys			=	getKeys();
	$mysql_modul		=	getModuls(false);
	
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
	if($user_right['right_hp_main']['key'] != $mysql_keys['right_hp_main'])
	{
		reloadSite();
	};
	
	/*
		Ports abfragen
	*/
	if(MASTERSERVER_INSTANZ != "" && MASTERSERVER_INSTANZ != "nope")
	{
		$tsPorts		=	getTeamspeakPorts(MASTERSERVER_INSTANZ);
	};
	
	/*
		Themes
	*/
	$themes									=	array();
	$files 									= 	scandir('../../css/themes/');
	$files[0]								=	"style.css";
	$index 									=	0;
	unset($files[1]);
	
	foreach($files AS $file)
	{ 
		if($file != "style.css")
		{
			$data 							= 	file("../../css/themes/$file");
		}
		else
		{
			$data 							= 	file("../../css/$file");
		};
		
		$themes[$index]						=	array();
		$themes[$index]['filename']			=	($file == "style.css") ? "css/$file" : "css/themes/$file";
		$themes[$index]['file']				=	$file;
		
		foreach($data as $line)
		{
			if(strpos($line, "#name") !== false)
			{
				$tmpLine					=	explode(":", $line);
				$themes[$index]['name']		=	trim($tmpLine[1]);
			}
			else if(strpos($line, "#autor") !== false)
			{
				$tmpLine					=	explode(":", $line);
				$themes[$index]['autor']	=	trim($tmpLine[1]);
			}
			else if(strpos($line, "#img") !== false)
			{
				$tmpLine					=	explode(":", $line);
				$themes[$index]['img']		=	trim($tmpLine[1]);
			}
			else if(strpos($line, "#txtcolor") !== false)
			{
				$tmpLine					=	explode(":", $line);
				$themes[$index]['txtcolor']	=	trim($tmpLine[1]);
			};
		};
		$index++;
	};
?>

<div id="adminContent">
	<!-- Versioninformationen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-info"></i> <?php echo $language['version_info']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row" style="padding:.75rem;">
				<div class="col-lg-1"></div>
				<div class="col-lg-5 col-md-6">
					<?php echo $language['installed_version']; ?>:
				</div>
				<div class="col-lg-5 col-md-6 <?php echo (INTERFACE_VERSION == checkNewVersion()) ? "text-success" : "text-danger"; ?>" style="text-align:center;">
					<?php echo INTERFACE_VERSION; ?>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<div class="row" style="padding:.75rem;">
				<div class="col-lg-1"></div>
				<div class="col-lg-5 col-md-6">
					<?php echo $language['newest_version']; ?>:
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
	
	<!-- Homepagesettings -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-cogs"></i> <?php echo $language['homepage_settings']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setConfig('homepagesettings');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<div class="form-group">
				<label for="heading"><?php echo $language['webinterface_title']; ?></label>
				<input type="text" class="form-control" id="heading" aria-describedby="headingHelp" placeholder="Enter Heading" value="<?php xssEcho(HEADING); ?>">
				<small id="headingHelp" class="form-text text-muted"><?php echo $language['webinterface_title_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="chatname"><?php echo $language['teamspeak_name']; ?></label>
				<input type="text" class="form-control" id="chatname" aria-describedby="chatnameHelp" placeholder="Enter Chatname" value="<?php xssEcho(TS3_CHATNAME); ?>">
				<small id="chatnameHelp" class="form-text text-muted"><?php echo $language['teamspeak_name_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="masterserverSelectInstanz"><?php echo $language['masterserver_instanz']; ?></label>
				<select id="masterserverSelectInstanz" onChange="adminSettingsChangePort();" class="form-control c-select" style="width:100%;" aria-describedby="masterserverInstanzHelp"
					<?php if($mysql_modul['masterserver'] != "true") { echo "disabled"; }; ?>>
					<option value="nope" <?php echo (MASTERSERVER_INSTANZ == "" || MASTERSERVER_INSTANZ == "nope") ? "selected" : ""; ?>><?php echo $language['no_masterserver']; ?></option>
					<?php for ($i = 0; $i < count($ts3_server); $i++) { ?>
						<option value="<?php echo $i; ?>" <?php echo (MASTERSERVER_INSTANZ == "$i") ? "selected" : ""; ?>>
							<?php if($ts3_server[$i]['alias'] != '') {
									xssEcho($ts3_server[$i]['alias']);
								} else {
									xssEcho($ts3_server[$i]['ip']); 
								}; ?>
						</option>
					<?php } ?>
				</select>
				<small id="masterserverInstanzHelp" class="form-text text-muted"><?php echo $language['masterserver_instanz_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="masterserverSelectPort"><?php echo $language['masterserver_port']; ?></label>
				<select id="masterserverSelectPort" class="form-control c-select" style="width:100%;" aria-describedby="masterserverPortHelp"
					<?php if($mysql_modul['masterserver'] != "true" || MASTERSERVER_INSTANZ == "" || MASTERSERVER_INSTANZ == "nope") { echo "disabled"; }; ?>>
					<option value="nope" <?php echo (MASTERSERVER_INSTANZ == "" || MASTERSERVER_INSTANZ == "nope") ? "selected" : ""; ?>><?php echo $language['no_masterserver']; ?></option>
					<?php if(MASTERSERVER_INSTANZ != "" && MASTERSERVER_INSTANZ != "nope" && !empty($tsPorts)) {
						foreach($tsPorts AS $port) { ?>
							<option value="<?php echo $port; ?>" <?php echo (MASTERSERVER_PORT == $port) ? "selected" : ""; ?>>
								<?php echo $port; ?>
							</option>
						<?php };
					}; ?>
				</select>
				<small id="masterserverPortHelp" class="form-text text-muted"><?php echo $language['masterserver_port_info']; ?></small>
			</div>
		</div>
	</div>
	
	<!-- Mailsettings -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-inbox"></i> <?php echo $language['mail_settings']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setConfig('mailsettings');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['mails']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setMails" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if(USE_MAILS == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="form-group">
				<label for="mailadress"><?php echo $language['interface_mail']; ?></label>
				<input type="email" class="form-control" id="mailadress" aria-describedby="mailadressHelp" placeholder="Enter Mail" value="<?php xssEcho(MAILADRESS); ?>">
				<small id="mailadressHelp" class="form-text text-muted"><?php echo $language['mailadress_info']; ?></small>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					SMTP
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setMailSmtp" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if(MAIL_SMTP == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="form-group">
				<label for="smtpHost"><?php echo $language['smtp_host']; ?></label>
				<input type="text" class="form-control" id="smtpHost" aria-describedby="smtpHostHelp" placeholder="Enter Host" value="<?php xssEcho(MAIL_SMTP_HOST); ?>">
				<small id="smtpHostHelp" class="form-text text-muted"><?php echo $language['smtp_host_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="smtpPort"><?php echo $language['smtp_port']; ?></label>
				<input type="text" class="form-control" id="smtpPort" aria-describedby="smtpPortHelp" placeholder="Enter Port" value="<?php xssEcho(MAIL_SMTP_PORT); ?>">
				<small id="smtpPortHelp" class="form-text text-muted"><?php echo $language['smtp_port_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="smtpUser"><?php echo $language['smtp_user']; ?></label>
				<input type="text" class="form-control" id="smtpUser" aria-describedby="smtpUserHelp" placeholder="Enter User" value="<?php xssEcho(MAIL_SMTP_USERNAME); ?>">
				<small id="smtpUserHelp" class="form-text text-muted"><?php echo $language['smtp_user_info']; ?></small>
			</div>
			<div class="form-group">
				<label for="smtpPassword"><?php echo $language['smtp_password']; ?></label>
				<input type="password" class="form-control" id="smtpPassword" aria-describedby="smtpPasswordHelp" placeholder="Enter Password" value="<?php xssEcho(MAIL_SMTP_PASSWORD); ?>">
				<small id="smtpPasswordHelp" class="form-text text-muted"><?php echo $language['smtp_password_info']; ?></small>
			</div>
		</div>
	</div>
	
	<!-- Own sites -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title">
				<div class="pull-xs-left">
					<i class="fa fa-file"></i> <?php echo $language['own_sites']; ?>
				</div>
				<div class="pull-xs-right">
					<div style="margin-top:0px;padding: .175rem 1rem;"
						onclick="setConfig('ownsites');" class="pull-xs-right btn btn-secondary user-header-icons">
						<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
					</div>
				</div>
				<div style="clear:both;"></div>
			</h4>
		</div>
		<div class="card-block">
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['own_news_site']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setOwnNewsSite" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if(CUSTOM_NEWS_PAGE == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['own_dashboard_site']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setOwnDashboardSite" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if(CUSTOM_DASHBOARD_PAGE == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
		</div>
	</div>

	<!-- Homepagemodule -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-external-link"></i> <?php echo $language['homepage_moduls']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['self_register']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setModulFreeRegister" onChange="changeModul('setModulFreeRegister')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['free_register'] == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['create_server_antrag']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setModulServerAntrag" onChange="changeModul('setModulServerAntrag')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['free_ts3_server_application'] == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['write_news']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setModulWriteNews" onChange="changeModul('setModulWriteNews')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['write_news'] == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['interface']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setModulWebinterface" onChange="changeModul('setModulWebinterface')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['webinterface'] == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12 top-bottom-margin">
					<?php echo $language['masterserver']; ?>
				</div>
				<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 top-bottom-margin">
					<input data-width="100%" id="setModulMasterserver" onChange="changeModul('setModulMasterserver')" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="secondary" data-on="<?php echo $language['active']; ?>" data-off="<?php echo $language['deactive']; ?>" <?php if($mysql_modul['masterserver'] == 'true') { echo 'checked'; } ?>>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Spracheinstellungen -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-flag"></i> <?php echo $language['languagesettings']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row">
				<?php foreach ($installedLanguages AS $choosedLanguage => $languageLink) { ?>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
						<label class="c-input c-radio" data-tooltip="tooltip" data-placement="top" title="Translate by: <?php xssEcho($languageLink); ?>">
							<input name="langRadio" onClick="changeLanguage('<?php echo $choosedLanguage; ?>');" type="radio" <?php if($choosedLanguage == LANGUAGE) { echo 'checked'; } ?>>
							<span class="c-indicator"></span>
							<?php xssEcho($choosedLanguage); ?>
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
			<div id="styleSlider" class="carousel slide" data-ride="carousel">
				<ol class="carousel-indicators">
					<?php
					$firstLoad				=	true;
					foreach($themes AS $index=>$theme) {  ?>
						<li data-target="#styleSlider" data-slide-to="<?php echo $index; ?>" 
							<?php
								echo (str_replace(".css", "", $theme['file']) == STYLE || (STYLE == "" && $firstLoad)) ? 'class="active"' : '';
								$firstLoad	=	false;
							?>>
						</li>
					<?php }; ?>
				</ol>
				<div class="carousel-inner" role="listbox">
					<?php
						$firstLoad			=	true;
						foreach($themes AS $index=>$theme)
						{
							$src			=	"images/".$theme['img'];
							$active			=	(str_replace(".css", "", $theme['file']) == STYLE || (STYLE == "" && $firstLoad)) ? "active" : "";
							$txtcolor		=	(!empty($theme['txtcolor'])) ? $theme['txtcolor'] : '';
							
							if(!file_exists("../../".$src))
							{
								$src		=	"images/noImg.gif";
							};
							
							echo '<div class="carousel-item '.$active.'">
									<img class="d-block img-fluid" src="'.$src.'">
									<div class="carousel-caption d-none d-md-block">
										<h3 style="color: '.$txtcolor.'">'.$theme['name'].'</h3>
										<p class="hidden-md-down" style="color: '.$txtcolor.'">Created by '.$theme['autor'].'</p>
										<button onClick="changeTheme(\''.str_replace(".css", "", $theme['file']).'\');" class="btn btn-sm btn-success">'.$language['submit'].'</button>
									</div>
								</div>';
							
							$firstLoad		=	false;
						};
					?>
				</div>
				<a class="left carousel-control" href="#styleSlider" role="button" data-slide="prev">
					<span class="icon-prev" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="right carousel-control" href="#styleSlider" role="button" data-slide="next">
					<span class="icon-next" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
		</div>
	</div>
</div>

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