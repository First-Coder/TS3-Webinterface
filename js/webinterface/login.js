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
	If someone push ENTER
*/
	$('#loginUser').keypress(function(e)
	{
		if (e.which == 13)
		{
			loginUser();
			e.preventDefault();
		};
	});
	
	$('#loginPw').keypress(function(e)
	{
		if (e.which == 13) 
		{
			loginUser();
			e.preventDefault();
		};
	});

/*
	Free Register
*/
	$('#registerBtn').click(function()
	{
		createUser();
	});
	
	function createUser()
	{
		var pwContent			=	$('#loginPw').val();
		var mailContent			=	$('#loginUser').val();
		var regex_check_mail	=	true;
		var regex_check_pw		=	true;
		
		// Benutzer pruefen
		var regex 				=	/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
		regex_check_mail		= 	regex.test(mailContent);
		
		if(!regex_check_mail)
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: hp_user_change_user_failed,
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
		
		// Password pruefen
		var regex 				=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
		regex_check_pw			= 	regex.test(pwContent);
		
		if(!regex_check_pw)
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: hp_user_change_pw1_failed,
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
		
		if(regex_check_pw && regex_check_mail)
		{
			// Werte übergeben
			var dataString 		= 	'action=createUser&username='+mailContent+'&password='+pwContent;
			
			$.ajax({
				type: "POST",
				url: "functionsPost.php",
				data: dataString,
				cache: true,
				async: true,
				success: function(data){
					if(data == 'done')
					{
						loginUser();
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
	Login
*/
	$('#loginBtn').click(function()
	{
		loginUser();
	});
	
	function loginUser()
	{
		if(sessionStorage.getItem("login_try") < 3)
		{
			var username	=	$("#loginUser").val();
			var password	=	$("#loginPw").val();
			
			var dataString = 'action=loginUser&username='+username+'&password='+password;
			if($.trim(username).length>0 && $.trim(password).length>0)
			{
				$.ajax({
					type: "POST",
					url: "functionsPost.php",
					data: dataString,
					cache: true,
					async: true,
					success: function(data)
					{
						session_logintry	=	sessionStorage.getItem("login_try");
						if(typeof session_logintry == 'object')
						{
							sessionStorage.setItem("login_try", "1");
						};
						
						if(data > 0 && data != 1337)
						{
							$(".preloader").fadeIn("slow", function()
							{
								if ('replaceState' in history)
								{
									history.replaceState(null, document.title, "index.php?web_profil_dashboard");
								};
								
								$("#hp").load("web_main.php");
							});
						}
						else if(data == 1337)
						{
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: user_is_blocked+"<br /><font size=2px>"+user_blocked_info+"</font>",
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
							switch(session_logintry)
							{
								case '0':		sessionStorage.setItem("login_try", "1");break;
								case '1':		sessionStorage.setItem("login_try", "2");break;
								case '2':		sessionStorage.setItem("login_try", "3");break;
							};
							session_logintry	=	sessionStorage.getItem("login_try");
							
							$.notify({
								title: '<strong>'+failed+'</strong><br />',
								message: user_or_pw_wrong+"<br /><font size=2px>"+session_logintry+"/3 "+login_try+"</font>",
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
			}
			else
			{
				$.notify({
					title: '<strong>'+failed+'</strong><br />',
					message: write_user_and_pw,
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
		else
		{
			$.notify({
				title: '<strong>'+failed+'</strong><br />',
				message: user_session_blocked+"<br /><font size=2px>"+user_session_blocked_info+"</font>",
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
	};