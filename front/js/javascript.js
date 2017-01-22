function min_perform_otp_task() {
	document.getElementById("min_request_otp").disabled=true;
	document.getElementById("min_otp_value_div").style.display = 'block';
	document.getElementById("min_progress_bar_div").style.display = 'block';
	var elem = document.getElementById("min_progress_bar");
	var timeLimit = 10;
	var width = 1;
	var id = setInterval(frame, timeLimit);
	function frame() {
		if(width >= 100){
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
	var php_error;

	$.ajax({
			'type': 'POST',
			'url': 'https://www.minbazaar.com/wp-content/plugins/minbazaar_user_authentication/external/send_otp.php',
			'data': {'mobile_no':mobile_no},
			error: function(xhr, status, error) {
				alert(xhr.responseText);
			},
			success: function(data) {
				php_error = data;
			}
	});

	alert(php_error);

}

