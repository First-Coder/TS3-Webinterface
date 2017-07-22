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
	require_once(__DIR__."/../../config/config.php");
	require_once(__DIR__."/../../config/instance.php");
	require_once(__DIR__."/../../lang/lang.php");
	require_once(__DIR__."/../../php/functions/functions.php");
	require_once(__DIR__."/../../php/functions/functionsSql.php");
	require_once(__DIR__."/../../php/functions/functionsTeamspeak.php");
	
	/*
		Variables
	*/
	$LoggedIn			=	(checkSession()) ? true : false;
	
	/*
		Get the Modul Keys / Permissionkeys
	*/
	$mysql_keys				=	getKeys();
	$mysql_modul			=	getModuls();
	
	/*
		Is Client logged in?
	*/
	if($_SESSION['login'] != $mysql_keys['login_key'])
	{
		reloadSite();
	};
	
	/*
		Get Client Permissions
	*/
	$user_right			=	getUserRights('pk', $_SESSION['user']['id']);
	
	/*
		Get Link information
	*/
	$LinkInformations	=	getLinkInformations();
	
	if(empty($LinkInformations) || $mysql_modul['webinterface'] != 'true')
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Teamspeak Functions
	*/
	$tsAdmin = new ts3admin($ts3_server[$LinkInformations['instanz']]['ip'], $ts3_server[$LinkInformations['instanz']]['queryport']);
	
	if($tsAdmin->getElement('success', $tsAdmin->connect()))
	{
		$tsAdmin->login($ts3_server[$LinkInformations['instanz']]['user'], $ts3_server[$LinkInformations['instanz']]['pw']);
		$tsAdmin->selectServer($LinkInformations['sid'], 'serverId', true);
		
		$server		= 	$tsAdmin->serverInfo();
		
		if(((!isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_view') || !isPortPermission($user_right, $LinkInformations['instanz'], $server['data']['virtualserver_port'], 'right_web_server_banner'))
				&& $user_right['right_web_global_server']['key'] != $mysql_keys['right_web_global_server']) || $user_right['right_web']['key'] != $mysql_keys['right_web'])
		{
			reloadSite(RELOAD_TO_SERVERVIEW);
		};
	}
	else
	{
		reloadSite(RELOAD_TO_MAIN);
	};
	
	/*
		Main Image
	*/
	$mainImage					=	"./images/ts_banner/default_banner.png";
	
	/*
		Get Fonts
	*/
	$fonts						=	"";
	foreach (scandir(__dir__."/../../fonts/") as $datei)
	{
		if(strpos($datei, ".ttf") !== false && strpos($datei, "fontawesome") === false)
		{
			$fonts				.=	'<option value="'.$datei.'" selected>'.str_replace(".ttf", "", $datei).'</option>';
		};
	};
	
	/*
		Get Default Bannerimages
	*/
	$defaultImages				=	"";
	foreach (scandir(__dir__."/../../images/ts_banner/") as $datei)
	{
		if(strpos($datei, ".png") !== false && strpos($datei, "default_banner") !== false)
		{
			$defaultImages		.=	'<option value="'.$datei.'">'.str_replace(".png", "", $datei).'</option>';
		};
	};
	
	/*
		Useable elements
	*/
	$subDraggableElements = [
		'x' 					=>	0,
		'y' 					=> 	0,
		'fontfile' 				=> 	'',
		'fontsize'				=>	16,
		'color'					=>	'',
		'text'					=>	''
	];
	$draggableCustomElements = [
		'custom-text-1'			=>	$subDraggableElements,
		'custom-text-2'			=>	$subDraggableElements
	];
	$draggableElements = [
        '%status%' 				=> 	$subDraggableElements,
		'%sid%' 				=> 	$subDraggableElements,
		'%sport%' 				=> 	$subDraggableElements,
		'%platform%' 			=> 	$subDraggableElements,
		'%servername%' 			=> 	$subDraggableElements,
		'%serverversion%' 		=> 	$subDraggableElements,
		'%packetloss_floored%' 	=> 	$subDraggableElements,
		'%ping_floored%' 		=> 	$subDraggableElements,
		'%packetloss_00%' 		=> 	$subDraggableElements,
		'%maxclients%' 			=> 	$subDraggableElements,
		'%clientsonline%' 		=> 	$subDraggableElements,
		'%channelcount%' 		=> 	$subDraggableElements,
		'%packetloss%' 			=> 	$subDraggableElements,
		'%ping%' 				=> 	$subDraggableElements,
		'%realclients%' 		=> 	$subDraggableElements,
		'%nickname%' 			=> 	$subDraggableElements,
		'%timeHi%' 				=> 	$subDraggableElements,
		'%timeHis%' 			=> 	$subDraggableElements,
		'%date%' 				=> 	$subDraggableElements
    ];
	
	/*
		Get Elementinformations
	*/
	$settingsFile				=	__dir__."/../../images/ts_banner/".$LinkInformations['instanz']."_".$server['data']['virtualserver_port']."_settings.json";
	if(file_exists($settingsFile))
	{
		$settings				=	json_decode(file_get_contents($settingsFile));
	};
	
	/*
		Function for new Draggable element
	*/
	function addDraggableElement($text, $id)
	{
		global $language, $fonts;
		echo	'<div class="row small-top-bottom-margin" style="text-align: initial;">
					<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6">
						'.$language['pulltext'].':
						<div class="'.$id.' draggable-double">'.$text.'</div>
						<div id="'.$id.'" class="draggable '.$id.'">
							'.$text.'
						</div>
					</div>
					<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6 draggable-infobox">
						<div class="input-group" style="margin-bottom: 10px;">
							<span class="input-group-addon">
								'.$language['font'].'
							</span>
							<select id="'.$id.'_font" class="form-control fontpick" classname="'.$id.'">
								'.$fonts.'
							</select>
						</div>
						<div class="input-group">
							<span class="input-group-addon">
								'.$language['textsize'].'
							</span>
							<input id="'.$id.'_fontsize" class="form-control sizepick" classname="'.$id.'" type="number" value="16"/>
							<span class="input-group-addon">
								px
							</span>
						</div>
						<div class="small-top-bottom-margin">
							<div class="draggable-colorpicker">'.$language['color'].': <input classname="'.$id.'" type="text" class="colorpick"/></div>
						</div>
					</div>
				</div>
				<hr/>';
	};
?>

<!-- Serverbanner hochladen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-upload"></i> <?php echo $language['upload_serverbanner']; ?></h4>
	</div>
	<div class="card-block" style="text-align: center;">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<form class="dropzone" drop-zone="" id="file-dropzone"></form>
			</div>
		</div>
		<p><?php echo $language['upload_serverbanner_info']; ?></p>
	</div>
</div>

<!-- Serverbannerlink -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title"><i class="fa fa-link"></i> <?php echo $language['serverbanner_link']; ?></h4>
	</div>
	<div class="card-block" style="text-align: center;">
		<div class="input-group" style="margin-bottom: 10px;">
			<span class="input-group-addon">
				<i class="fa fa-link" aria-hidden="true"></i>
			</span>
			<?php
				$iframeLink		=	str_replace("index", "iframeServerBanner", explode("?", $_SERVER['HTTP_REFERER'])[0]);
				$iframeText		=	"<iframe allowtransparency=\"true\" src=\"".$iframeLink."?port=".$server['data']['virtualserver_port']."&instanz=".$LinkInformations['instanz']."\" style=\"height:100%;width:100%\" scrolling=\"auto\" frameborder=\"0\">Your Browser will not show Iframes</iframe>";
			?>
			<textarea rows="2" readonly class="form-control"><?php echo $iframeText; ?></textarea>
		</div>
	</div>
</div>

<!-- Serverbanner erstellen -->
<div class="card">
	<div class="card-block card-block-header">
		<h4 class="card-title">
			<div class="pull-xs-left">
				<i class="fa fa-plus"></i> <?php echo $language['create_serverbanner']; ?>
			</div>
			<div class="pull-xs-right">
				<div style="margin-top:0px;padding: .175rem 1rem;"
					onclick="createBanner();" class="pull-xs-right btn btn-secondary user-header-icons">
					<i class="fa fa-fw fa-save"></i> <?php echo $language['save']; ?>
				</div>
			</div>
			<div style="clear:both;"></div>
		</h4>
	</div>
	<div class="card-block" style="text-align: center;">
		<div class="input-group" style="margin-bottom: 10px;">
			<span class="input-group-addon">
				<i class="fa fa-image" aria-hidden="true"></i>
			</span>
			<select id="bannerBgImage" class="form-control fontpick">
				<?php
					if(file_exists($settingsFile))
					{
						echo '<option selected value="'.explode("/", $settings->settings->bgimage)[3].'">'.$language['use_current'].'</option>';
					};
					echo $defaultImages;
				?>
			</select>
		</div>
		<div id="draggableEnd">
			<img class="img-fluid createBannerImage" src="<?php echo (file_exists($settingsFile)) ? $settings->settings->bgimage : $mainImage; ?>"/>
			<hr/>
			<div class="row small-top-bottom-margin" style="text-align: initial;">
				<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6">
					<?php echo $language['pulltext']; ?>:
					<div class="custom-text-1 draggable-double">custom-text-1</div>
					<div id="custom-text-1" class="draggable custom-text-1">
						custom-text-1
					</div>
				</div>
				<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6 draggable-infobox">
					<div class="input-group" style="margin-bottom: 10px;">
						<span class="input-group-addon">
							<?php echo $language['name']; ?>
						</span>
						<input id="custom-text-1_text" class="form-control textinput" classname="custom-text-1"/>
					</div>
					<div class="input-group" style="margin-bottom: 10px;">
						<span class="input-group-addon">
							<?php echo $language['font']; ?>
						</span>
						<select id="custom-text-1_font" class="form-control fontpick" classname="custom-text-1">
							<?php echo $fonts; ?>
						</select>
					</div>
					<div class="input-group">
						<span class="input-group-addon">
							<?php echo $language['textsize']; ?>
						</span>
						<input id="custom-text-1_fontsize" class="form-control sizepick" classname="custom-text-1" type="number" value="16"/>
						<span class="input-group-addon">
							px
						</span>
					</div>
					<div class="small-top-bottom-margin">
						<div class="draggable-colorpicker"><?php echo $language['color']; ?>: <input classname="custom-text-1" type="text" class="colorpick"/></div>
					</div>
				</div>
			</div>
			<hr/>
			<div class="row small-top-bottom-margin" style="text-align: initial;">
				<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6">
					<?php echo $language['pulltext']; ?>:
					<div class="custom-text-2 draggable-double">custom-text-2</div>
					<div id="custom-text-2" class="draggable custom-text-2">
						custom-text-2
					</div>
				</div>
				<div class="col-lg-6 col-xl-6 col-md-6 col-sm-6 col-xs-6 draggable-infobox">
					<div class="input-group" style="margin-bottom: 10px;">
						<span class="input-group-addon">
							<?php echo $language['name']; ?>
						</span>
						<input id="custom-text-2_text" class="form-control textinput" classname="custom-text-2"/>
					</div>
					<div class="input-group" style="margin-bottom: 10px;">
						<span class="input-group-addon">
							<?php echo $language['font']; ?>
						</span>
						<select id="custom-text-2_font" class="form-control fontpick" classname="custom-text-2">
							<?php echo $fonts; ?>
						</select>
					</div>
					<div class="input-group">
						<span class="input-group-addon">
							<?php echo $language['textsize']; ?>
						</span>
						<input id="custom-text-2_fontsize" class="form-control sizepick" classname="custom-text-2" type="number" value="16"/>
						<span class="input-group-addon">
							px
						</span>
					</div>
					<div class="small-top-bottom-margin">
						<div class="draggable-colorpicker"><?php echo $language['color']; ?>: <input classname="custom-text-2" type="text" class="colorpick"/></div>
					</div>
				</div>
			</div>
			<hr/>
			<?php
				foreach($draggableElements AS $element=>$tmp)
				{
					addDraggableElement($element, "element_".str_replace("%", "", $element));
				};
			?>
		</div>
	</div>
</div>

<script src="js/sonstige/dropzone.js"></script>
<script src="js/bootstrap/bootstrap-table.js"></script>
<script src="js/sonstige/interact.js"></script>
<script src="js/sonstige/spectrum.js"></script>
<script>
	var port 						= 	'<?php echo $server['data']['virtualserver_port']; ?>',
		instanz						=	'<?php echo $LinkInformations['instanz']; ?>',
		hasSettings					=	'<?php echo (file_exists($settingsFile)) ? "true" : "false"; ?>';
	
	/*
		Upload picture
	*/
	$('#file-dropzone').dropzone({
		url: "./php/functions/functionsUploadServerBanner.php",
		method: "POST",
		acceptedFiles: '.png',
		dictDefaultMessage: lang.serverbanner_upload_info,
		destination: "/files/backups",
		init: function() {
			this.on('thumbnail', function(file) {
				if ( file.width != 900 || file.height != 450 )
				{
					file.rejectDimensions();
				}
				else
				{
					file.acceptDimensions();
				};
			});
		},
		accept: function(file, done)
		{
			file.acceptDimensions = done;
			file.rejectDimensions = function() {
				done(lang.upload_serverbanner_size)
			};
		},
		success: function(file, data) {
			if(data == "done")
			{
				setNotifySuccess(lang.upload_successful);
			}
			else
			{
				setNotifyFailed(data);
			};
		}
	});
	
	/*
		Create Replaceall function
	*/
	String.prototype.replaceAll = function(search, replacement)
	{
		var target = this;
		return target.replace(new RegExp(search, 'g'), replacement);
	};
	
	/*
		Change example text with the new options you set
	*/
	$( ".sizepick" ).change(function() {
		if($(this).val() <= 40)
		{
			$('.'+$(this).attr("classname")).css("font-size", $(this).val()+"px");
		};
	});
	
	$(".colorpick").spectrum({
		color: "#333",
		showPalette: true,
		showInitial: true,
		showInput: true,
		change: function(color)
		{
			$('.'+$(this).attr("classname")).css("color", color.toHexString());
		}
	});
	
	/*
		Draggable
	*/
	interact('.draggable').draggable({
		inertia: true,
		restrict: {
			restriction: "#draggableEnd",
			endOnly: true,
			elementRect: { top: 0, left: 0, bottom: 0, right: 0 }
		},
		autoScroll: true,
		onmove: dragMoveListener
	});

	function dragMoveListener (event)
	{
		var target = event.target,
		
		x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx,
		y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
		
		target.style.webkitTransform =
		target.style.transform =
		'translate(' + x + 'px, ' + y + 'px)';
		
		target.setAttribute('data-x', x);
		target.setAttribute('data-y', y);
	}
	
	window.dragMoveListener = dragMoveListener;
	
	/*
		Update Bannerelements
	*/
	if(hasSettings == "true")
	{
		setTimeout(function()
		{
			var settings	=	<?php echo json_encode($settings); ?>;
			for(var element in settings['data'])
			{
				var queryElement					=	"#element_"+element.replaceAll("%", ""),
					queryElements					=	".element_"+element.replaceAll("%", ""),
					startElementPosition			=	document.querySelector(queryElement).getBoundingClientRect(),
					picElementPosition				=	document.querySelector('.createBannerImage').getBoundingClientRect(),
					width							=	$('.createBannerImage').css("width").replace("px", "") / 900,
					height							=	$('.createBannerImage').css("height").replace("px", "") / 450,
					newXPos, newYPos;
				
				newXPos								=	(picElementPosition.left-startElementPosition.left)+(settings['data'][element].x*width);
				newYPos								=	(picElementPosition.top-startElementPosition.top)+(settings['data'][element].y*height)-settings['data'][element].fontsize;
				
				$("#element_"+element.replaceAll("%", "")+"_fontsize").val(settings['data'][element].fontsize);
				
				$(queryElements).css("color", settings['data'][element].color);
				$(queryElements).css("font-size", settings['data'][element].fontsize+"px");
				$(queryElement).css("transform", "translate("+newXPos+"px, "+newYPos+"px)");
				$(queryElement).attr("data-x", newXPos);
				$(queryElement).attr("data-y", newYPos);
			};
			
			for(var element in settings['custom'])
			{
				var queryElement					=	"#"+element,
					queryElements					=	"."+element,
					startElementPosition			=	document.querySelector(queryElement).getBoundingClientRect(),
					picElementPosition				=	document.querySelector('.createBannerImage').getBoundingClientRect(),
					width							=	$('.createBannerImage').css("width").replace("px", "") / 900,
					height							=	$('.createBannerImage').css("height").replace("px", "") / 450,
					newXPos, newYPos;
				
				newXPos								=	(picElementPosition.left-startElementPosition.left)+(settings['custom'][element].x*width);
				newYPos								=	(picElementPosition.top-startElementPosition.top)+(settings['custom'][element].y*height)-settings['custom'][element].fontsize;
				
				$("#"+element+"_text").val(settings['custom'][element].text);
				$("#"+element+"_fontsize").val(settings['custom'][element].fontsize);
				
				$(queryElements).css("color", settings['custom'][element].color);
				$(queryElements).css("font-size", settings['custom'][element].fontsize+"px");
				$(queryElement).css("transform", "translate("+newXPos+"px, "+newYPos+"px)");
				$(queryElement).attr("data-x", newXPos);
				$(queryElement).attr("data-y", newYPos);
			};
		}, 600);
	};
	
	/*
		Create Banner
	*/
	function createBanner()
	{
		var bgimage		=	$('#bannerBgImage').val();
		var data 		=
			{
				"settings":
				{
					"bgimage": "./images/ts_banner/"+bgimage
				},
				"data": <?php echo json_encode($draggableElements); ?>,
				"custom": <?php echo json_encode($draggableCustomElements); ?>
			};
		
		for(var element in data["custom"])
		{
			var queryElement						=	"#"+element,
				pos									=	getPositions(queryElement);
			
			if(pos.x >= 0 && pos.y >= 0)
			{
				data["custom"][element].x 			=	pos.x;
				data["custom"][element].y 			=	pos.y+parseInt($(queryElement).css("font-size").replace("px", ""));
				
				var colorElements					=	$(queryElement).css("color").replace("rgb(", "").replace(")", "").split(",");
				data["custom"][element].color		=	rgbToHex(parseInt(colorElements[0].trim()), parseInt(colorElements[1].trim()), parseInt(colorElements[2].trim()));
				data["custom"][element].fontfile	=	"./fonts/"+$("#"+element+"_font").val();
				data["custom"][element].fontsize	=	$(queryElement).css("font-size").replace("px", "");
				data["custom"][element].text		=	$("#"+element+"_text").val();
			}
			else
			{
				delete data["custom"][element];
			};
		};
		
		for(var element in data["data"])
		{
			var queryElement						=	"#element_"+element.replaceAll("%", ""),
				pos									=	getPositions(queryElement);
			
			if(pos.x >= 0 && pos.y >= 0)
			{
				data["data"][element].x 			=	pos.x;
				data["data"][element].y 			=	pos.y+parseInt($(queryElement).css("font-size").replace("px", ""));
				
				var colorElements					=	$(queryElement).css("color").replace("rgb(", "").replace(")", "").split(",");
				data["data"][element].color			=	rgbToHex(parseInt(colorElements[0].trim()), parseInt(colorElements[1].trim()), parseInt(colorElements[2].trim()));
				data["data"][element].fontfile		=	"./fonts/"+$("#element_"+element.replaceAll("%", "")+"_font").val();
				data["data"][element].fontsize		=	$(queryElement).css("font-size").replace("px", "");
			}
			else
			{
				delete data["data"][element];
			};
		};
		
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsTeamspeakPost.php",
			data: {
				action:		'createBanner',
				instanz:	instanz,
				port:		port,
				data:		JSON.stringify(data)
			},
			success: function(data)
			{
				if(data == "done")
				{
					setNotifySuccess(lang.serverbanner_created);
				}
				else
				{
					setNotifyFailed(data);
				};
			}
		});
	};
	
	function getPositions(id)
	{
		var imgElement	= 	document.querySelector('.createBannerImage').getBoundingClientRect(),
			width		=	$('.createBannerImage').css("width").replace("px", "") / 900,
			height		=	$('.createBannerImage').css("height").replace("px", "") / 450,
			idElement	=	document.querySelector(id).getBoundingClientRect();
		
		return {
			'x':	((idElement.left - imgElement.left) >= 0 && (idElement.left - imgElement.left) <= (900*width)) ? (idElement.left - imgElement.left)/width : -1,
			'y':	((idElement.top - imgElement.top) >= 0 && (idElement.top - imgElement.top) <= (450*height)) ? (idElement.top - imgElement.top)/height : -1
		}
	};
	
	function componentToHex(c)
	{
		var hex = c.toString(16);
		return hex.length == 1 ? "0" + hex : hex;
	}

	function rgbToHex(r, g, b)
	{
		return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
	};
</script>
<script src="js/webinterface/teamspeak.js"></script>
<script src="js/sonstige/preloader.js"></script>