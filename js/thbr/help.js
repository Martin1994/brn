function switch_tab(target){
	$(".tab").removeClass("selected");
	$('.tab[target="'+target+'"]').addClass("selected");
	
	$('.block.selected').fadeOut(200, function(){
		$('.block[block="'+target+'"]').fadeIn(200);
	});
	
	$(".block").removeClass("selected");
	$('.block[block="'+target+'"]').addClass("selected");
	
	window.location.hash = target;
}

$(document).ready(function(){
	if($('.tab[target="'+window.location.hash.substr(1)+'"]').size() > 0){
		switch_tab(window.location.hash.substr(1));
		$('.block[block="'+window.location.hash.substr(1)+'"]').fadeIn(200);
	}else{
		switch_tab("intro");
		$('.block[block="intro"]').fadeIn(200);
	}
	$(".tab").click(function(){
		if(!$(this).hasClass("selected")){
			switch_tab($(this).attr("target"));
		}
	});
});