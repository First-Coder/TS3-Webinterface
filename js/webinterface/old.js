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
	$.ajax({
		type: "POST",
		url: "./php/functions/functionsTeamspeakPost.php",
		data: {
			action:		'getTeamspeakBaum',
			instanz:	instanz,
			port:		port
		},
		success: function(data)
		{
			var tree 				=	JSON.parse(data),
				delTimer			=	Date.now(),
				lastCid				=	0,
				channelTabs			=	0,
				tmp					=	0,
				subChannelsDone		=	false;
			
			setElementInnerHtml('server_name', tree['header']);
			setElementInnerHtml('globalServername', tree['globalHeader']);
			
			if(tree['headerimg_exist'] == "1")
			{
				setElementInnerHtml('server_icon', tree['headerimg']);
			};
			
			for(var channel of tree['channels'])
			{
				setChannel(channel, lastCid, tmp, delTimer);
				lastCid				=	channel.cid;
				tmp++;
			};
			
			while(!subChannelsDone)
			{
				tmp					=	0;
				subChannelsDone		=	true;
				channelTabs++;
				
				for(var channel of tree['subChannels'])
				{
					if(!setSubChannel(channel, channelTabs, delTimer))
					{
						subChannelsDone		=	false;
					}
					else
					{
						delete tree['subChannels'][tmp];
					};
					
					tmp++;
				};
			};
			
			for(var client of tree['clients'])
			{
				setClient(client, delTimer);
			};
			
			$('#tree_loading').slideUp("slow", function()
			{
				$('#tree').slideDown("slow", function()
				{
					if(treeInterval != -1)
					{
						setTimeout(updateTree, treeInterval, delTimer);
					};
				});
			});
		}
	});
});

function updateTree(delTimer)
{
	var oldDelTimer				=	delTimer;
	delTimer					=	Date.now();
	
	if(document.getElementById('tree'))
	{
		var returnData;
		$.ajax({
			type: "POST",
			url: "./php/functions/functionsTeamspeakPost.php",
			data: {
				action:		'getTeamspeakBaum',
				instanz:	instanz,
				port:		port
			},
			success: function(data)
			{
				var tree 				=	JSON.parse(data),
					lastCid				=	0,
					channelTabs			=	0,
					tmp					=	0,
					subChannelsDone		=	false;
				
				setElementInnerHtml('server_name', tree['header']);
				setElementInnerHtml('globalServername', tree['globalHeader']);
				setElementInnerHtml('server_status', tree['serverstatus']);
				setElementInnerHtml('server_timeup', tree['servertimeup']);
				setElementInnerHtml('server_channels', tree['serverchannels']);
				setElementInnerHtml('max_clients', (tree['serverclients'] - tree['serverqclients'])+' / '+tree['servermaxclients']+' ('+tree['serverqclients']+')');
				setElementInnerHtml('server_password', tree['serverpassword']);
				
				if(tree['headerimg_exist'] == "1")
				{
					setElementInnerHtml('server_icon', tree['headerimg']);
				};
				
				if(tree['serverstatus2'] == 'online' && !$('#tree').hasClass("tree-background-success"))
				{
					$('#tree').removeClass("tree-background-failed");
					$('#tree').addClass("tree-background-success");
				};
				
				if(tree['serverstatus2'] != 'online' && !$('#tree').hasClass("tree-background-failed"))
				{
					$('#tree').removeClass("tree-background-success");
					$('#tree').addClass("tree-background-failed");
				};
				
				for(var channel of tree['channels'])
				{
					delChannel(channel, tmp);
					setChannel(channel, lastCid, tmp, delTimer, true);
					lastCid				=	channel.cid;
					tmp++;
				};
				
				for(var channel of tree['subChannels'])
				{
					delSubChannel(channel);
					setSubChannel(channel, -1, delTimer, true, lastCid);
					lastCid				=	channel.sub_cid;
				};
				
				for(var client of tree['clients'])
				{
					delClient(client);
					setClient(client, delTimer);
				};
				
				$('.channel[delTimer='+oldDelTimer+']').each(function(){$(this).remove()});
				$('.tree_user[delTimer='+oldDelTimer+']').each(function(){$(this).remove()});
				
				setTimeout(updateTree, treeInterval, delTimer);
			}
		});
	};
};

function delSubChannel(channel)
{
	if(document.getElementById('channel_'+channel.sub_cid))
	{
		var selectedChannel		=	$('#channel_'+channel.sub_cid);
		if(selectedChannel.attr('channelpid') != channel.sub_pid)
		{
			selectedChannel.remove();
			delSubSubChannel(channel.sub_cid);
		};
	};
};

function delSubSubChannel(id)
{
	var tmp						=	id;
	var tmp2					=	"0";
	var checkDone				=	false;
	while(!checkDone)
	{
		checkDone				=	true;
		
		$('.channel[channelpid='+tmp+']').each(function(){
			tmp2				=	$(this).attr('id');
			checkDone			=	false;
			$(this).remove();
		});
		tmp						=	tmp2.replace('channel_', '');
	};
};

function delChannel(channel, tmp)
{
	if(document.getElementById('channel_'+channel.cid))
	{
		var selectedChannel		=	$('#channel_'+channel.cid);
		if(selectedChannel.attr('cidnumber') != tmp)
		{
			selectedChannel.remove();
		};
	};
};

function setChannel(channel, lastCid, tmp, delTimer, update = false)
{
	var channelId									=	document.getElementById('channel_'+channel.cid)
	if(!channelId)
	{
		var inhalt 									= 	'<div delTimer="'+delTimer+'" class="row ts_row channel tree_channel" tabs="0" cidnumber="'+tmp+'" channelpid="'+channel.pid+'" id="channel_'+channel.cid+'"';
		inhalt										+=	'onClick="showChannelInformations(\''+channel.cid+'\')">';
	}
	else
	{
		var inhalt									=	'';
	};
	inhalt											+=		'<div class="col-md-12">';
	if(channel.spacer == '1')
	{
		inhalt										+=			'<div class="col-md-12 col-xs-12" style="overflow:hidden;text-align:'+channel.align+';">';
		inhalt										+=				channel.channelname;
		inhalt										+=			'</div>';
		inhalt										+=		'</div>';
		if(!channelId)
		{
			inhalt									+=	'</div>';
		};
	}
	else
	{
		inhalt										+=			'<div class="col-md-7 col-xs-7">';
		inhalt										+=				channel.img_before;
		inhalt										+=				'&nbsp;&nbsp;' + channel.channelname;
		inhalt										+=			'</div>';
		inhalt										+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
		if(channel.channel_flag_password == 1)
		{
			inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
		}
		if(channel.channel_flag_default == 1)
		{
			inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
		}
		if(channel.channel_codec == 3 || channel.channel_codec == 5)
		{
			inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
		}
		if(channel.channel_needed_talk_power > 0)
		{
			inhalt									+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
		}
		if(channel.channel_icon_id != '')
		{
			inhalt									+=				channel.channel_icon_id;
		}
		inhalt										+=			'</div>';
		inhalt										+=		'</div>';
		if(!channelId)
		{
			inhalt									+=	'</div>';
		};
	};
	
	if(channelId)
	{
		$('#channel_'+channel.cid).attr("delTimer", delTimer);
		document.getElementById('channel_'+channel.cid).innerHTML = inhalt;
	}
	else
	{
		var einzufuegenesObjekt						= 	document.createElement("div");
		einzufuegenesObjekt.innerHTML 				= 	inhalt;
		
		if(lastCid == 0)
		{
			$(inhalt).insertAfter("#header_tree");
		}
		else
		{
			var insertId							=	"#channel_"+lastCid;
			if(typeof($("#channel_"+lastCid).next()) != "undefined")
			{
				if($("#channel_"+lastCid).next().attr('channelpid') != 0 && $("#channel_"+lastCid).next().attr("id") != "tree_content" && update)
				{
					var tmpId							=	"#channel_"+lastCid;
					while(checkPid != 0)
					{
						var checkPid					=	$(tmpId).next().attr('channelpid');
						if(checkPid != 0)
						{
							tmpId						=	"#"+$(tmpId).next().attr('id');
						}
						else
						{
							insertId					=	"#"+$(tmpId).attr('id');
						};
					};
				};
			};
			$(inhalt).insertAfter(insertId);
		};
	};
};

function setSubChannel(channel, wantedSubs, delTimer, update = false, lastCid = -1)
{
	if(typeof(channel) == "undefined")
	{
		return true;
	};
	
	var channelId										=	document.getElementById('channel_'+channel.sub_cid);
	
	if(!document.getElementById('channel_'+channel.sub_pid))
	{
		return false;
	};
	
	if(!channelId)
	{
		var tabs										=	$('#channel_'+channel.sub_pid).attr('tabs');
		tabs++;
	}
	else
	{
		var tabs										=	$('#channel_'+channel.sub_cid).attr('tabs');
	};
	
	if(wantedSubs != tabs && !channelId && !update)
	{
		return false;
	}
	else
	{
		if(!channelId)
		{
			var inhalt 									= 	'<div delTimer="'+delTimer+'" class="row ts_row channel tree_sub_channel" tabs="'+tabs+'" channelpid="'+channel.sub_pid+'" id="channel_'+channel.sub_cid+'"';
			inhalt										+=	'onClick="showChannelInformations(\''+channel.sub_cid+'\')">';
		}
		else
		{
			var inhalt									=	'';
		};
		inhalt											+=		'<div class="col-md-12">';
		inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
		for (var i = 0; i < tabs; i++)
		{
			inhalt										+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
		};
		inhalt											+=				channel.sub_img_before;
		inhalt											+=				'&nbsp;&nbsp;' + channel.channelname;
		inhalt											+=			'</div>';
		inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
		if(channel.sub_channel_flag_password == 1)
		{
			inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/password.png\" alt=\"\" />";
		};
		if(channel.sub_channel_flag_default == 1)
		{
			inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/default.png\" alt=\"\" />";
		};
		if(channel.sub_channel_codec == 3 || channel.sub_channel_codec == 5)
		{
			inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/music.png\" alt=\"\" />";
		};
		if(channel.sub_channel_needed_talk_power > 0)
		{
			inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\"images/ts_viewer/moderated.png\" alt=\"\" />";
		};
		if(channel.sub_channel_icon_id != '')
		{
			inhalt										+=				channel.sub_channel_icon_id;
		};
		inhalt											+=			'</div>';
		inhalt											+=		'</div>';
		if(!channelId)
		{
			inhalt										+=	'</div>';
		};
			
		var einzufuegenesObjekt							= 	document.createElement("div");
		einzufuegenesObjekt.innerHTML 					= 	inhalt;
		
		if(channelId)
		{
			$('#channel_'+channel.sub_cid).attr("delTimer", delTimer);
			channelId.innerHTML 						= 	inhalt;
		}
		else
		{
			var channelBefore							=	'channel_'+channel.sub_pid;
			if(update)
			{
				if($('#'+channelBefore).attr('channelpid') == 0)
				{
					$(inhalt).insertAfter("#"+channelBefore);
				}
				else
				{
					$(inhalt).insertAfter("#channel_"+lastCid);
				};
			}
			else
			{
				var channelBefore							=	'channel_'+channel.sub_pid;
				$('.tree_sub_channel').each(function()
				{
					if($(this).attr('channelpid') == channel.sub_pid)
					{
						channelBefore						=	$(this).attr('id');
					};
				});
				
				if(document.getElementById(channelBefore))
				{
					$(inhalt).insertAfter("#"+channelBefore);
				};
			};
		};
		
		return true;
	};
};

function delClient(client)
{
	var clientId									=	'client_'+client.nick_clid;
	if(document.getElementById(clientId))
	{
		if($('#'+clientId).attr('cid') != client.nick_cid)
		{
			$('#'+clientId).remove();
		};
	};
};

function setClient(client, delTimer)
{
	var clientId									=	document.getElementById('client_'+client.nick_clid);
	
	if(!clientId)
	{
		var inhalt 									= 	'<div delTimer="'+delTimer+'" class="row ts_row tree_user" cid="'+client.nick_cid+'" id="client_'+client.nick_clid+'"';
		inhalt										+=	'onClick="showClientInformations(\''+client.nick_clid+'\')">';
	}
	else
	{
		var inhalt 									= 	'';
	};
	inhalt											+=		'<div class="col-md-12">';
	inhalt											+=			'<div class="col-md-7 col-xs-7">&nbsp;&nbsp;&nbsp;&nbsp;';
	for(i = 0; i <= $('#channel_'+client.nick_cid).attr('tabs'); i++)
	{
		inhalt										+=				'&nbsp;&nbsp;&nbsp;&nbsp;';
	};
	if(client.nick_status == "away")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/away.png\" alt=\"\" />";
	}
	else if(client.nick_status == "hwhead")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwhead.png\" alt=\"\" />";
	}
	else if(client.nick_status == "hwmic")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/hwmic.png\" alt=\"\" />";
	}
	else if(client.nick_status == "head")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/head.png\" alt=\"\" />";
	}
	else if(client.nick_status == "mic")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/mic.png\" alt=\"\" />";
	}
	else if(client.nick_status == "player_command")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander.png\" alt=\"\" />";
	}
	else if(client.nick_status == "player_command_on")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_commander_on.png\" alt=\"\" />";
	}
	else if(client.nick_status == "player_on")
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player_on.png\" alt=\"\" />";
	}
	else
	{
		inhalt										+=				"<img style=\"height:16px;width:16px;\" src=\"images/ts_viewer/player.png\" alt=\"\" />";
	};
	inhalt											+=				'&nbsp;&nbsp;' + client.nickname;
	inhalt											+=				'&nbsp;&nbsp;' + client.nick_away_message;
	inhalt											+=			'</div>';
	inhalt											+=			'<div class="col-md-5 col-xs-5" style="text-align:right;">';
	for(var j in client.cgroup)
	{
		inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+client.cgroup[j]+"\" alt=\"\" />";
	};
	for(var j in client.sgroup)
	{
		inhalt										+=				"<img class=\"right-img\" style=\"height:16px;width:16px;\" src=\""+client.sgroup[j]+"\" alt=\"\" />";
	};
	if(client.nick_country != '')
	{
		inhalt										+=				"<img class=\"right-img\" src=\"images/ts_countries/"+client.nick_country+".png\" alt=\"\" />";
	};
	inhalt											+=			'</div>';
	inhalt											+=		'</div>';
	if(!clientId)
	{
		inhalt										+=	'</div>';
	};
	
	var einzufuegenesObjekt							= 	document.createElement("div");
	einzufuegenesObjekt.innerHTML 					= 	inhalt;
	
	if(document.getElementById('channel_'+client.nick_cid))
	{
		if(clientId)
		{
			$('#client_'+client.nick_clid).attr("delTimer", delTimer);
			clientId.innerHTML 						= 	inhalt;
		}
		else
		{
			$(inhalt).insertAfter("#channel_"+client.nick_cid);
		};
	};
};

function setElementInnerHtml(id, insert)
{
	if(document.getElementById(id))
	{
		document.getElementById(id).innerHTML 					= 	insert;
		return true;
	}
	else
	{
		return false;
	};
}

function showChannelInformations(id)
{
	if(typeof serverId != 'undefined')
	{
		var modal	=	$('#modalChannelView');
		modal.load("./php/teamspeak/web_teamspeak_serverview_modal_channelview.php", {id: id, instanz: instanz, serverId: serverId, port: port}, function()
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
		modal.load("./php/teamspeak/web_teamspeak_serverview_modal_clientview.php", {id: id, instanz: instanz, serverId: serverId, port: port}, function()
		{
			modal.modal('show');
		});
	};
};