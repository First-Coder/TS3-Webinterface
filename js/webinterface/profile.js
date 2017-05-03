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
	Profil Edit: Update Informations
*/
	function profilUpdate(id)
	{
		idContent = $('#'+id).val().replace(/(<([^>]+)>)/ig,"");
		
		if(idContent != '')
		{
			var regex_check				=	true;
			var pw_check				=	true;
			
			// Textfelder auf Fehler prüfen
			if(id == 'profileVorname' || id == 'profileNachname')
			{
				var regex 				=	/^[a-zA-Z0-9_]+$/;
				regex_check				= 	regex.test(idContent);
				
				if(!regex_check)
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_change_name_failed,
						icon: 'fa fa-close'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
			else if(id == 'profilePassword')
			{
				var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,12}$/;
				regex_check				= 	regex.test(idContent);
				
				if(!regex_check)
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_change_pw1_failed,
						icon: 'fa fa-close'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
				
				if(idContent != $('#profilePassword2').val())
				{
					pw_check			=	false;
					
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_change_pw2_failed,
						icon: 'fa fa-close'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			}
			else if(id == 'profileUser')
			{
				var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
				regex_check				= 	regex.test(idContent);
				
				if(!regex_check)
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: hp_user_change_user_failed,
						icon: 'fa fa-close'
					},{
						type: 'danger',
						allow_dismiss: true,
						placement:
						{
							from: 'bottom',
							align: 'right'
						}
					});
				};
			};
			
			if(regex_check && pw_check)
			{
				var dataString	=	"action=updateUser&id="+id+"&content="+idContent;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						if(data == 'done')
						{
							$.notify({
								
								title: '<strong>'+success+'</strong><br />',
								message: settigns_saved,
								icon: 'fa fa-check'
							},{
								type: 'success',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						}
						else
						{
							$.notify({
								
								title: '<strong>'+failed+'</strong><br />',
								message: settings_not_saved,
								icon: 'fa fa-warning'
							},{
								type: 'danger',
								allow_dismiss: true,
								placement:
								{
									from: 'bottom',
									align: 'right'
								}
							});
						};
					}
				});
			};
		};
	};

/*
	Profil Permissions: Refresh Permissions
*/
	function rightsUpdate(which, instanz, port)
	{
		switch(which)
		{
			case 'refresh_global_hp_rights':
				var dataString = 'action=refreshRights&id='+user_session_id;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						var informations		= 	JSON.parse(data);
						
						$('#global_hp_rights_box').fadeTo("slow", 0.4, function()
						{
							if(typeof(informations['right_hp_main']) != 'undefined')
							{
								$('#right_hp_main').removeClass('table-danger');
								$('#right_hp_main').addClass('table-success');
								document.getElementById('right_hp_main_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_hp_main').removeClass('table-success');
								$('#right_hp_main').addClass('table-danger');
								document.getElementById('right_hp_main_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_hp_ts3']) != 'undefined')
							{
								$('#right_hp_ts3').removeClass('table-danger');
								$('#right_hp_ts3').addClass('table-success');
								document.getElementById('right_hp_ts3_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_hp_ts3').removeClass('table-success');
								$('#right_hp_ts3').addClass('table-danger');
								document.getElementById('right_hp_ts3_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_hp_user_create']) != 'undefined')
							{
								$('#right_hp_user_create').removeClass('table-danger');
								$('#right_hp_user_create').addClass('table-success');
								document.getElementById('right_hp_user_create_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_hp_user_create').removeClass('table-success');
								$('#right_hp_user_create').addClass('table-danger');
								document.getElementById('right_hp_user_create_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_hp_user_delete']) != 'undefined')
							{
								$('#right_hp_user_delete').removeClass('table-danger');
								$('#right_hp_user_delete').addClass('table-success');
								document.getElementById('right_hp_user_delete_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_hp_user_delete').removeClass('table-success');
								$('#right_hp_user_delete').addClass('table-danger');
								document.getElementById('right_hp_user_delete_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_hp_user_edit']) != 'undefined')
							{
								$('#right_hp_user_edit').removeClass('table-danger');
								$('#right_hp_user_edit').addClass('table-success');
								document.getElementById('right_hp_user_edit_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_hp_user_edit').removeClass('table-success');
								$('#right_hp_user_edit').addClass('table-danger');
								document.getElementById('right_hp_user_edit_text').innerHTML		=	hp_right_no;
							};
							
							$('#global_hp_rights_box').fadeTo("slow", 1.0);
						});
					}
				});
				break;
			case 'refresh_global_ts_rights':
				var dataString = 'action=refreshRights&id='+user_session_id;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						var informations		= 	JSON.parse(data);
						
						$('#global_ts_rights_box').fadeTo("slow", 0.4, function()
						{
							if(typeof(informations['right_web']) != 'undefined')
							{
								$('#right_web').removeClass('table-danger');
								$('#right_web').addClass('table-success');
								document.getElementById('right_web_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web').removeClass('table-success');
								$('#right_web').addClass('table-danger');
								document.getElementById('right_web_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_web_global_message_poke']) != 'undefined')
							{
								$('#right_web_global_message_poke').removeClass('table-danger');
								$('#right_web_global_message_poke').addClass('table-success');
								document.getElementById('right_web_global_message_poke_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_global_message_poke').removeClass('table-success');
								$('#right_web_global_message_poke').addClass('table-danger');
								document.getElementById('right_web_global_message_poke_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_web_server_create']) != 'undefined')
							{
								$('#right_web_server_create').removeClass('table-danger');
								$('#right_web_server_create').addClass('table-success');
								document.getElementById('right_web_server_create_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_create').removeClass('table-success');
								$('#right_web_server_create').addClass('table-danger');
								document.getElementById('right_web_server_create_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['right_web_server_delete']) != 'undefined')
							{
								$('#right_web_server_delete').removeClass('table-danger');
								$('#right_web_server_delete').addClass('table-success');
								document.getElementById('right_web_server_delete_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_delete').removeClass('table-success');
								$('#right_web_server_delete').addClass('table-danger');
								document.getElementById('right_web_server_delete_text').innerHTML		=	hp_right_no;
							};
							
							$('#global_ts_rights_box').fadeTo("slow", 1.0);
						});
					}
				});
				break;
			case 'refresh_instanz_rights':
				var dataString = 'action=refreshRights&id='+user_session_id;
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						var informations		= 	JSON.parse(data);
						
						$('#instanz_rights_box_'+instanz+'_'+port).fadeTo("slow", 0.4, function()
						{
							if(typeof(informations['ports']['right_web_server_edit'][instanz]) != 'undefined' && informations['ports']['right_web_server_edit'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_edit_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_edit_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_edit_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_edit_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_edit_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_edit_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_start_stop'][instanz]) != 'undefined' && informations['ports']['right_web_server_start_stop'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_start_stop_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_start_stop_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_start_stop_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_start_stop_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_start_stop_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_start_stop_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_message_poke'][instanz]) != 'undefined' && informations['ports']['right_web_server_message_poke'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_message_poke_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_message_poke_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_message_poke_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_message_poke_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_message_poke_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_message_poke_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_mass_actions'][instanz]) != 'undefined' && informations['ports']['right_web_server_mass_actions'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_mass_actions_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_mass_actions_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_mass_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_mass_actions_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_mass_actions_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_mass_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_protokoll'][instanz]) != 'undefined' && informations['ports']['right_web_server_protokoll'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_protokoll_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_protokoll_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_protokoll_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_protokoll_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_protokoll_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_protokoll_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_icons'][instanz]) != 'undefined' && informations['ports']['right_web_server_icons'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_icons_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_icons_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_icons_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_icons_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_icons_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_icons_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_bans'][instanz]) != 'undefined' && informations['ports']['right_web_server_bans'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_bans_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_bans_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_bans_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_bans_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_bans_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_bans_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_token'][instanz]) != 'undefined' && informations['ports']['right_web_server_token'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_token_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_token_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_token_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_token_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_token_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_token_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_backups'][instanz]) != 'undefined' && informations['ports']['right_web_server_backups'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_backups_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_backups_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_backups_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_backups_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_backups_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_backups_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_server_clients'][instanz]) != 'undefined' && informations['ports']['right_web_server_clients'][instanz].indexOf(port) != -1)
							{
								$('#right_web_server_clients_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_server_clients_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_server_clients_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_yes;
							}
							else
							{
								$('#right_web_server_clients_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_server_clients_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_server_clients_'+instanz+'_'+port+'_text').innerHTML			=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_client_actions'][instanz]) != 'undefined' && informations['ports']['right_web_client_actions'][instanz].indexOf(port) != -1)
							{
								$('#right_web_client_actions_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_client_actions_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_client_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_client_actions_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_client_actions_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_client_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_client_rights'][instanz]) != 'undefined' && informations['ports']['right_web_client_rights'][instanz].indexOf(port) != -1)
							{
								$('#right_web_client_rights_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_client_rights_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_client_rights_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_client_rights_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_client_rights_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_client_rights_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							if(typeof(informations['ports']['right_web_channel_actions'][instanz]) != 'undefined' && informations['ports']['right_web_channel_actions'][instanz].indexOf(port) != -1)
							{
								$('#right_web_channel_actions_'+instanz+'_'+port).removeClass('table-danger');
								$('#right_web_channel_actions_'+instanz+'_'+port).addClass('table-success');
								document.getElementById('right_web_channel_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_yes;
							}
							else
							{
								$('#right_web_channel_actions_'+instanz+'_'+port).removeClass('table-success');
								$('#right_web_channel_actions_'+instanz+'_'+port).addClass('table-danger');
								document.getElementById('right_web_channel_actions_'+instanz+'_'+port+'_text').innerHTML		=	hp_right_no;
							};
							
							$('#instanz_rights_box_'+instanz+'_'+port).fadeTo("slow", 1.0);
						});
					}
				});
				break;
		};
	};