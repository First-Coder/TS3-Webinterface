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

function addModerator(){$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'addModerator',value:encodeURIComponent($("#ticketAddRoleText").val())},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_add_moderator);ticketInit()}else{setNotifyFailed(data)}}})};function editModerator(id){var value=$("#ticketEditRoleText"+id).val(),oldValue=$("#ticketEditRoleText"+id).attr("oldValue");if(value==""){deleteModerator(id)}else{$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'editModerator',value:encodeURIComponent(value),oldValue:encodeURIComponent(oldValue)},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_edit_moderator);$("#ticketEditRoleText"+id).attr("oldValue",value)}else{setNotifyFailed(data)}}})}};function deleteModerator(id){$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'deleteModerator',oldValue:encodeURIComponent($("#ticketEditRoleText"+id).attr("oldValue"))},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_delete_moderator);$("#ticketEditRoleBox"+id).remove()}else{setNotifyFailed(data)}}})};function createTicket(){if($('#ticketBetreff').val()==''||$('#ticketMessage').val()==''){setNotifyFailed(lang.ticket_fill_all)}else{$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'addTicket',subject:encodeURIComponent($('#ticketBetreff').val()),message:encodeURIComponent($('#ticketMessage').val()),department:encodeURIComponent($('#ticketBereich').val())},success:function(data){if(data==lang.ticket_create){setNotifySuccess(data);ticketInit()}else{setNotifyFailed(data)}}})}};function deleteTicket(id){$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'deleteTicket',id:id},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_deleted);$('#MainTicket'+id).remove()}else{setNotifyFailed(data)}}})};function closeTicket(id){$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'closeTicket',id:id},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_close);$('#answerbox'+id).remove();$('#closedDate'+id).text("Just now");$('#status'+id).removeClass("label-success");$('#status'+id).addClass("label-danger");if(document.getElementById("deleteTicketSection"+id)){$('#deleteTicketSection'+id).show()};document.getElementById("status"+id).innerHTML='<i class="fa fa-close"></i> '+lang.closed}else{setNotifyFailed(data)}}})};function answerTicket(id){var msg=$('#answer'+id).val();if(msg==""){setNotifyFailed(lang.ticket_fill_all)}else{$.ajax({type:"POST",url:"./php/functions/functionsTicketPost.php",data:{action:'answerTicket',id:id,msg:encodeURIComponent(msg)},success:function(data){if(data=='done'){setNotifySuccess(lang.ticket_answer);ticketInit()}else{setNotifyFailed(data)}}})}}