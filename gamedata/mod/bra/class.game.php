<?php
//TODO DN死亡原因
class game_bra extends game
{
	public function game_end($type = 'timeup', $winner = array(), $mode = 'team')
	{
		$winner = parent::game_end($type, $winner, $mode);
		
		return;
	}
	
	public function game_forbid_area()
	{
		$return = parent::game_forbid_area();
		
		global $db, $map;
		$forbidden = $this->gameinfo['forbiddenlist'];
		$safe = array();
		$all = array();
		for($i = 0; $i < sizeof($map); $i ++){
			if(false === in_array($i, $forbidden)){
				$safe[] = $i;
			}
			$all[] = $i;
		}
		
		//禁区死亡
		$players_dying = $db->select('players', '*', array('type' => GAME_PLAYER_USER, 'area' => array('$in' => $forbidden), 'hp' => array('$gt' => 0), 'tactic' => array('$ne' => 3)));
		if(is_array($players_dying)){
			foreach($players_dying as $pdata){
				$player = new_player($pdata);
				$player->sacrifice(array('type' => 'forbid'));
			}
		}
		unset($players_dying);
		
		//NPC换区
		$this->moving_NPC($all, $safe);
		
		return $return;
	}
	
	public function moving_NPC($areas, $targets)
	{
		global $db;
		
		if(sizeof($targets) === 0){
			return;
		}
		
		//99区为凸眼鱼吹走的区域
		$npcs = $db->select('players', array('_id', 'skill'), array('type' => GAME_PLAYER_NPC, 'area' => array('$in' => $areas), 'area' => array('$ne' => 99), 'hp' => array('$gt' => 0)));
		foreach($npcs as $ndata){
			if(false === in_array('Peg', $ndata['skill'])){
				$target = $targets[array_rand($targets)];
				$db->update('players', array('area' => $target), array('_id' => $ndata['_id']));
			}
		}
		
		return;
	}
	
	protected function npc_area(&$player)
	{
		if(false === isset($player['pls'])){
			$player['pls'] = 99;
		}
		switch(intval($player['pls'])){
			case 99:
				global $map;
				$forbidden = $this->gameinfo['forbiddenlist'];
				$safe = array();
				for($i = 0; $i < sizeof($map); $i ++){
					if(false === in_array($i, $forbidden)){
						$safe[] = $i;
					}
				}
				return $safe[array_rand($safe)];
				
			default:
				return intval($player['pls']);
		}
		return false;
	}
	
	protected function new_player()
	{
		$player = parent::new_player();
		$player['exp'] = $GLOBALS['gameinfo']['round'] * 75; //晚进游戏的补偿
		return $player;
	}
	
	public function player_data_preprocess(&$data)
	{
		parent::player_data_preprocess($data);
		
		//怒气精度调整
		$data['rage'] /= $GLOBALS['health_accuracy'];
	}
	
	public function player_data_postprocess(&$data)
	{
		parent::player_data_postprocess($data);
		
		//怒气精度调整
		$data['rage'] = round($data['rage'] * $GLOBALS['health_accuracy']);
	}
	
	protected function blank_player()
	{
		$data = parent::blank_player();
		//增加怒气栏位
		$data['rage'] = 0;
		return $data;
	}
	
	protected function np_generate_combat_index($skills)
	{
		$index = parent::np_generate_combat_index($skills);
		
		foreach($skills as $skill){
			switch($skill){
				case 'Pro_P':
					$index['p'] += 20;
					break;
				
				case 'Pro_K':
					$index['k'] += 20;
					break;
				
				case 'Pro_G':
					$index['g'] += 20;
					break;
				
				case 'Pro_C':
					$index['c'] += 20;
					break;
				
				case 'Pro_D':
					$index['d'] += 20;
					break;
			}
		}
		
		return $index;
	}
	
	public function enter_game()
	{
		if(!($this->gameinfo['gamestate'] & GAME_STATE_COMBO)){
			parent::enter_game();
			$GLOBALS['a']->action('brief', array('html' => $this->generate_welcome_message()));
		}else{
			$GLOBALS['a']->action('need_join'); //TODO
		}
	}
	
	protected function generate_welcome_message()
	{
		$message = '
<p class="welcome">
<img border="0" src="img/i_hayashida.gif" width="70" height="70"><br /><br />
你是转校生？我是班主任林田。<br />嘿嘿，你很懂挑学校嘛！ (露出邪恶的笑容)<br />

转校手续刚办完，明天就是毕业旅行。<br>
你可真幸运，千万记着不要迟到！<br><br>

张开眼睛后，发现自己在一个像教室的地方。我不是应该去了修学旅行吗···？<br>
「对了，在去修学旅行的巴士中忽然睡意袭来···」<br>
纵览四周，看见其他的学生好像也在。用心地看的话，发现了大家的颈上套上了银色项圈，<br>
用手碰自己的颈，也感觉到冷冷的金属触感。<br>
正在疑惑大家为什么都套上同样的那个银色项圈的时候...<br><br>
突然，从前面的门，一个男人全副武装装备的军人走了进来···。<br><br>
<img border="0" src="./img/n_1.gif" width="70" height="70"><br><br>
「大家好，一年前的时候我也是这次计划的担当者。很荣幸能再担任此次计划的任务。很好！<br>
随着时间日子人民越来越安于现状，过着幸福日子的时候，相信各位已经忘记了国家曾多努力多辛苦才能建成今天的社会地位，<br>
如今国家开始衰退，想再振兴，但人们已经再没有自信，这是很危险的。因此，伟大的人们商量制定了这个计划。<br><br>

<font color="#ff0000" face="verdana" size="6">
<span id="br" style="width:100%;filter:blur(add=1,direction=135,strength=9):glow(strength=5,color=gold); font-weight:700; text-decoration:underline">
■ BATTLE ROYALE ■</span></font><br>

今天起开始，在这里诸位要开始互相杀害对方。<br>
如果你想取下那个项圈，尝试打算逃走的话，你将会立即被杀。<br><br>
直到剩下一人生存为止，乖乖遵守别犯规。<br>
哎呀，老师都忘记了说，这里是一个四面环海的荒岛。<br><br>
而这里是这个岛的分校。<br>
老师会一直在这里看着各位努力。<br><br>
那么，开始说这个计划如何执行。你从这里出去后去哪里也可以。<br>
每天8小时 (0点和8点和6点)，做全岛广播。一日三回。<br><br>
在那里，大家会看到地图，这个区域什么时候危险老师会告知。<br>
好好地了解地图，离开那一个区域，<br>
要很快地从那个区域出来喔。<br><br>
为何会这样说呢，不逃离广播危险区域的范围，那个项圈是会爆炸的。<br><br>
因此呀，潜伏在该区域中的建筑物中也是不行。<br>
就算挖洞隐藏无线电波也会找到你引爆喔。<br>
对了，建筑物平常是可以让你任意隐藏的。<br><br>
但还是你要知道。计划有时间限制。你只有<b><font color="red">一天</font></b>时间去完成。<br><br>
时间够如果还留下不止一人，剩下的那些人的项圈一样会爆炸。因为冠军只能够存活<u>—人</u>。<br><br>
既然参加了游戏就要全力以赴，老师可不想看到没胜利者呢！<br>
你们每个人将被派发到一个物品包，里面有食物和水，指南针，以及一件武器。<br><br>
下面开始，按照学号，拿好你们的东西，一个个离开这里！<br><br>
<br>
<font color="#666666">（点击任意处继续）</font>
</p>
		';
		return $message;
	}
	
	public function insert_news($type, $args = array())
	{
		$content = '';
		switch($type){
			case 'damage':
				$damage = $args['damage'];
				$attacker = $args['attacker'];
				$defender = $args['defender'];
				
				if($damage > 1000){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>燃烧自己的生命得到了不可思议的力量！！！！！ 「<span class="damage">'.$damage.'</span>」 点的伤害值，没天理啊…<span class="username">'.$defender.'</span>死-定-了！！！！！</span>';
				}else if($damage > 750){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>受到天神的加护，打出惊天动地的一击 – <span class="username">'.$defender.'</span>被打掉<span class="damage">'.$damage.'</span>点生命值！！！！！</span>';
				}else if($damage > 600){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>手中的武器闪耀出七彩光芒！！！！<span class="username">'.$defender.'</span>招架不住，生命减少<span class="damage">'.$damage.'</span>点！！！！</span>';
				}else if($damage > 500){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>使出呼风唤雨的力量！！！！可怜的<span class="username">'.$defender.'</span>受到了<span class="damage">'.$damage.'</span>点的伤害！！！！</span>';
				}else if($damage > 400){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>使出浑身解数奋力一击！！！受到了<span class="damage">'.$damage.'</span>点伤害！！！<span class="username">'.$defender.'</span>还活着吗？</span>';
				}else if($damage > 300){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>发出会心一击！！！<span class="username">'.$defender.'</span>损失了<span class="damage">'.$damage.'</span>点生命！！！</span>';
				}else if($damage > 250){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>简直不是人！！<span class="username">'.$defender.'</span>瞬间被打了<span class="damage">'.$damage.'</span>点伤害！！</span>';
				}else if($damage > 200){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>拿了什么神兵！？<span class="username">'.$defender.'</span>被打了<span class="damage">'.$damage.'</span>滴血！！</span>';
				}else{
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>对<span class="username">'.$defender.'</span>做出了<span class="damage">'.$damage.'</span>点的攻击！ 一定是有练过！</span>';
				}
				break;
			
			case 'flounder':
				$content = '<span class="system"><span class="username">'.$args['caster'].'</span> 招来一阵怪风，把地上的尸体都吹走了！</span>';
				break;
			
			case 'kill':
				$args['type'] = isset($args['type']) ? $args['type'] : 'default';
				switch($args['type']){
					case 'forbid':
						$content = '<span class="username">'.$args['deceased'].'</span>因滞留禁区死亡';
						break;
						
					default:
						$content = parent::insert_news($type, $args);
						return $content;
						break;
				}
				break;
			
			default:
				$content = parent::insert_news($type, $args);
				return $content;
				break;
		}
		
		global $db;
		$db->insert('news', array('time' => time(), 'content' => $content));
		
		$this->update_news_cache();

		return $content;
	}
	
	public function record_battle_damage($damage, player $attacker, player $defender)
	{
		if($damage >= 100){
			$GLOBALS['g']->insert_news('damage', array(
				'attacker' => $attacker->name,
				'defender' => $defender->name,
				'damage' => intval($damage * 10) / 10
				));
		}
	}
	
}

?>