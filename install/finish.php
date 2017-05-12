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
	
	/*
		Try to delete all files
	*/
	$files 		= 	scandir('./');
	unset($files[0]);
	unset($files[1]);
	
	foreach($files AS $file)
	{ 
		unlink($file);
	};
	
	unlink("../install");
?>

<!-- Fertigstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-smile-o"></i> <?php echo $language['done_1']; ?></h4>
	</div>
	<div class="card-block">
		<p><?php echo $language['done_2']; ?></p>
		<p><?php echo $language['install_ordner_loeschen']; ?></p>
		<button onClick="pageReload();" class="btn btn-custom" style="width: 100%;"><i class="fa fa fa-smile-o" aria-hidden="true"></i> <?php echo $language['fertigstellen']; ?></button>
	</div>
</div>