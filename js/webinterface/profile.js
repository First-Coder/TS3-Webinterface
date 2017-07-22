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
		if(id == 'profileUser' || id == 'profilePassword')
		{
			idContent = $('#'+id).val();
		}
		else
		{
			idContent = encodeURIComponent($('#'+id).val());
		};
		
		if(idContent != '')
		{
			var regex_check				=	true;
			var pw_check				=	true;
			
			if(id == 'profileVorname' || id == 'profileNachname')
			{
				var regex 				=	/^[a-zA-Z0-9_]+$/;
				regex_check				= 	regex.test(idContent);
				
				if(!regex_check)
				{
					setNotifyFailed(lang.change_name_failed);
				};
			}
			else if(id == 'profilePassword')
			{
				var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
				regex_check				= 	regex.test(idContent);
				
				if(!regex_check)
				{
					setNotifyFailed(lang.change_pw1_failed);
				};
				
				if(idContent != $('#profilePassword2').val())
				{
					pw_check			=	false;
					setNotifyFailed(lang.change_pw2_failed);
				};
			}
			else if(id == 'profileUser')
			{
				regex_check				= 	emailRegex.test(idContent);
				
				if(!regex_check)
				{
					setNotifyFailed(lang.change_user_failed);
				};
			};
			
			if(regex_check && pw_check)
			{
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsSqlPost.php",
					data: {
						action:		'updateUser',
						id:			id,
						content:	idContent
					},
					success: function(data)
					{
						if(data == 'done')
						{
							setNotifySuccess(lang.settigns_saved);
						}
						else
						{
							setNotifyFailed(lang.settings_not_saved);
						};
					}
				});
			};
		};
	};