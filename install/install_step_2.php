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

<!-- Step 2 -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-database"></i> <?php echo $language['datenbankverbindung']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row step-margin">
			<div class="col-md-5 install-padding-select">
				SQL Mode:
			</div>
			<div class="col-md-7">
				<select id="check_sqlmode" class="form-control">
					<option value="mysql" selected>MySQL / Maria DB</option>
					<option value="pgsql">PostgreSQL</option>
				</select>
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5 install-padding-input">
				SQL <?php echo $language['hostname']; ?>:
			</div>
			<div class="col-md-7">
				<input id="check_hostname" style="width:100%;" type="text"  class="form-control" value="<?php echo SQL_Hostname; ?>">
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5 install-padding-input">
				SQL Port:
			</div>
			<div class="col-md-7">
				<input id="check_port" style="width:100%;" type="number"  class="form-control" value="<?php echo SQL_Port; ?>">
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5 install-padding-input">
				SQL <?php echo $language['datenbank']; ?>:
			</div>
			<div class="col-md-7">
				<input id="check_database" style="width:100%;" type="text"  class="form-control" value="<?php echo SQL_Datenbank; ?>">
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5 install-padding-input">
				SQL <?php echo $language['benutzername']; ?>:
			</div>
			<div class="col-md-7">
				<input id="check_username" style="width:100%;" type="text"  class="form-control" value="<?php echo SQL_Username; ?>">
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5 install-padding-input">
				SQL <?php echo $language['passwort']; ?>:
			</div>
			<div class="col-md-7">
				<input id="check_password" style="width:100%;" type="password"  class="form-control">
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-5">
				SSL Require:
			</div>
			<div class="col-md-7">
				<label class="c-input c-checkbox">
					<input id="checkSslRequire" type="checkbox">
					<span class="c-indicator"></span>
					<?php echo $language['ja']; ?>
				</label>
			</div>
		</div>
		<div class="row step-margin">
			<div class="col-md-12">
				<button id="check_database_connection" onClick="checkDatabaseConnection();" class="btn btn-success" style="width: 100%;"><i class="fa fa-edit" aria-hidden="true"></i> <?php echo $language['datenbank_pruefen']; ?></button>
			</div>
		</div>
	</div>
</div>

<!-- Console -->
<div class="card">
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
	var datenbank_erstellen		=	'<?php echo $language['datenbank_erstellen']; ?>';
	var erneut_versuchen		=	'<?php echo $language['erneut_versuchen']; ?>';
</script>

