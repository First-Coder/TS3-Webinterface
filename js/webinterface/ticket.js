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
	Add Moderator
*/
	function addModerator()
	{
		var value 		=	$("#ticketAddRoleText").val();
		
		var dataString 		= 	'action=addModerator&value='+value;
		
		$.ajax({
			type: "POST",
			url: "functionsTicketPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ticket_add_moderator,
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
					
					$('html').animate({scrollTop:0}, 'slow');	//IE, FF
					$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
					
					ticketInit();
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
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

/*
	Moderator edit
*/
	function editModerator(id)
	{
		var value 		=	$("#ticketEditRoleText"+id).val();
		var oldValue	=	$("#ticketEditRoleText"+id).attr("oldValue");
		
		if(value == "")
		{
			deleteModerator(id);
		}
		else
		{
			var dataString 		= 	'action=editModerator&value='+value+'&oldValue='+oldValue;
			
			$.ajax({
				type: "POST",
				url: "functionsTicketPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ticket_edit_moderator,
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
						
						$("#ticketEditRoleText"+id).attr("oldValue", value);
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
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

/*
	Delete Moderator
*/
	function deleteModerator(id)
	{
		var oldValue	=	$("#ticketEditRoleText"+id).attr("oldValue");
		var dataString 	= 	'action=deleteModerator&oldValue='+oldValue;
		
		$.ajax({
			type: "POST",
			url: "functionsTicketPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ticket_delete_moderator,
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
					
					$("#ticketEditRoleBox"+id).remove();
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
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
	
/*
	Ticketslide
*/
	function slideTicket(id, icon)
	{
		var icon		=	$('#'+icon);
		var box 		=	$('#'+id);
		
		if(icon.hasClass("fa-arrow-right"))
		{
			icon.removeClass("fa-arrow-right");
			icon.addClass("fa-arrow-down");
			
			box.slideDown("slow");
		}
		else
		{
			icon.removeClass("fa-arrow-down");
			icon.addClass("fa-arrow-right");
			
			box.slideUp("slow");
		};
	};
	
/*
	Ticket erstellen
*/
	function createTicket(id, benutzer, vorname, nachname)
	{
		var betreff = $('#ticketBetreff').val();
		var bereich = $('#ticketBereich').val();
		var message = $('#ticketMessage').val();
		
		if(betreff == '' || message == '')
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: ticket_fill_all,
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
		}
		else
		{
			var dataString 		= 	'action=addTicket&pk='+id+'&subject='+betreff+'&message='+message+'&department='+bereich;
			
			$.ajax({
				type: "POST",
				url: "functionsTicketPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ticket_create,
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
						
						$('html').animate({scrollTop:0}, 'slow');	//IE, FF
						$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
						
						ticketInit();
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
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
	
/*
	Ticket loeschen
*/
	function deleteTicket(id)
	{
		var dataString 		= 	'action=deleteTicket&id='+id;
		
		$.ajax({
			type: "POST",
			url: "functionsTicketPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ticket_deleted,
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
					
					$('#MainTicket'+id).remove();
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
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
	
/*
	Ticket schliessen
*/
	function closeTicket(id)
	{
		var dataString 		= 	'action=closeTicket&id='+id;
		
		$.ajax({
			type: "POST",
			url: "functionsTicketPost.php",
			data: dataString,
			cache: true,
			async: true,
			success: function(data){
				if(data == 'done')
				{
					$.notify({
						title: '<strong>'+success+'</strong><br />',
						message: ticket_close,
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
					
					$('#answerbox'+id).remove();
					$('#closedDate'+id).text("Just now");
					$('#status'+id).removeClass("label-success");
					$('#status'+id).addClass("label-danger");
					$('#deleteTicketSection'+id).show();
					
					document.getElementById("status"+id).innerHTML = '<i class="fa fa-close"></i> '+closedText;
				}
				else
				{
					$.notify({
						title: '<strong>'+failed+'</strong><br />',
						message: data,
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
	
/*
	Ticket Antworten
*/
	function answerTicket(id, pk, moderator)
	{
		var msg 	= 	$('#answer'+id).val();
		
		if(msg == "")
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: ticket_fill_all,
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
		}
		else
		{
			var dataString 		= 	'action=answerTicket&id='+id+'&pk='+pk+'&moderator='+moderator+'&msg='+msg;
			
			$.ajax({
				type: "POST",
				url: "functionsTicketPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						$.notify({
							title: '<strong>'+success+'</strong><br />',
							message: ticket_answer,
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
						
						$('html').animate({scrollTop:0}, 'slow');	//IE, FF
						$('body').animate({scrollTop:0}, 'slow');	//chrome, don't know if Safari works
						
						ticketInit();
					}
					else
					{
						$.notify({
							title: '<strong>'+failed+'</strong><br />',
							message: data,
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