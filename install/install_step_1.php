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
?>

<!-- Info
	complete 	=> 	Fertig
	active		=>	Hier bin ich
	disabled	=>	War ich noch nie
-->

<!-- Installationsfortschritt -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-info"></i> <?php echo $language['webinterfaceinstallation']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row bs-wizard" style="border-bottom:0;">
			<div id="step_1" class="col-xs-3 bs-wizard-step active">
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="text-center"><p><i class="fa fa-paragraph"></i><font class="hidden-md-down"> <?php echo $language['datenrichtlinien']; ?></font></p></div>
			</div>
			
			<div id="step_2" class="col-xs-3 bs-wizard-step disabled">
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="text-center"><p><i class="fa fa-database"></i><font class="hidden-md-down"> <?php echo $language['datenbankverbindung']; ?></font></p></div>
			</div>
			
			<div id="step_3" class="col-xs-3 bs-wizard-step disabled">
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="text-center"><p><i class="fa fa-cog"></i><font class="hidden-md-down"> <?php echo $language['einstellungen']; ?></font></p></div>
			</div>
			
			<div id="step_4" class="col-xs-3 bs-wizard-step disabled">
			  <div class="progress"><div class="progress-bar"></div></div>
			  <a href="#" class="bs-wizard-dot"></a>
			  <div class="text-center"><p><i class="fa fa-unlock"></i><font class="hidden-md-down"> <?php echo $language['rechte_setzen']; ?></font></p></div>
			</div>
		</div>
	</div>
</div>

<!-- Datenrichtlinien -->
<div id="steps">
	<!-- Step 1 -->
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-paragraph"></i> <?php echo $language['datenrichtlinien']; ?></h4>
		</div>
		<div class="card-block">
			<div class="row step-margin">
				<div class="col-md-12 col-xs-12">
					<strong><?php echo $language['allgemeiner_datenschutzhinweis']; ?>:</strong><br />
					<p>
						(1) <?php echo $language['allgemeiner_datenschutzhinweis_1']; ?>
					</p>
					<p>
						(2) <?php echo $language['allgemeiner_datenschutzhinweis_2']; ?>
					</p>
					<p>
						(3) <?php echo $language['allgemeiner_datenschutzhinweis_3']; ?>
					</p>
					<p>
						(4) <?php echo $language['allgemeiner_datenschutzhinweis_4']; ?>
					</p>
				</div>
				<div class="col-md-12 col-xs-12">
					<strong><?php echo $language['haftungsausschluss']	; ?>:</strong><br />
					<p>
						<?php echo $language['haftungsausschluss_1']; ?><br /><?php echo $language['haftungsausschluss_1_1']; ?>
					</p>
					<p>
						<?php echo $language['haftungsausschluss_2']; ?><br /><?php echo $language['haftungsausschluss_2_1']; ?>
					</p>
					<p>
						<?php echo $language['haftungsausschluss_3']; ?><br /><?php echo $language['haftungsausschluss_3_1']; ?>
					</p>
					<p>
						 <?php echo $language['haftungsausschluss_4']; ?><br /><?php echo $language['haftungsausschluss_4_1']; ?>
					</p>
					<p>
						<?php echo $language['haftungsausschluss_5']; ?><br /><?php echo $language['haftungsausschluss_5_1']; ?>
					</p>
				</div>
			</div>
			<div class="row step-margin">
				<div class="col-md-12 col-xs-12 checkBoxes1">
					<label class="c-input c-checkbox">
						<input id="check_datenschutz" type="checkbox">
						<span class="c-indicator"></span>
						<?php echo $language['accept_1']; ?>
					</label>
				</div>
			</div>
			<div class="row step-margin">
				<div class="col-md-12 col-xs-12 checkBoxes2">
					<label class="c-input c-checkbox">
						<input id="check_haftungsausschluss" type="checkbox">
						<span class="c-indicator"></span>
						<?php echo $language['accept_2']; ?>
					</label>
				</div>
			</div>
			<div class="row step-margin">
				<div class="col-md-12 col-xs-12 checkBoxes3">
					<label class="c-input c-checkbox">
						<input id="check_ts_damage" type="checkbox">
						<span class="c-indicator"></span>
						<?php echo $language['accept_3']; ?>
					</label>
				</div>
			</div>
		</div>
	</div>
</div>