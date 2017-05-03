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
	require_once("functions.php");
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys		=	getKeys();
	$mysql_modul	=	getModuls();
	
	/*
		Is Client logged in?
	*/
	$isLogged		=	false;
	if($_SESSION['login'] == $mysql_keys['login_key'])
	{
		$isLogged	=	true;
	};
	
	/*
		Get Client Permissions
	*/
	if($isLogged)
	{
		$user_right	=	getUserRightsWithTime(getUserRights('pk', $_SESSION['user']['id']));
	};
	
	/*
		Read News
	*/
	$alledateien 	= 	scandir('news');
?>

<!-- Neuigkeiten -->
<?php
	if($mysql_modul['write_news'] == "true")
	{
		foreach (array_reverse($alledateien) as $datei)
		{
			if($datei != "." && $datei != "..")
			{
				$time	=	str_replace(".json", "", $datei);
				$cont	=	array();
				$json 	= 	file_get_contents("news/".$datei);
				$cont	=	json_decode($json, true); ?>
				
				<div class="card" id="<?php echo $time; ?>">
					<div class="card-block card-block-header">
						<div style="float:left;">
							<h4 class="card-title"><i class="fa fa-newspaper-o"></i> <?php echo htmlspecialchars($cont['title']); ?></h4>
						</div>
						<div style="float:right;">
							<?php echo date("d.m.Y (G:i)", $time); ?>
						</div>
						<h6 style="clear:both;" class="card-subtitle text-muted"><?php echo htmlspecialchars($cont['subtitle']); ?></h6>
					</div>
					<div class="card-block">
						<p style="font-style:normal;">
							<?php echo strip_tags($cont['content'],
								"<a><font></font><div></div><br><b></b><i></i><strike></strike><u></u><ul></ul><li></li><ol></ol><img>"); ?>
						</p>
						<?php if($isLogged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main']) { ?>
							<div style="width:20%;float:right;">
								<button onClick="deleteNews('<?php echo $time; ?>');" style="width:100%;" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> <font class="hidden-xs-down"><?php echo $language['delete']; ?></font></button>
							</div>
							<div style="clear:both;"></div>
						<?php }; ?>
					</div>
				</div>
				
			<?php };
		};
	};
?>

<?php if(($isLogged && $user_right['right_hp_main'] == $mysql_keys['right_hp_main']) && $mysql_modul['write_news'] == "true") { ?>
	<div class="card">
		<div class="card-block card-block-header">
			<h4 class="card-title"><i class="fa fa-edit"></i> <?php echo $language['write_new_news']; ?></h4>
		</div>
		<div class="card-block">
			<input id="newsTitle" class="form-control" placeholder="<?php echo $language['title']; ?>"/>
			<input id="newsSubTitle" class="form-control" placeholder="<?php echo $language['subtitle']; ?>"/>
			
			<div id="alerts" class="alert-danger"></div>
			<div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
				<div class="btn-group">
					<a class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="Font"><i class="fa fa-font" aria-hidden="true"></i><b class="caret"></b></a>
					<ul class="dropdown-menu"></ul>
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="Font Size"><i class="fa fa-text-height" aria-hidden="true"></i></i><b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-edit="fontSize 5"><font size="5">Huge</font></a></li>
						<li><a data-edit="fontSize 3"><font size="3">Normal</font></a></li>
						<li><a data-edit="fontSize 1"><font size="1">Small</font></a></li>
					</ul>
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary" data-edit="bold" title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="italic" title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="strikethrough" title="Strikethrough"><i class="fa fa-strikethrough" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="underline" title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline" aria-hidden="true"></i></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary" data-edit="insertunorderedlist" title="Bullet list"><i class="fa fa-list-ul" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="insertorderedlist" title="Number list"><i class="fa fa-list-ol" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="outdent" title="Reduce indent (Shift+Tab)"><i class="fa fa-outdent" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="indent" title="Indent (Tab)"><i class="fa fa-indent" aria-hidden="true"></i></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary" data-edit="justifyleft" title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="justifycenter" title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="justifyright" title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="justifyfull" title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify" aria-hidden="true"></i></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="Hyperlink"><i class="fa fa-link" aria-hidden="true"></i></a>
					<div class="dropdown-menu input-append">
					<input class="span2" placeholder="URL" type="text" data-edit="createLink"/>
					<button class="btn btn-secondary" type="button">Add</button>
					</div>
					<a class="btn btn-secondary" data-edit="unlink" title="Remove Hyperlink"><i class="fa fa-chain-broken" aria-hidden="true"></i></a>
				</div>

				<div class="btn-group">
					<a class="btn btn-secondary" title="Insert picture (or just drag & drop)" id="pictureBtn"><i class="fa fa-file-image-o" aria-hidden="true"></i></a>
					<input type="file" data-role="magic-overlay" data-target="#pictureBtn" data-edit="insertImage" />
				</div>
				<div class="btn-group">
					<a class="btn btn-secondary" data-edit="undo" title="Undo (Ctrl/Cmd+Z)"><i class="fa fa-undo" aria-hidden="true"></i></a>
					<a class="btn btn-secondary" data-edit="redo" title="Redo (Ctrl/Cmd+Y)"><i class="fa fa-repeat" aria-hidden="true"></i></a>
				</div>
				<input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="">
			</div>

			<div id="editor"></div>
			<button onClick="writeNews();" class="btn btn-success" style="width:100%;"><i class="fa fa-paper-plane"></i> <?php echo $language['senden']; ?></button>
		</div>
	</div>
<?php }; ?>

<!-- AB HIER AN DARF NICHT GEAENDERT WERDEN!!!! -->
<!-- Mitwirkende -->
<div id="mitwirkende" class="card">
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

<!-- Sprachdatein laden -->
<script>
	var news_created		=	"<?php echo $language['newpost']; ?>";
	var news_deleted		=	"<?php echo $language['delpost']; ?>";
</script>

<!-- Javascripte Laden -->
<script src="js/editor/prettify/prettify.js"></script>
<script src="js/editor/jquery.hotkeys.js"></script>
<script src="js/editor/bootstrap-wysiwyg.js"></script>
<script>
	$(function(){
		function initToolbarBootstrapBindings()
		{
			var fonts 	= 	['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 
							'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
							'Times New Roman', 'Verdana'],
			fontTarget 	= 	$('[title=Font]').siblings('.dropdown-menu');
			
			$.each(fonts, function (idx, fontName)
			{
				fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
			});
			
			$('a[title]').tooltip({container:'body'});
			
			$('.dropdown-menu input')
				.click(function() {return false;})
				.change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
				.keydown('esc', function () {this.value='';$(this).change();});

			$('[data-role=magic-overlay]').each(function ()
			{ 
				var overlay = $(this), target = $(overlay.data('target')); 
				overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
			});
			
			if ("onwebkitspeechchange"  in document.createElement("input"))
			{
				var editorOffset = $('#editor').offset();
				$('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+$('#editor').innerWidth()-35});
			}
			else
			{
				$('#voiceBtn').hide();
			};
		};
		
		function showErrorAlert (reason, detail)
		{
			var msg='';
			if (reason==='unsupported-file-type')
			{
				msg = "Unsupported format " +detail;
			}
			else
			{
				console.log("error uploading file", reason, detail);
			}
			$('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+ 
			'<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
		};
		initToolbarBootstrapBindings();  
		$('#editor').wysiwyg({ fileUploadError: showErrorAlert} );
		window.prettyPrint && prettyPrint();
	});
</script>
<script src="js/sonstige/preloader.js"></script>