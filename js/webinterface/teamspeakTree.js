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

$(function(){
	var dataString 		= 	'action=getTeamspeakBaum&instanz='+instanz+'&port='+port;
	$.ajax({
		type: "POST",
		url: "functionsTeamspeakPost.php",
		data: dataString,
		cache: true,
		async: true,
		success: function(data){
			// Variabeln deklarieren
			var tree 				=	JSON.parse(data);
			var getChannelInfos		=	new Array();
			var getSubChannelInfos	=	new Array();
			var getChannelPid		=	new Array();
			var getSubChannelPid	=	new Array();
			var savedClientData		=	new Array();
			
			// Header einfügen
			document.getElementById('server_name').innerHTML		=	tree['header'];
			if(document.getElementById('globalServername'))
			{
				document.getElementById('globalServername').innerHTML	=	tree['globalHeader'];
			};
			
			if(tree['headerimg_exist'] == 1)
			{
				document.getElementById('server_icon').innerHTML	=	tree['headerimg'];
			};
			
			// Channelinformationen abfragen
			for(var channels in tree['channels'])
			{
				getChannelPid[tree[channels]['cid']]			=	tree[channels]['pid'];
			};
			
			// Channels erstellen
			getChannelInfos['channelLength']					=	0;
			for(var channel in tree['channels'])
			{
				var inhalt 										= 	'<div class="row ts_row channel tree_channel" channelid="'+tree[channel]['cid']+'" channelpid="'+tree[channel]['pid']+'" id="channel_'+channel+'"';
				inhalt											+=	'onClick="showChannelInformations(\''+tree[channel]['cid']+'\')">';
				inhalt											+=		'<div class="col-md-12">';
				if(tree[channel]['spacer'] == '1')
				{
					inhalt										+=			'<div class="col-md-12 col-xs-12" style="overflow:hidden;text-align:'+tree[channel]['align']+';">';
					inhalt										+=				tree['channels'][channel];
					inhalt										+=			'</div>';
					inhalt										+=		'</div>';
					inhalt										+=	'</div>';
				}
				else
				{
					inhalt										+=			'<div class="col-md-7 col-xs-7">';
					inhalt										+=				tree[channel]['img_before'];
					inhalt										+=				'&nbsp;&nbsp;' + tree['channels'][channel];
					inhalt										+=			'</div>';
					inhalt										+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
					if(tree[channel]['channel_flag_password'] == 1)
					{
						inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
					}
					if(tree[channel]['channel_flag_default'] == 1)
					{
						inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
					}
					if(tree[channel]['channel_codec'] == 3 || tree[channel]['channel_codec'] == 5)
					{
						inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
					}
					if(tree[channel]['channel_needed_talk_power'] > 0)
					{
						inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
					}
					if(tree[channel]['channel_icon_id'] != '')
					{
						inhalt									+=				tree[channel]['channel_icon_id'];
					}
					inhalt										+=			'</div>';
					inhalt										+=		'</div>';
					inhalt										+=	'</div>';
				};
				
				var einzufuegenesObjekt							= 	document.createElement("div");
				einzufuegenesObjekt.innerHTML 					= 	inhalt;
				
				if(channel == 0)
				{
					$(inhalt).insertAfter("#header_tree");
				}
				else
				{
					$(inhalt).insertAfter("#channel_"+(channel-1));
				};
				
				getChannelInfos['channelLength']++;
			};
			
			// Subchannelinformationen abfragen
			for(var subs in tree['sub_channels'])
			{
				getSubChannelPid[tree[subs]['sub_cid']]	=	tree[subs]['sub_pid'];
			};
			
			// Subchannelbaum erstellen
			for(var subs in tree['sub_channels'])
			{
				getSubChannelInfos[tree[subs]['sub_cid']]			=	new Array();
				getSubChannelInfos[tree[subs]['sub_cid']]['conn']	=	true;
				getSubChannelInfos[tree[subs]['sub_cid']]['einr']	=	0;
				
				var channelPid										=	getChannelPid[tree[subs]['sub_pid']];
				if(channelPid != 0)
				{
					channelPid										=	getSubChannelPid[tree[subs]['sub_pid']];
					var temp										=	0;
					
					while(typeof(channelPid) != 'undefined')
					{
						// Baum hochzählen
						getSubChannelInfos[tree[subs]['sub_cid']]['einr']++;
						
						temp		=	channelPid
						channelPid	=	getSubChannelPid[temp];
					};
				};
			};
			
			// Subchannel erstellen
			var createSubChannels									=	false;
			var momEinrueckung										=	0;
			while(createSubChannels == false)
			{
				createSubChannels									=	true;
				for(var k in tree['sub_channels'])
				{
					if(momEinrueckung == getSubChannelInfos[tree[k]['sub_cid']]['einr'])
					{
						getSubChannelInfos[tree[k]['sub_cid']]['pid']	=	tree[k]['sub_pid'];
						getSubChannelInfos[tree[k]['sub_cid']]['k']		=	k;
						createSubChannels								=	false;
						
						var inhalt 										= 	'<div class="row ts_row channel tree_sub_channel" channelid="'+tree[k]['sub_cid']+'" channelpid="'+tree[k]['sub_pid']+'" id="sub_channel_'+tree[k]['sub_cid']+'"';
						inhalt											+=	'onClick="showChannelInformations(\''+tree[k]['sub_cid']+'\')">';
						inhalt											+=		'<div class="col-md-12">';
						inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
						for (var i = 0; i < getSubChannelInfos[tree[k]['sub_cid']]['einr']; i++)
						{
							inhalt										+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
						};
						inhalt											+=				tree[k]['sub_img_before'];
						inhalt											+=				'&nbsp;&nbsp;' + tree['sub_channels'][k];
						inhalt											+=			'</div>';
						inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
						if(tree[k]['sub_channel_flag_password'] == 1)
						{
							inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
						};
						if(tree[k]['sub_channel_flag_default'] == 1)
						{
							inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
						};
						if(tree[k]['sub_channel_codec'] == 3 || tree[k]['sub_channel_codec'] == 5)
						{
							inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
						};
						if(tree[k]['sub_channel_needed_talk_power'] > 0)
						{
							inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
						};
						if(tree[k]['sub_channel_icon_id'] != '')
						{
							inhalt										+=				tree[k]['sub_channel_icon_id'];
						};
						inhalt											+=			'</div>';
						inhalt											+=		'</div>';
						inhalt											+=	'</div>';
							
						var einzufuegenesObjekt							= 	document.createElement("div");
						einzufuegenesObjekt.innerHTML 					= 	inhalt;
						
						var channelBefore								=	$(".channel[channelpid="+tree[k]['sub_pid']+"]").attr('id');
						if(typeof(channelBefore) == 'undefined')
						{
							$(inhalt).insertAfter("#"+$(".channel[channelid="+tree[k]['sub_pid']+"]").attr('id'));
						}
						else
						{
							$(inhalt).insertAfter("#"+$(".channel[channelpid="+tree[k]['sub_pid']+"]").last().attr('id'));
						};
					};
				};
				momEinrueckung++;
			};
			
			// Nickname erstellen
			for(var k in tree['nickname'])
			{
				savedClientData[tree[k]['nick_clid']]					=	new Array();
				savedClientData[tree[k]['nick_clid']]['isInChannel']	= 	tree[k]['nick_cid'];
				savedClientData[tree[k]['nick_clid']]['isConnected']	=	true;
				
				var inhalt 										= 	'<div class="row ts_row tree_user" clientid="'+tree[k]['nick_clid']+'" id="client_'+tree[k]['nick_clid']+'"';
				inhalt											+=	'onClick="showClientInformations(\''+tree[k]['nick_clid']+'\')">';
				inhalt											+=		'<div class="col-md-12">';
				inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
				if (typeof(getSubChannelInfos[tree[k]['nick_cid']]) != 'undefined' && getSubChannelInfos[tree[k]['nick_cid']] != null)
				{
					for(i = 0; i <= getSubChannelInfos[tree[k]['nick_cid']]['einr']; i++)
					{
						inhalt									+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
					};
				};
				if(tree[k]['nick_status'] == "away")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/away.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "hwhead")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwhead.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "hwmic")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwmic.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "head")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/head.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "mic")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/mic.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "player_command")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "player_command_on")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander_on.png\" alt=\"\" />";
				}
				else if(tree[k]['nick_status'] == "player_on")
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_on.png\" alt=\"\" />";
				}
				else
				{
					inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player.png\" alt=\"\" />";
				};
				inhalt											+=				'&nbsp;&nbsp;' + tree['nickname'][k];
				inhalt											+=				'&nbsp;&nbsp;' + tree[k]['nick_away_message'];
				inhalt											+=			'</div>';
				inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
				for(var j in tree[k]['cgroup'])
				{
					inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['cgroup'][j]+"\" alt=\"\" />";
				};
				for(var j in tree[k]['sgroup'])
				{
					inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['sgroup'][j]+"\" alt=\"\" />";
				};
				if(tree[k]['nick_country'] != '')
				{
					inhalt										+=				"<img class=\"right-img\" src=\"images/ts_countries/"+tree[k]['nick_country']+".png\" alt=\"\" />";
				};
				inhalt											+=			'</div>';
				inhalt											+=		'</div>';
				inhalt											+=	'</div>';
				
				var einzufuegenesObjekt							= 	document.createElement("div");
				einzufuegenesObjekt.innerHTML 					= 	inhalt;
				
				$(inhalt).insertAfter("#"+$(".channel[channelid="+tree[k]['nick_cid']+"]").attr('id'));
			};
			
			// Baum einblenden lassen
			$('#tree_loading').slideUp("slow", function()
			{
				$('#tree').slideDown("slow", function()
				{
					if(treeInterval != -1)
					{
						// Intervall setzen
						var refreshTree = setInterval(function()
						{
							if (!document.getElementById('tree'))
							{
								clearInterval(refreshTree);
							}
							else
							{
								var dataString 		= 	'action=getTeamspeakBaum&instanz='+instanz+'&port='+port;
								$.ajax({
									type: "POST",
									url: "functionsTeamspeakPost.php",
									data: dataString,
									cache: true,
									async: true,
									success: function(data){
										if (document.getElementById('tree'))
										{
											var tree 				=	JSON.parse(data);
											
											// Rechte spalte aktualisieren
											if(document.getElementById('server_status'))
											{
												if(tree['serverstatus'] == 'online')
												{
													document.getElementById('server_status').innerHTML 	=	'<font style="color:green;">'+tree['serverstatus']+'</font>';
												}
												else
												{
													document.getElementById('server_status').innerHTML 	=	'<font style="color:#900;">'+tree['serverstatus']+'</font>';
												};
												
												document.getElementById('server_timeup').innerHTML		=	tree['servertimeup'];
												document.getElementById('server_channels').innerHTML	=	tree['serverchannels'];
												document.getElementById('max_clients').innerHTML		=	(tree['serverclients'] - tree['serverqclients'])+' / '+tree['servermaxclients']+' ('+tree['serverqclients']+')';
												document.getElementById('server_password').innerHTML	=	tree['serverpassword'];
											};
											
											// Background aktualisieren
											if(tree['serverstatus'] == 'online')
											{
												$('#tree').css("background", "rgba(0,199,0,0.2)");
											}
											else
											{
												$('#tree').css("background", "rgba(199,0,0,0.2)");
											};
											
											// Header einfügen
											document.getElementById('server_name').innerHTML		=	tree['header'];
											if(document.getElementById('globalServername'))
											{
												document.getElementById('globalServername').innerHTML	=	tree['globalHeader'];
											};
											
											if(tree['headerimg_exist'] == 1)
											{
												document.getElementById('server_icon').innerHTML	=	tree['headerimg'];
											};
											
											// Channelinformationen abfragen
											for(var channels in tree['channels'])
											{
												getChannelPid[tree[channels]['cid']]	=	tree[channels]['pid'];
											};
											
											// Channel bauen/bearbeiten
											var tempChannel	=	0;
											for(var k in tree['channels'])
											{
												if(tempChannel > (getChannelInfos['channelLength']-1))
												{
													var inhalt 										= 	'<div class="row ts_row channel tree_channel" channelid="'+tree[k]['cid']+'" channelpid="'+tree[k]['pid']+'" id="channel_'+k+'"';
													inhalt											+=	'onClick="showChannelInformations(\''+tree[channel]['cid']+'\')">';
													inhalt											+=		'<div class="col-md-12">';
													if(tree[k]['spacer'] == '1')
													{
														inhalt										+=			'<div class="col-md-12 col-xs-12" style="overflow:hidden;text-align:'+tree[k]['align']+';">';
														inhalt										+=				tree['channels'][k];
														inhalt										+=			'</div>';
														inhalt										+=		'</div>';
														inhalt										+=	'</div>';
													}
													else
													{
														inhalt										+=			'<div class="col-md-7 col-xs-7">';
														inhalt										+=				tree[k]['img_before'];
														inhalt										+=				'&nbsp;&nbsp;' + tree['channels'][k];
														inhalt										+=			'</div>';
														inhalt										+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
														if(tree[k]['channel_flag_password'] == 1)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
														};
														if(tree[k]['channel_flag_default'] == 1)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
														};
														if(tree[k]['channel_codec'] == 3 || tree[k]['channel_codec'] == 5)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
														};
														if(tree[k]['channel_needed_talk_power'] > 0)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
														};
														if(tree[k]['channel_icon_id'] != '')
														{
															inhalt									+=				tree[k]['channel_icon_id'];
														};
														inhalt										+=			'</div>';
														inhalt										+=		'</div>';
														inhalt										+=	'</div>';
													};
													
													var einzufuegenesObjekt							= 	document.createElement("div");
													einzufuegenesObjekt.innerHTML 					= 	inhalt;
													
													if(k == 0)
													{
														$(inhalt).insertAfter("#header_tree");
													}
													else
													{
														$(inhalt).insertAfter("#channel_"+(k-1));
													};
													
													getChannelInfos['channelLength']++
												}
												else
												{
													var inhalt										=		'<div class="col-md-12">';
													if(tree[k]['spacer'] == '1')
													{
														inhalt										+=			'<div class="col-md-12 col-xs-12" style="overflow:hidden;text-align:'+tree[k]['align']+';">';
														inhalt										+=				tree['channels'][k];
														inhalt										+=			'</div>';
														inhalt										+=		'</div>';
													}
													else
													{
														inhalt										+=			'<div class="col-md-7 col-xs-7">';
														inhalt										+=				tree[k]['img_before'];
														inhalt										+=				'&nbsp;&nbsp;' + tree['channels'][k];
														inhalt										+=			'</div>';
														inhalt										+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
														if(tree[k]['channel_flag_password'] == 1)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
														};
														if(tree[k]['channel_flag_default'] == 1)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
														};
														if(tree[k]['channel_codec'] == 3 || tree[k]['channel_codec'] == 5)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
														};
														if(tree[k]['channel_needed_talk_power'] > 0)
														{
															inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
														};
														if(tree[k]['channel_icon_id'] != '')
														{
															inhalt									+=				tree[k]['channel_icon_id'];
														};
														inhalt										+=			'</div>';
														inhalt										+=		'</div>';
													};
													
													document.getElementById('channel_'+k).innerHTML	=	inhalt;
												};
												
												tempChannel++;
											};
											
											// Channels löschen lassen
											if(tempChannel != getChannelInfos['channelLength'])
											{
												for(i = 0; i < (getChannelInfos['channelLength'] - tempChannel);i++)
												{
													var elem = document.getElementById('channel_'+(parseInt(i)+(parseInt(getChannelInfos['channelLength'])-1)));
													elem.parentNode.removeChild(elem);
													
													getChannelInfos['channelLength']--;
												};
											};
											
											// Subchannelinformationen abfragen
											for(var subs in tree['sub_channels'])
											{
												getSubChannelPid[tree[subs]['sub_cid']]	=	tree[subs]['sub_pid'];
											};
											
											// Subchannelbaum erstellen
											for(var subs in tree['sub_channels'])
											{
												if(typeof(getSubChannelInfos[tree[subs]['sub_cid']]) == 'undefined')
												{
													getSubChannelInfos[tree[subs]['sub_cid']]		=	new Array();
												};
												
												getSubChannelInfos[tree[subs]['sub_cid']]['einr']	=	0;
												
												var channelPid										=	getChannelPid[tree[subs]['sub_pid']];
												if(channelPid != 0)
												{
													channelPid										=	getSubChannelPid[tree[subs]['sub_pid']];
													var temp										=	0;
													
													while(typeof(channelPid) != 'undefined')
													{
														// Baum hochzählen
														getSubChannelInfos[tree[subs]['sub_cid']]['einr']++;
														
														temp		=	channelPid
														channelPid	=	getSubChannelPid[temp];
													};
												};
											};
											
											//Subchannels bauen/bearbeiten
											for(var k in tree['sub_channels'])
											{
												if(getSubChannelInfos[tree[k]['sub_cid']]['pid'] != tree[k]['sub_pid'])
												{
													// Subchannel löschen und Daten ändern
													getSubChannelInfos[tree[k]['sub_cid']]['pid']	=	tree[k]['sub_pid'];
													if(getSubChannelInfos[tree[k]['sub_cid']]['conn'] == false)
													{
														var elem = document.getElementById('sub_channel_'+tree[k]['sub_cid']);
														elem.parentNode.removeChild(elem);
													};
													
													// Subchannel erstellen
													var inhalt 										= 	'<div class="row ts_row channel tree_sub_channel" channelid="'+tree[k]['sub_cid']+'" channelpid="'+tree[k]['sub_pid']+'" id="sub_channel_'+tree[k]['sub_cid']+'"';
													inhalt											+=	'onClick="showChannelInformations(\''+tree[k]['sub_cid']+'\')">';
													inhalt											+=		'<div class="col-md-12">';
													inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
													for (var i = 0; i < getSubChannelInfos[tree[k]['sub_cid']]['einr']; i++)
													{
														inhalt										+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
													};
													inhalt											+=				'&nbsp;&nbsp;Loading... Please Wait ;)';
													inhalt											+=			'</div>';
													inhalt											+=		'</div>';
													inhalt											+=	'</div>';
														
													var einzufuegenesObjekt							= 	document.createElement("div");
													einzufuegenesObjekt.innerHTML 					= 	inhalt;
													
													var channelBefore								=	$(".channel[channelpid="+tree[k]['sub_pid']+"]").attr('id');
													if(typeof(channelBefore) == 'undefined')
													{
														$(inhalt).insertAfter("#"+$(".channel[channelid="+tree[k]['sub_pid']+"]").attr('id'));
													}
													else
													{
														$(inhalt).insertAfter("#"+$(".channel[channelpid="+tree[k]['sub_pid']+"]").last().attr('id'));
													};
												}
												else
												{
													$("#sub_channel_"+getSubChannelInfos[tree[k]['sub_cid']]['k']).attr({
														channelid: tree[k]['sub_cid'],
														channelpid: tree[k]['sub_pid']
													});
													
													var inhalt										=		'<div class="col-md-12">';
													inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
													for (var i = 0; i < getSubChannelInfos[tree[k]['sub_cid']]['einr']; i++)
													{
														inhalt										+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
													};
													inhalt											+=				tree[k]['sub_img_before'];
													inhalt											+=				'&nbsp;&nbsp;' + tree['sub_channels'][k];
													inhalt											+=			'</div>';
													inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
													if(tree[k]['sub_channel_flag_password'] == 1)
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
													};
													if(tree[k]['sub_channel_flag_default'] == 1)
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
													};
													if(tree[k]['sub_channel_codec'] == 3 || tree[k]['sub_channel_codec'] == 5)
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
													};
													if(tree[k]['sub_channel_needed_talk_power'] > 0)
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
													}
													if(tree[k]['sub_channel_icon_id'] != '')
													{
														inhalt										+=				tree[k]['sub_channel_icon_id'];
													};
													inhalt											+=			'</div>';
													inhalt											+=		'</div>';
													
													document.getElementById('sub_channel_'+tree[k]['sub_cid']).innerHTML	=	inhalt;
												};
												getSubChannelInfos[tree[k]['sub_cid']]['k']			=	k;
												getSubChannelInfos[tree[k]['sub_cid']]['conn']		=	true;
											};
											
											// Subchannel löschen lassen
											for(var k in getSubChannelInfos)
											{
												if(getSubChannelInfos[k]['conn'] != true)
												{
													var elem = document.getElementById('sub_channel_'+k);
													elem.parentNode.removeChild(elem);
													
													delete getSubChannelInfos[k];
												}
												else
												{
													getSubChannelInfos[k]['conn'] 				= 	false;
												};
											};
											
											// Namen einbauen/bearbeiten
											for(var k in tree['nickname'])
											{
												if(typeof(savedClientData[tree[k]['nick_clid']]) == 'undefined')
												{
													savedClientData[tree[k]['nick_clid']]					=	new Array();
													savedClientData[tree[k]['nick_clid']]['isInChannel'] 	= -1;
												};
												
												savedClientData[tree[k]['nick_clid']]['isConnected']	=	true;
												
												if(savedClientData[tree[k]['nick_clid']]['isInChannel'] != tree[k]['nick_cid'])
												{
													// Nick löschen
													var elem = document.getElementById('client_'+tree[k]['nick_clid']);
													if(elem != null)
													{
														elem.parentNode.removeChild(elem);
													};
													
													var inhalt 										= 	'<div class="row ts_row tree_user" clientid="'+tree[k]['nick_clid']+'" id="client_'+tree[k]['nick_clid']+'"';
													inhalt											+=	'onClick="showClientInformations(\''+tree[k]['nick_clid']+'\')">';
													inhalt											+=		'<div class="col-md-12">';
													inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
													if (typeof(getSubChannelInfos[tree[k]['nick_cid']]) != 'undefined' && getSubChannelInfos[tree[k]['nick_cid']] != null)
													{
														for(i = 0; i <= getSubChannelInfos[tree[k]['nick_cid']]['einr']; i++)
														{
															inhalt									+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
														};
													};
													if(tree[k]['nick_status'] == "away")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/away.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "hwhead")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwhead.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "hwmic")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwmic.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "head")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/head.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "mic")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/mic.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_command")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_command_on")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander_on.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_on")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_on.png\" alt=\"\" />";
													}
													else
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player.png\" alt=\"\" />";
													};
													inhalt											+=				'&nbsp;&nbsp;' + tree['nickname'][k];
													inhalt											+=				'&nbsp;&nbsp;' + tree[k]['nick_away_message'];
													inhalt											+=			'</div>';
													inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
													for(var j in tree[k]['cgroup'])
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['cgroup'][j]+"\" alt=\"\" />";
													};
													for(var j in tree[k]['sgroup'])
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['sgroup'][j]+"\" alt=\"\" />";
													};
													if(tree[k]['nick_country'] != '')
													{
														inhalt										+=				"<img class=\"right-img\" src=\"images/ts_countries/"+tree[k]['nick_country']+".png\" alt=\"\" />";
													};
													inhalt											+=			'</div>';
													inhalt											+=		'</div>';
													inhalt											+=	'</div>';
													
													var einzufuegenesObjekt							= 	document.createElement("div");
													einzufuegenesObjekt.innerHTML 					= 	inhalt;
													
													$(inhalt).insertAfter("#"+$(".channel[channelid="+tree[k]['nick_cid']+"]").attr('id'));
													
													savedClientData[tree[k]['nick_clid']]['isInChannel'] = tree[k]['nick_cid'];
												}
												else
												{
													var inhalt										=		'<div class="col-md-12">';
													inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
													if (typeof(getSubChannelInfos[tree[k]['nick_cid']]) != 'undefined' && getSubChannelInfos[tree[k]['nick_cid']] != null)
													{
														for(i = 0; i <= getSubChannelInfos[tree[k]['nick_cid']]['einr']; i++)
														{
															inhalt									+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
														};
													};
													if(tree[k]['nick_status'] == "away")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/away.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "hwhead")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwhead.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "hwmic")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwmic.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "head")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/head.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "mic")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/mic.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_command")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_command_on")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander_on.png\" alt=\"\" />";
													}
													else if(tree[k]['nick_status'] == "player_on")
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_on.png\" alt=\"\" />";
													}
													else
													{
														inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player.png\" alt=\"\" />";
													};
													inhalt											+=				'&nbsp;&nbsp;' + tree['nickname'][k];
													inhalt											+=				'&nbsp;&nbsp;' + tree[k]['nick_away_message'];
													inhalt											+=			'</div>';
													inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
													for(var j in tree[k]['cgroup'])
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['cgroup'][j]+"\" alt=\"\" />";
													};
													for(var j in tree[k]['sgroup'])
													{
														inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+tree[k]['sgroup'][j]+"\" alt=\"\" />";
													};
													if(tree[k]['nick_country'] != '')
													{
														inhalt										+=				"<img class=\"right-img\" src=\"images/ts_countries/"+tree[k]['nick_country']+".png\" alt=\"\" />";
													};
													inhalt											+=			'</div>';
													inhalt											+=		'</div>';
													
													var elem = document.getElementById('client_'+tree[k]['nick_clid']);
													if(elem != null)
													{
														elem.innerHTML								=	inhalt;
													};
												};
											};
											
											// Nicks löschen lassen
											for(var k in savedClientData)
											{
												if(savedClientData[k]['isConnected'] != true)
												{
													var elem = document.getElementById('client_'+k);
													elem.parentNode.removeChild(elem);
													
													delete savedClientData[k];
												}
												else
												{
													savedClientData[k]['isConnected'] 				= 	false;
												};
											};
										};
									}
								});
							};
						}, treeInterval);
					};
				});
			});
		}
	});
});


function showChannelInformations(id)
{
	if(typeof serverId != 'undefined')
	{
		var modal	=	$('#modalChannelView');
		modal.load("modalChannelView.php", {id: id, instanz: instanz, serverId: serverId, port: port}, function()
		{
			modal.modal('show');
		});
	};
};

function showClientInformations(id)
{
	if(typeof serverId != 'undefined')
	{
		var modal	=	$('#modalClientView');
		modal.load("modalClientView.php", {id: id, instanz: instanz, serverId: serverId, port: port}, function()
		{
			modal.modal('show');
		});
	};
};