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
?>

<!-- AB HIER AN DARF NICHT GEAENDERT WERDEN!!!! -->
<!-- Mitwirkende -->
<div id="mitwirkende" class="card" style="display: none;">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-thumbs-up"></i> <?php echo $language['mitwirkende']; ?></h4>
	</div>
	<div class="card-block">
		<div class="row">
			<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
				<p><i class="fa fa-cogs"></i> <?php echo $language['mitwirkende_head_sponsor']; ?>:</p>
				<ul>
					<li>
						<b>Wallpaper Cave:</b><br /><?php echo $language['mitwirkende_wallpapercave_info']; ?><br />
							<?php echo $language['homepage']; ?>: <a href="http://wallpapercave.com/">http://wallpapercave.com/</a>
					</li>
					<li>
						<b>Eazy-Sponsoring:</b><br /><?php echo $language['mitwirkende_easzy_info']; ?><br />
							<?php echo $language['mitwirkende_easzy_info2']; ?>
					</li>
				</ul>
			</div>
			<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
				<p><i class="fa fa-code"></i> <?php echo $language['mitwirkende_head_devellop']; ?>:</p>
				<ul>
					<li>
						<b>Bootstrap</b><br /><?php echo $language['mitwirkende_bootstrap_info']; ?><br />
							<?php echo $language['homepage']; ?>: <a href="http://getbootstrap.com/">http://getbootstrap.com/</a>
					</li>
					<li>
						<b>ts3admin.class</b><br /><?php echo $language['mitwirkende_stefan_info']; ?><br />
							<?php echo $language['homepage']; ?>: <a href="http://ts3admin.info/">http://ts3admin.info/</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>