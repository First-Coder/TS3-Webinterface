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

<!-- Rechte setzen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-unlock"></i> <?php echo $language['rechte_setzen']; ?></h4>
	</div>
	<div class="card-block">
		<table class="table" id="tablePermissions">
			<thead>
				<tr>
					<th><?php echo $language['ordnername']; ?></th>
					<th><?php echo $language['rechte']; ?></th>
				</tr>
			</thead>
			<tbody>
				<!-- Install -->
				<tr id="tbl_install" style="<?php if(substr(sprintf('%o', fileperms('../install')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
					<td>/install</td>
					<td id="tbl_install_txt"><?php echo substr(sprintf('%o', fileperms('../install')), -4); ?></td>
				</tr>
				<!-- Install .js -->
				<tr id="tbl_install_js" style="<?php if(substr(sprintf('%o', fileperms('../install/js')), -4) == '0777') { echo "background-color:rgba(0,199,0,0.2);"; } else { echo "background-color:rgba(199,0,0,0.2);"; } ?>">
					<td>/install/js</td>
					<td id="tbl_install_js_txt"><?php echo substr(sprintf('%o', fileperms('../install/js')), -4); ?></td>
				</tr>
			</tbody>
		</table>
		<p style="text-align: center;"><?php echo $language['rechte_1']; ?><br /><?php echo $language['rechte_2']; ?></p>
	</div>
</div>

<!-- Lanugage -->
<script>
	var fertigstellen		=	'<?php echo $language['fertigstellen']; ?>';
</script>
