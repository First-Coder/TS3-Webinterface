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
	
	/*
		Is Client logged in?
	*/
	$urlData				=	explode("\?", $_SERVER['HTTP_REFERER'], -1);
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		echo '<script type="text/javascript">';
		echo 	'window.location.href="'.$urlData[0].'";';
		echo '</script>';
	};
	
	/*
		Get private information above the Client
	*/
	$userInformations		=	getUserInformations($_SESSION['user']['id']);
?>

<!-- Login Informationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-pencil"></i> <?php echo $language['profile_login_headline']; ?></h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tbody>
				<tr>
					<td class="input-padding">
						<?php echo $language['mail']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileUser" type="text" class="form-control" placeholder="<?php echo $_SESSION['user']['benutzer']; ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileUser')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['password']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profilePassword" type="password" class="form-control">
							<span class="input-group-btn">
								<div class="btn btn-default" style="cursor:default;margin-top:3px;"><i class="fa fa-key"></i></div>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div class="input-group">
							<input id="profilePassword2" type="password" class="form-control">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profilePassword')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- Login Informationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-pencil"></i> <?php echo $language['profile_perso_infos']; ?></h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tbody>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_perso_vorname']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileVorname" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['vorname']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileVorname')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_perso_nachname']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileNachname" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['nachname']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileNachname')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_perso_telefon']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileTelefon" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['telefon']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileTelefon')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- Kontaktinformationen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-pencil"></i> <?php echo $language['profile_kontakt_infos']; ?></h4>
	</div>
	<div class="card-block">
		<table class="table table-condensed">
			<tbody>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_homepage']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileHomepage" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['homepage']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileHomepage')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_skype']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileSkype" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['skype']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileSkype')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_steam']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileSteam" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['steam']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileSteam')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_twitter']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileTwitter" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['twitter']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileTwitter')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_facebook']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileFacebook" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['facebook']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileFacebook')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="input-padding">
						<?php echo $language['profile_kontakt_google']; ?>:
					</td>
					<td>
						<div class="input-group">
							<input id="profileGoogle" type="text" class="form-control" placeholder="<?php echo htmlspecialchars($userInformations['google']); ?>">
							<span class="input-group-btn">
								<button onClick="profilUpdate('profileGoogle')" class="btn btn-success" type="button"><i class="fa fa-check"></i></button>
							</span>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!-- Sprachdatein laden -->
<script>
	var settigns_saved				=	'<?php echo $language['settigns_saved']; ?>';
	var settings_not_saved			=	'<?php echo $language['settings_not_saved']; ?>';
	
	var hp_user_change_name_failed	=	'<?php echo $language['hp_user_change_name_failed']; ?>';
	var hp_user_change_pw1_failed	=	'<?php echo $language['hp_user_change_pw1_failed']; ?>';
	var hp_user_change_pw2_failed	=	'<?php echo $language['hp_user_change_pw2_failed']; ?>';
	var hp_user_change_user_failed	=	'<?php echo $language['hp_user_change_user_failed']; ?>';
	
	var success						=	'<?php echo $language['success']; ?>';
	var failed						=	'<?php echo $language['failed']; ?>';
</script>

<!-- Javascripte Laden -->
<script src="js/webinterface/profile.js"></script>
<script src="js/sonstige/preloader.js"></script>
