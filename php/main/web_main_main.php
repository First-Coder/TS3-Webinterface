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
	require_once("../../config/config.php");
	require_once("../../lang/lang.php");
	require_once("../functions/functions.php");
	require_once("../functions/functionsSql.php");
	
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Check Session
	*/
	$LoggedIn		=	(checkSession($mysql_keys['login_key'])) ? true : false;
	
	/*
		Get Client Permissions
	*/
	if($LoggedIn)
	{
		$user_right	=	getUserRights('pk', $_SESSION['user']['id'], true, "global");
	};
	
	/*
		Read News
	*/
	$alledateien 	= 	scandir('../../files/news');
?>

<!-- Neuigkeiten -->
<div id="otherContent" class="cke_contents_ltr">
	<?php
		if($mysql_modul['write_news'] == "true" && CUSTOM_NEWS_PAGE == "false")
		{
			foreach (array_reverse($alledateien) as $datei)
			{
				if($datei != "." && $datei != "..")
				{
					$time	=	str_replace(".json", "", $datei);
					$cont	=	array();
					$json 	= 	file_get_contents("../../files/news/".$datei);
					$cont	=	json_decode($json, true); ?>
					
					<div class="card" id="<?php xssEcho($time); ?>">
						<div class="card-block card-block-header">
							<div style="float:left;">
								<h4 class="card-title"><i class="fa fa-newspaper-o"></i> <?php xssEcho($cont['title']); ?></h4>
							</div>
							<div style="float:right;">
								<?php echo date("d.m.Y (G:i)", $time); ?>
							</div>
							<h6 style="clear:both;" class="card-subtitle text-muted"><?php xssEcho($cont['subtitle']); ?></h6>
						</div>
						<div class="card-block">
							<p style="font-style:normal;">
								<?php echo $cont['content']; ?>
							</p>
							<?php if($LoggedIn && $user_right['right_hp_main'] == $mysql_keys['right_hp_main']) { ?>
								<div style="width:20%;float:right;">
									<button onClick="AreYouSure('<?php echo $language['delete_news']; ?>', 'deleteNews(\'<?php echo $time; ?>\');');" style="width:100%;" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> <font class="hidden-xs-down"><?php echo $language['delete']; ?></font></button>
								</div>
								<div style="clear:both;"></div>
							<?php }; ?>
						</div>
					</div>
					
				<?php };
			};
		}
		else if(CUSTOM_NEWS_PAGE == "true")
		{
			include("../../config/custompages/custom_news.php");
		};
	?>
	
	<?php if($LoggedIn && $user_right['right_hp_main'] == $mysql_keys['right_hp_main'] && $mysql_modul['write_news'] == "true" && CUSTOM_NEWS_PAGE == "false") { ?>
		<div class="card">
			<div class="card-block card-block-header">
				<h4 class="card-title"><i class="fa fa-edit"></i> <?php echo $language['write_news']; ?></h4>
			</div>
			<div class="card-block">
				<input id="newsTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['title']; ?>"/>
				<input id="newsSubTitle" class="form-control small-top-bottom-margin" placeholder="<?php echo $language['subtitle']; ?>"/>
				<textarea id="editor" rows="10" cols="80">
					Text...
				</textarea>
				<button onClick="writeNews();" class="btn btn-success small-top-bottom-margin" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
			</div>
		</div>
	<?php }; ?>
</div>

<!-- AB HIER AN DARF NICHT GEAENDERT WERDEN!!!! -->
<!-- Mitwirkende -->
<?php include("./web_main_support.php"); ?>

<!-- Javascripte Laden -->
<script src="editor/ckeditor.js"></script>
<script>
	if(document.getElementById('editor'))
	{
		var editor = CKEDITOR.replace('editor');
	};
</script>
<script src="js/sonstige/preloader.js"></script>