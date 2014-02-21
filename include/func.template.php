<?php

function render_page($page, $tpl = false)
{
	template('Header', $tpl);
	template($page, $tpl);
	template('Footer', $tpl);
	include(ROOT_DIR.'/include/inc.release.php');
}

function template($page, $tpl = false)
{
	if(false === $tpl){
		$tpl = get_user_template();
	}
	$dir = $GLOBALS['template_dir'].'/'.$tpl.'/';
	
	if(file_exists($dir.$page.'.html')){
		include($dir.$page.'.html');
	}else{
		include($dir.'settings.php');
		if(!$template_parent){
			throw_error($page.' doesn\'t exist in '.$tpl);
		}
		template($page, $template_parent);
	}
}

function get_user_template()
{
	global $template_name;
	return $template_name;
}

?>