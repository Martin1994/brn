<?php

//MOD名
$mod_name = 'BRN';

//MOD版本
$mod_version = '1.0';

//MOD作者
$mod_author = array(
	array('name' => 'Martin Chloride', 'url' => 'http://martincl2.me')
	);

//MOD协议
$mod_license = array('name' => 'CC-BY-SA 协议', 'url' => 'http://creativecommons.org/licenses/by-sa/3.0/legalcode');

//MOD补充说明
$mod_extra_info = '';

//公告
$bulletin = '欢迎使用 BRN 引擎';

//模板文件夹
$template_dir = ROOT_DIR.'/template';

//模板名
$template_name = 'default';

//网页标题
$page_title = 'BR 大逃杀';

//网页标头
$page_header = 'B R 大 逃 杀';

//禁区时间（秒）
$round_time = 1800;

//生命、体力存储精度（倍数）
$health_accuracy = 1000;

//币种
$currency = '元';

//治疗速度
$heal_rate = array(
	'hp' => 1, //每秒生命回复速度
	'sp' => 2 //每秒体力回复速度
	);

//毒相关参数
$poison = array(
	'Hlast' => 1, //食品中毒的默认持续时间（治疗倍数）
	'Wlast' => 1, //被淬毒武器击中后的持续时间（攻击倍数）
	'Wlast_min' => 30, //被淬毒武器击中后的最小持续时间（秒）
	'Wturn' => 10, //武器淬毒的有效回合数
	'recover' => false, //中毒后是否还有自动生命回复
	'damage' => 1 //中毒后每秒受到的伤害
	);

//NPC自动回血
$npc_recover = true;

//基础经验值等级区间
$base_exp = 9;

//基础物品寻找几率
$item_found_rate = 60;

//基础敌人遭遇几率
$enemy_found_rate = 70;

//基础尸体遭遇几率
$corpse_found_rate = 50;

//命中率
$base_hit_rate = array(
	'p' => 80,
	'k' => 80,
	'g' => 75,
	'c' => 100,
	'd' => 70
	);

//命中率每点熟练增加
$extra_hit_rate = array(
	'p' => 0.05,
	'k' => 0.05,
	'g' => 0.025,
	'c' => 0,
	'd' => 0.075
	);

//各种攻击方式可能导致受伤的部位
$hurt_position = array(
	'p' => array('h', 'a'),
	'k' => array('b', 'h', 'a'),
	'g' => array('b', 'h', 'a', 'f'),
	'c'=> array('h', 'a'),
	'd' => array('b', 'h', 'a', 'f'),
	'fist' => array()
	);

//致伤率
$hurt_rate = array('p' => 15, 'k' => 30, 'g' => 30, 'c' => 15, 'd' => 40, 'fist' => 0);

//数值名（仅用于显示）
$healthinfo = array(
	'hp' => '生命',
	'sp' => '体力'
	);

//地方生命状态（仅用于显示）
$hp_status = array(
	'normal' => '通常',
	'attention' => '注意',
	'dangerous' => '危险',
	'dead' => '死亡'
	);

//基础姿态名（仅用于显示）
$poseinfo = Array(
	'通常'
	);

//应战方针名（仅用于显示）
$tacticinfo = Array(
	'通常'
	);

//行动名（仅用于显示）
$actioninfo = array(
	'move' => '移动',
	'search' => '搜索',
	'create_team' => '创建队伍',
	'join_team' => '加入队伍'
	);

//柜台名
$shopinfo = array(
	0 => '补给品',
	1 => '刀具',
	2 => '投掷物',
	3 => '易爆品',
	4 => '钝器',
	5 => '枪械',
	6 => '护甲',
	7 => '道具'
	);

//空手时的武器（仅用于显示）
$fist = '拳头';

//雾天头像
$fog_avatar = 'img/question.gif';

//头像数量
$icon_num = array(
	'f' => 20,
	'm' => 21
	);

//社团名
$clubinfo = Array(
	0 => '无',
	1 => '回家社'
	);

//性别
$genderinfo = array(
	'f' => '女',
	'm' => '男'
	);

//地图
$map = array(
	0 => '基地',
	1 => '外围'
	);

//天气名字
$weatherinfo = array(
	0 => '晴'
	);

//物品类型（仅用于显示）
$iteminfo = Array(
	'WP' => '武器(殴)',
	'WG' => '武器(射)',
	'WK' => '武器(斩)',
	'WC' => '武器(投)',
	'WD' => '武器(爆)',
	'DB' => '防具(体)',
	'DH' => '防具(头)',
	'DA' => '防具(腕)',
	'DF' => '防具(足)',
	'A'  => '饰品',
	'HH' => '生命恢复',
	'HS' => '体力恢复',
	'HB' => '命体恢复',
	'PH' => '生命恢复',
	'PS' => '体力恢复',
	'PB' => '命体恢复',
	'R' => '雷达',
	'TO' => '陷阱',
	'TN' => '陷阱',
	'Y' => '特殊',
	'GB' => '弹药',
	'default' => '物品'
	);

//物品类型所对应的装备位置
$equipment_position = array(
	'WP' => 'wep',
	'WK' => 'wep',
	'WG' => 'wep',
	'WC' => 'wep',
	'WD' => 'wep',
	'DB' => 'arb',
	'DH' => 'arh',
	'DA' => 'ara',
	'DF' => 'arf',
	'A' => 'art'
	);

//武器类型的攻击方式（显示用）
$weapon_types = array(
	'p' => '殴',
	'k' => '斩',
	'g' => '射',
	'c' => '投',
	'd' => '爆'
	);

//每点熟练度所增加的攻击系数
$proficiency_modulus = array(
	'p' => 0.05,
	'k' => 0.05,
	'g' => 0.06,
	'c' => 0.03,
	'd' => 0.04
	);

//每种熟练度的基础攻击系数
$proficiency_intercept = array(
	'p' => 1,
	'k' => 1,
	'g' => 1,
	'c' => 1,
	'd' => 1
	);

//buff名字
$buff_name = array(
	'poison' => '中毒'
	);

//空物品
$null_item = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());

//可随机到的天气数量
$normal_weather = 1;
	
//商店位置
$shopmap = array(0);
	
//诊所位置
$clinicmap = array(
	//MapID => Multiple
	0 => 2
	);

//每禁（回合）增加的禁区数
$round_area = 3;

//连斗所需的回合数
$combo_round = 3;

//各行动的消耗
$consumption = array(
	'search' => array('sp' => 15),
	'move' => array('sp' => 15),
	'create_team' => array('sp' => 25), //创建完队伍后会自动加入，因此会消耗两次体力
	'join_team' => array('sp' => 25)
	);

//武器攻击系数
$weapon_modulus = array(
	'P' => 2,
	'K' => 2,
	'G' => 1,
	'C' => 2,
	'D' => 2,
	'default' => 2
	);

//默认弹夹容量
$clip = 12;

//武器损耗率（有限耐物品）
$attrit_rate = array(
	'WP' => 10,
	'WK' => 10,
	'default' => 100
	);

//武器损耗率（无限耐物品）
$mar_rate = array(
	'WP' => 20,
	'WK' => 20,
	'default' => 20
	);

//物品合成列表
$mixinfo = array(
	array('stuff' => array('水','地雷'),'result' => array('水鸳鸯','WD',10,12),),
	array('stuff' => array('简易雷达','天线'),'result' => array('雷达','R',1,1,2),),
	array('stuff' => array('手机','笔记本电脑'),'result' => array('移动PC','Y',1,1),)
	);

//攻击系数
$modulus_attack = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//防御系数
$modulus_defend = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//命中率系数
$modulus_hit_rate = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//遇敌率系数
$modulus_find = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//躲避系数
$modulus_hide = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//结局
$ending_type = array(
	'error' => '<span class="error">游戏故障</span>',
	'timeup' => '<span class="dieout">全灭</span>',
	'survive' => '<span class="survive">最后幸存</span>',
	'restart' => '<span class="restart">游戏重设</span>'
	);
?>