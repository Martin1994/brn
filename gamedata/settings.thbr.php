<?php

//MOD名
$mod_name = 'Touhou Battle Royale';

//MOD版本
$mod_version = '0.35 beta';

//MOD作者
$mod_author = array(
	array('name' => 'Martin Chloride', 'url' => 'http://martincl2.me')
	);

//MOD协议
$mod_license = array('name' => 'CC-BY-SA 协议', 'url' => 'http://creativecommons.org/licenses/by-sa/3.0/legalcode');

//MOD补充说明
$mod_extra_info = '';
	
//公告
$bulletin = '<p>内测中，如发现任何bug请及时反馈<br />QQ群：30173786</p><p>第一次游玩前请阅读帮助</p><p>讨论板：<a href="http://thbr.sinaapp.com/bbs">http://thbr.sinaapp.com/bbs</a></p>';

//模板名
$template_name = 'thbr';

//地图图像
$map_image_url = 'img/thbr/map.png';

//头像文件夹
$avatar_dir = 'thbr';

//网页标题
$page_title = '东方大逃杀';

//网页标头
$page_header = 'Touhou Battle Royale';

//禁区时间（秒）
$round_time = 1800;

//币种
$currency = '饮茶券';

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
	'extra_package' => '背包扩容',
	'extra_hp' => '额外生命',
	'att_buff' => '攻击增益',
	'def_buff' => '防御增益',
	'att_debuff' => '攻击削减',
	'def_debuff' => '防御削减',
	'recover_hp' => '生命回复',
	'recover_sp' => '体力回复',
	'invincible' => '无敌',
	'shield' => '替身人偶',
	'infrared_moon' => '赤月下',
	'ultrashort_EEG' => '超短脳波',
	'scapegoat_dummy' => '身代わり人形',
	'control_rod' => '制御棒',
	'grand_patriots_elixir' => '国士无双',
	'fantasy_nature' => '梦想天生',
	'ridicule' => '自信受挫',
	'wandering_soul' => '彷徨幽灵',
	'ageless_dream' => '无寿の夢',
	'ageless_land' => '无寿国への約束手形',
	'horai' => '蓬莱之药',
	'lunar_incense' => '仙香玉兔',
	//套装
	'kedama_suit' => '毛玉套装',
	'reisen_suit' => '铃仙套装',
	'eirin_suit' => '八意套装',
	'yukari_suit' => '八云套装',
	'scarlet_suit' => '斯卡雷特套装',
	'konpaku_suit' => '魂魄套装',
	'cirno_suit' => '琪露诺套装',
	'reimu_suit' => '博丽套装',
	'marisa_suit' => '雾雨套装',
	'rin_suit' => '冴月套装',
	'aya_suit' => '射命丸套装',
	'sakuya_suit' => '十六夜套装',
	'rinnosuke_suit' => '森近套装',
	'keine_suit' => '上白泽套装',
	'yuyuko_suit' => '幽幽子套装',
	'komeiji_suit' => '古名地套装',
	'alice_suit' => '玛格特罗依德套装'
	));
	
//buff说明
$buff_help = array_merge($buff_help, array(
	'shield' => '代替自身受到伤害',
	'infrared_moon' => '无法被远程攻击及 SpellCard 击中',
	'ultrashort_EEG' => '出现多个分身与本体共同攻击',
	'scapegoat_dummy' => '永久降低攻击并提升防御',
	'control_rod' => '永久提升攻击并降低防御',
	'grand_patriots_elixir' => '永久提升攻击并提升防御',
	'fantasy_nature' => '击中敌人一定次数后会释放「夢想天生」',
	'ridicule' => '暂时降低防御',
	'wandering_soul' => '暂时提升防御',
	'ageless_dream' => '生命持续流失，击中敌人后效果会消失',
	'ageless_land' => '击中敌人后会消失，若到时限仍未击中敌人则会造成大量伤害',
	'horai' => '死后原地满状态复活',
	'lunar_incense' => '时限到之后直接死亡'
	));

//陷阱致伤率（各部位单独计算）
$trap_injure_rate = 25;

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

//基础先发几率
$base_emptive = 50;

//命中率
$base_hit_rate = array(
	'p' => 75,
	'k' => 75,
	'g' => 70,
	'c' => 100,
	'd' => 60,
	'sc' => 75
	);

//命中率每点熟练增加
$extra_hit_rate = array(
	'p' => 0.07,
	'k' => 0.08,
	'g' => 0.04,
	'c' => 0,
	'd' => 0.1,
	'sc' => 0.1
	);

//反击率
$base_counter_rate = array(
	'p' => 80,
	'k' => 70,
	'g' => 80,
	'c' => 50,
	'd' => 20,
	'sc' => 100
	);

//各种攻击方式可能导致受伤的部位
$hurt_position = array(
	'p' => array('h', 'a'),
	'k' => array('b', 'h', 'a'),
	'g' => array('b', 'h', 'a', 'f'),
	'c'=> array('h', 'a'),
	'd' => array('b', 'h', 'a', 'f'),
	'sc' => array('b', 'h', 'a', 'f'),
	'fist' => array()
	);

//致伤率
$hurt_rate = array('p' => 15, 'k' => 30, 'g' => 30, 'c' => 15, 'd' => 40, 'sc' => 60, 'fist' => 0);

//武器类型的攻击方式（显示用）
$weapon_types = array(
	'p' => '殴',
	'k' => '斩',
	'g' => '射',
	'c' => '投',
	'd' => '爆',
	'sc' => '符卡',
	);
	
//数值名（仅用于显示）
$healthinfo = array(
	'hp' => '生命',
	'sp' => '体力'
	);

//头像数量
$icon_num = array(
	'f' => 8,
	'm' => 8
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
	8 => '生物社',
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
	0 => '大结界',
	1 => '血色海岸',
	2 => '红魔馆钟楼',
	3 => '红魔馆',
	4 => '妖怪之山',
	5 => '人间之里',
	6 => '观音堂',
	7 => '雾之湖',
	8 => '博丽神社',
	9 => '地灵殿',
	10 => '魔法森林',
	11 => '隧道', //old
	12 => '玛格特罗依德宅',
	13 => '西行妖',
	14 => '香霖堂',
	15 => '白玉楼',
	16 => '迷途竹林',
	17 => '源二郎池', //old,
	18 => '南村住宅区', //old
	19 => '永远亭',
	20 => '迷途之家',
	21 => '南海岸' //old
	);

//天气名字
$weatherinfo = array(
	0 => '蒼天',
	1 => '快晴',
	2 => '曇天',
	3 => '晴嵐',
	4 => '天気雨',
	5 => '台風',
	6 => '疎雨',
	7 => '雪',
	8 => '川霧',
	9 => '<span class="noumu">濃霧</span>',
	10 => '<span class="hanagumori">花曇</span>',
	11 => '<span class="nagi">凪</span>',
	12 => '<span class="diamond-dust">钻石星辰</span>',
	13 => '<span class="scarlet-moon">血月</span>'
	);

//物品类型（仅用于显示）
$iteminfo = array_merge($iteminfo, array(
	'YS' => '召唤',
	'SW' => 'SpellCard(进攻)',
	'SY' => 'SpellCard(特效)',
	'M' => '合成素材'
	));

//基础姿态名
$poseinfo = Array(
	'通常',
	'攻击',
	'防守',
	'探索',
	'隐藏',
	'治疗',
	'狙击'
	);

//致死原因（仅用于显示）
$deathreasoninfo = array_merge($deathreasoninfo, array(
	'ageless_land' => 'Ageless Land'
	));

//每点熟练度所增加的攻击系数
$proficiency_modulus = array(
	'p' => 0.5,
	'k' => 0.5,
	'g' => 0.6,
	'c' => 0.4,
	'd' => 0.5,
	'sc' => 0.2
	);

//每种熟练度的基础攻击系数
$proficiency_intercept = array(
	'p' => 0,
	'k' => 10,
	'g' => 20,
	'c' => 2,
	'd' => 6,
	'sc' => 100
	);

//可随机到的天气数量
$normal_weather = 9;
	
//商店位置
$shopmap = array(14, 19);
	
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
$mixinfo = array
(
	array(
		'stuff' => array('手机','笔记本电脑'),
		'result' => array('移动PC','Y',1,1,array('immortal'=>true))),
	array(
		'stuff' => array('杂炊','松茸'),
		'result' => array('松茸御饭','HS',200,3,)),
	array(
		'stuff' => array('咖喱','面包'),
		'result' => array('咖喱面包','HB',400,1,)),
	array(
		'stuff' => array('牛奶','立顿茶包','糯米丸子'),
		'result' => array('珍珠奶茶','HB',500,4,)),
	array(
		'stuff' => array('酒精','水'),
		'result' => array('伏特加','HS',700,1,array('side-effect' => array('att' => 50, 'def' => -25, 'duration' => 180)))),
	array(
		'stuff' => array('肥料','金坷垃'),
		'result' => array('「とある科學の超肥料砲」','SW',221,10,)),
	array(
		'stuff' => array('魔理沙·迷你八卦炉','霖之助·迷你八卦炉强化图'),
		'result' => array('霖之助·迷你八卦炉×强袭','WD',200,20,array('suit' => 'rinnosuke'))),
	array(
		'stuff' => array('绯色金属','陶土','霖之助·迷你八卦炉设计图','The Grimoire of Marisa'),
		'result' => array('魔理沙·迷你八卦炉×永续','WD',50,0,array('suit' => 'marisa', 'immortal' => true))),
	array(
		'stuff' => array('绯色金属','陶土','霖之助·迷你八卦炉设计图'),
		'result' => array('魔理沙·迷你八卦炉','WD',50,20,array('suit' => 'marisa'))),
	array(
		'stuff' => array('魂魄·楼观剑','魂魄·白楼剑'),
		'result' => array('魂魄对剑「白楼观」','WK',125,65,array('multistage' => array(0.6, 0.7), 'suit' => 'konpaku', 'single-buff' => true))),
	array(
		'stuff' => array('笔记本电脑','Linux Live CD'),
		'result' => array('码符「Matrix的苏醒」','SW',65,18,)),
	array(
		'stuff' => array('移动PC','Linux Live CD'),
		'result' => array('码符「Matrix的复生」','SW',125,15,)),
	array(
		'stuff' => array('大冰块','轻油'),
		'result' => array('火焰轻油冰块','WD',20,5,)),
	array(
		'stuff' => array('大冰块','汽油'),
		'result' => array('火焰汽油冰块','WD',50,2,)),
	array(
		'stuff' => array('大冰块','水'),
		'result' => array('纯净水冰块','HS',250,2,)),
	array(
		'stuff' => array('冰精的微型冰块','水'),
		'result' => array('矿泉水冰块','HH',275,2,)),
	array(
		'stuff' => array('大冰块','冰精的微型冰块','水'),
		'result' => array('有顶天之酒冰块','HB',300,2,)),
	array(
		'stuff' => array('大冰块','冰刃'),
		'result' => array('易碎冰块','WD',40,15,)),
	array(
		'stuff' => array('八云紫结界原理图纸','博丽大结界作用说明书'),
		'result' => array('结界干扰器','Y',1,1,array('immortal'=>true))),
	array(
		'stuff' => array('触手证明书','触手','魔法催化剂','盐','蘑菇'),
		'result' => array('码符「终极BUG·拉电闸」','SW',1000,1,)),
	array(
		'stuff' => array('水','空白的SpellCard'),
		'result' => array('「スペル増幅」','SY',100,1,)),
	array(
		'stuff' => array('矿泉水','空白的SpellCard'),
		'result' => array('伊吹瓢','SY',1000,1,)),
	array(
		'stuff' => array('面包','空白的SpellCard'),
		'result' => array('「体力回復」','SY',100,1,)),
	array(
		'stuff' => array('咖喱面包','空白的SpellCard'),
		'result' => array('病気平癒守','SY',1000,1,)),
	array(
		'stuff' => array('红色水笔','空白的SpellCard'),
		'result' => array('红符「不夜城レッド」','SW',80,5,)),
	array(
		'stuff' => array('红色水笔','红色水笔','空白的SpellCard'),
		'result' => array('長視「赤月下」','SY',120,1,)),
	array(
		'stuff' => array('红色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('短視「超短脳波」','SY',120,1,)),
	array(
		'stuff' => array('红色水笔','红色水笔','红色水笔','红色水笔','空白的SpellCard'),
		'result' => array('日符「ロイヤルフレア」','SY',1,1,)),
	array(
		'stuff' => array('蓝色水笔','蓝色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('月符「サイレントセレナ」','SY',1,1,)),
	array(
		'stuff' => array('绿色水笔','竹子','年糕','空白的SpellCard'),
		'result' => array('生薬「国士無双の薬」','SY',30,1,)),
	array(
		'stuff' => array('黄色水笔','怪力药丸','空白的SpellCard'),
		'result' => array('制御棒','SY',15,1,)),
	array(
		'stuff' => array('蓝色水笔','忍耐药丸','空白的SpellCard'),
		'result' => array('身代わり人形','SY',15,1,)),
	array(
		'stuff' => array('橙色水笔','空白的SpellCard'),
		'result' => array('龍星','SY',60,1,)),
	array(
		'stuff' => array('黑色水笔','空白的SpellCard'),
		'result' => array('足軽「スーサイドスクワッド」','SY',100,2,)),
	array(
		'stuff' => array('绿色水笔','空白的SpellCard'),
		'result' => array('断命剑「冥想斩」','SW',125,1,)),
	array(
		'stuff' => array('蓝色水笔','空白的SpellCard'),
		'result' => array('人符「现世斩」','SW',100,1,)),
	array(
		'stuff' => array('蓝色水笔','水','空白的SpellCard'),
		'result' => array('氷符「アイシクルフォール」','SW',300,1,array('range-missile' => true))),
	array(
		'stuff' => array('蓝色水笔','冰晶核','空白的SpellCard'),
		'result' => array('雪符「ダイアモンドブリザード」','SW',400,1,)),
	array(
		'stuff' => array('绿色水笔','毒药','空白的SpellCard'),
		'result' => array('毒煙幕「瓦斯織物の玉」','SY',300,1,)),
	array(
		'stuff' => array('红色水笔','橙色水笔','空白的SpellCard'),
		'result' => array('伤魂「ソウルスカルプチュア」','SW',175,2,)),
	array(
		'stuff' => array('红色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('禁忌「恋の迷宫」','SW',175,3,)),
	array(
		'stuff' => array('红色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('幻爆「近眼花火」','SW',100,6,)),
	array(
		'stuff' => array('红色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('霊符「夢想封印」','SW',180,4,)),
	array(
		'stuff' => array('红色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('霊符「夢想封印」','SW',180,4,)),
	array(
		'stuff' => array('霊符「夢想封印」','塞钱'),
		'result' => array('神霊「夢想封印瞬」','SW',450,1,)),
	array(
		'stuff' => array('红色水笔','绿色水笔','空白的SpellCard'),
		'result' => array('花符「幻想郷の開花」','SW',200,3,)),
	array(
		'stuff' => array('绿色水笔','橙色水笔','空白的SpellCard'),
		'result' => array('运命「ミゼラブルフェイト」','SW',150,8,)),
	array(
		'stuff' => array('绿色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('境符「二次元と三次元の境界」','TN',188,2,)),
	array(
		'stuff' => array('绿色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('结界「魅力的な四重结界」','TN',288,1,)),
	array(
		'stuff' => array('绿色水笔','白色水笔','空白的SpellCard'),
		'result' => array('「反魂蝶 八分咲」','SW',140,12,)),
	array(
		'stuff' => array('橙色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('風符「風神一扇」','SW',150,3,)),
	array(
		'stuff' => array('風符「風神一扇」','天狗之羽'),
		'result' => array('疾風「風神少女」','SW',450,1,array('multistage' => array(0.1,0.35,0.1,0.35,0.1)))),
	array(
		'stuff' => array('橙色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('御札「神社繁榮祈願札」','TN',100,1,array('steal' => 0.25))),
	array(
		'stuff' => array('橙色水笔','白色水笔','空白的SpellCard'),
		'result' => array('禁弾「过去を刻む时计」','SW',185,6,)),
	array(
		'stuff' => array('橙色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('月金符「サンシャインリフレクター」','SW',100,3,)),
	array(
		'stuff' => array('黄色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('三華「崩山彩極砲」','SW',130,2,)),
	array(
		'stuff' => array('黄色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('土着神「ケロちゃん风雨に负け ず」','SW',320,1,)),
	array(
		'stuff' => array('黄色水笔','白色水笔','空白的SpellCard'),
		'result' => array('恋心「ダブルスパーク」','SW',255,2,)),
	array(
		'stuff' => array('黑色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('難題「仏の御石の鉢　-砕けぬ意思-」','SW',70,5,)),
	array(
		'stuff' => array('蓝色水笔','白色水笔','空白的SpellCard'),
		'result' => array('大奇迹「八坂の神风」','SW',120,12,)),
	array(
		'stuff' => array('蓝色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array(' 恋符「マスタースパーク」','SW',220,2,)),
	array(
		'stuff' => array('恋符「マスタースパーク」','恋心「ダブルスパーク」'),
		'result' => array(' 恋符「マスタースパーク」·改','SW',350,2,array('multiple' => array(0.5, 0.5)))),
	array(
		'stuff' => array('霊符「夢想妙珠」','神灵「梦想封印」','塞钱'),
		'result' => array('「夢想天生」','SY',1200,1,)),
	array(
		'stuff' => array('魔符「アーティフルサクリファス」','上海人形'),
		'result' => array('魔操「归于虚无」','SW',500,1,)),
	array(
		'stuff' => array('生薬「国士無双の薬」','蓬莱玉枝'),
		'result' => array('禁薬「蓬莱の薬」','SY',1,1,)),
	array(
		'stuff' => array('优昙钵华','蓬莱玉枝','空白的SpellCard'),
		'result' => array('秘薬「仙香玉兎」','SW',750,1,array('lunar-incense' => true))),
	array(
		'stuff' => array('花符「幻想郷の开花」','阳伞'),
		'result' => array('幻想「花鳥風月、嘯風弄月」','SW',800,2,)),
	array(
		'stuff' => array('伤魂「ソウルスカルプチュア」','月时计'),
		'result' => array('幻葬「夜雾の幻影杀人鬼」','SW',225,5,array('multistage' => array(0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1)))),
	array(
		'stuff' => array('红符「不夜城レッド」','运命「ミゼラブルフェイト」'),
		'result' => array('红魔「スカーレットデビル」','SW',250,3,)),
	array(
		'stuff' => array('禁忌「恋の迷宫」','禁弾「过去を刻む时计」'),
		'result' => array('QED「495年の波纹」','SW',350,3,)),
	array(
		'stuff' => array('华符「彩光莲华掌」','三华「崩山彩极炮」'),
		'result' => array('星气「星脉地转弹」','SW',320,2,)),
	array(
		'stuff' => array('境符「二次元与三次元的境界」','结界「魅力的な四重结界」'),
		'result' => array('「深弾幕結界　-夢幻泡影-」','TN',400,1,array('multistage' => array(0.2, 0.3, 0.5)))),
	array(
		'stuff' => array('断命剑「冥想斩」','人符「现世斩」'),
		'result' => array('断迷剣「迷津慈航斬」','SW',400,1,array('multistage' => array(0.25, 0.25, 0.5)))),
	array(
		'stuff' => array('断命剑「冥想斩」','人符「现世斩」','半灵碎片'),
		'result' => array('人鬼「未来永劫斩」','SW',400,1,array('multistage' => array(0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 1.5)))),
	array(
		'stuff' => array('便携结界','蓝色水笔','空白的SpellCard'),
		'result' => array('幻想「第一種永久機関」','SW',400,1,array('multistage' => array(0.1, 0.1, 0.1, 0.4, 0.1, 0.1, 0.3, 0.1, 0.1, 0.1)))),
	array(
		'stuff' => array('稻草人偶','红色水笔','空白的SpellCard'),
		'result' => array('想起「ストロードールカミカゼ」','SW',110,3,array('multistage' => array(0.5, 0.1, 0.1, 0.5)))),
	array(
		'stuff' => array('《无意识的哲学》1869初版','蓝色水笔','空白的SpellCard'),
		'result' => array('「嫌われ者のフィロソフィ」','SW',375,1,array('multistage' => array(0.25, 0.25, 0.25, 0.25)))),
	array(
		'stuff' => array('土着神「ケロちゃん风雨に负けず」','大奇迹「八坂の神风」'),
		'result' => array('「风神様の神徳」','WD',555,1,)),
	array(
		'stuff' => array('「反魂蝶 八分咲」','幽灵印花折扇'),
		'result' => array('「西行寺无余涅盘」','SW',480,1,)),
	array(
		'stuff' => array('黑色水笔','白色水笔','空白的SpellCard'),
		'result' => array('无敌连段「AAAA-ESC」','SW',400,1,)),
	array(
		'stuff' => array('盐','大冰块'),
		'result' => array('盐水','HB',50,6,)),
	array(
		'stuff' => array('黑历史全系列光盘A','黑历史全系列光盘B','黑历史全系列光盘C'),
		'result' => array('黑历史史册残页','YS',1,1,array('id' => 109))),
	array(
		'stuff' => array('云南白药','邦迪创可贴'),
		'result' => array('云南白药创可贴','HH',400,1,)),
	array(
		'stuff' => array('巫女服','裹胸布'),
		'result' => array('仿制的灵梦巫女服','DB',35,60,)),
	array(
		'stuff' => array('巫女服','裹胸布','塞钱'),
		'result' => array('定制的灵梦巫女服','DB',120,60,)),
	array(
		'stuff' => array('国符「三种の神器 剣」','国符「三种の神器 玉」','国符「三种の神器 鏡」','国体「三种の神器 郷」'),
		'result' => array('终符「幻想天皇」','SW',2000,1,)),
	);

//攻击系数
$modulus_attack = array(
	'weather' => array(1.5, 1.2, 1, 0.9, 0.8, 0.73, 0.75, 0.93, 1, 1.05, 1, 1.2, 1, 0.95),
	'area' => array(6 => 1.1, 9 => 0.9, 14 => 0.9, 18 => 1.1),
	'pose' => array(1, 1.2, 0.8, 0.95, 0.8, 0.7, 1),
	'tactic' => array(1, 0.8, 1.05, 0.9, 0.7)
	);

//防御系数
$modulus_defend = array(
	'weather' => array(1.1, 1.1, 1, 1.5, 0.8, 0.73, 0.8, 1, 0.8, 0.8, 1, 1.1, 0.5, 0.97),
	'area' => array(1 => 0.9, 2 => 1.1, 11 => 0.9, 12 => 1.1, 20 => 1.1),
	'pose' => array(1, 0.8, 1.2, 0.9, 0.95, 0.85, 1),
	'tactic' => array(1, 1.2, 0.9, 1.1, 0.95)
	);

//命中率系数
$modulus_hit_rate = array(
	'weather' => array(1.25, 1.3, 1, 0.8, 1.07, 0.7, 0.8, 0.97, 0.8, 0.8, 0.9, 1.1, 0.95, 0.85),
	'area' => array(),
	'pose' => array(),
	'tactic' => array()
	);

//遇敌率系数
$modulus_find = array(
	'weather' => array(1.1, 1.2, 1, 0.98, 0.97, 0.85, 0.94, 0.88, 0.9, 0.9, 1, 1.05, 0.95, 0.8),
	'area' => array(1.1, 1, 1, 1.1, 0.9, 1.1, 1, 1.1, 0.9, 1, 1.1, 1, 1, 0.9, 1, 0.9, 0.9, 0.9, 1, 1.1, 1, 1.1),
	'pose' => array(3 => 1.2, 4 => 0.9, 5 => 0.8, 6 => 1.2),
	'tactic' => array()
	);

//躲避系数
$modulus_hide = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(6 => 0.8),
	'tactic' => array(1 => 1.05, 3 => 1.2, 4 => 0.9)
	);

//先发系数
$modulus_emptive = array(
	'weather' => array(1.3, 1.1, 1, 0.95, 1, 1, 1, 1.05, 0.88, 0.85, 0.95, 1.1, 1.1, 1.2),
	'area' => array(),
	'pose' => array(2 => 0.9, 3 => 1.1, 4 => 1.2, 5 => 0.8, 6 => 0.7),
	'tactic' => array()
	);

//反击系数
$modulus_counter = array(
	'weather' => array(),
	'area' => array(),
	'pose' => array(6 => 1.1),
	'tactic' => array(2 => 1.2, 3 => 0)
	);

//结局
$ending_type = array(
	'error' => '<span class="error">游戏故障</span>',
	'timeup' => '<span class="dieout">无人参加</span>',
	'survive' => '<span class="survive">最后幸存</span>',
	'eliminate' => '<span class="eliminate">紧急结束</span>',
	'restart' => '<span class="restart">游戏重设</span>'
	);

?>