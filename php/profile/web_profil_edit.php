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
		Get private information above the Client
	*/
	$userInformations		=	getUserInformations($_SESSION['user']['id']);
?>

<!-- Login Informationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-sign-in"></i> <?php echo $language['login_informations']; ?></h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label for="profileUser"><?php echo $language['mail']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					@
				</span>
				<input type="email" class="form-control" id="profileUser" aria-describedby="profileUserHelp" placeholder="<?php xssEcho($_SESSION['user']['benutzer']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileUser')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileUserHelp" class="form-text text-muted"><?php echo $language['mail_help']; ?></small>
		</div>
		<div class="form-group">
			<label for="profilePassword"><?php echo $language['password']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-key"></i>
				</span>
				<input type="password" class="form-control" id="profilePassword" placeholder="******">
			</div>
		</div>
		<div class="form-group">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-refresh"></i>
				</span>
				<input type="password" class="form-control" id="profilePassword" aria-describedby="profilePasswordHelp" placeholder="******">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profilePassword')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profilePasswordHelp" class="form-text text-muted"><?php echo $language['password_help']; ?></small>
		</div>
	</div>
</div>

<!-- Perso Informationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-address-card-o"></i> <?php echo $language['perso_infos']; ?></h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label for="profileVorname"><?php echo $language['firstname']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user-o"></i>
				</span>
				<input type="text" class="form-control" id="profileVorname" aria-describedby="firstnameInfo" placeholder="<?php xssEcho($userInformations['vorname']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileVorname')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="firstnameInfo" class="form-text text-muted"><?php echo $language['firstname_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileNachname"><?php echo $language['lastname']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user-o"></i>
				</span>
				<input type="text" class="form-control" id="profileNachname" aria-describedby="lastnameInfo" placeholder="<?php xssEcho($userInformations['nachname']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileNachname')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="lastnameInfo" class="form-text text-muted"><?php echo $language['lastname_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileTelefon"><?php echo $language['telefon']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user-o"></i>
				</span>
				<input type="tel" class="form-control" id="profileTelefon" aria-describedby="telefonInfo" placeholder="<?php xssEcho($userInformations['telefon']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileTelefon')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="telefonInfo" class="form-text text-muted"><?php echo $language['telefon_info']; ?></small>
		</div>
	</div>
</div>

<!-- Kontaktinformationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-address-book-o"></i> <?php echo $language['kontakt_infos']; ?></h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label for="profileHomepage"><?php echo $language['homepage']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					http://
				</span>
				<input type="url" class="form-control" id="profileHomepage" aria-describedby="profileHomepageInfo" placeholder="<?php xssEcho(str_replace("%2F", "/", $userInformations['homepage'])); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileHomepage')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileHomepageInfo" class="form-text text-muted"><?php echo $language['homepage_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileSkype"><?php echo $language['skype']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-skype"></i>
				</span>
				<input type="text" class="form-control" id="profileSkype" aria-describedby="profileSkypeInfo" placeholder="<?php xssEcho($userInformations['skype']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileSkype')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileSkypeInfo" class="form-text text-muted"><?php echo $language['skype_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileSteam"><?php echo $language['steam']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-steam"></i>
				</span>
				<input type="text" class="form-control" id="profileSteam" aria-describedby="profileSteamInfo" placeholder="<?php xssEcho($userInformations['steam']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileSteam')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileSteamInfo" class="form-text text-muted"><?php echo $language['steam_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileTwitter"><?php echo $language['twitter']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-twitter"></i>
				</span>
				<input type="text" class="form-control" id="profileTwitter" aria-describedby="profileTwitterInfo" placeholder="<?php xssEcho($userInformations['twitter']); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileTwitter')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileTwitterInfo" class="form-text text-muted"><?php echo $language['twitter_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileFacebook"><?php echo $language['facebook']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-facebook"></i>
				</span>
				<input type="url" class="form-control" id="profileFacebook" aria-describedby="profileFacebookInfo" placeholder="<?php xssEcho(str_replace("%2F", "/", $userInformations['facebook'])); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileFacebook')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileFacebookInfo" class="form-text text-muted"><?php echo $language['facebook_info']; ?></small>
		</div>
		<div class="form-group">
			<label for="profileGoogle"><?php echo $language['google_plus']; ?></label>
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-google"></i>
				</span>
				<input type="url" class="form-control" id="profileGoogle" aria-describedby="profileGoogleInfo" placeholder="<?php xssEcho(str_replace("%2F", "/", $userInformations['google'])); ?>">
				<span class="input-group-btn">
					<button onClick="profilUpdate('profileGoogle')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
				</span>
			</div>
			<small id="profileGoogleInfo" class="form-text text-muted"><?php echo $language['google_plus_info']; ?></small>
		</div>
	</div>
</div>

<!-- Javascripte Laden -->
<script src="js/webinterface/profile.js"></script>
<script src="js/sonstige/preloader.js"></script>
