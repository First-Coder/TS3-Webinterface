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
?>

<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-database"></i> <?php echo $language['datenbankverbindung']; ?></h4>
	</div>
	<div class="card-block">
		<div class="form-group">
			<label>SQL Mode</label>
			<select id="check_sqlmode" class="form-control c-select">
				<option value="mysql" selected>MySQL / Maria DB</option>
				<option value="pgsql">PostgreSQL</option>
			</select>
		</div>
		<div class="form-group">
			<label>SQL <?php echo $language['hostname']; ?></label>
			<input type="text" class="form-control" id="check_hostname">
		</div>
		<div class="form-group">
			<label>SQL Port</label>
			<input type="number" class="form-control" id="check_port" value="3306">
		</div>
		<div class="form-group">
			<label>SQL <?php echo $language['datenbank']; ?></label>
			<input type="text" class="form-control" id="check_database">
		</div>
		<div class="form-group">
			<label>SQL <?php echo $language['benutzername']; ?></label>
			<input type="text" class="form-control" id="check_username">
		</div>
		<div class="form-group">
			<label>SQL <?php echo $language['passwort']; ?></label>
			<input type="password" class="form-control" id="check_password">
		</div>
		<div class="form-group">
			<div>SSL Require</div>
			<div style="width: 100%;text-align: center;">
				<label class="c-input c-checkbox">
					<input id="checkSslRequire" type="checkbox">
					<span class="c-indicator"></span>
					<?php echo $language['ja']; ?>
				</label>
			</div>
		</div>
		<button id="databaseConnectionBttn" onClick="checkDatabaseConnection();" class="btn btn-custom btn-sm" style="width: 100%;"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo $language['datenbank_pruefen']; ?></button>
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