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
	require_once("../config.php");
?>

<!-- Benutzer erstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-user"></i> <?php echo $language['benutzerdaten_angeben']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row step-margin">
			<div class="col-md-1"></div>
			<div class="col-md-4 install-padding-input">
				<?php echo $language['benutzername']; ?>:
			</div>
			<div class="col-md-6">
				<input id="username" style="width:100%;" class="form-control" type="text">
			</div>
			<div class="col-md-1"></div>
		</div>
		<div class="row step-margin">
			<div class="col-md-1"></div>
			<div class="col-md-4 install-padding-input">
				<?php echo $language['passwort']; ?>:
			</div>
			<div class="col-md-6">
				<input id="password" style="width:100%;" class="form-control" type="password">
			</div>
			<div class="col-md-1"></div>
		</div>
		<div class="row step-margin">
			<div class="col-md-12 col-xs-12">
				<p style="text-align:center;">
					<?php echo $language['benutzerdaten_angeben_1']; ?>
				</p>
				<p style="text-align:center;">
					<b><?php echo $language['benutzerdaten_angeben_2']; ?></b>
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Module und sonstige Einstellungen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-cogs"></i> <?php echo $language['einstellungen']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row step-margin">
			<div class="col-md-1"></div>
			<div class="col-md-4 install-padding-input">
				<?php echo $language['homepagetitel']; ?>:
			</div>
			<div class="col-md-6">
				<input id="heading" style="width:100%;" class="form-control" type="text" value="<?php echo HEADING; ?>">
			</div>
			<div class="col-md-1"></div>
		</div>
		<div class="row step-margin">
			<div class="col-md-1"></div>
			<div class="col-md-4 install-padding-input">
				<?php echo $language['teamspeakname']; ?>:
			</div>
			<div class="col-md-6">
				<input id="ts3Chatname" style="width:100%;" class="form-control" type="text" value="<?php echo TS3_CHATNAME; ?>">
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
</div>

<!-- Console -->
<button id="submitBttn" onClick="submitSettings();" class="btn btn-success" style="width: 100%;"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $language['uebernehmen']; ?></button>
<div class="card" id="submitConsole" style="display: none;">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-terminal"></i> <?php echo $language['konsole']; ?></h4>
	</div>
	<div class="card-block" id="console_block">
		<div class="row">
			<div class="col-md-12">
				<div id="database_errorbox" class="alert alert-warning">
					Waiting...
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Lanugage -->
<script>
	var falsches_passwort		=	'<?php echo $language['falsches_passwort']; ?>';
	var falscher_benutzer		=	'<?php echo $language['falscher_benutzer']; ?>';
	var uebernehmen				=	'<?php echo $language['uebernehmen']; ?>';
	var erneut_versuchen		=	'<?php echo $language['erneut_versuchen']; ?>';
	var weiter_info				=	'<?php echo $language['weiter_info']; ?>';
</script>