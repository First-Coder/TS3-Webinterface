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
	Disclaimer and Support
*/
	$( "#showSupport" ).click(function() {
		if($(this).html().indexOf("fa-thumbs-up") != -1)
		{
			$('#otherContent').slideUp("fast", function()
			{
				$('#mitwirkende').slideDown("fast");
				document.getElementById('showSupport').innerHTML = '<i class="fa fa-arrow-left"></i> '+lang.back;
			});
		}
		else
		{
			$('#mitwirkende').slideUp("fast", function()
			{
				$('#otherContent').slideDown("fast");
				document.getElementById('showSupport').innerHTML = '<i class="fa fa-thumbs-up"></i> '+lang.mitwirkende+' & Disclaimer';
			});
		};
	});
	
/*
	Forgot password
*/
	function forgotPassword()
	{
		var mailContent			=	$('#loginUser').val(),
			regex_check_mail	= 	emailRegex.test(mailContent);
		
		if(!regex_check_mail)
		{
			setNotifyFailed(lang.change_user_failed);
		}
		else
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'forgotPassword',
					username:	mailContent
				},
				success: function(data){
					if(data == "done")
					{
						setNotifySuccess(lang.password_reset_success);
					}
					else
					{
						setNotifyFailed(lang.password_reset_failed);
					};
				}
			});
		};
	};

/*
	Free Register
*/
	function createUser()
	{
		var	pwContent			=	$('#loginPw').val(),
			mailContent			=	$('#loginUser').val(),
			regex_check_mail	=	true,
			regex_check_pw		=	true,
			regex_check_mail	= 	emailRegex.test(mailContent);
		
		if(!regex_check_mail)
		{
			setNotifyFailed(lang.change_user_failed);
		};
		
		regex 					=	/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,100}$/;
		regex_check_pw			= 	regex.test(pwContent);
		
		if(!regex_check_pw)
		{
			setNotifyFailed(lang.change_pw1_failed);
		};
		
		if(regex_check_pw && regex_check_mail)
		{
			$.ajax({
				type: "POST",
				url: "./php/functions/functionsSqlPost.php",
				data: {
					action:		'createUser',
					username:	mailContent,
					password:	pwContent
				},
				success: function(data){
					if(data == 'done')
					{
						loginUser();
					}
					else
					{
						setNotifyFailed(data);
					};
				}
			});
		};
	};
	
	
/*
	Login
*/
	function loginUser()
	{
		if(sessionStorage.getItem("login_try") < 3)
		{
			var username	=	$("#loginUser").val();
			var password	=	$("#loginPw").val();
			
			if($.trim(username).length>0 && $.trim(password).length>0)
			{
				$.ajax({
					type: "POST",
					url: "./php/functions/functionsSqlPost.php",
					data: {
						action:		'loginUser',
						username:	username,
						password:	password
					},
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
								
								$("#hp").load("./php/main/web_main.php");
							});
						}
						else if(data == 1337)
						{
							setNotifyFailed(lang.user_is_blocked+"<br /><font size=2px>"+lang.user_blocked_info+"</font>");
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
							
							setNotifyFailed(lang.user_or_pw_wrong+"<br /><font size=2px>"+session_logintry+"/3 "+lang.trys+"</font>");
						};
					}
				});
			}
			else
			{
				setNotifyFailed(lang.write_user_and_pw);
			};
		}
		else
		{
			setNotifyFailed(lang.user_session_blocked+"<br /><font size=2px>"+lang.user_session_blocked_info+"</font>");
		};
	};