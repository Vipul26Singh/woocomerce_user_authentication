function min_perform_otp_task() {
	window.run_preogress_otp_minb = true;
	document.getElementById("min_request_otp").disabled=true;
	document.getElementById("inner-otp-error").style.display='none';
	document.getElementById("min_otp_value_div").style.display = 'block';
	document.getElementById("min_progress_bar_div").style.display = 'block';
	var elem = document.getElementById("min_progress_bar");
	var timeLimit = 1000;
	var width = 1;
	var id = setInterval(frame, timeLimit);
	function frame() {
		
		if(window.run_preogress_otp_minb == false || width >= 100){
			clearInterval(id);
			document.getElementById("min_progress_bar_div").style.display = 'none';
			document.getElementById("initial_button_text").style.display = 'none';
			document.getElementById("final_button_text").style.display = 'block';
			document.getElementById("min_request_otp").disabled=false;
		}else {
			width++;
			elem.style.width = width + '%';
		}
	}
	var mobile_no = document.getElementById("reg_mobile").value;
	callAjaxOtpMinb(mobile_no);	
}

function callAjaxOtpMinb(mobile_no){
	jQuery.ajax({
                        'type': 'POST',
                        'url': 'https://www.minbazaar.com/wp-content/plugins/minbazaar_user_authentication/external/send_otp.php',
                        'data': {'mobile_no':mobile_no, 'auth_key':'ck_bca5ee0c5f916c12896590606abab1c4cee4cc08'},
                         error: function(xhr, status, error) {
                           alert(xhr.responseText);
                            },
                        success: handleSuccessOtpMinb
        });
}

function handleSuccessOtpMinb(php_error){
	window.run_preogress_otp_minb=false;

	if(php_error){
                document.getElementById('inner-otp-error').innerHTML = "<strong>Error:</strong>"+php_error;
                document.getElementById("inner-otp-error").style.display = 'block';
        }else{
		document.getElementById("min_request_otp").style.display = 'none';
	}
}

