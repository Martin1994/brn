<?php
define('GAME_STATE_START', 1);
define('GAME_STATE_COMBO', 2);
define('GAME_PLAYER_USER', 1);
define('GAME_PLAYER_NPC', 3);
//TODO: 尝试加入GAME_PLAYER_BOSS
//TODO: MOD的SQL读取
class game
{
	
	public $gameinfo;
	protected $gameinfo_bak;
	protected $players; //玩家控制系统中的玩家池，脚本结束是会自动更新数据库
	/* @var IChloroDB $db 数据库操作类的引用，摧毁类时有依赖 */
	protected $db;
	
	/**
	 * 游戏类的初始化函数
	 * 会载入gameinfo
	 * 会载入gamesettings
	 * 会在此做出游戏开始与结束的判定（只有全灭结局会在此做出结束判定）
	 */
	public function initialize()
	{
		
		$this->gameinfo = $this->gameinfo_bak = $this->get_gameinfo(); //初始化并备份gameinfo，脚本结束时如果检测到没有更改就不会更新gameinfo
		$GLOBALS['gameinfo'] = &$this->gameinfo; //兼容老代码
		$this->players = array(); //初始化玩家池
		$this->db = $GLOBALS['db']; //引用db类，在摧毁类时有依赖（如果db类先被摧毁会导致无法更新数据库）

		//Load local settings
		$s_cache = cache_read('localsettings.'.$this->gameinfo['settings'].'.serialize');
		if(false !== $s_cache){
			$a_cache = unserialize($s_cache);
		}else{
			$result = $this->db->select('gamesettings', array('settings'), array('name' => $this->gameinfo['settings']));
			if(!is_array($result)){
				throw_error('Failed to access to gamesettings.');
				exit();
			}
			$a_cache = $result[0]['settings'];
			cache_write('localsettings.'.$this->gameinfo['settings'].'.serialize', serialize($result[0]['settings']));
		}
		unset($s_cache);
		foreach($a_cache as $key => $value){
			$GLOBALS[$key] = $value;
		}

		global $map;
		$gameinfo = &$this->gameinfo;

		if(($gameinfo['gamestate'] & GAME_STATE_START) === 0){
			if($gameinfo['starttime'] < time()){
				$this->game_start();
			}else{
				return;
			}
		}

		while($gameinfo['areatime'] <= time() && ($gameinfo['gamestate'] & GAME_STATE_START)){
			$areanum = $this->game_forbid_area();
			if($areanum >= sizeof($map)){
				if($this->gameinfo['validnum'] == 0){
					$this->game_end('noplayer');
				}else{
					$this->game_end('timeup');
				}
			}
		}
		
		return;
	}
	
	/**
	 * 游戏进入下一禁时会调用的函数
	 * 注意开局也会调用这个函数，并将禁数由-1提升至0
	 *
	 * @return int 禁区数量
	 */
	public function game_forbid_area()
	{
		global $a, $db, $gameinfo, $map, $mapsize, $round_area, $weatherinfo, $normal_weather, $combo_round;
		
		$mapsize = sizeof($map);
		
		$gameinfo['round'] ++;
		$gameinfo['areatime'] = $this->get_next_areatime();
		
		//生成禁区列表
		$Farealist = $this->generate_forbidden_area($gameinfo['arealist'], $gameinfo['round'], $round_area);
		$gameinfo['forbiddenlist'] = $Farealist;
		
		//生成危险区列表
		$Darealist = $this->generate_forbidden_area($gameinfo['arealist'], $gameinfo['round'] + 1, $round_area);
		$gameinfo['dangerouslist'] = $Darealist;
		
		$gameinfo['weather'] = mt_rand(0, $normal_weather - 1);
		
		//提前更新gameinfo
		cache_write('gameinfo.serialize', serialize($this->gameinfo));

		//连斗
		if($gameinfo['round'] >= $combo_round){
			$gameinfo['gamestate'] |= GAME_STATE_COMBO;
			$survivor = $db->select('players', '*', array('type' => GAME_PLAYER_USER, 'hp' => array('$gt' => 0)));
			if($survivor !== false){
				$player = null;
				foreach($survivor as $player_data){
					$player = new_player($player_data);
					if(intval($player->hp) > 0){
						break;
					}
				}
				if($player == null){
					$this->game_end('timeup');
				}else {
					$this->game_end('survive', $player, 'individual');
				}
			}else if($survivor == false){
				$this->game_end('timeup');
			}
		}
		
		$a->action('area_info', $this->get_areainfo(), true);
		$a->action('weather', array('name' => $weatherinfo[$gameinfo['weather']]), true);
		
		$this->update_mapitem($gameinfo['round']);
		$this->update_npc($gameinfo['round']);
		$this->update_shopitem($gameinfo['round']);
		
		return sizeof($gameinfo['forbiddenlist']);
	}
	
	/**
	 * 游戏开始时会调用的函数
	 * 进行各种初始化动作，为新一局的游戏创建全新的数据库，清空缓存与推送池
	 * 如果MOD中有新的数据库存储，请务必继承此函数并做出相应处理
	 */
	public function game_start()
	{
		global $db, $c, $gameinfo, $map, $round_area;
		$round = -1;
		$gameinfo['gamestate'] = 0;
		
		/*==================Shops Initialization==================*/
		$column = file_get_contents('gamedata/sql/shop.sql');
		$db->create_table('shop', $column);
		unset($column);
		
		/*==================Items Initialization==================*/
		$column = file_get_contents('gamedata/sql/items.sql');
		$db->create_table('items', $column);
		unset($column);
		
		/*=================Players Initialization=================*/
		$column = file_get_contents('gamedata/sql/players.sql');
		$db->create_table('players', $column);
		unset($column);
		
		/*===================Team Initialization==================*/
		$column = file_get_contents('gamedata/sql/team.sql');
		$db->create_table('team', $column);
		unset($column);
		
		/*===================News Initialization==================*/
		$column = file_get_contents('gamedata/sql/news.sql');
		$db->create_table('news', $column);
		unset($column);
		
		/*========Generate a sequence of forbidden regions========*/
		$arealist = $this->generate_forbidden_sequence();
		
		/*==================Clean up comet pool===================*/
		$c->clear_all();
		
		/*=====================Save Gameinfo======================*/
		$gameinfo['gamenum'] += 1;
		$gameinfo['round'] = $round;
		$gameinfo['forbiddenlist'] = array();
		$gameinfo['alivenum'] = 0;
		$gameinfo['validnum'] = 0;
		$gameinfo['deathnum'] = 0;
		$gameinfo['arealist'] = $arealist;
		$gameinfo['areatime'] = $gameinfo['starttime'];
		$gameinfo['gamestate'] |= GAME_STATE_START;
		$gameinfo['hdamage'] = 0;
		$gameinfo['hplayer'] = '';
		
		/*======================Insert News=======================*/
		$this->insert_news('start', $gameinfo['gamenum']);
		
		return;
	}
	
	/**
	 * 游戏结束时（所有结局）会调用的函数
	 * 进行各种初始化动作，为新一局的游戏创建全新的数据库，清空缓存与推送池
	 * 如果MOD中有对游戏结果的进一步处理（比如玩家积分），请务必继承此函数
	 *
	 * @param string $type 游戏结局
	 * @param player|array $winners 胜利者player对象（可以是数组也可以是单个对象）
	 * @param string $mode 胜利方式（团队或个人）
	 * @return array 胜利玩家的id
	 */
	public function game_end($type = 'timeup', $winners = array(), $mode = 'team') //TODO: 发送推送消息（剧情）
	{
		global $gameinfo, $db, $a;
		
		if(is_array($winners) === false){
			$winners = array($winners);
		}

		$winner_ids = array();
		foreach($winners as $winner){
			$winner_ids[] = $winner->_id;
		}

		//将队伍玩家全部加入胜利者名单中 TODO: 分离各函数
		if($mode === 'team'){
			foreach($winners as $player){
				if($player->teamID != -1){
					$teammates = $db->select('players', '*', array('teamID' => $player->teamID));
					foreach($teammates as $teammate){
						if(false === in_array($teammate['_id'], $winner_ids)){
							$winners[] = new_player($teammate);
						}
					}
				}
			}
		}

		$team_names = array();
		//获取队伍名字
		foreach($winners as $player){
			if($player->teamID == -1){
				$team_names[$player->_id] = '无队伍';
			}else{
				$team = $db->select('team', array('name'), array('_id' => $player['teamID']));
				if($team){
					$team_names[$player->_id] = $team[0]['name'];
				}else{
					$team_names[$player->_id] = '无队伍'; //存储异常
				}
			}
		}
		
		//生成简略信息
		$winner_info = array();
		foreach($winners as &$player){ //此处不用引用会将所有胜利者都变成下标为0的玩家
			$winner_info[] = array(
				'name' => ($player->teamID == -1 ? '' : '['.$team_names[$player->_id].']').$player->name,
				'icon' => $player->icon,
				'motto' => $player->motto
				);
		}
		
		//生成上局胜利玩家
		$winner_name = array();
		foreach($winner_info as $info){
			$winner_name[] = $info['name'];
		}

		$this->insert_news('end_info', array('type' => $type, 'winner' => $winners));

		$gameinfo['gamestate'] = 0;
		$gameinfo['winner'] = $winner_name;
		$gameinfo['winmode'] = $type;
		
		$gameinfo['starttime'] = $this->get_next_game_time();

		$this->insert_news('end');
		
		$a->action('end', array(), true);
		
		$news = $db->select('news', array('time', 'content'));

		$winner_data = array();
		foreach($winners as $winner){
			$winner_data[] = $winner->data;
		}
		
		$db->insert('history', array('gamenum' => $this->gameinfo['gamenum'], 'type' => $type, 'time' => time(), 'winners' => $winner_data, 'winner_info' => $winner_info, 'news' => $news));

		return $winners;
	}

	/**
	 * 返回下次禁区的时间
	 * 如果MOD中有其他需求，请继承或重载此函数
	 *
	 * @return int 下次禁区的时间戳
	 */
	private function get_next_game_time()
	{
		//return time(); //当前时间
		//return $this->gameinfo['areatime'] - $this->gameinfo['areatime'] % $GLOBALS['round_time']; //下个整点
		return time() - time() % $GLOBALS['round_time'] + $GLOBALS['round_time']; //下个整秒数（如3600=一小时，可在设置中调整）
	}
	
	public function renew_top_player(player $hplayer, $damage)
	{
		$this->gameinfo['hdamage'] = intval($damage * 10) / 10;
		$this->gameinfo['hplayer'] = $hplayer->name;
	}
	
	/**
	 * 生成禁区顺序
	 * 如果MOD中有其他需求，请继承此函数
	 *
	 * @return array 禁区顺序
	 */
	protected function generate_forbidden_sequence()
	{
		global $map;
		$mapsize = sizeof($map);
		$arealist = array();
		for($i = 0; $i < $mapsize ; $i++){
			$arealist[$i] = $i;
		}
		//map_0 will be forbbiden automatically
		for($i = 1; $i < $mapsize - 1; $i++){
			$exchange = mt_rand($i, $mapsize - 1);
			$temp = $arealist[$i];
			$arealist[$i] = $arealist[$exchange];
			$arealist[$exchange] = $temp;
		}
		return $arealist;
	}
	
	/**
	 * 根据回合数生成禁区列表
	 * 如果MOD中使用其它方式生成禁区，请重载此函数，但务必保证相同输入会得到相同输出，因为危险区域也会通过此函数生成
	 *
	 * @return array 禁区列表
	 */
	protected function generate_forbidden_area($area_list, $round, $round_area)
	{
		$Farealist = array();
		$areanum = 1 + $round_area * $round;
		$areanum = (sizeof($area_list) > $areanum) ? $areanum : sizeof($area_list);
		for($i = 0; $i < $areanum; $i ++){
			$Farealist[] = $area_list[$i];
		}
		return $Farealist;
	}
	
	/**
	 * 旧物品兼容函数
	 * 旧引擎的物品数据可以通过此函数不经修改而直接使用在新引擎中，自动添加、转换特性
	 * 如果MOD中有其他对于旧数据的处理，请继承此函数
	 * 由于这是个兼容性函数，并不推荐在做MOD时过度依赖此函数，仅建议用作兼容用途
	 */
	public function convert_item(array &$item)
	{
		$short = false;
		if(isset($item['n'])){
			$short = true;
			$item['itm'] = $item['n'];
			$item['itmk'] = $item['k'];
			$item['itme'] = $item['e'];
			$item['itms'] = $item['s'];
			$item['itmsk'] = $item['sk'];
		}
		
		$item['itmsk'] = $this->parse_itmsk($item['itmsk']);
		
		$kind = str_split($item['itmk']);
		switch($kind[0]){
			case 'W': //武器
				if($item['itmk'] === 'WG' && false === isset($item['itmsk']['alt'])){
					$item['itmsk']['alt'] = array('k' => 'P', 'e' => intval($item['itme'] / 5));
				}
				if(isset($kind[2])){
					$alt_kind = $kind[2];
					$item['itmk'] = 'W'.$kind[1];
					$item['itmsk']['alt'] = array('k' => $alt_kind);
				}
				if($kind[1] === 'G' && $item['itms'] == 0){
					$item['itmk'] = 'W'.$item['itmsk']['alt']['k'];
					$item['itmsk']['alt']['k'] = 'G';
					if(isset($item['itmsk']['alt']['e'])){
						$e = $item['itme'];
						$item['itme'] = $item['itmsk']['alt']['e'];
						$item['itmsk']['alt']['e'] = $e;
					}
				}
				break;
			
			case 'P': //毒物
				$times = isset($kind[2]) ? $kind[2] : 1; //剧毒加成
				$item['itmsk']['poison'] = 0;
				global $poison;
				for($i = 0; $i < $times; $i ++){
					$item['itmsk']['poison'] += $poison['Hlast'] * $item['itme'];
				}
				$item['itmk'] = 'H'.$kind[1];	
				break;
		}
		
		if($short){
			$item['n'] = $item['itm'];
			$item['k'] = $item['itmk'];
			$item['e'] = $item['itme'];
			$item['s'] = $item['itms'];
			$item['sk'] = $item['itmsk'];
			
			unset($item['itm']);
			unset($item['itmk']);
			unset($item['itme']);
			unset($item['itms']);
			unset($item['itmsk']);
		}
	}
	
	/**
	 * 添加N禁时新增的地图物品
	 *
	 * @param int $round 禁区次数（开局是0）
	 * @return boolean
	 */
	protected function update_mapitem($round)
	{
		global $db, $map;

		$fp = fopen(get_mod_path(MOD_NAME).'/init/mapitem.php', 'r');
		if(!$fp){
			throw_error('Failed to open data file.');
		}
		
		$mapsize = sizeof($map);
		
		$data = array();
		while(!feof($fp)){
			$line = fgets($fp, 4096);
			
			if(!$line || substr($line, 0, 2) == '//' || substr($line, 0, 1) == '#' || substr($line, 0, 1) == ';'){
				continue;
			}
			
			//$item = explode(',', $line);
			//array_pop($item);
			
			//由于子属性（json）中可能含有逗号，因此不能使用explode
			$item = array();
			$offset = 0;
			$next_offset = 0;
			for($index = 0; $index < 7; $index++){
				$next_offset = strpos($line, ',', $offset);
				if(false === $next_offset){
					continue 2;
				}
				array_push($item, substr($line, $offset, $next_offset - $offset));
				$offset = $next_offset + 1;
			}
			array_push($item, substr($line, $offset, strrpos($line, ',') - $offset));
			
			if(!$item){
				continue;
			}
			
			if(intval($item[0]) !== intval($round) && $item[0] != 99){
				continue;
			}
			
			for($i = 0; $i < $item[2]; $i++){
				$itemdata = array(
					'area' => (intval($item[1]) == 99) ? $this->random(1, $mapsize - 1) : intval($item[1]), //Map_0 has no random item
					'itm' => $item[3],
					'itmk' => $item[4],
					'itme' => intval($item[5]),
					'itms' => intval($item[6]),
					'itmsk' => isset($item[7]) ? $item[7] : ''
					);
				$this->convert_item($itemdata);
				$data[] = $itemdata;
			}
		}
		
		return $db->batch_insert('items', $data, true);
	}
	
	/**
	 * 由NPC的原始数据创建一个完整的NPC数组
	 * 如果MOD中添加了新的玩家数据并需要自定义NPC的该数据，请在npc.php中添加一个键并在此添加进数组
	 *
	 * @param array $player NPC数据
	 * @return array
	 */
	protected function new_npc(&$player)
	{
		global $health_accuracy;
		
		return array_merge($this->blank_player(), array(
			//TODO: add motto
			'name' => $player['name'],
			'type' => GAME_PLAYER_NPC,
			'uid' => '-1',
			'gender' => $this->npc_gender($player),
			'number' => $player['number'],
			'icon' => 'img/n_'.$player['icon'].'.gif',
			'club' => isset($player['club']) ? $player['club'] : 0,
			'pose' => isset($player['pose']) ? $player['pose'] : 0,
			'tactic' => isset($player['tactic']) ? $player['tactic'] : 0,
			'killmsg' => '你看到了一行击杀留言', //TODO
			'lastword' => '你看到了一行死亡讯息', //TODO
			'hp' => isset($player['hp']) ? $player['hp'] * $health_accuracy : $player['mhp'] * $health_accuracy,
			'mhp' => $player['mhp'] * $health_accuracy,
			'sp' => isset($player['sp']) ? $player['sp'] * $health_accuracy : $player['msp'] * $health_accuracy,
			'msp' => $player['msp'] * $health_accuracy,
			'baseatt' => $player['att'],
			'basedef' => $player['def'],
			'area' => $this->npc_area($player),
			'skill' => $player['skill'],
			'lvl' => $player['lvl'],
			'money' => $player['money'],
			'proficiency' => array(
				'p' => $player['proficiency'],
				'k' => $player['proficiency'],
				'g' => $player['proficiency'],
				'c' => $player['proficiency'],
				'd' => $player['proficiency']
				),
			'package' => $this->npc_package($player),
			'equipment' => $this->parse_equipment($this->npc_equipment($player)),
			'capacity' => $player['capacity']
			));
	}
	
	/**
	 * 创建第N禁的NPC
	 *
	 * @param int $round 禁区次数（开局是0）
	 * @return boolean
	 */
	protected function update_npc($round){
		global $db, $health_accuracy;

		/** @var array $npcinfo */
		include (get_mod_path(MOD_NAME).'/init/npc.php');
		
		$players = array();
		foreach($npcinfo as $npc){
			if(!isset($npc['round'])){
				$npc['round'] = 0;
			}
			if(intval($npc['round']) !== intval($round)){
				continue;
			}
			
			$sub = isset($npc['sub']) ? $npc['sub'] : array(array());
			unset($npc['sub']);
			
			$cursor = 0;
			$subsize = sizeof($sub);
			for($i = 0; $i < $npc['num']; $i++){
				$player = array_merge($npc, $sub[$cursor]);
				$player['number'] = $i + 1; //下标从0开始，但是编号从1开始，所以+1
				$players[] = $this->new_npc($player);
				$cursor ++;
				if($cursor >= $subsize){
					$cursor = 0;
				}
			}
		}
		if(sizeof($players) === 0){
			return true;
		}else{
			return $db->batch_insert('players', $players, true);
		}
	}
	
	/**
	 * 添加第N禁时的商店物品
	 *
	 * @param int $round （开局是0）
	 * @return boolean
	 */
	protected function update_shopitem($round){
		global $db, $shopmap;
		
		$fp = fopen(get_mod_path(MOD_NAME).'/init/shopitem.php', 'r');
		if(!$fp){
			throw_error('Failed to open data file.');
		}
		
		$data = array();
		while(!feof($fp)){
			$line = fgets($fp, 4096);
			
			if(!$line || substr($line, 0, 2) == '//' || substr($line, 0, 1) == '#' || substr($line, 0, 1) == ';'){
				continue;
			}
			
			//$item = explode(',', $line);
			//array_pop($item);
			
			//由于子属性（json）中可能含有逗号，因此不能使用explode
			$item = array();
			$offset = 0;
			$next_offset = 0;
			for($index = 0; $index < 10; $index++){
				if($index === 7){
					$length = strlen($line);
					$next_offset = strrpos($line, ',');
					$next_offset = strrpos($line, ',', $next_offset - $length - 1);
					$next_offset = strrpos($line, ',', $next_offset - $length - 1);
				}else{
					$next_offset = strpos($line, ',', $offset);
				}
				if(false === $next_offset){
					continue 2;
				}
				array_push($item, substr($line, $offset, $next_offset - $offset));
				$offset = $next_offset + 1;
			}
			
			if(!$item){
				continue;
			}
			
			if(intval($item[0]) !== intval($round) && $item[0] != 99){
				continue;
			}
			
			if(intval($item[1]) == 99){
				$area = $shopmap;
			}else{
				$area = array(0 => intval($item[1]));
			}
			
			foreach($area as $subarea){
				$data[] = array(
					'area' => intval($subarea),
					'num' => intval($item[2]),
					'itm' => $item[3],
					'itmk' => $item[4],
					'itme' => intval($item[5]),
					'itms' => intval($item[6]),
					'itmsk' => $this->parse_itmsk($item[7]),
					'kind' => intval($item[8]),
					'price' => intval($item[9]) 
					);
			}
			
		}
		
		return $db->batch_insert('shop', $data, true);
	}
	
	/**
	 * 确定NPC的性别
	 * 如果MOD中有有其他性别设定，请务必重载此函数
	 *
	 * @param array $player NPC的数据数组
	 * @return string
	 */
	protected function npc_gender(&$player){
		if(false === isset($player['gd'])){
			$player['gd'] = 'r';
		}
		switch($player['gd']){
			case 'm':
				return 'm';
			 
			case 'f':
				return 'f';
				
			case 'r':
			default:
				return (mt_rand(0, 1) === 0) ? 'f' : 'm';
		}
	}
	
	/**
	 * 确定NPC的位置
	 * 如果MOD有特殊的地图设定（比如某编号的地图不会被随机到），请务必重载此函数
	 *
	 * @param &$player(array) NPC的数据数组
	 * @return int
	 */
	protected function npc_area(&$player)
	{
		if(false === isset($player['pls'])){
			$player['pls'] = 99;
		}
		switch(intval($player['pls'])){
			case 99:
				global $gameinfo, $mapsize;
				return mt_rand(sizeof($gameinfo['forbiddenlist']), $mapsize - 1);
				
			default:
				return intval($player['pls']);
		}
	}
	
	/**
	 * 格式化npc的背包
	 * 这是对旧引擎数据格式的一个兼容函数
	 * 如果MOD中对NPC背包的数据格式有改变，请务必重载此函数
	 *
	 * @param $player array NPC数据数组
	 * @return array
	 */
	protected function npc_package(&$player)
	{
		$package = array();
		
		for($i = 1; isset($player['itm'.$i]) && isset($player['itmk'.$i]) && isset($player['itme'.$i]) && isset($player['itms'.$i]); $i++){
			$package[$i - 1] = array('n' => $player['itm'.$i], 'k' => $player['itmk'.$i], 'e' => $player['itme'.$i], 's' => $player['itms'.$i]);
			$package[$i - 1]['sk'] = isset($player['itmsk'.$i]) ? $this->parse_itmsk($player['itmsk'.$i]) : array();
		}
		$player['capacity'] = $i - 1;
		
		return $package;
	}
	
	/**
	 * 格式化NPC的装备
	 * 这是对旧引擎数据格式的一个兼容函数
	 * 如果MOD中对NPC装备的数据格式有改变，请务必重载此函数
	 *
	 * @return array
	 */
	protected function npc_equipment(&$player)
	{
		$equipment = array();
		
		if(isset($player['arb']) && isset($player['arbk']) && isset($player['arbe']) && isset($player['arbs'])){
			$equipment['arb'] = array(
				'n' => $player['arb'],
				'k' => $player['arbk'],
				'e' => $player['arbe'],
				's' => $player['arbs']
				);
			$equipment['arb']['sk'] = isset($player['arbsk']) ? $this->parse_itmsk($player['arbsk']) : array();
		}
		
		if(isset($player['arh']) && isset($player['arhk']) && isset($player['arhe']) && isset($player['arhs'])){
			$equipment['arh'] = array(
				'n' => $player['arh'],
				'k' => $player['arhk'],
				'e' => $player['arhe'],
				's' => $player['arhs']
				);
			$equipment['arh']['sk'] = isset($player['arhsk']) ? $this->parse_itmsk($player['arhsk']) : array();
		}
		
		if(isset($player['arf']) && isset($player['arfk']) && isset($player['arfe']) && isset($player['arfs'])){
			$equipment['arf'] = array(
				'n' => $player['arf'],
				'k' => $player['arfk'],
				'e' => $player['arfe'],
				's' => $player['arfs']
				);
			$equipment['arf']['sk'] = isset($player['arfsk']) ? $this->parse_itmsk($player['arfsk']) : array();
		}
		
		if(isset($player['ara']) && isset($player['arak']) && isset($player['arae']) && isset($player['aras'])){
			$equipment['ara'] = array(
				'n' => $player['ara'],
				'k' => $player['arak'],
				'e' => $player['arae'],
				's' => $player['aras']
				);
			$equipment['ara']['sk'] = isset($player['arask']) ? $this->parse_itmsk($player['arask']) : array();
		}
		
		if(isset($player['art']) && isset($player['artk']) && isset($player['arte']) && isset($player['arts'])){
			$equipment['art'] = array(
				'n' => $player['art'],
				'k' => $player['artk'],
				'e' => $player['arte'],
				's' => $player['arts']
				);
			$equipment['art']['sk'] = isset($player['artsk']) ? $this->parse_itmsk($player['artsk']) : array();
		}
		
		if(isset($player['wep']) && isset($player['wepk']) && isset($player['wepe']) && isset($player['weps'])){
			$equipment['wep'] = array(
				'n' => $player['wep'],
				'k' => $player['wepk'],
				'e' => $player['wepe'],
				's' => $player['weps']
				);
			$equipment['wep']['sk'] = isset($player['wepsk']) ? $this->parse_itmsk($player['wepsk']) : array();
		}
		
		return $equipment;
	}
	
	/**
	 * 获得下一个禁区的时间
	 * 如果MOD中对禁区时间的算法有改变，请重载此函数
	 *
	 * @return int 禁区时间戳
	 */
	public function get_next_areatime()
	{
		global $round_time;
		$time = $this->gameinfo['areatime'];
		return $time - $time % $round_time + $round_time;
	}
	
	/**
	 * 格式化物品子属性
	 * 兼容旧格式数据，也同时接受直接设定子属性
	 *
	 * @param string|array 物品子属性
	 * @return string 格式化后的子属性
	 */
	protected function parse_itmsk($itmsk)
	{
		if(!$itmsk){
			return array();
		}else if(is_array($itmsk)){
			return $itmsk;
		}else if(substr($itmsk, 0, 1) === '{' && substr($itmsk, -1, 1) === '}'){
			return json_decode($itmsk, true);
		}else{
			$itmsk = str_split($itmsk);
			$result = array();
			foreach($itmsk as $sk){
				$result[$sk] = true;
			}
			return $result;
		}
	}
	
	/**
	 * 获取当前玩家的数据
	 *
	 * @return boolean|player
	 */
	public function current_player()
	{
		global $cuser, $db;

		$data = $db->select('players', '*', array(
			'uid' => $cuser['_id'],
			'type' => GAME_PLAYER_USER
			));
		
		if($data === false){
			return false;
		}else{
			return new_player($data[0]);
		}
	}
	
	/**
	 * 生成一个空白玩家的数组并设置初始值
	 * 如果MOD中需要存储新的数据，可以在此设置一个初始值，如果同时需要使用mysql数据库，请一并修改相关sql文件
	 * 
	 * @return array
	 */
	protected function blank_player()
	{
		return array(
			'name' => '',
			'type' => 0,
			'uid' => '-1',
			'gender' => 0,
			'number' => 0,
			'icon' => '',
			'club' => 0,
			'motto' => '',
			'killmsg' => '',
			'lastword' => '',
			'skill' => array(),
			'daemontime' => time(),
			'deathtime' => time(),
			'killer' => array(),
			'deathreason' => '',
			'cooldowntime' => array(),
			'buff' => array(),
			'action' => array(),
			'pose' => 0,
			'tactic' => 0,
			'hp' => 0,
			'mhp' => 0,
			'sp' => 0,
			'msp' => 0,
			'baseatt' => 0,
			'basedef' => 0,
			'att' => 0,
			'def' => 0,
			'area' => 0,
			'lvl' => 0,
			'exp' => 0,
			'upexp' => 0,
			'money' => 0,
			'killnum' => 0,
			'proficiency' => array(
				'p' => 0,
				'k' => 0,
				'g' => 0,
				'c' => 0,
				'd' => 0
				),
			'teamID' => -1,
			'package' => array(),
			'equipment' => array(),
			'capacity' => 5
			);
	}
	
	/**
	 * 创建新加入游戏的玩家
	 * 如果MOD中有其他玩家初始化的设定，请继承此函数
	 * TODO: 改名，该名字作用不明显
	 *
	 * @return array
	 */
	protected function new_joined_player()
	{
		global $cuser, $health_accuracy, $gameinfo, $param;
		
		$name = $this->np_generate_name($cuser);
		$gender = $this->np_generate_gender($cuser);
		$icon = $this->np_generate_icon($cuser, $gender);
		$club = $this->np_generate_club($cuser);
		$skill = $this->np_generate_skill($cuser, $club);
		$health = $this->np_generate_health($skill);
		$combat_index = $this->np_generate_combat_index($skill);
		$area = $this->np_generate_area($skill);
		$item = $this->np_generate_item($club, $gender);
		
		$player = array(
			'name' => $name,
			'uid' => $cuser['_id'],
			'type' => GAME_PLAYER_USER,
			'gender' => $gender,
			'number' => intval($gameinfo['validnum']),
			'icon' => $icon,
			'club' => $club,
			'motto' => $param['motto'],
			'killmsg' => $param['killmsg'],
			'lastword' => $param['lastword'],
			'skill' => $skill,
			'hp' => $health['hp'] * $health_accuracy,
			'mhp' => $health['mhp'] * $health_accuracy,
			'sp' => $health['sp'] * $health_accuracy,
			'msp' => $health['msp'] * $health_accuracy,
			'baseatt' => $combat_index['baseatt'],
			'basedef' => $combat_index['basedef'],
			'area' => $area,
			'lvl' => 0,
			'money' => $item['money'],
			'proficiency' => array(
				'p' => $combat_index['p'],
				'k' => $combat_index['k'],
				'g' => $combat_index['g'],
				'c' => $combat_index['c'],
				'd' => $combat_index['d']
				),
			'package' => $item['package'],
			'equipment' => $this->parse_equipment($item['equipment']),
			'capacity' => $item['capacity']
			);
		
		return array_merge($this->blank_player(), $player);
	}
	
	/**
	 * 玩家进入游戏
	 * 增加游戏激活与生存人数；发布新激活玩家消息；向新玩家发布初始化界面命令；初始化新玩家的推送数据
	 * 如果MOD中对新玩家有其他设定，请继承此函数
	 */
	public function enter_game()
	{
		global $db, $a, $c, $cuser, $gameinfo, $param;
		//TODO: 连斗判定禁止加入
		$player_probe = $db->select('players', '_id', array('uid' => $cuser['_id'], 'type' => GAME_PLAYER_USER));
		if($player_probe !== false){
			$a->action('error', array('msg' => ($cuser['username'].' 已经加入了游戏')));
			return;
		}
		
		include(get_mod_path(MOD_NAME).'/init/player.php');
		
		$gameinfo['validnum'] ++;
		$gameinfo['alivenum'] ++;
		
		$player = $this->new_joined_player();
		
		$db->insert('players', $player);
		
		do{
			$loop = false;
			$GLOBALS['cplayer'] = $this->current_player();
			if($GLOBALS['cplayer'] === false){
				$loop = true;
				usleep(100000);
			}
		}while($loop); //防止从库延迟
		
		$this->insert_news('join', array('username' => $player['name']));
		
		$this->update_user_game_settings(array(
			'icon' => intval($param['icon']),
			'iconuri' => $player['icon'],
			'gender' => $player['gender'],
			'club' => $player['club']
			));
			
		$c->create($cuser['_id']);
		
		return;
	}
	
	/**
	 * 格式化玩家名字
	 * 首先会判定玩家名是否合法
	 * 如果MOD中有其他对玩家名字的设定（如战队、自动称号等），请继承此函数
	 *
	 * @param array $user 玩家数据（users表）
	 * @return string
	 */
	protected function np_generate_name(&$user)
	{
		global $param;
		if(isset($param['name'])){
			$name = $param['name'];
			if(strlen($name) > 16){
				throw_error('Player name is too long.');
			}
		}else{
			$name = $user['username'];
		}
		return $name;
	}
	
	/**
	 * 格式化玩家性别
	 * 如果MOD中有其他性别设定，请重载此函数
	 *
	 * @param array $user 玩家数据（users表）
	 * @return string
	 */
	protected function np_generate_gender(&$user)
	{
		global $param;
		if(isset($param['gender'])){
			$gender = $param['gender'];
		}else{
			$gender = $user['gender'];
		}
		
		switch($gender){
			case 'f':
				return 'f';
				
			case 'm':
				return 'm';
			
			case 'r':
			default:
				return (mt_rand(0, 1) === 0) ? 'f' : 'm';
		}
	}
	
	/**
	 * 生成玩家头像地址
	 * 如果MOD中更改了头像地址的存储方式，请重载或继承此函数
	 *
	 * @param array $user 玩家数据（users表）
	 * @param string $gender 性别
	 * @return string
	 */
	protected function np_generate_icon(&$user, $gender)
	{
		global $param, $icon_num;
		
		if(false === isset($param['icon'])){
			$param['icon'] = $user['icon'];
			return $user['iconuri'];
		}
		
		$icon = $param['icon'];
		
		if($icon === 'customed'){
			return 'img/upload/'.md5($user['username']).'.img';
		}else{
			if($icon > $icon_num[$gender]){
				throw_error("头像设置错误");
			}
			
			if($icon == 0){
				$icon = mt_rand(1, $icon_num[$gender]);
			}
		
			return 'img/'.$gender.'_'.$icon.'.gif';
		}
	}
	
	/**
	 * 生成玩家社团
	 * 如果MOD中有其他社团设定，请重载此函数
	 *
	 * @param array $user 玩家数据（users表）
	 * @return int
	 */
	protected function np_generate_club(&$user)
	{
		return $this->random(1, 10);
	}
	
	/**
	 * 生成玩家技能
	 * 每个社团都可以有对应的附属技能，在资源文件中修改
	 *
	 * @param array $user 玩家数据（users表）
	 * @param int $club 社团编号
	 * @return array
	 */
	protected function np_generate_skill(&$user, $club)
	{
		global $param;
		if(isset($param['skill'])){//TODO:技能合法判定
			$skills = json_decode($param['skill']);
			if($skills == null){
				throw_error('Unexpected Skill');
			}
		}else{
			$skills = array();
		}
		
		if(isset($GLOBALS['clubskill'][intval($club)])){
			$skills[] = $GLOBALS['clubskill'][intval($club)];
		}
		
		return $skills;
	}
	
	/**
	 * 生成玩家属性点
	 * 如果MOD中有新的生成方式，请重载此函数
	 *
	 * @param array $skill 玩家技能
	 * @return array
	 */
	protected function np_generate_health($skill)
	{
		global $new_player;
		$health['mhp'] = $new_player['mhp'];
		$health['msp'] = $new_player['msp'];
		$health['hp'] = $health['mhp'];
		$health['sp'] = $health['msp'];
		return $health;
	}
	
	/**
	 * 生成玩家战斗属性
	 * 会将所有熟练全部设置为proficiency键的值，并根据部分被动技能增加初始值
	 * 如果MOD中有新的生成方式，请重载此函数
	 *
	 * @param array $skill 玩家技能
	 * @return array
	 */
	protected function np_generate_combat_index($skill)
	{
		global $new_player;
		$index = array();

		$index['baseatt'] = $new_player['baseatt'];
		$index['basedef'] = $new_player['basedef'];
		$index['p'] = $new_player['proficiency'];
		$index['k'] = $new_player['proficiency'];
		$index['c'] = $new_player['proficiency'];
		$index['g'] = $new_player['proficiency'];
		$index['d'] = $new_player['proficiency'];
		
		return $index;
	}
	
	/**
	 * 生成玩家出生地点
	 * 如果MOD中有新的设定，请重载此函数
	 *
	 * @param array $skill 玩家技能
	 * @return int
	 */
	protected function np_generate_area($skill)
	{
		global $new_player;
		$area = $new_player['area'];
		return $area;
	}
	
	/**
	 * 生成玩家包裹与装备
	 * 如果MOD中有新的设定，请重载此函数
	 *
	 * @param int $club 社团编号
	 * @param string $gender 性别
	 * @return array array('equipment' => array 装备, 'package' => array 包裹)
	 */
	protected function np_generate_item($club, $gender)
	{
		global $new_player;
		$item['money'] = $new_player['money']; //初始资金
		$item['capacity'] = $new_player['capacity']; //初始背包容量
		
		$equipment0 = $GLOBALS['universalpackage']; //通用装备
		$equipment1 = isset($GLOBALS['genderpackage'][$gender]) ? $GLOBALS['genderpackage'][$gender] : $GLOBALS['genderpackage']['default']; //性别定制装备
		$equipment2 = isset($GLOBALS['clubpackage'][$club]) ? $GLOBALS['clubpackage'][$club] : $GLOBALS['clubpackage']['default']; //社团定制装备
		
		if(isset($equipment0['item'])){
			$item0 = &$equipment0['item'];
			unset($equipment0['item']);
		}else{
			$item0 = array();
		}
		if(isset($equipment1['item'])){
			$item1 = &$equipment1['item'];
			unset($equipment1['item']);
		}else{
			$item1 = array();
		}
		if(isset($equipment2['item'])){
			$item2 = &$equipment2['item'];
			unset($equipment2['item']);
		}else{
			$item2 = array();
		}
		
		$item['equipment'] = array_merge($equipment0, $equipment1, $equipment2);
		$item['package'] = array_merge($item0, $item1, $item2);
	
		return $item;
	}
	
	/**
	 * 自动填充新NPC身上没有的装备
	 * 
	 * @param array $equipment 自动填充前的装备数组
	 * @return array 自动填充后的装备数组
	 */
	protected function parse_equipment($equipment)
	{
		if(false === isset($equipment['wep']))
		{
			$equipment['wep'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		if(false === isset($equipment['arb']))
		{
			$equipment['arb'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		if(false === isset($equipment['arh']))
		{
			$equipment['arh'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		if(false === isset($equipment['ara']))
		{
			$equipment['ara'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		if(false === isset($equipment['arf']))
		{
			$equipment['arf'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		if(false === isset($equipment['art']))
		{
			$equipment['art'] = array('n' => '', 'k' => '', 'e' => 0, 's' => 0, 'sk' => array());
		}
		
		return $equipment;
	}
	
	/**
	 * 记录战斗伤害
	 * 引擎中是个空函数，如果MOD中需要使用，请重载此函数
	 *
	 * @param float $damage 伤害值
	 * @param player $attacker 攻击者
	 * @param player $defender 防御者
	 */
	public function record_battle_damage($damage, player $attacker, player $defender)
	{
		return;
	}
	
	/**
	 * 进入游戏时提交用户偏好，并更新游戏记录
	 * 如果MOD中保存了其他用户偏好，请重载此函数
	 *
	 * @param array $userparam 用户设定（来自进入游戏前的页面）
	 * @return boolean 数据库操作结果
	 */
	protected function update_user_game_settings($userparam)
	{
		global $db, $cuser, $param;
		
		$user['motto'] = isset($param['motto']) ? $param['motto'] : '';
		$user['killmsg'] = isset($param['killmsg']) ? $param['killmsg'] : '';
		$user['lastword'] = isset($param['lastword']) ? $param['lastword'] : '';
		$user['gender'] = $userparam['gender'];
		$user['icon'] = $userparam['icon'];
		$user['iconuri'] = $userparam['iconuri'];
		$user['club'] = $userparam['club'];
		$user['validgames'] = $cuser['validgames'] + 1;
		$user['ip'] = '0.0.0.0'; //TODO
		$user['lastgame'] = $this->gameinfo['gamenum'];
		
		$condition = array('_id' => $cuser['_id']);
		
		$_SESSION['cuser'] = array_merge($_SESSION['cuser'], $user);
		
		return $db->update('users', $user, $condition);
	}
	
	/**
	 * 根据进行状况数据库与当前存活信息生成进行状况页面并写入缓存（进行状况只会在修改的时候才会生成页面，玩家看到的都是缓存）
	 * 此函数会在生存人数各消息变化以及有新进行状况的时候自动调用，注意此函数不负责生成页面，而是会调用render_news()函数生成页面
	 * 如果MOD中有在进行状况页面有其他需要显示的数据，请重载此函数
	 */
	public function update_news_cache($render = false)
	{
		if(false === $render){
			cache_destroy('news');
			return true;
		}
		global $db;
		
		$players = $db->select(
			'players',
			array('name', 'number', 'gender', 'icon', 'killnum', 'lvl', 'motto', 'type'),
			array('type' => GAME_PLAYER_USER, 'hp' => array('$gt' => 0)),
			0,
			array('killnum' => -1, 'lvl' => -1)
			);
		
		$news = $db->select('news', '*');
		
		if($players === false){
			$players = array();
		}
		
		if($news === false){
			$news = array();
		}
		
		$contents = $this->render_news($players, $news);
		
		cache_write('news', $contents);
		
		return true;
	}
	
	/*
	 * 插入一条新的游戏状态
	 * 如果MOD需要显示其他内容，请继承此函数
	 * 继承函数时请建议使用switch语句，在无任何匹配时调用父函数，否则不调用父函数并写入数据库
	 */
	public function insert_news($type, $args = array())
	{
		global $a;
		
		$content = '';
		switch($type){
			case 'start':
				$content = '<span class="system">第<span class="gamenum">'.$this->gameinfo['gamenum'].'</span>局游戏，开始了</span>';
				break;
			
			case 'end':
				$content = '<span class="system">第<span class="gamenum">'.$this->gameinfo['gamenum'].'</span>局游戏，结束了</span>';
				break;
			
			case 'end_info':
				switch($args['type']){
					case 'survive':
						$winner = $args['winner'][0];
						$content = '<span class="system">'.$winner->name.' 在游戏中最后幸存，游戏结束</span>';
						break;
				
					case 'noplayer':
						$content = '<span class="system">无人参加，游戏结束</span>';
						break;
				
					case 'timeup':
						$content = '<span class="system">游戏时限已到仍未分出胜负，所有幸存者被处决</span>';
						break;
					
					case 'restart':
						$content = '<span class="system">游戏被重设</span>';
						break;
					
					case 'stop':
					default:
						$content = '<span class="system">游戏突然停止了</span>';
						break;
				}
				break;
			
			case 'join':
				$content = '<span class="join"><span class="username">'.$args['username'].'</span>加入了游戏</span>';
				$a->action('notice', array('msg' => $args['username'].'加入了游戏', 'time' => time()));
				break;
			
			case 'kill':
				$args['type'] = isset($args['type']) ? $args['type'] : 'default';
				switch($args['type']){
					case 'weapon_p':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>殴打致死';
						break;
					
					case 'weapon_k':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>斩杀';
						break;
					
					case 'weapon_g':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>射杀';
						break;
					
					case 'weapon_c':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>掷死';
						break;
					
					case 'weapon_d':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>炸死';
						break;
					
					case 'injure':
						$content = '<span class="username">'.$args['killer'].'导致<span class="username">'.$args['deceased'].'</span>旧伤复发致死';
						break;
					
					case 'poison':
						if(isset($args['killer']) && $args['killer']){
							$content = '<span class="username">'.$args['killer'].'</span>下的毒致使<span class="username">'.$args['deceased'].'</span>毒发身亡';
						}else{
							$content = '<span class="username">'.$args['deceased'].'</span>毒发身亡';
						}
						break;
					
					case 'trap':
						if(isset($args['killer']) && $args['killer']){
							$content = '<span class="username">'.$args['killer'].'</span>的<span class="weapon">'.$args['weapon'].'</span>被<span class="username">'.$args['deceased'].'</span>触发，导致其身亡';
						}else{
							$content = '<span class="username">'.$args['deceased'].'</span>成为了陷阱下的冤魂';
						}
						break;
					
					default:
						if(isset($args['killer']) && $args['killer']){
							$content = '<span class="username">'.$args['killer'].'</span>导致<span class="username">'.$args['deceased'].'</span>神秘死亡';
						}else{
							$content = '<span class="username">'.$args['deceased'].'</span>神秘死亡';
						}
						break;
				}	
				break;
			
			default:
				$content = '未知的类型：'.$type.'.';
				break;
		}
		
		global $db;
		$db->insert('news', array('time' => time(), 'content' => $content));
		
		$this->update_news_cache();
		
		return $content;
	}
	
	/**
	 * 生成进行状况页面
	 * 如果MOD中对进行状况页面有修改，请继承或重载此函数
	 *
	 * @return string 生成的内容
	 */
	//TODO: 引擎与模板之间的逻辑略混乱；修改方案：缓存中只有数据格式，样式控制交给js
	//不应把这个函数放在类中
	public function render_news($players, $news)
	{
		$contents = '<div id="news_playerlist">';
		foreach($players as $player){
			$contents .=
				'<div class="player">'.
					'<div class="icon"><img src="'.$player['icon'].'"></div>'.
					'<div class="info">'.
						'<div class="name">'.$player['name'].'</div>'.
						'<div class="number">'.$player['number'].'号</div>'.
						'<div class="gender">'.$GLOBALS['genderinfo'][$player['gender']].'</div>'.
						'<div class="killnum">击杀数量：'.$player['killnum'].'</div>'.
						'<div class="level">等级：'.$player['lvl'].'</div>'.
						'<div class="motto">座右铭：'.$player['motto'].'</div>'.
					'</div>'.
				'</div>';
		}
		$contents .= '</div>';
		
		$contents .= '<div id="news_newslist">';
		foreach($news as $piece){
			$contents .=
				'<div class="news">'.
					'<div class="piece"><span class="time">'.date('H:i:s', $piece['time']).'</span> '.$piece['content'].'</div>'.
				'</div>';
		}
		$contents .= '</div>';
		
		return $contents;
	}
	
	/**
	 * 判定（xx几率发生xx）
	 * 如果MOD中对判定有其他要求，请继承或重载此函数
	 *
	 * @param float $threshold 发生概率
	 * @param float $max 全概率
	 * @return boolean 判定结果
	 */
	public function determine($threshold, $max = 100)
	{
		return ($this->random(0, $max - 1) < $threshold);
	}
	
	/**
	 * 生成随机数
	 * 如果MOD中对随机数有其他要求，请继承或重载此函数
	 *
	 * @param float $min 最小值（含）
	 * @param float $max 最大值（含）
	 * @return int 生成的随机数
	 */
	public function random($min = 0, $max = 99)
	{
		return mt_rand($min, $max);
	}

	/**
	 * 生成正态分布的随机数
	 * @param float $average 期望值
	 * @param float $stdev 标准差
	 * @param boolean $nonnegative 禁止生成负数结果
	 * @return float 生成的随机数
	 */
	public function nb_random($average, $stdev, $nonnegative = false)
	{
		$result = $average;

		for($i = 0; $i < 12; $i++){
			$delta = (mt_rand() / mt_getrandmax() - 0.5) * $stdev;
			$result += $delta;
		}

		if($nonnegative && $result < 0){
			$result = 0;
		}

		return $result;
	}
	
	/**
	 * 玩家数据的预处理（在从数据库调出后正式使用这些数据前）
	 * 将生命与体力的数值从存储值调整为显示值；将拾取中的物品移至包裹的0偏移位置
	 * 非常好用的函数，请在MOD中善加使用
	 *
	 * @param array $data 处理前的玩家数据数组（引用）
	 */
	public function player_data_preprocess(&$data)
	{
		global $health_accuracy;
		
		$data['hp'] /= $health_accuracy;
		$data['mhp'] /= $health_accuracy;
		$data['sp'] /= $health_accuracy;
		$data['msp'] /= $health_accuracy;
		
		$items = array();
		if(isset($data['collecting']['n'])){
			$items[0] = $data['collecting'];
		}
		unset($data['collecting']);
		foreach($data['package'] as $key => &$item){
			$items[$key + 1] = $item;
		}
		$data['package'] = &$items;
	}
	
	/**
	 * 玩家数据的后处理（在执行完游戏功能后存储入数据库前）
	 * 将生命与体力的数值从显示值调整为存储值；将拾取中的物品从包裹的0偏移位置移至单独的键；清空空的包裹物品
	 * 非常好用的函数，请在MOD中善加使用
	 *
	 * @param array $player 处理前的玩家数据数组（引用）
	 */
	public function player_data_postprocess(&$player)
	{
		global $health_accuracy;
		
		if(isset($player['package'][0])){
			$player['collecting'] = &$player['package'][0];
		}else{
			$player['collecting'] = array();
		}
		$items = array();
		foreach($player['package'] as $key => &$item){
			if($key > 0 && $item['n'] != ''){
				$items[] = $item;
			}
		}
		$player['package'] = &$items;
		
		$player['hp'] = round($player['hp'] * $health_accuracy);
		$player['mhp'] = round($player['mhp'] * $health_accuracy);
		$player['sp'] = round($player['sp'] * $health_accuracy);
		$player['msp'] = round($player['msp'] * $health_accuracy);
	}
	
	/**
	 * 获取当前的禁区状况
	 * 如果MOD中对禁区状况的存储或判定方式有所改变，请继承或重载此函数
	 *
	 * @return array array('forbidden' => array 禁区, 'dangerous' => array 即将成为禁区)
	 */
	public function get_areainfo()
	{
		global $gameinfo, $round_area;
		$list = $gameinfo['arealist'];
		$forbidden = $gameinfo['forbiddenlist'];
		$dangerous = $gameinfo['dangerouslist'];
		return array('forbidden' => $forbidden, 'dangerous' => $dangerous);
	}
	
	/**
	 * 获取游戏信息（即老引擎的gameinfo.php）
	 * 如果MOD中游戏信息的存储方式有所改变，请继承或重载此函数
	 *
	 * @return array 游戏信息
	 */
	protected function get_gameinfo()
	{
		global $db;
		
		$cache = cache_read('gameinfo.serialize');
		if(false !== $cache){
			$gameinfo = unserialize($cache);
		}else{
			$result = $db->select('gameinfo', '*');
			if(!is_array($result)){
				throw_error('Failed to access to gameinfo.');
				return null;
			}
			$gameinfo = $result[0];
		}
		
		return $gameinfo;
	}
	
	/**
	 * 以ID作为条件获取玩家数据
	 * 如果该ID已存在于玩家池中则不会访问数据库
	 * 如果MOD中有其他实现，请继承或重载此函数
	 * 
	 * @param string $pid 玩家ID
	 * @return array 玩家数据
	 */
	public function get_player_by_id($pid)
	{
		if(isset($this->players[strval($pid)])){
			$data = &$this->players[strval($pid)];
			return $data;
		}else{
			$data = $this->db->select('players', '*', array('_id' => $pid));
			if(!$data){
				return false;
			}
			return $data[0];
		}
	}
	
	/**
	 * 添加玩家到玩家池
	 * 在player类初始化时会自动将玩家数据添加到玩家池中
	 * 游戏结束时玩家池内的玩家数据会自动更新至数据库
	 * 添加玩家时会自动转换玩家数据（调用game::player_data_preprocess）
	 * 如果添加至游戏池时检测到该玩家已经在玩家池中，那么传来的玩家数据将会变成玩家池中已存在的数据
	 * 如果MOD中有其他动作，请继承此函数
	 * 
	 * @return array 处理后的玩家数据（引用）
	 */
	public function &push_player_pool(array &$data)
	{
		$pid = $data['_id'];
		if(isset($this->players[strval($pid)])){
			$data = &$this->players[strval($pid)];
		}else{
			$this->player_data_preprocess($data);
			
			$this->players[strval($pid)] = &$data;
		}
		
		return $data;
	}
	
	/**
	 * 类销毁时会自动调用的函数
	 * 判断游戏信息是否和一开始相同，不同的话就存储游戏信息
	 * 将玩家池中的所有数据更新至数据库，更新前会自动转换玩家数据（调用game::player_data_postprocess）
	 * 如果MOD中有其他在类销毁时需要处理的内容，请继承此函数
	 * 
	 * @return boolean 常为true
	 */
	public function __destruct()
	{
		//处理玩家池
		foreach($this->players as $pid => &$player){
			$this->player_data_postprocess($player);
		}
		if(sizeof($this->players) == 1){
			$key = array_keys($this->players);
			$this->db->update('players', $this->players[$key[0]], array('_id' => $this->players[$key[0]]['_id']));
		}else{
			$this->db->batch_update('players', $this->players);
		}
		
		foreach($this->gameinfo as $key => $value){
			if($value != $this->gameinfo_bak[$key]){
				cache_write('gameinfo.serialize', serialize($this->gameinfo));
				$this->db->update('gameinfo', $this->gameinfo);
			}
		}
		return true;
	}
	
}

?>