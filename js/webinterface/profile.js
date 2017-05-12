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

function profilUpdate(id){if(id=='profileUser'||id=='profilePassword'?idContent=$('#'+id).val():idContent=encodeURIComponent($('#'+id).val()),idContent!=''){var regex_check=!0;var pw_check=!0;if(id=='profileVorname'||id=='profileNachname'){var regex=/^[a-zA-Z0-9_]+$/;regex_check=regex.test(idContent),regex_check||setNotifyFailed(lang.change_name_failed);}else if(id=='profilePassword'){var regex=/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;regex_check=regex.test(idContent),regex_check||setNotifyFailed(lang.change_pw1_failed),idContent!=$('#profilePassword2').val()&&(pw_check=!1,setNotifyFailed(lang.change_pw2_failed));}else if(id=='profileUser'){var regex=/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;regex_check=regex.test(idContent),regex_check||setNotifyFailed(lang.change_user_failed);}regex_check&&pw_check&&$.ajax({type:'POST',url:'./php/functions/functionsSqlPost.php',data:{action:'updateUser',id:id,content:idContent},success:function(data){data=='done'?setNotifySuccess(lang.settigns_saved):setNotifyFailed(lang.settings_not_saved);}});}}