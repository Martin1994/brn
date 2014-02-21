<?php

$GLOBALS['new_player'] = array(
	'capacity' => 5,
	'mhp' => 200,
	'msp' => 300,
	'baseatt' => 100,
	'basedef' => 100,
	'proficiency' => 0,
	'area' => 0,
	'money' => 200
	);
	
$GLOBALS['clubskill'] = array(
	1 => 'Pro_P',
	2 => 'Pro_K',
	3 => 'Pro_G',
	4 => 'Pro_C',
	5 => 'Pro_D',
	6 => 'Pheidippides',
	7 => 'Hacker',
	8 => 'Glutton',
	9 => 'Wrath',
	10 => 'Detector'
	);

function i($itm, $itmk, $itme, $itms, $itmsk = array())
{
	return array('n' => $itm, 'k' => $itmk, 'e' => $itme, 's' => $itms, 'sk' => $itmsk);
}

function r($set)
{
	if(isset($GLOBALS['randomitem'][$set])){
		$size = sizeof($GLOBALS['randomitem'][$set]);
		return $GLOBALS['randomitem'][$set][mt_rand(0, $size - 1)];
	}else{
		return r('default');
	}
}

$GLOBALS['randomitem'] = array(
	'WP' => array(
		i('', '', 0, 0), //空手
		i('球棒', 'WP', 15, 10),
		i('球拍', 'WP', 15, 7),
		i('高尔夫球杆', 'WP', 20, 5)
		),
	
	'WK' => array(
		i('菜刀', 'WK', 15, 10),
		i('剪刀', 'WK', 10, 7),
		i('美工刀', 'WK', 5, 5)
		),
	
	'WC' => array(
		i('扑克牌', 'WC', 1, 52),
		i('鸡蛋', 'WC', 1, 8),
		i('游戏代币', 'WC', 5, 20)
		),
	
	'WD' => array(
		i('粗提炼三硝基甲苯', 'WD', 75, 3),
		i('鞭炮', 'WD', 15, 20),
		i('瘴气瓶', 'WD', 0, 5, array('poison' => 0))
		),
	
	'weapon' => array(
		i('铁管', 'WP', 75, 3),
		i('玻璃碎片', 'WK', 15, 20),
		i('气枪', 'WG', 20, 6, array('ammo' => 6)),
		i('石子', 'WC', 5, 10),
		i('花瓶', 'WP', 10, 1),
		i('木剑', 'WK', 10, 5),
		i('锤子', 'WP', 25, 5),
		i('锈铁皮', 'WK', 10, 20, array('poison' => 30)),
		i('铁皮', 'WK', 15, 20),
		i('书包', 'WP', 15, 5)
		),
	
	'default' => array(
		i('蓝屏', 'Y', '1', '1')
		)
	);
$GLOBALS['universalpackage'] = array(
	'item' => array(i('面包', 'HH', 50, 2), i('矿泉水', 'HS', 30, 5))
	);

$GLOBALS['genderpackage'] = array(
	'f' => array(
		'arb' => i('水手服', 'DB', 10, 25)
		),
	
	'm' => array(
		'arb' => i('校服', 'DB', 25, 10)
		)
	);

$GLOBALS['clubpackage'] = array(
	1 => array(
		'wep' => r('WP'),
		'ara' => i('搏斗护手', 'DA', 5, 25, array('pugilism' => 10))
		),
	
	2 => array(
		'wep' => r('WK')
		),
	
	3 => array(
		'wep' => r('weapon'),
		'item' => array(i('子弹', 'GB', 1, 24))
		),
	
	4 => array(
		'wep' => r('WC')
		),
	
	5 => array(
		'wep' => r('WD')
		),
	
	6 => array(
		'wep' => r('weapon'),
		'arf' => i('钉鞋', 'DF', 10, 10)
		),
	
	7 => array(
		'wep' => i('Linux Live CD', 'WC', 1, 1)
		),
	
	8 => array(
		'wep' => r('weapon'),
		'item' => array(i('毒药', 'Y', 1, 10), i('解毒剂', 'Y', 1, 1))
		),
	
	9 => array(
		'wep' => r('weapon'),
		'arb' => i('Cosplay', 'DB', 25, 25)
		),
	
	10 => array(
		'wep' => r('weapon'),
		'ara' => i('手表', 'DA', 5, 20)
		),
	
	'default' => array(
		'wep' => r('weapon')
		)
	);

?>