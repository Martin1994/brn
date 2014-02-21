<?php

//MOD名
$mod_name = 'Touhou Battle Royale';

//MOD版本
$mod_version = '0.2 beta';

//MOD作者
$mod_author = array(
	array('name' => 'Martin Chloride', 'url' => 'http://martincl2.me')
	);

//MOD协议
$mod_license = array('name' => 'CC-BY-SA 协议', 'url' => 'http://creativecommons.org/licenses/by-sa/3.0/legalcode');

//MOD补充说明
$mod_extra_info = '';
	
//公告
$bulletin = '<p>内测中，如发现任何bug请及时反馈<br />QQ群：30173786</p><p>第一次游玩前请阅读帮助</p>';

//模板名
$template_name = 'thbr';

//网页标题
$page_title = '东方大逃杀';

//网页标头
$page_header = 'Touhou Battle Royale';

//禁区时间（秒）
$round_time = 1800;

//币种
$currency = '博丽神社的饮茶券';

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
	'sc' => '附卡',
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
	'迷途之家',				//0
	'血色海岸',				//1
	'红魔馆钟楼',			//2
	'红魔馆',					//3
	'妖怪之山',				//4
	'人间之里',				//5
	'观音堂',					//6		Old
	'雾之湖',					//7
	'博丽神社',				//8
	'地灵殿',					//9
	'魔法森林',				//10
	'隧道',						//11	Old
	'玛格特罗依德宅',	//12
	'西行妖',					//13
	'香霖堂',					//14
	'白玉楼',					//15
	'迷途竹林',				//16
	'源二郎池',				//17	Old
	'南村住宅区',			//18	Old
	'永远亭',					//19
	'灯塔',						//20	Old
	'南海岸'					//21	Old
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
	'SY' => 'SpellCard(特效)'
	));

//每点熟练度所增加的攻击系数
$proficiency_modulus = array(
	'p' => 0.45,
	'k' => 0.5,
	'g' => 0.6,
	'c' => 0.4,
	'd' => 0.5,
	'sc' => 0.2
	);

//每种熟练度的基础攻击系数
$proficiency_intercept = array(
	'p' => 3,
	'k' => 3,
	'g' => 10,
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
		'result' => array('移动PC','Y',1,1,array('immortal'=>true)),
		'intro' => array('通讯','比较低级的 <span class="red" >笔记本电脑</span> 是不配备无线网卡的。但是随着 <span class="red" >手机</span> 网络的日益发达，只需将 <span class="red" >手机</span> 与 <span class="red" >笔记本电脑</span> 连接，稍加调整即可有无线信号收发功能。',),),
	array(
		'stuff' => array('杂炊','松茸'),
		'result' => array('松茸御饭','HS',200,3,),
		'intro' => array('烹饪','<span class="red b">杂炊</span> 和 <span class="red b">松茸</span> 都是很有营养的东西，因此这道菜能让你精力充沛。',),),
	array(
		'stuff' => array('咖喱','面包'),
		'result' => array('咖喱面包','HB',400,1,),
		'intro' => array('烹饪','如果将 <span class="red b">咖喱</span> 涂抹在 <span class="red b">面包</span> 上味道会更好。',),),
	array(
		'stuff' => array('牛奶','立顿茶包','糯米丸子'),
		'result' => array('珍珠奶茶','HB',500,4,),
		'intro' => array('经营','小的食品店成本并不高。打个比方，一杯 <span class="yellow b">珍珠奶茶</span> 仅需 <span class="red b">牛奶</span> 、 <span class="red b">立顿茶包</span> 和 <span class="red b">糯米丸子</span> 即可。',),),
	array(
		'stuff' => array('酒精','水'),
		'result' => array('伏特加','HS',700,1,),
		'intro' => array('百科',' <span class="yellow" >伏特加</span> 的酒精浓度非常高，分明就是 <span class="red" >酒精</span> 兑 <span class="red" >水</span> 嘛！',),),
	array(
		'stuff' => array('肥料','金坷垃'),
		'result' => array('「とある科學の超肥料砲」','SW',221,10,),
		'intro' => array('百科',' <span class="red" >肥料</span> 馋了 <span class="red" >金坷拉</span> ，一袋能顶两袋撒。',),),
	array(
		'stuff' => array('魔理沙·迷你八卦炉','霖之助·迷你八卦炉强化图'),
		'result' => array('霖之助·迷你八卦炉','WD',200,20,array('suit' => 'rinnosuke')),
		'intro' => array('历史',' <span class="green" >霖之助</span> 曾经送给 <span class="green" >魔理沙</span> 过一个 <span class="red" >迷你八卦炉</span> ，若是你能搞到 <span class="red" >强化图</span> ，那 <span class="yellow" >迷你八卦炉</span> 的威力会提高很多。问题是，你搞得到么？',),),
	array(
		'stuff' => array('绯色金属','陶土','霖之助·迷你八卦炉设计图'),
		'result' => array('魔理沙·迷你八卦炉','WD',50,20,array('suit' => 'marisa')),
		'intro' => array('历史',' <span class="green" >霖之助</span> 曾经送给 <span class="green" >魔理沙</span> 过一个 <span class="red" >迷你八卦炉</span> ，若是你能搞到 <span class="red" >设计图</span> 和原料 <span class="red" >绯色金属</span> 、 <span class="red" >陶土</span> ，那你也能拥有一个 <span class="yellow" >迷你八卦炉</span> 。问题是，你搞得到么？',),),
	array(
		'stuff' => array('咲夜·红魔银刃','鲜血'),
		'result' => array('「红魔血刃」','WC',85,10,array('alt' => array('k' => 'k', 'e' => '50'))),
		'intro' => array('时政',' <span class="green" >咲夜</span> 的 <span class="red" >餐刀</span> 是给红魔馆里 <span class="green" >斯卡雷特姐妹</span> 用的。因此，少不了沾满 <span class="red" >鲜血</span> 。',),),
	array(
		'stuff' => array('魂魄·楼观剑','魂魄·白楼剑'),
		'result' => array('魂魄对剑「白楼观」','WK',125,65,array('multistage' => array(0.6, 0.7), 'suit' => 'konpaku', 'single-buff' => true)),
		'intro' => array('历史',' <span class="green" >妖梦</span> 和她的爷爷  <span class="green" >妖忌</span> 都是二刀流的使用者。那两把刀分别是 <span class="red" >楼观剑</span> 和 <span class="red" >白楼剑</span> 。不要问我为什么读着觉得像 <span class="yellow" >白楼观剑</span> 。',),),
	array(
		'stuff' => array('笔记本电脑','Linux Live CD'),
		'result' => array('码符「Matrix的苏醒」','SW',65,18,),
		'intro' => array('百科','TODO',),),
	array(
		'stuff' => array('移动PC','Linux Live CD'),
		'result' => array('码符「Matrix的复生」','SW',125,15,),
		'intro' => array('百科','TODO',),),
	array(
		'stuff' => array('大冰块','轻油'),
		'result' => array('火焰轻油冰块','WD',20,5,),
		'intro' => array('理化','其实 <span class="red" >大冰块</span> 也会变成危险品，比方说混入 <span class="red" >轻油</span> 的时候。',),),
	array(
		'stuff' => array('大冰块','汽油'),
		'result' => array('火焰汽油冰块','WD',50,2,),
		'intro' => array('理化','其实 <span class="red" >大冰块</span> 也会变成危险品，比方说混入 <span class="red" >汽油</span> 的时候。',),),
	array(
		'stuff' => array('大冰块','水'),
		'result' => array('纯净水冰块','HS',250,2,),
		'intro' => array('理化','其实 <span class="red" >大冰块</span> 也可以用来提高身体对水分的吸收率。例如你可以将其放入 <span class="red" >水</span> 中。',),),
	array(
		'stuff' => array('冰精的微型冰块','水'),
		'result' => array('矿泉水冰块','HH',275,2,),
		'intro' => array('理化','<span class="red" >冰精的微型冰块</span> 可以用来提高身体对水分的吸收率。一般人们比较倾向于将其放入 <span class="red" >水</span> 中。',),),
	array(
		'stuff' => array('大冰块','冰精的微型冰块','水'),
		'result' => array('有顶天之酒冰块','HB',500,2,),
		'intro' => array('理化','其实 <span class="red" >大冰块</span> 与 <span class="red" >冰精的微型冰块</span> 也可以用来提高身体对水分的吸收率。例如你可以将其放入 <span class="red" >水</span> 中。',),),
	array(
		'stuff' => array('大冰块','冰刃'),
		'result' => array('易碎冰块','WD',40,15,),
		'intro' => array('理化',' <span class="yellow" >易碎冰块</span> 是具有伤害性的，很容易<span class="b">炸</span>开。因此千万不要将 <span class="red" >大冰块</span> 与 <span class="red" >冰刃</span> 一起存放。',),),
	array(
		'stuff' => array('八云紫结界原理图纸','博丽大结界作用说明书'),
		'result' => array('结界干扰器','Y',1,1,),
		'intro' => array('历史','幻想乡一直被博丽大结界所笼罩着，成为孤立的一片乐土；要说幻想乡中另一种举足轻重的结界，便是八云紫的结界。传说能得知两者的作用原理将会引发不小的异变',),),
	array(
		'stuff' => array('调教证明书','魔法蘑菇料理','魔法催化剂','盐','蘑菇'),
		'result' => array('码符「终极BUG·拉电闸」','SW',1000,1,),
		'intro' => array('百科',' <span class="yellow" >终极BUG·拉电闸</span> 十分危险！一旦使用对手将处于黑屏状态，而且未保存的工作都将丢失！制作方法：将 <span class="red" >调教证明书</span> 挂在 <span class="red" >史前章鱼</span> 的所有触手上，会有很卡怕的事情发生，千万不要尝试！',),),
	array(
		'stuff' => array('水','空白的SpellCard'),
		'result' => array('「スペル増幅」','SY',100,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('矿泉水','空白的SpellCard'),
		'result' => array('伊吹瓢','SY',1000,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('面包','空白的SpellCard'),
		'result' => array('「体力回復」','SY',100,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('咖喱面包','空白的SpellCard'),
		'result' => array('病気平癒守','SY',1000,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('红色水笔','空白的SpellCard'),
		'result' => array('红符「不夜城レッド」','SW',80,5,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >红色水笔</span> 这样就能画出 <span class="red" >红符「不夜城レッド」</span>拥有爆炸般的魔法效果。',),),
	array(
		'stuff' => array('红色水笔','红色水笔','空白的SpellCard'),
		'result' => array('長視「赤月下」','SY',120,1,),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('红色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('短視「超短脳波」','SY',120,1,),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('红色水笔','红色水笔','红色水笔','红色水笔','空白的SpellCard'),
		'result' => array('日符「ロイヤルフレア」','SY',1,1,),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('蓝色水笔','蓝色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('月符「サイレントセレナ」','SY',1,1,),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('绿色水笔','竹子','年糕','空白的SpellCard'),
		'result' => array('生薬「国士無双の薬」','SY',10,1,),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('黄色水笔','空白的SpellCard'),
		'result' => array('制御棒','SY',15,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('蓝色水笔','空白的SpellCard'),
		'result' => array('身代わり人形','SY',15,1,),
		'intro' => array('符卡','',),),
	array(
		'stuff' => array('橙色水笔','空白的SpellCard'),
		'result' => array('龙星','SY',60,1,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,用 <span class="red" >橙色水笔</span> 描绘出 <span class="red" >天霸風神脚</span>的动作就可以使用这个神技',),),
	array(
		'stuff' => array('黑色水笔','空白的SpellCard'),
		'result' => array('足軽「スーサイドスクワッド」','SY',100,2,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >黑色水笔</span> 这样就能画出 <span class="red" >鬼神「ミッシングパープルパワー」</span>并获得强大的打击技能。',),),
	array(
		'stuff' => array('绿色水笔','毒药','空白的SpellCard'),
		'result' => array('毒煙幕「瓦斯織物の玉」','SY',300,1,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,使用 <span class="red" >绿色水笔</span> 能画出 <span class="red" >断命剑「冥想斩」</span>的轨迹,闭上眼睛静下心灵,斩断心中迷惘的一击。',),),
	array(
		'stuff' => array('红色水笔','橙色水笔','空白的SpellCard'),
		'result' => array('伤魂「ソウルスカルプチュア」','SW',175,2,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >橙色水笔</span>以及<span class="red" >红色水笔</span> 这样就能画出 <span class="red" >伤魂「ソウルスカルプチュア」</span>消磨他人灵魂的强大的切割术',),),
	array(
		'stuff' => array('红色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('禁忌「恋の迷宫」','SW',175,3,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >黄色水笔</span>以及<span class="red" >红色水笔</span> 这样就能画出 <span class="red" >禁忌「恋の迷宫」</span>强大的灵力斩击对方心魄',),),
	array(
		'stuff' => array('红色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('幻爆「近眼花火」','SW',100,6,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >黑色水笔</span>以及<span class="red" >红色水笔</span> 这样就能画出 <span class="red" >幻爆「近眼花火」</span>月兔的幻觉兵器之一,拥有强大的破坏力',),),
	array(
		'stuff' => array('红色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('神灵「梦想封印」','SW',180,4,),
		'intro' => array('符卡',' <span class="red" >红</span>  <span class="red" >白</span>的巫女所拥有的强力技能附在了<span class="red" >空白的SC</span> 上,形成了 <span class="red" >神灵「梦想封印」</span>',),),
	array(
		'stuff' => array('黑色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('霊符「夢想妙珠」','SW',180,4,),
		'intro' => array('符卡',' <span class="red" >红</span>  <span class="red" >白</span>的巫女所拥有的强力技能附在了<span class="red" >空白的SC</span> 上,形成了 <span class="red" >神灵「梦想封印」</span>',),),
	array(
		'stuff' => array('红色水笔','绿色水笔','空白的SpellCard'),
		'result' => array('花符「幻想郷の开花」','SW',200,3,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,用 <span class="red" >绿色水笔</span> 加<span class="red" >红色水笔</span>据说能得到 <span class="red" >花符「幻想郷の开花」</span>。六十年一轮回的异变,你能阻止得了吗？',),),
	array(
		'stuff' => array('绿色水笔','橙色水笔','空白的SpellCard'),
		'result' => array('运命「ミゼラブルフェイト」','SW',150,8,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,用 <span class="red" >绿色水笔</span> 加<span class="red" >橙色水笔</span>这样就能画出 <span class="red" >运命</span>的轨迹。为敌人带来悲惨的命运...',),),
	array(
		'stuff' => array('绿色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('境符「二次元与三次元的境界」','TN',188,2,),
		'intro' => array('符卡','据说<span class="green" >紫妈</span>用 <span class="red" >绿色水笔</span> 加<span class="red" >黄色水笔</span>在 <span class="red" >空白的符卡</span> 上画出 <span class="red" >境符「二次元与三次元的境界」</span>,来往于幻想乡与现世。',),),
	array(
		'stuff' => array('绿色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('结界「魅力的な四重结界」','TN',288,1,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,用 <span class="red" >绿色水笔</span> 加<span class="red" >黑色水笔</span>能画出 <span class="red" >結界「魅力的な四重結界」</span>,不过这岛上的结界你觉得有魅力吗?',),),
	array(
		'stuff' => array('绿色水笔','白色水笔','空白的SpellCard'),
		'result' => array('「反魂蝶 八分咲」','SW',140,12,),
		'intro' => array('符卡','用 <span class="red" >绿色与白色水笔</span> 在 <span class="red" >空白的SC</span> 上使用,可以获得幽幽子的符卡 <span  class="red" >「反魂蝶 八分咲」</span>',),),
	array(
		'stuff' => array('橙色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('爆符「メガフレア」','SW',150,3,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >黑色水笔</span>和<span class="red" >橙色水笔</span> 这样就能画出太阳黑子 <span class="red" >爆符「メガフレア」</span>。就形成了',),),
	array(
		'stuff' => array('橙色水笔','黄色水笔','空白的SpellCard'),
		'result' => array('御札「神社繁榮祈願札」','TN',100,1,array('steal' => 0.25)),
		'intro' => array('符卡','TODO',),),
	array(
		'stuff' => array('橙色水笔','白色水笔','空白的SpellCard'),
		'result' => array('禁弾「过去を刻む时计」','SW',185,6,),
		'intro' => array('符卡','在 <span class="red" >空白SC</span> 上,用 <span class="red" >白色水笔</span> 和<span class="red" >橙色水笔</span> 画出固定的图案,空白SC就会变成 <span class="red" >禁弹</span>走在过去的时钟,记录的是芙兰对蕾米的眷恋',),),
	array(
		'stuff' => array('橙色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('月金符「サンシャインリフレクター」','SW',150,3,),
		'intro' => array('符卡','蓝色与 <span class="red" >白色</span>在 <span class="red" >空白SC</span> 上画的风景画,没有蓝色了？<span  class="red" >橙色</span> 代替好了, <span  class="red" >月神</span>的舞蹈可是美丽而且致命的',),),
	array(
		'stuff' => array('黄色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('三华「崩山彩极炮」','SW',130,2,),
		'intro' => array('符卡','不知道为什么,中国的 <span class="red" >三华</span> 在 <span class="red" >空白 SC</span> 上是<span class="red" >黄色</span>和  <span  class="red" >黑色</span>... 这完全不符合中国的颜色嘛..',),),
 	array(
		'stuff' => array('黄色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('土着神「ケロちゃん风雨に负け ず」','SW',100,8,),
		'intro' => array('符卡','在 <span class="red " >空白SC</span> 上,用 <span class="red" >黄色水笔</span> 和 <span class="red" >蓝色水笔</span> 画出青蛙的图案,空白SC就会 变成 <span class="red" >青蛙子</span>,小青蛙无畏风雨~ ',),),
	array(
		'stuff' => array('黄色水笔','白色水笔','空白的SpellCard'),
		'result' => array('恋符「双重火花」','SW',215,2,),
		'intro' => array('符卡',' <span class="red " >黄色</span>和 <span class="red" >白色</span> 在<span  class="red" >空白的SC</span> 融合, <span class="red" >魔炮</span>就是这么来的,一发威力不够？这个是双倍的。',),),
	array(
		'stuff' => array('黑色水笔','黑色水笔','空白的SpellCard'),
		'result' => array('難題「仏の御石の鉢　-砕けぬ意思-」','SW',100,8,),
		'intro' => array('符卡','在 <span class="red" >空白的符卡</span> 上,可以使用 <span class="red" >黑色水笔</span>以及<span class="red" >蓝色水笔</span> 这样就能画出 <span class="red" >難題「仏の御石の鉢　-砕けぬ意思-」</span>辉夜的5个难题之一,你有信心破解么？',),),
	array(
		'stuff' => array('蓝色水笔','白色水笔','空白的SpellCard'),
		'result' => array('大奇迹「八坂の神风」','SW',120,12,),
		'intro' => array('符卡',' <span class="red" >蓝</span> <span class="red" >白</span> 的巫女附在<span class="red" >空白SC</span> 上面的力量足以引发 <span class="red" >大奇迹</span>如果做出来会发现,风,真的很大....',),),
	array(
		'stuff' => array('蓝色水笔','蓝色水笔','空白的SpellCard'),
		'result' => array('恋符「极限火花」','SW',220,2,),
		'intro' => array('符卡',' <span class="red" >黑</span> <span class="red" >白</span>色的少女把心中的不快通通附在了<span class="red" >空白的SC</span> 上,然后一口气放出去 <span class="red" >恋符「极限火花」</span>在此形成',),),
	array(
		'stuff' => array('恋符「极限火花」','恋符「双重火花」'),
		'result' => array('邪恋「超·究级火花」','SW',350,2,),
		'intro' => array('符卡',' <span class="red" >极限火花</span>与 <span class="red" >双重火花</span> 分开来使用实在太麻烦了,准备,瞄准,把魔力一口气全部释放出去,<span  class="red" >邪恋！「超·究级火花」！</span> ',),),
	array(
		'stuff' => array('霊符「夢想妙珠」','神灵「梦想封印」'),
		'result' => array('「梦想天生」','SY',1000,1,),
		'intro' => array('符卡',' 博丽巫女的绝技有<span class="red" >梦想妙珠</span>还有 <span class="red" >梦想封印</span> 话说另一个是叫什么来着？对了对了,是 <span  class="red" >梦想天生</span>',),),
	array(
		'stuff' => array('魔符「アーティフルサクリファス」','上海人形'),
		'result' => array('魔操「归于虚无」','SW',235,15,),
		'intro' => array('高级符卡','以 <span class="red" >上海人形</span>加强<span class="red" >魔符「アーティフルサクリファス</span>,就可以获得<span class="red" >魔操「归于虚无」</span>。',),),
	array(
		'stuff' => array('難題「仏の御石の鉢　-砕けぬ意思-」','蓬莱玉枝'),
		'result' => array('神宝「蓬莱の玉の枝　-夢色の郷-」','SW',250,15,),
		'intro' => array('高级符卡',' <span class="red" >辉夜的五个难题中的一个</span>加之<span class="red" >蓬莱的树枝</span>就可以获得<span class="red" >神宝「蓬莱の玉の枝　-夢色の郷-」</span>。',),),
	array(
		'stuff' => array('花符「幻想郷の开花」','阳伞'),
		'result' => array('幻想「花鸟风月、啸风弄月」','SW',400,2,),
		'intro' => array('高级符卡','以 <span class="red" >风见幽香的SC</span>加上他的<span class="red" >阳伞</span>获得最强SC之一的<span class="red" >幻想「花鸟风月、啸风弄月」</span>',),),
	array(
		'stuff' => array('伤魂「ソウルスカルプチュア」','月时计'),
		'result' => array('幻葬「夜雾の幻影杀人鬼」','SW',225,5,),
		'intro' => array('高级符卡','以 <span class="red" >在咲夜的SC中</span>以<span class="red" >月时计</span>加强对时间的控制力,就可以获得<span class="red" >幻葬「夜雾の幻影杀人鬼」</span>',),),
	array(
		'stuff' => array('爆符「メガフレア」','八沢鸦'),
		'result' => array('「アビスノヴァ」','SW',275,4,),
		'intro' => array('符卡','在 <span class="red" >爆符「メガフレア」</span>上,再附加上 <span class="red" >八沢鸦</span>的力量,就可以制作出强大的核能武器<span class="red" >「アビスノヴァ」</span>。',),),
	array(
		'stuff' => array('红符「不夜城レッド」','运命「ミゼラブルフェイト」'),
		'result' => array('红魔「スカーレットデビル」','SW',250,3,),
		'intro' => array('符卡','以 <span class="red" >大小姐的两张SC</span>融合而成 ,便是更为强大的<span class="red" >红魔「スカーレットデビル」</span>。',),),
	array(
		'stuff' => array('禁忌「恋の迷宫」','禁弾「过去を刻む时计」'),
		'result' => array('QED「495年の波纹」','SW',350,3,),
		'intro' => array('符卡',' <span class="red" >禁忌「恋の迷宫」</span>, <span class="red" >禁弾「过去を刻む时计」</span> 无时无刻不在表现着芙兰对蕾米的眷恋.. <span  class="red" >495年</span>...依旧存活的证明....',),),
	array(
		'stuff' => array('华符「彩光莲华掌」','三华「崩山彩极炮」'),
		'result' => array('星气「星脉地转弹」','SW',320,2,),
		'intro' => array('符卡','以 <span class="red" >中国的两张SC</span>融合而成 ,最为强大的格斗系技能<span class="red" >星气「星脉地转弹」</span>。',),),
	array(
		'stuff' => array('境符「二次元与三次元的境界」','结界「魅力的な四重结界」'),
		'result' => array('「深弾幕結界　-夢幻泡影-」','TN',400,1,),
		'intro' => array('符卡','以 <span class="red" >紫的两张境界SC</span>融合而成 ,可以控制<span class="red" >更为强大的境界</span>谨防掉入间隙。',),),
	array(
		'stuff' => array('断命剑「冥想斩」','人符「现世斩」'),
		'result' => array('人鬼「未来永劫斩」','SW',280,2,array('multistage' => array(0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 1))),
		'intro' => array('符卡',' 生命二刀流<span class="red" >断命剑「冥想斩」</span>, <span class="red" >人符「现世斩」</span> 以及.... <span  class="red" >人鬼</span>,...情未了？',),),
	array(
		'stuff' => array('土着神「ケロちゃん风雨に负けず」','大奇迹「八坂の神风」'),
		'result' => array('「风神様の神徳」','WD',275,3,'s'),
		'intro' => array('符卡','以 <span class="red" >早苗和青蛙子的两张</span>融合而成 ,可以出现<span class="red" >神奈子的高级SC</span>。',),),
	array(
		'stuff' => array('「反魂蝶 八分咲」','幽灵印花折扇'),
		'result' => array('「西行寺无余涅盘」','SW',480,1,),
		'intro' => array('符卡','以 <span class="red" >反魂蝶</span>以<span class="red" >扇子</span>加强灵力,就可以获得拥有控制死亡力量的<span class="red" >「西行寺无余涅盘」</span>。',),),
	array(
		'stuff' => array('黑色水笔','白色水笔','空白的SpellCard'),
		'result' => array('无敌连段「AAAA-ESC」','SW',500,1,),
		'intro' => array('弹幕','传说一部分 <span class="green" >SkillCard</span> 能够自行制作的。比如用 <span class="red" >黑色水笔</span> 和 <span class="red" >白色水笔</span> 涂在 <span class="red" >空白的SpellCard</span> 上，图案会自行变化。变化完毕后这张 <span class="green" >SpellCard</span> 就能使用了。',),),
	array(
		'stuff' => array('盐','大冰块'),
		'result' => array('盐水','HB',50,6,),
		'intro' => array('理化',' <span class="red" >盐</span> 可以加速 <span class="red" >大冰块</span> 的融化，但是..所融化出来的只可能是 <span class="yellow" >盐水</span> ..',),),
	array(
		'stuff' => array('黑历史全系列光盘A','黑历史全系列光盘B','黑历史全系列光盘C'),
		'result' => array('黑历史史册残页','YS',1,1,array('id' => 109)),
		'intro' => array('百科',' <span class="red" >黑历史全系列光盘A</span> 、 <span class="red" >黑历史全系列光盘B</span> 和 <span class="red" >黑历史全系列光盘C</span> 都安装好后便可以通过模拟器开始游戏',),),
	array(
		'stuff' => array('云南白药','邦迪创可贴'),
		'result' => array('云南白药创可贴','HH',400,1,),
		'intro' => array('百科',' <span class="red" >云南白药</span> <span class="red" >邦迪创可贴</span> 伤口好得快， <span class="yellow" >云南白药创可贴</span> 伤口好得快……',),),
	array(
		'stuff' => array('剪刀','巫女服','裹胸布'),
		'result' => array('仿制的灵梦巫女服','DB',120,60,),
		'intro' => array('缝纫','可以将 裹胸布 和 巫女服 用 剪刀 加工，得到灵梦的巫女服（cosplay限定）',),),
	array(
		'stuff' => array('国符「三种の神器 剣」','国符「三种の神器 玉」','国符「三种の神器 鏡」','国体「三种の神器 郷」'),
		'result' => array('终符「幻想天皇」','SW',2000,1,),
		'intro' => array('历史','武烈天皇、平将门、足利义满、盟军总部',),),
	);

//攻击系数
$modulus_attack = array(
	'weather' => array(1.5, 1.2, 1, 0.9, 0.8, 0.73, 0.75, 0.93, 1, 1.05, 1, 1.2, 1, 0.95),
	'area' => array(6 => 1.1, 9 => 0.9, 14 => 0.9, 18 => 1.1),
	'pose' => array(1, 1.2, 0.8, 0.95, 0.8, 0.7),
	'tactic' => array(1, 0.8, 1.05, 0.9, 0.7)
	);

//防御系数
$modulus_defend = array(
	'weather' => array(1.1, 1.1, 1, 1.5, 0.8, 0.73, 0.8, 1, 0.8, 0.8, 1, 1.1, 0.5, 0.97),
	'area' => array(1 => 0.9, 2 => 1.1, 11 => 0.9, 12 => 1.1, 20 => 1.1),
	'pose' => array(1, 0.8, 1.2, 0.9, 0.95, 0.85),
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
	'weather' => array(1.3, 1.1, 1, 0.95, 1, 1, 1, 1.05, 0.88, 0.85, 0.95, 1.1, 1.1, 1.2),
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

//结局
$ending_type = array(
	'error' => '<span class="error">游戏故障</span>',
	'timeup' => '<span class="dieout">全灭</span>',
	'survive' => '<span class="survive">最后幸存</span>',
	'eliminate' => '<span class="eliminate">游戏紧急结束</span>',
	'restart' => '<span class="restart">游戏重设</span>'
	);

?>