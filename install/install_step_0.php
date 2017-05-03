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

<!-- Sprache wählen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-flag"></i> <?php echo $language['choose_language']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input id="checkGerman" name="langRadio" type="radio">
					<span class="c-indicator"></span>
					<img height="20" width="20" class="img-rounded hover_red" src="images/german.gif"> German
				</label>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-row">
				<label class="c-input c-radio">
					<input name="langRadio" type="radio" checked>
					<span class="c-indicator"></span>
					<img height="20" width="20" class="img-rounded hover_red" src="images/english.gif"> English
				</label>
			</div>
		</div>
	</div>
</div>