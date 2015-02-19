function enter_change_avatar(gender, icon){
	var uri;
	if(icon == 0){
		uri = "img/thbr/random.gif";
	}else {
		uri = "img/thbr/" + gender + "_" + icon + ".png";
	}
	$("#F-enter-avatar").attr("src", uri);
}

function enter_submit(e){
	e.preventDefault();
	gender = $("#F-enter-form input[name='gender']:checked").val();
	icon = $("#F-enter-form .icon-selector select#F-enter-form-icon-"+gender).children('option:selected').val();
	request('enter_game', {
		icon : icon,
		gender : gender,
		motto : $("#F-enter-form-motto").val(),
		killmsg : $("#F-enter-form-killmsg").val(),
		lastword : $("#F-enter-form-lastword").val(),
		club : $("#F-enter-form-club").val()
	});
}