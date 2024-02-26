<?php

function comet_push($content)
{
	return '<script>m('.$content.');</script>';
}

function comet_reconnect()
{
	return '<script>r();</script>';
}

function comet_hello()
{
	return '<script>h();</script>';
}
