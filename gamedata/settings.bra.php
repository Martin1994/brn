<?php

//MOD名
$mod_name = 'Battle Royale Advanced+';

//MOD版本
$mod_version = '0.1 beta';

//MOD作者
$mod_author = array(
	array('name' => 'Martin Chloride', 'url' => 'http://martincl2.me')
	);

//MOD协议
$mod_license = array('name' => 'CC-BY-SA 协议', 'url' => 'http://creativecommons.org/licenses/by-sa/3.0/legalcode');

//MOD补充说明
$mod_extra_info = '';

//公告
$bulletin = '欢迎来到全新的 Battle Royale Advanced+ 服务器';

//网页标题
$page_title = '生 存 游 戏';

//网页标头
$page_header = '生 存 游 戏';

//禁区时间（秒）
$round_time = 3600;

//地图图像
$map_image_url = 'img/bra/map.png';

//头像文件夹
$avatar_dir = 'bra';

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

//buff名字
$buff_name = array_merge($buff_name, array(
	'injured_body' => '胸部受伤',
	'injured_head' => '头部受伤',
	'injured_arm' => '腕部受伤',
	'injured_foot' => '足部受伤'
	));

//buff说明
$buff_help = array_merge($buff_help, array(
	'injured_body' => '防御力下降，治疗速度减半，再次受伤会撕裂伤口',
	'injured_head' => '准确率下降，再次受伤会撕裂伤口',
	'injured_arm' => '攻击力下降，探索体力消耗增加，再次受伤会撕裂伤口',
	'injured_foot' => '移动体力消耗增加，再次受伤会撕裂伤口'
	));

//NPC自动回血
$npc_recover = false;

//基础经验值等级区间
$base_exp = 9;

//基础物品寻找几率
$item_found_rate = 60;

//基础敌人遭遇几率
$enemy_found_rate = 70;

//基础尸体遭遇几率
$corpse_found_rate = 50;

//基础先发几率
$base_emptive = 50;

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
	'p' => 0.02,
	'k' => 0.02,
	'g' => 0.01,
	'c' => 0,
	'd' => 0.03
	);

//反击率
$base_counter_rate = array(
	'p' => 50,
	'k' => 50,
	'g' => 50,
	'c' => 50,
	'd' => 50
	);
	
//数值名（仅用于显示）
$healthinfo = array(
	'hp' => '生命',
	'sp' => '体力'
	);

//基础姿态名
$poseinfo = Array(
	'通常',
	'攻击姿态',
	'防守姿态',
	'探索姿态',
	'隐藏姿态',
	'治疗姿态'
	);

//应战方针名
$tacticinfo = Array(
	'通常',
	'重视防御',
	'重视反击',
	'重视躲避'
	);

//行动名（仅用于显示）
$actioninfo = array(
	'move' => '移动',
	'search' => '搜索',
	'create_team' => '创建队伍',
	'join_team' => '加入队伍',
	'wound_dressing' => '包扎伤口'
	);

//致死原因（仅用于显示）
$deathreasoninfo = array_merge($deathreasoninfo, array(
	'forbid' => '滞留禁区'
	));

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

//头像数量
$icon_num = array(
	'f' => 20,
	'm' => 21
	);

//社团名
$clubinfo = Array(
	0 => '无',
	1 => '棒球社',
	2 => '击剑社',
	3 => '弓道社',
	4 => '篮球社',
	5 => '化学社',
	6 => '足球社',
	7 => '电脑社',
	8 => '烹饪社',
	9 => '动漫社',
	10 => '侦探社'
	);

//性别
$genderinfo = array(
	'f' => '女',
	'm' => '男'
	);

//地图
$map = array(
	0 => '分校',
	1 => '北海岸',
	2 => '北村住宅区',
	3 => '北村公所',
	4 => '邮电局',
	5 => '消防署',
	6 => '观音堂',
	7 => '清水池',
	8 => '西村神社',
	9 => '墓地',
	10 => '山丘地带',
	11 => '隧道',
	12 => '西村住宅区',
	13 => '寺庙',
	14 => '废校',
	15 => '南村神社',
	16 => '森林地带',
	17 => '源二郎池',
	18 => '南村住宅区',
	19 => '诊所',
	20 => '灯塔',
	21 => '南海岸'
	);

//天气名字
$weatherinfo = array(
	0 => '晴天',
	1 => '大晴',
	2 => '多云',
	3 => '小雨',
	4 => '暴雨',
	5 => '台风',
	6 => '雷雨',
	7 => '下雪',
	8 => '起雾',
	9 => '浓雾',
	10 => '<span class="miasma">瘴气</span>',
	11 => '<span class="tornado">龙卷风</span>',
	12 => '<span class="blizzard">暴风雪</span>',
	13 => '<span class="hail">冰雹</span>'
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

//每点熟练度所增加的攻击系数
$proficiency_modulus = array(
	'p' => 0.5,
	'k' => 0.5,
	'g' => 0.6,
	'c' => 0.3,
	'd' => 0.4
	);

//每种熟练度的基础攻击系数
$proficiency_intercept = array(
	'p' => 0,
	'k' => 0,
	'g' => 0,
	'c' => 0,
	'd' => 0
	);

//可随机到的天气数量
$normal_weather = 10;
	
//商店位置
$shopmap = array(0, 14);
	
//诊所位置
$clinicmap = array(
	//MapID => Multiple
	19 => 2
	);

//各行动的消耗
$consumption = array(
	'search' => array('sp' => 15),
	'move' => array('sp' => 15),
	'create_team' => array('sp' => 25),
	'join_team' => array('sp' => 25),
	'wound_dressing' => array('sp' => 25)
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
$clip = 6;

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
$mixinfo = array
	( 
	array('stuff' => array('轻油','肥料'),'result' => array('火药','Y',1,1),),
	array('stuff' => array('水','地雷'),'result' => array('水鸳鸯','WD',10,12),),
	array('stuff' => array('灯油','钉'),'result' => array('☆仙女棒☆','WD',25,24),),
	array('stuff' => array('雷达用电池','打火机'),'result' => array('☆自爆电池☆','WD',20,30),),
	array('stuff' => array('汽油','空瓶'),'result' => array('☆火焰瓶☆','WD',30,15),),
	array('stuff' => array('信管','火药'),'result' => array('★炸药★','WD',50,18),),
	array('stuff' => array('导火线','火药'),'result' => array('★炸药★','WD',50,18),),
	array('stuff' => array('喷雾器罐','打火机'),'result' => array('★简易火焰放射器★','WG',65,18),),
	array('stuff' => array('简易雷达','天线'),'result' => array('雷达','R',1,1,2,array('immortal'=>true)),),
	array('stuff' => array('安雅人体冰雕','解冻药水'),'result' => array('武器师安雅的奖赏','Y',1,1),),
	array('stuff' => array('手机','笔记本电脑'),'result' => array('移动PC','Y',1,1,array('immortal'=>true)),),
	array('stuff' => array('杂炊','松茸'),'result' => array('松茸御饭','HS',150,3),),
	array('stuff' => array('咖喱','面包'),'result' => array('咖喱面包','HH',80,2),),
	array('stuff' => array('牛奶','立顿茶包','糯米丸子'),'result' => array('珍珠奶茶','HB',150,4),)
	);

//攻击系数
$modulus_attack = array(
	'weather' => array(1.2, 1.2, 1, 0.8, 0.95, 0.93, 0.93, 0.9, 1, 1.05, 1.2, 0.93, 0.8, 0.95),
	'area' => array(6 => 1.1, 9 => 0.9, 14 => 0.9, 18 => 1.1),
	'pose' => array(1, 1.2, 0.8, 0.95, 0.8, 0.7),
	'tactic' => array(1, 0.8, 1.05, 0.9, 0.7)
	);

//防御系数
$modulus_defend = array(
	'weather' => array(1.3, 1.1, 1, 0.97, 0.97, 0.95, 0.9, 0.85, 0.8, 0.7, 0.5, 0.95, 0.8, 0.97),
	'area' => array(1 => 0.9, 2 => 1.1, 11 => 0.9, 12 => 1.1, 20 => 1.1),
	'pose' => array(1, 0.8, 1.2, 0.9, 0.95, 0.85),
	'tactic' => array(1, 1.2, 0.9, 1.1, 0.95)
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
	'weather' => array(1.1, 1.2, 1, 0.98, 0.97, 0.93, 0.9, 0.95, 1.1, 1, 1, 0.93, 0.95, 0.7),
	'area' => array(1.1, 1, 1, 1.1, 0.9, 1.1, 1, 1.1, 0.9, 1, 1.1, 1, 1, 0.9, 1, 0.9, 0.9, 0.9, 1, 1.1, 1, 1.1),
	'pose' => array(3 => 1.2, 4 => 0.9, 5 => 0.8),
	'tactic' => array()
	);

//躲避系数
$modulus_hide = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array(1 => 1.05, 3 => 1.2, 4=> 0.9)
	);

//先发系数
$modulus_emptive = array(
	'weather' => array(1.2, 1.1, 1, 0.97, 0.95, 0.95, 0.93, 1.1, 0.9, 0.9, 0.9, 0.95, 1, 0.95),
	'area' => array(),
	'pose' => array(2 => 0.9, 3 => 1.1, 4 => 1.2, 5 => 0.8),
	'tactic' => array()
	);

//反击系数
$modulus_counter = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(),
	'tactic' => array(2 => 1.2, 3 => 0)
	);

?>