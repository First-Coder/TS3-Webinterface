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
		<h4 class="card-title"><i class="fa fa-paragraph"></i> <?php echo $language['datenrichtlinien']; ?></h4>
	</div>
	<div class="card-block">
		<h5 class="text-muted" style="margin-bottom: 1.5rem;"><?php echo $language['allgemeiner_datenschutzhinweis']; ?></h5>
		<ol class="custom-list">
			<li><a onClick="return false;"><?php echo $language['allgemeiner_datenschutzhinweis_1']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['allgemeiner_datenschutzhinweis_2']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['allgemeiner_datenschutzhinweis_3']; ?></a></li> 
			<li><a onClick="return false;"><?php echo $language['allgemeiner_datenschutzhinweis_4']; ?></a></li>                 
		</ol>
		<hr/>
		<h5 class="text-muted" style="margin-bottom: 1.5rem;"><?php echo $language['haftungsausschluss']; ?></h5>
		<ol class="custom-list">
			<li><a onClick="return false;"><?php echo $language['haftungsausschluss_1_1']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['haftungsausschluss_2_1']; ?></a></li>
			<li><a onClick="return false;"><?php echo $language['haftungsausschluss_3_1']; ?></a></li> 
			<li><a onClick="return false;"><?php echo $language['haftungsausschluss_4_1']; ?></a></li>    
			<li><a onClick="return false;"><?php echo $language['haftungsausschluss_5_1']; ?></a></li>				
		</ol>
		<hr/>
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
		<button onClick="goToDatabaseConnection();" class="btn btn-custom btn-sm" style="width: 100%;"><?php echo $language['weiter']; ?> <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
	</div>
</div>