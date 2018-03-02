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
	require_once("./lang.php");
	require_once("../config/config.php");
?>

<!-- Benutzer erstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-cogs"></i> <?php echo $language['einstellungen']; ?></h4>
	</div>
	<div class="card-block">
		<h5 class="text-muted" style="margin-bottom: 1.5rem;"><?php echo $language['benutzerdaten_angeben']; ?></h5>
		<div class="form-group">
			<label><?php echo $language['benutzername']; ?></label>
			<input type="text" class="form-control" id="username">
			<small class="form-text text-muted"><?php echo $language['benutzername_info']; ?></small>
		</div>
		<div class="form-group">
			<label><?php echo $language['passwort']; ?></label>
			<input type="password" class="form-control" id="password">
			<small class="form-text text-muted"><?php echo $language['passwort_info']; ?></small>
		</div>
		<div class="alert-info">
			<p style="text-align:center;">
				<?php echo $language['benutzerdaten_angeben_1']; ?>
			</p>
		</div>
		<hr/>
		<h5 class="text-muted" style="margin-bottom: 1.5rem;">Interface <?php echo $language['einstellungen']; ?></h5>
		<div class="form-group">
			<label><?php echo $language['homepagetitel']; ?></label>
			<input type="text" class="form-control" id="heading" value="<?php echo HEADING; ?>">
		</div>
		<div class="form-group">
			<label><?php echo $language['teamspeakname']; ?></label>
			<input type="text" class="form-control" id="ts3Chatname" value="<?php echo TS3_CHATNAME; ?>">
		</div>
		<button id="submitBttn" onClick="submitSettings();" class="btn btn-custom btn-sm" style="width: 100%;"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $language['uebernehmen']; ?></button>
	</div>
</div>

<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-terminal"></i> <?php echo $language['konsole']; ?></h4>
	</div>
	<div class="card-block" id="console_block">
		<div class="row">
			<div class="col-md-12">
				<div id="database_errorbox" class="alert alert-info">
					Waiting...
				</div>
			</div>
		</div>
	</div>
</div>