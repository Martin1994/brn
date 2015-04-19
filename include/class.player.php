<?php
//TODO: 超大型提示（伤害 击杀 损坏等）
//TODO: 中毒体力回复bug（待验证）
//TODO: 尸体雾天bug（应该在mod里）
/**
 * Class player
 * @property string $_id 玩家id
 * @property string $uid 玩家用户id（"-1"代表NPC）
 * @property int $type 玩家类型（见GAME类开头的定义）
 * @property string $gender 性别
 * @property string $icon 头像相对路径
 * @property int $club 社团编号
 * @property string $name 玩家名字
 * @property array $skill 可用技能
 * @property int $daemontime 上次调用daemon函数的时间
 * @property int $deathtime 死亡时间
 * @property array $killer 凶手的玩家id
 * @property string $deathreason 死因
 * @property array $buff buff列表
 * @property array $action 动作列表
 * @property int $tactic 应战策略编号
 * @property int $pose 基础姿态编号
 * @property float $hp 生命值
 * @property float $mhp 最大生命值
 * @property float $sp 体力值
 * @property float $msp 最大体力值
 * @property float $att 攻击力（计算后）
 * @property float $def 防御力（计算后）
 * @property float $baseatt 基础攻击力（不计装备）
 * @property float $basedef 基础防御力（不计装备）
 * @property int $area 地图编号
 * @property int $lvl 玩家等级
 * @property int $exp 玩家经验
 * @property int $upexp 升级所需经验
 * @property int $money 玩家所持金钱
 * @property int $killnum 击杀数
 * @property array $proficiency 熟练度
 * @property string $teamID 队伍id（"-1"为无队伍）
 * @property array $collecting 拾取中的物品（空数组为无拾取）
 * @property int $capacity 背包容量
 * @property array $package 背包
 * @property array $equipment 装备
 */
class player
{

	/**
	 * 玩家类初始化时调用的函数
	 * 加入玩家控制系统（自动在执行结束时更新玩家数据并自动完成数据的预处理后处理）
	 * 如果该玩家刚刚生成，会初始化攻击力和经验
	 * 在执行命令前，会先执行上次操作至今的每秒动作（回血 buff等）
	 * 如果MOD中有其他需要执行的功能，请继承此函数
	 *
	 * @param array $data 玩家数据，与数据库中的结构相同
	 */
	public $data;

	public function __construct(&$data)
	{
		global $g;
		//加入玩家池
		$g->push_player_pool($data);
		
		$this->data = &$data;
		
		//初始化攻击力
		if($this->att == 0 && $this->def == 0){
			$this->active_suits();
			$this->calculate_battle_info();
		}
		
		//初始化经验值
		if($this->exp == 0 && $this->lvl > 0){
			$this->lvl --;
			$this->calculate_target_experience();
			$this->exp = $this->upexp;
			$this->lvl ++;
			$this->calculate_target_experience();
		}
		
		//执行每秒持续动作
		while($this->is_alive() && $this->daemontime < time()){
			$this->daemon();
		}
		
		return;
	}
	
	/**
	 * 增加一个buff
	 * 增加完毕后会自动更新生命恢复速率
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @param string $name buff名
	 * @param int $duration buff持续时间
	 * @param array $param 其他参数
	 */
	public function buff($name, $duration = 0, array $param = array())
	{
		if(substr($name, -5) === '_suit'){
			foreach($this->data['buff'] as &$buff){
				if($buff['type'] === $name){
					$buff['param'] = array_merge($buff['param'], $param);
					$this->ajax('buff', array('buff' => $this->parse_buff()));
					return;
				}
			}
		}
		
		$time = ($duration == 0) ? 0 : time() + $duration;
		if($param){
			$this->data['buff'][] = array('type' => $name, 'time' => $time, 'param' => $param);
		}else{
			$this->data['buff'][] = array('type' => $name, 'time' => $time);
		}
		
		$this->ajax('buff', array('buff' => $this->parse_buff()));
		
		if($name == 'poison'){
			$hr = $this->get_heal_rate();
			$this->ajax('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
		}
	}
	
	/**
	 * 移除一个buff
	 * 移除完毕后会自动更新生命恢复速率
	 * 如果MOD中有其他设定，请继承或重载此函数
	 * 若要判断被移除的buff是什么，在unset（调用父函数）之前判断 $this->data['buff'][$key]['type'] 的内容
	 *
	 * @param int $key 需要移除的buff编号
	 */
	public function remove_buff($key)
	{
		$buff = &$this->data['buff'][$key];
		
		unset($this->data['buff'][$key]);
		
		$this->ajax('buff', array('buff' => $this->parse_buff()));
		
		if($buff['type'] == 'poison'){
			$hr = $this->get_heal_rate();
			$this->ajax('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
		}
	}
	
	/**
	 * 将现有buff生成为一个只有种类与持续时间的数组，用于显示
	 *
	 * @return array 用于显示的buff数组
	 */
	public function parse_buff()
	{
		$buffs = array();
		foreach($this->buff as $buff){
			$buffs[] = array(
				'type' => $buff['type'],
				'sec' => ($buff['time'] == 0) ? -1 : ($buff['time'] - time())
				);
		}
		return $buffs;
	}
	
	/**
	 * 获得经验
	 * 到达经验会自动升级
	 * 如果MOD中有其他设定，请继承或重载此函数，注意升级时执行的动作不在此函数内，而是在levelup()函数内
	 *
	 * @param int $add 增加的经验值
	 */
	public function experience($add = 1)
	{
		$this->exp += $add;
		$result = array('current' => $this->exp);
		
		while($this->exp >= $this->upexp){
			$this->lvl ++;
			$this->calculate_target_experience();
			$this->levelup();
			$result['target'] = $this->upexp;
			$result['level'] = $this->lvl;
		}
		
		$this->ajax('exp', $result);
	}
	
	/**
	 * 升级时调用的函数
	 * 会自动更新进行状况的缓存
	 * 如果MOD中有其他设定（例如属性提升），请继承或重载此函数
	 *
	 * @param string $extra_text 如果MOD中需要显示额外的内容而又不想重载函数，可以传入此参数
	 */
	protected function levelup($extra_text = '')
	{
		//升级时若要执行属性提升，请继承此函数
		$this->notice('你升级了'.$extra_text);
		$GLOBALS['g']->update_news_cache();
	}
	
	/**
	 * 计算达到下一等级需要多少经验
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @return int 需要的经验值
	 */
	public function calculate_target_experience()
	{
		return $this->upexp = ($this->lvl * 2 + 1) * $GLOBALS['base_exp'];
	}
	
	/**
	 * 移动
	 * 如果目的地相同会自动转换为探索
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param int $destination 目的地的地图编号
	 */
	public function move($destination)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $map, $shopmap;
		$data = &$this->data;
		$destination = intval($destination);
		
		if($data['action'] && isset($data['action']['battle'])){
			$this->error('战斗中，无法移动');
		}
		
		if(isset($this->package[0])){
			$this->error('请在移动前先决定如何处理拾取到的物品');
		}
		
		if(intval($data['area']) === $destination){
			//已经在目的地
			$this->search();
		}else{
			
			if($destination >= sizeof($map)){
				$this->error('无效的目的地');
			}
			
			$status = $this->area_status($destination);
			
			if($status === false){
				$this->error($map[$destination].' 为禁区，禁止进入');
			}
			
			//消耗
			$consumption = $this->get_consumption('move');
			
			$this->check_health($consumption, 'move');
			
			//开始移动
			$data['area'] = $destination;
			$this->feedback('移动到了 '.$map[$destination]);
			$this->ajax('location', array('name' => $map[$data['area']], 'shop' => in_array(intval($data['area']), $shopmap, true)));
			
			$this->discover('move');
			
			$hr = $this->get_heal_rate();
			$this->ajax('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
		}
	}
	
	/**
	 * 探索
	 * 如果MOD中有其他设定，请继承此函数
	 */
	public function search()
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if($this->action && isset($this->action['battle'])){
			$this->error('战斗中，无法搜索');
		}
		
		if(isset($this->package[0])){
			$this->error('请在搜索前先决定如何处理拾取到的物品');
		}
		
		if($this->area_status($this->area) === false){
			$this->error('这里是禁区，还是赶紧离开吧');
		}
		
		//消耗
		$consumption = $this->get_consumption('search');
		
		$this->check_health($consumption, 'search');
		
		$this->discover('search');
	}
	
	/**
	 * 获取商店物品列表（根据当前玩家所在位置）
	 *
	 * @param int $kind 物品种类（柜台）
	 * @param boolean $show 是否用于显示（否则就是用于判断商店货物存在），区别在于返回实际数据还是显示数据，比如无限大和0
	 *
	 * @return array 物品列表
	 */
	public function get_goods($kind = 0, $show = false)
	{
		global $shopmap;
		
		if(false === in_array(intval($this->area), $shopmap)){
			$this->feedback('这个地区没有商店');
			return array();
		}
		
		$items = $this->get_shop_items(array('area' => intval($this->area), 'kind' => intval($kind)));
		if($items === false){
			$items = array();
		}
		
		global $iteminfo;
		
		foreach($items as &$item){
			$item['n'] = &$item['itm'];
			unset($item['itm']);
			$item['k'] = $item['itmk'];
			unset($item['itmk']);
			$item['e'] = &$item['itme'];
			unset($item['itme']);
			if($show && $item['itms'] == 0){
				$item['s'] = '∞';
			}else{
				$item['s'] = &$item['itms'];
			}
			unset($item['itms']);
			$item['max'] = $this->goods_max_num($item);
			$item['k'] = isset($iteminfo[$item['k']]) ? $iteminfo[$item['k']] : $iteminfo['default'];
		}
		
		return $items;
	}
	
	/**
	 * 获取商店物品
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @param array $condition 物品数组
	 * @return array|boolean
	 */
	protected function get_shop_items($condition)
	{
		return $GLOBALS['db']->select('shop', '*', $condition);
	}
	
	/**
	 * 获取商店货品最大购买量
	 * 不可合成的物品都为1
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @param array $item 物品数组
	 * @return int
	 */
	protected function goods_max_num($item)
	{
		if($this->item_mergable($item)){
			return $item['num'];
		}else{
			return 1;
		}
	}

	/**
	 * 购买物品
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param array $cart 购物车
	 */
	public function buy(array $cart)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $db;
		$kind = 0;
		foreach($cart as $iid => $num){
			$goods = $this->get_shop_items(array('_id' => $iid, 'area' => $this->area));
			if(false === $goods){
				continue;
			}
			$goods = $goods[0];
			$goods['n'] = $goods['itm'];
			$goods['k'] = $goods['itmk'];
			$goods['e'] = $goods['itme'];
			$goods['s'] = $goods['itms'];
			$goods['sk'] = $goods['itmsk'];
			
			$kind = $goods['kind'];
			
			if(isset($this->package[$this->capacity]) && isset($this->package[0])){
				$this->feedback('背包满了，不能再买新的东西了');
				break;
			}
			
			$num = intval($num);
			if($num > $goods['num']){
				$num = $goods['num'];
			}
			$max = $this->goods_max_num($goods);
			
			for($i = 0; $i < $num; $i++){
				if(($this->money - $goods['price']) < 0){
					$this->feedback('资金不足');
					break;
				}
				$this->data['money'] -= $goods['price'];
			}
			
			if($i > 0){
				$goods['num'] -= $i;
				
				$this->feedback($i.'个'.$goods['itm'].'购买成功');
			}else{
				continue;
			}
			
			if($goods['num'] <= 0){
				$db->delete('shop', array('_id' => $goods['_id']));
			}else{
				$db->update('shop', array('num' => $goods['num']), array('_id' => $goods['_id']));
			}
			
			$new_item = array(
				'n' => $goods['n'],
				'k' => $goods['k'],
				'e' => $goods['e'],
				's' => $goods['s'] * $i,
				'sk' => $goods['sk']
				);
			
			if(false === isset($this->package[$this->capacity])){
				$this->data['package'][$this->capacity] = $new_item;
			}else{
				$this->data['package'][0] = $new_item;
			}
			$this->rearrange_package();
		}
		$this->ajax('item', array('package' => $this->parse_package()));
		$this->ajax('money', array('money' => $this->money));
		$this->ajax('shop', array(
			'kind' => $kind,
			'goods' => $this->get_goods($kind)
			));
	}
	
	/**
	 * 使用物品
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param $iid(int) 要使用的物品ID
	 * @param $param(array) 使用参数（绝大多数物品没有参数，有参数的物品如果无参数传入的话会向玩家弹出对话框要求输入参数）
	 */
	public function item_apply($iid, $param = array())
	{
		if(false === $this->is_alive()){
			return $this->error('你已经死了');
		}
		
		$iid = intval($iid);
		
		if($iid > $this->capacity || $iid < 0){
			$this->error('请指定正确的道具');
		}
		
		if(false === isset($this->package[$iid])){
			$this->error('道具不存在');
		}
		
		$item = new_item($this, $this->package[$iid], $iid);
		$item->apply($param);
		$this->ajax('item', array('package' => $this->parse_package()));
	}
		
	/**
	 * 获取默认毒效
	 * 食品毒效为持续时间，武器毒效为持续攻击次数
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @param string $kind 物品类型
	 * @return int $item_e 毒效
	 */
	public function get_poison_power($kind, $item_e)
	{
		global $poison;
		switch(substr($kind, 0, 1)){
			case 'W':
				return $poison['Wturn'];
				break;
			
			case 'H':
				return intval($poison['Hlast'] * $item_e);
				break;
			
			default:
				return -1;
				break;
		}
	}
	
	/**
	 * 获取补给品治疗效果（倍数）
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @return array
	 */
	public function get_potion_effect()
	{
		return array('hp' => 1, 'sp' => 1);
	}
	
	/**
	 * 获取可下毒（淬毒）的物品
	 * 如果MOD中有其他设定，请继承或重载此函数
	 */
	public function get_envenomable_items()
	{
		$result = array();
		
		if($this->equipment['wep']['n'] !== ''){
			$result['wep'] = $this->equipment['wep'];
		}
		
		foreach($this->package as $iid => $item){
			if(substr($item['k'], 0, 1) === 'H'){
				$result[$iid] = $item;
			}
		}
		
		return $result;
	}
	
	/**
	 * 装备物品
	 * 会由使用物品的函数自动调用
	 * 装备完毕后会自动更新玩家战斗属性
	 * 如果该部位已有物品会被自动换下
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * param int $iid 要装备的物品ID
	 */
	public function equip($iid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $equipment_position;
		if(false === isset($equipment_position[$this->package[$iid]['k']])){
			$this->error('错误的装备类型'); //理论上这条不会显示
		}
		
		$position = $equipment_position[$this->package[$iid]['k']];
		
		$temp = $this->equipment[$position];
		$this->equipment[$position] = $this->package[$iid];
		$this->package[$iid] = $temp;
		if($this->package[$iid]['n'] == ''){
			unset($this->package[$iid]);
		}
		
		$this->feedback($this->equipment[$position]['n'].' 装备成功');
		
		$this->active_suits();
		
		//更新缓存与用户端显示
		$this->calculate_battle_info();
		$this->rearrange_package();
		$this->ajax('item', array('equipment' => $this->parse_equipment()));
	}
	
	/**
	 * 卸载装备
	 * 卸载完毕后会自动更新玩家战斗属性
	 * 如果MOD中有其他设定，请继承或重载此函数
	 */
	public function unload($position)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		switch($position){
			case 'wep':
			case 'arb':
			case 'arh':
			case 'ara':
			case 'arf':
			case 'art':
				break;
			
			default:
				return $this->error('请指定正确的部位');
				break;
		}
		$n = $this->equipment[$position]['n'];
		if($n == ''){
			return $this->error('该部位没有任何装备');
		}
		
		for($i = 1; $i <= $this->capacity; $i ++){
			if(false === isset($this->package[$i])){
				$iid = $i;
				break;
			}
		}
		if(false === isset($iid)){
			if(isset($this->package[0])){
				$this->error('物品栏已满， '.$n.' 不能卸载');
			}else{
				$iid = 0;
			}
		}
		
		global $null_item;
		
		$this->package[$iid] = $this->equipment[$position];
		$this->equipment[$position] = $null_item;
		
		$this->feedback($n.' 卸载成功');
		
		$this->active_suits();
		
		//更新缓存与用户端显示
		$this->calculate_battle_info();
		$this->rearrange_package();
		$this->ajax('item', array('equipment' => $this->parse_equipment(), 'package' => $this->parse_package()));
	}
	
	/**
	 * 丢弃物品
	 * 如果MOD中有其他设定，请继承此函数
	 */
	public function item_drop($iid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		$iid = intval($iid);
		
		if($iid > $this->capacity || $iid < 0){
			$this->error('请指定正确的道具');
		}
		
		if(false === isset($this->package[$iid])){
			$this->error('道具不存在');
		}
		
		$name = $this->package[$iid]['n'];
		$item = array(
			'itm' => $this->package[$iid]['n'],
			'itmk' => $this->package[$iid]['k'],
			'itme' => intval($this->package[$iid]['e']),
			'itms' => intval($this->package[$iid]['s']),
			'itmsk' => $this->package[$iid]['sk'],
			'area' => intval($this->area)
			);
		
		global $db;
		$db->insert('items', $item);
		
		unset($this->package[$iid]);
		
		//更新缓存与用户端显示
		$this->rearrange_package();
		$this->ajax('item', array('package' => $this->parse_package()));
		
		$this->feedback($name.' 丢弃成功');
	}
	
	/**
	 * 拾取物品
	 * 正在拾取的物品存放在包裹0偏移的位置
	 * 如果MOD中有其他设定，请继承此函数
	 */
	public function collect()
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if(false === isset($this->package[0])){
			$this->error('没有要拾取的物品');
		}else if(isset($this->package[$this->capacity])){
			$this->error('物品栏已满');
		}else{
			$this->package[$this->capacity] = &$this->package[0];
			unset($this->package[0]);
			$this->rearrange_package();
		}
		$this->ajax('item', array('package' => $this->parse_package()));
	}
	
	/**
	 * 自动整理包裹
	 * 由于BRN不允许背包隔着空间存放，因此每次有空格可能出现时都会自动整理包裹
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * return null
	 */
	public function rearrange_package()
	{
		$items = array();
		$items[0] = array();
		foreach($this->package as $key => $item){
			if($key == 0){
				$items[0] = $item;
			}else{
				$items[] = $item;
			}
		}
		if(false === isset($items[0]['n']) || $items[0]['n'] == ''){
			unset($items[0]);
		}
		$this->package = $items;
	}
	
	/**
	 * 合并物品时处理相同的子属性
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * param $sk(string) 子属性
	 * param $old(int) 其中一个合并物品的子属性值
	 * param $new(int) 另外一个合并物品的子属性值
	 * return mixed 处理完毕后的子属性值
	 */
	protected function merge_item_subkind($sk, $old, $new)
	{
		switch($sk){
			case 'poison': //TODO: 兼容带凶手和效果的毒
				if($old > $new){
					return $old;
				}else{
					return $new;
				}
				break;
			
			default:
				return $old;
				break;
		}
	}
	
	/**
	 * 物品合成
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @return boolean 合成成功或失败
	 */
	public function item_compose($iids)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return false;
		}
		
		$items = array();
		$check_repeat = array();
		foreach($iids as $iid){
			if(!isset($this->package[$iid])){
				return $this->error('物品不存在');
			}
			if(isset($check_repeat[$iid])){
				return $this->error('请不要选择重复的物品');
			}
			$check_repeat[$iid] = true;
			$items[] = $this->package[$iid]['n'];
		}
		$items_bak = $items;
		
		global $mixinfo;
		foreach($mixinfo as $mix){
			$items = $items_bak;
			foreach($items as $ikey => $item){
				foreach($mix['stuff'] as $skey => $stuff){
					if($stuff === $item){
						unset($items[$ikey]);
						unset($mix['stuff'][$skey]);
						break;
					}
				}
			}
			if(sizeof($items) === 0 && sizeof($mix['stuff']) === 0){
				//合成成功
				foreach($iids as $iid){
					if($this->item_compose_entire($this->package[$iid])){
						$this->item_consume($iid, $this->package[$iid]['s'], false);
						//$this->notice($this->package[$iid]['n'].' 用光了');
						//unset($this->package[$iid]);
					}else{
						$this->item_consume($iid, 1, false);
					}
				}
				$this->rearrange_package();
				$result = array(
					'n' => $mix['result'][0],
					'k' => $mix['result'][1],
					'e' => $mix['result'][2],
					's' => $mix['result'][3],
					'sk' => (isset($mix['result'][4]) && is_array($mix['result'][4])) ? $mix['result'][4] : array()
					);
				$GLOBALS['g']->convert_item($result);
				$this->data['package'][0] = $result;
				$this->rearrange_package();
				$this->feedback(implode('、', $items_bak).'合成出了'.$result['n']);
				$this->ajax('item', array('package' => $this->parse_package()));
				return true;
			}
		}
		$this->error(implode('、', $items_bak).'不能合成');
		return false;
	}
	
	/**
	 * 判断物品合成是否消耗整个物品
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * return boolean 是否消耗整个物品
	 */
	protected function item_compose_entire($item)
	{
		return !$this->item_mergable($item);
	}
	
	/**
	 * 物品合并
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param int $iid 物品在背包中的id
	 */
	public function item_merge($iids)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		$items = array();
		$check_repeat = array();
		foreach($iids as $iid){
			if(!isset($this->package[$iid])){
				$this->error('物品不存在');
				return;
			}
			if(isset($check_repeat[$iid])){
				$this->error('请不要选择重复的物品');
				return;
			}
			$check_repeat[$iid] = true;
			$items[] = $this->package[$iid];
		}
		
		if(false === $this->items_mergable($items)){
			$this->error('这些物品不能合并');
			return;
		}
		
		$n = $items[0]['n'];
		$k = $items[0]['k'];
		$e = $items[0]['e'];
		$s = 0;
		$sk = array();
		foreach($items as $item){
			foreach($item['sk'] as $key => $value){
				if(isset($sk[$key])){
					$sk[$key] = $this->merge_item_subkind($key, $sk[$key], $value);
				}else{
					$sk[$key] = $value;
				}
			}
			$s += $item['s'];
		}
		$item = array(
			'n' => $n,
			'k' => $k,
			'e' => $e,
			's' => $s,
			'sk' => $sk
			);
		
		foreach($iids as $iid){
			unset($this->data['package'][$iid]);
		}
		
		$this->rearrange_package();
		
		if(isset($this->data['package'][$this->capacity])){
			$this->data['package'][0] = $item;
		}else{
			$this->data['package'][$this->capacity] = $item;
		}
		
		$this->rearrange_package();
		
		$this->ajax('item', array('package' => $this->parse_package()));
		$this->feedback('合并成功');
	}
	
	/**
	 * 判断物品是否可以合并
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param array $item 物品数据
	 * @return boolean 是否可以合并
	 */
	protected function item_mergable($item)
	{
		switch($item['k']){
			case 'WP':
			case 'WK':
			case 'WG':
			case 'DB':
			case 'DH':
			case 'DA':
			case 'DF':
			case 'A':
				return false;
		}
		
		switch($item['n']){
			case '笔记本电脑':
			case '手机':
				return false;
		}
		
		return true;
	}
	
	/**
	 * 判断一组物品是否可以合并
	 * 会首先调用item_mergable()函数判定这种物品是否可以合并
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @return boolean 是否可以合并
	 */
	protected function items_mergable($items)
	{
		if(false === $this->item_mergable($items[0])){
			return false;
		}
		
		//信任内部输入，不做判定
		$n = $items[0]['n'];
		$k = $items[0]['k'];
		$e = $items[0]['e'];
		
		foreach($items as $item){
			if($item['n'] !== $n){
				return false;
			}
			if($item['k'] !== $k){
				return false;
			}
			if(intval($item['e']) !== intval($e)){
				return false;
			}
			if(isset($item['sk']['immortal'])){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 获取行动消耗
	 * 如果MOD中有其他设定，修改MOD中的$consumption数组或继承此函数
	 *
	 * @param string 行动类型
	 * @return array 行动消耗
	 */
	protected function get_consumption($action)
	{
		if(isset($GLOBALS['consumption'][$action])){
			return $GLOBALS['consumption'][$action];
		}else{
			$this->notice('有什么奇怪的动作在消耗体力');
			return array();
		}
	}
	
	/**
	 * 计算战斗信息（攻防）
	 * 攻防并不会每次战斗时都计算一遍，只会在玩家发生变化时计算
	 * 如果MOD中有其他设定（如套装），请继承此函数
	 *
	 * @param boolean $ajax 是否向玩家发送ajax更新数据（主要用于继承）
	 */
	public function calculate_battle_info($ajax = true)
	{
		$data = &$this->data;
		
		$data['att'] = $data['baseatt'];
		$data['def'] = $data['basedef'];
		
		global $weapon_modulus;
		$weapon_kind = substr($data['equipment']['wep']['k'], 1, 1);
		$modulus = isset($weapon_modulus[$weapon_kind]) ? $weapon_modulus[$weapon_kind] : $weapon_modulus['default'];
		$data['att'] += $data['equipment']['wep']['e'] * $modulus;
		$data['def'] += $data['equipment']['arb']['e'];
		$data['def'] += $data['equipment']['arh']['e'];
		$data['def'] += $data['equipment']['arf']['e'];
		$data['def'] += $data['equipment']['ara']['e'];
		
		if($ajax){
			$this->ajax('battle_data', array('att' => $this->att, 'def' => $this->def));
		}
	}
	
	/**
	 * 激活已装备的套装
	 * 激活表现为玩家增加一个"套装名_suit"的buff，有一个"quantity"参数代表装备件数，具体实现请在buff处处理
	 */
	public function active_suits()
	{
		//获取套装穿着数量
		$suits = array();
		foreach($this->equipment as $item){
			if($item['n'] != '' && isset($item['sk']['suit'])){
				if(isset($suits[$item['sk']['suit']])){
					$suits[$item['sk']['suit']] ++;
				}else{
					$suits[$item['sk']['suit']] = 1;
				}
			}
		}
		
		//添加新套装buff
		$additional_num = isset($this->equipment['art']['sk']['suit-joker']) ? $this->equipment['art']['sk']['suit-joker'] : 0;
		foreach($suits as $suit_name => &$suit_num){
			$suit_num += $additional_num;
			
			if($suit_num <= 1){
				unset($suits[$suit_name]);
				continue;
			}
			
			$this->buff($suit_name.'_suit', 0, array('quantity' => $suit_num));
		}
		
		//删除失效套装buff
		foreach($this->buff as $bid => &$buff){
			if(substr($buff['type'], -5) !== '_suit'){
				continue;
			}
			
			foreach($suits as $suit_name => &$suit_num){
				if($suit_name.'_suit' === $buff['type']){
					continue 2;
				}
			}
			
			$this->remove_buff($bid);
		}
	}
	
	/**
	 * 获取恢复速度
	 * 中毒也计算入恢复速度内
	 * 如果MOD中有其他设定，请继承或重载此函数
	 */
	public function get_heal_rate()
	{
		$heal_rate = $this->get_base_heal_rate();
		
		$hpr = $heal_rate['hp'];
		$spr = $heal_rate['sp'];
		
		
		global $poison;
		$damager = 0;
		foreach($this->buff as $buff){
			if($buff['type'] === 'poison'){
				$damager += $poison['damage'];
				if(false === $poison['recover']){
					$hpr = 0;
				}
			}
		}
		
		return array('hp' => $hpr - $damager, 'sp' => $spr);
	}
	
	/**
	 * 获取基础恢复速度（不计入毒物）
	 * 如果MOD中有其他设定（如技能加成），请继承此函数
	 */
	protected function get_base_heal_rate()
	{
		global $clinicmap, $npc_recover;
		
		$heal_rate = $GLOBALS['heal_rate'];
		
		//地域加成
		if(isset($clinicmap[$this->area])){
			$heal_rate['hp'] *= $clinicmap[$this->area];
		}
		
		//NPC回血设定
		if($this->type === GAME_PLAYER_NPC){
			if(false === $npc_recover){
				$heal_rate['hp'] = 0;
			}
		}
		
		return $heal_rate;
	}
	
	/**
	 * 恢复
	 * 如果恢复负数的生命，会调用damage()函数
	 * 如果MOD中有其他设定，请继承此函数
	 *
	 * @param string $type 恢复类型
	 * @param int $effect 恢复量
	 * @return int|boolean 恢复量/恢复未成功
	 */
	public function heal($type, $effect)
	{
		if($type === 'hp' && $effect < 0){
			return -$this->damage(-$effect);
		}
		if(isset($this->data[$type])){
			$before = $this->data[$type];
			$this->data[$type] += $effect;
			if($this->data[$type] > $this->data['m'.$type]){
				$this->data[$type] = $this->data['m'.$type];
			}
			return $this->data[$type] - $before;
		}else{
			//不存在补充的目标属性
			$this->notice('有什么未知的力量被补充了');
			return false;
		}
	}
	
	/**
	 * 造成伤害
	 * 如果MOD中有其他设定，请继承或重载此函数
	 *
	 * @param float $damage 伤害值
	 * @param array $source 伤害来源 //TODO: 来源文档说明
	 * @return float 最终造成的伤害值
	 */
	public function damage($damage, array $source = array())
	{
		if($damage < 0){
			return -$this->heal('hp', -$damage);
		}
		$this->data['hp'] -= $damage;
		if($this->hp <= 0){
			$this->sacrifice($source);
		}
		$this->ajax('health', array('hp' => $this->hp, 'sp' => $this->sp));
		return $damage;
	}
	
	public function sacrifice($source = array())
	{
		//死亡
		$this->data['hp'] = 0;
		//发送遗言
		$this->chat_send('<span class="lastword">'.$this->lastword.'</span>');
		
		//更新死亡信息
		$this->data['deathtime'] = time();
		$this->data['deathreason'] = isset($source['type']) ? $source['type'] : 'custom:神秘死亡';
		$this->data['killer'] = array();
		
		//处理凶手及显示消息
		if(isset($source['pid']) && false !== $source['pid']){
			$pids = is_array($source['pid']) ? $source['pid'] : array($source['pid']);
			$this->data['killer'] = $pids;
			$killers_data = array();
			foreach($pids as $pid){
				$data = $GLOBALS['g']->get_player_by_id($pid);
				if($data){
					$killers_data[] = $data;
				}
			}
			if(sizeof($killers_data) > 0){
				$killer_name = array();
				foreach($killers_data as $killer_data){
					$killer = new_player($killer_data);
					$killer->ajax('notice', array('msg' => $this->name.'被你杀死了', 'time' => time()));
					$killer->data['killnum'] = intval($killer->killnum) + 1;
					$killer_name[] = $killer->name;
				}
				$killer_name = implode('、', $killer_name);
				
				$kill_type = isset($source['type']) ? $source['type'] : 'unknown';
				$kill_item = isset($source['weapon']) ? $source['weapon'] : '神秘力量';
				$GLOBALS['g']->insert_news('kill', array('killer' => $killer_name, 'deceased' => $this->name, 'type' => $kill_type, 'weapon' => $kill_item));
			}else{
				$kill_type = isset($source['type']) ? $source['type'] : 'unknown';
				$kill_item = isset($source['weapon']) ? $source['weapon'] : '神秘力量';
				$GLOBALS['g']->insert_news('kill', array('deceased' => $this->name, 'type' => $kill_type, 'weapon' => $kill_item));
			}
		}else{
			$kill_type = isset($source['type']) ? $source['type'] : 'unknown';
			$kill_item = isset($source['weapon']) ? $source['weapon'] : '神秘力量';
			$GLOBALS['g']->insert_news('kill', array('deceased' => $this->name, 'type' => $kill_type, 'weapon' => $kill_item));
		}
		
		//显示死亡信息
		$this->show_death_info();
		$this->feedback($this->hp);
		
		if(intval($this->type) === GAME_PLAYER_USER){
			//处理游戏幸存与击杀人数
			$GLOBALS['g']->gameinfo['alivenum'] --;
			$GLOBALS['g']->gameinfo['deathnum'] ++;
			
			$alive_num = $GLOBALS['g']->gameinfo['alivenum'];

			//检查游戏是否结束
			if(($GLOBALS['g']->gameinfo['gamestate'] & GAME_STATE_COMBO) === GAME_STATE_COMBO){
				$this->chat_send($GLOBALS['g']->gameinfo['gamestate']);
				if($alive_num == 1){
					$survivor = $GLOBALS['db']->select('players', '*', array('type' => GAME_PLAYER_USER, 'hp' => array('$gt' => 0)));
					foreach($survivor as $player_data){
						$player = new_player($player_data);
						if(intval($player->hp) > 0){
							break;
						}
					}
					$GLOBALS['g']->game_end('survive', $player, 'individual');
				}else if($alive_num == 0){
					$GLOBALS['g']->game_end('timeup');
				}
			}
		}
	}
	
	public function show_death_info()
	{
		$killers_name = array();
		$killers_avatar = array();
		foreach($this->killer as $pid){
			$data = $GLOBALS['g']->get_player_by_id($pid);
			if($data){
				$killers_name[] = $data['name'];
				$killers_avatar[] = $data['icon'];
			}
		}
		$reason = strpos($this->deathreason, 'custom:') === 0 ? substr($this->deathreason, 7): isset($GLOBALS['deathreasoninfo'][$this->deathreason]) ? $GLOBALS['deathreasoninfo'][$this->deathreason] : $GLOBALS['deathreasoninfo']['default'];
		$this->ajax('die', array('reason' => $reason, 'time' => $this->deathtime, 'killer' => $killers_name, 'avatar' => $killers_avatar));
	}
	
	public function chat_send($msg)
	{
		global $a;
		$content = ' <span class="username">'.$this->name.':</span> ';
		$content.= '<span class="chatmsg">'.$msg.'</span>';
		$a->action('chat_msg', array('msg' => $content, 'time' => time()), true);
	}
	
	public function item_consume($id, $num = 0, $rearrange = true)
	{
		if(is_numeric($id)){
			$item_arr = &$this->package[$id];
		}else{
			$item_arr = &$this->equipment[$id];
		}
		$item = new_item($this, $item_arr, $id);
		$item->consume($num, $rearrange);
	}
	
	public function weapon_switch()
	{
		if(false === $this->is_alive()){
			return $this->error('你已经死了');
		}
		
		if($this->equipment['wep']['n'] == ''){
			return $this->error('尚未装备武器');
		}	
		$item = new_item($this, $this->equipment['wep'], 'wep');
		$item->weapon_transform();
	}
	
	public function weapon_reload($amount = 1)
	{
		if(false === $this->is_alive()){
			return $this->error('你已经死了');
		}
		
		if($this->equipment['wep']['n'] == ''){
			$this->error('没有装备武器');
			return false;
		}
		if($this->equipment['wep']['k'] !== 'WG'){
			if(isset($this->equipment['wep']['sk']['alt']) && $this->equipment['wep']['sk']['alt']['k'] === 'G'){
				$wep = new_item($this, $this->equipment['wep'], 'wep');
				$wep->weapon_transform(true);
			}else{
				$this->error('只有枪械才能填充弹药');
				return false;
			}
		}
		
		$clip = isset($this->equipment['wep']['sk']['ammo']) ? $this->equipment['wep']['sk']['ammo'] : $GLOBALS['clip'];
		
		if($this->equipment['wep']['s'] >= $clip){
			return false;
		}
		
		$this->equipment['wep']['s'] += $amount;
		
		if($this->equipment['wep']['s'] >= $clip){
			$this->equipment['wep']['s'] = $clip;
			$this->feedback($this->equipment['wep']['n'].'弹药填充完毕');
		}
		
		return true;
	}
	
	public function parse_equipment()
	{
		$equipment = array();
		
		foreach($this->equipment as $key => &$item){
			$equipment[$key] = array('n' => $item['n'], 'k' => $item['k'], 'e' => $item['e'], 's' => $item['s'], 'sk' => $item['sk']);
		}
		
		if(isset($equipment['wep']['sk']['poison'])){
			$equipment['wep']['n'] = '<div class="poison">'.$equipment['wep']['n'].'</div>';
		}
		
		$this->parse_item_text($equipment);
		
		return $equipment;
	}
	
	public function parse_package()
	{
		$package = array();
		
		foreach($this->package as $key => &$item){
			$package[$key] = array('n' => $item['n'], 'k' => $item['k'], 'e' => $item['e'], 's' => $item['s'], 'sk' => $item['sk']);
			if(in_array('Glutton', $this->skill)){
				if(substr($package[$key]['k'], 0, 1) === 'P'){
					$package[$key]['n'] = '<div class="poison">'.$package[$key]['n'].'</div>';
				}
			}
		}
		
		$this->parse_item_text($package);
		
		return $package;
	}
	
	public function team_create($name, $pass)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $db;
		
		$name = strval($name);
		
		$team = $db->select('team', '*', array('name' => $name));
		if(false !== $team){
			$this->error('队伍 '.$name.' 已存在');
			return;
		}
		unset($team);
		
		$consumption = $this->get_consumption('create_team');
		$this->check_health($consumption, 'create_team');
		
		$db->insert('team', array('name' => $name, 'pass' => md5($pass)));
		$this->team_join($name, $pass);
	}
	
	public function team_join($name, $pass)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $db;
		
		$name = strval($name);
		$pass = md5($pass);
		
		$consumption = $this->get_consumption('join_team');
		$this->check_health($consumption, 'join_team');
		
		$team = $db->select('team', '*', array('name' => $name));
		if(!$team){
			$this->error('队伍 '.$name.' 不存在');
			return;
		}
		
		$team = $db->select('team', '*', array('name' => $name, 'pass' => $pass));
		if(!$team){
			return $this->error('密码不正确');
			return;
		}
		
		$this->data['teamID'] = $team[0]['_id'];
		$this->feedback('已加入 '.$name);
		$this->ajax('team', $this->get_team_info());
		
		$teammate = $db->select('players', array('uid'), array('teamID' => $team[0]['_id']));
		if($teammate){
			foreach($teammate as $player){
				$GLOBALS['a']->action('notice', array('msg' => $this->name.'加入了你的队伍'), $player['uid']);
			}
		}
	}
	
	public function team_leave()
	{
		if(false === $this->is_alive()){
			return $this->error('你已经死了');
		}
		
		if(strval($this->teamID) === '-1'){
			$this->error('尚未加入任何队伍');
		}
		
		$team = $GLOBALS['db']->select('team', '*', array('_id' => $this->teamID));
		
		$name = $this->get_team_name();
		$this->data['teamID'] = -1;
		$this->feedback('已离开 '.$name);
		$this->ajax('team', $this->get_team_info());
		
		$teammate = $GLOBALS['db']->select('players', array('uid'), array('teamID' => $team[0]['_id']));
		if($teammate){
			foreach($teammate as $player){
				if($player['uid'] != $this->uid){
					$GLOBALS['a']->action('notice', array('msg' => $this->name.'离开了你的队伍'), $player['uid']);
				}
			}
		}
		
		return;
	}
	
	public function get_team_info()
	{
		return array('name' => $this->get_team_name(), 'joined' => (strval($this->teamID) !== '-1'));
	}

	public function get_team_name()
	{
		$teamID = $this->teamID;
		if(strval($teamID) === '-1'){
			return '无';
		}else{
			global $db;
			$team = $db->select('team', '*', array('_id' => $teamID));
			if(false === $team){
				return '无';
			}else{
				return $team[0]['name'];
			}
		}
	}
	
	public function pose($tid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $poseinfo;
		
		$tid = intval($tid);
		
		if($tid < 0 || $tid >= sizeof($poseinfo)){
			$this->error('姿态不存在');
			return;
		}
		
		$this->data['pose'] = $tid;
		$this->ajax('pose', array('tid' => $tid));
		
		$hr = $this->get_heal_rate();
		$this->ajax('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
	}
	
	public function tactic($tid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		global $tacticinfo;
		
		$tid = intval($tid);
		
		if($tid < 0 || $tid >= sizeof($tacticinfo)){
			return $this->error('策略不存在');
		}
		
		$this->data['tactic'] = $tid;
		$this->ajax('tactic', array('tid' => $tid));
		
		$hr = $this->get_heal_rate();
		$this->ajax('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
	}
	
	public function is_alive()
	{
		return ($this->hp > 0);
	}

	/**
	 * 获取躲避概率（降低其他人发现该玩家的概率）
	 * @return int
	 */
	public function get_hide_rate()
	{
		global $modulus_hide;
		
		$hide_rate = 1;
		
		if(isset($modulus_hide['weather'])){
			global $gameinfo;
			if(isset($modulus_hide['weather'][intval($gameinfo['weather'])])){
				$hide_rate *= $modulus_hide['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_hide['area'])){
			if(isset($modulus_hide['area'][intval($this->data['area'])])){
				$hide_rate *= $modulus_hide['area'][intval($this->data['area'])];
			}
		}
		
		if(isset($modulus_hide['pose'])){
			if(isset($modulus_hide['pose'][intval($this->data['pose'])])){
				$hide_rate *= $modulus_hide['pose'][intval($this->data['pose'])];
			}
		}
		
		if(isset($modulus_hide['tactic'])){
			if(isset($modulus_hide['tactic'][intval($this->data['tactic'])])){
				$hide_rate *= $modulus_hide['tactic'][intval($this->data['tactic'])];
			}
		}
		
		return $hide_rate - 1;
	}
	
	public function attack()
	{
		global $g;

		if(false === $this->is_alive()){
			$this->error('你已经死了', false);
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前尚未碰到敌人', false);
			return;
		}
		
		$enemy_data = $g->get_player_by_id($this->action['battle']['pid']);
		$enemy = new_player($enemy_data);
		
		if(false === $enemy->is_alive()){
			$this->update_enemy_info($enemy, true);
			$this->error('对方已阵亡', false);
			return;
		}
		
		$combat = new_combat($this, $enemy);
		$combat->battle_start();
		
		$this->ajax('battle', array(
			'enemy' => $this->get_enemy_info($enemy, true),
			'end' => $enemy->is_alive()
			));
		
		if($enemy->is_alive()){
			unset($this->data['action']['battle']);
		}
	}
	
	public function escape()
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前尚未碰到敌人');
			return;
		}
		
		unset($this->data['action']['battle']);
		$this->feedback("逃跑成功");
	}
	
	public function strip($iid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前尚未碰到敌人');
			return;
		}
		
		$enemy = $GLOBALS['db']->select('players', '*', array('_id' => $this->action['battle']['pid']));
		$enemy = new_player($enemy[0]);
		
		if($enemy->is_alive()){
			$this->update_enemy_info($enemy, false);
			$this->error('对方仍存活');
			return;
		}
		
		if($iid == ''){
			//不作为
			$this->feedback('你离开了');
			
		}else if($iid === 'money'){
			//捡钱
			if($enemy->money <= 0){
				$this->update_enemy_info($enemy, false);
				$this->error($enemy->name.'身上已经没有钱了');
				return;
			}
			
			$this->feedback('从'.$enemy->name.'身上成功获得'.$enemy->money.$GLOBALS['currency']);
			$this->data['money'] += $enemy->money;
			$enemy->money = 0;
			$enemy->ajax('money', array('money' => 0));
			$this->ajax('money', array('money' => $this->money));
			
		}else if(preg_match('/^\d+$/', $iid)){
			//捡道具
			$iid = intval($iid);
			if(false === isset($enemy->data['package'][$iid])){
				$enemy->rearrange_package();
				$this->update_enemy_info($enemy, false);
				$this->error($enemy->name.'身上已经没有这个物品');
				return;
			}
			
			if(isset($this->data['package'][$this->capacity])){
				if(isset($this->data['package'][0])){
					$this->error('包裹已满');
				}else{
					$this->data['package'][0] = $enemy->data['package'][$iid];
					unset($enemy->data['package'][$iid]);
				}
			}else{
				$this->data['package'][$this->capacity] = $enemy->data['package'][$iid];
				unset($enemy->data['package'][$iid]);
			}
			
			$enemy->rearrange_package();
			$enemy->ajax('item', array('package' => $enemy->parse_package()));
			$this->rearrange_package();
			$this->ajax('item', array('package' => $this->parse_package()));
			
		}else{
			//捡装备
			if(false === isset($enemy->data['equipment'][$iid]['n']) || $enemy->data['equipment'][$iid]['n'] == ''){
				$this->update_enemy_info($enemy, false);
				$this->error($enemy->name.'身上已经没有这个物品');
				return;
			}
			
			global $null_item;
			if(isset($this->data['package'][$this->capacity])){
				if(isset($this->data['package'][0])){
					$this->error('包裹已满');
				}else{
					$this->data['package'][0] = $enemy->data['equipment'][$iid];
					$enemy->data['equipment'][$iid] = $null_item;
				}
			}else{
				$this->data['package'][$this->capacity] = $enemy->data['equipment'][$iid];
				$enemy->data['equipment'][$iid] = $null_item;
			}
			
			$enemy->ajax('item', array('equipment' => $enemy->parse_equipment()));
			$this->rearrange_package();
			$this->ajax('item', array('package' => $this->parse_package()));
		}
		
		unset($this->data['action']['battle']);
		$this->update_enemy_info($enemy, true);
	}
	
	public function give($iid)
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前尚未碰到队友');
			return;
		}
		
		$enemy = $GLOBALS['db']->select('players', '*', array('_id' => $this->action['battle']['pid']));
		$enemy = new_player($enemy[0]);
		
		if($enemy->teamID === '-1' || $enemy->teamID !== $this->teamID){
			$this->update_enemy_info($enemy, false);
			$this->error('对方对你怀有敌意');
			return;
		}
		
		if($iid == ''){
			//不作为
			$this->feedback('你离开了');
			
		}else if($iid === 'money'){
			//给钱
			if($this->money <= 0){
				$this->error('你身上已经没有钱了');
				return;
			}
			
			$this->feedback($enemy->name.'获得了'.$this->money.$GLOBALS['currency']);
			$enemy->notice($this->name.'给了你'.$this->money.$GLOBALS['currency']);
			$enemy->data['money'] += $this->money;
			$this->money = 0;
			$this->ajax('money', array('money' => 0));
			$enemy->ajax('money', array('money' => $enemy->money));
			
		}else if(preg_match('/^\d+$/', $iid)){
			//给道具
			$iid = intval($iid);
			if(false === isset($this->data['package'][$iid])){
				$this->rearrange_package();
				$this->error('你身上没有这个物品');
				return;
			}
			
			$item_name = $this->data['package'][$iid]['n'];
			if(isset($enemy->data['package'][$enemy->capacity])){
				if(isset($enemy->data['package'][0])){
					$this->error('对方包裹已满');
					return;
				}else{
					$enemy->data['package'][0] = $this->data['package'][$iid];
					unset($this->data['package'][$iid]);
				}
			}else{
				$enemy->data['package'][$enemy->capacity] = $this->data['package'][$iid];
				unset($this->data['package'][$iid]);
			}
			
			$enemy->rearrange_package();
			$enemy->ajax('item', array('package' => $enemy->parse_package()));
			$this->rearrange_package();
			$this->ajax('item', array('package' => $this->parse_package()));
		
			$this->feedback($enemy->name.'获得了'.$item_name);
			$enemy->notice($this->name.'给了你'.$item_name);
			
		}else{
			//给装备
			if(false === isset($this->data['equipment'][$iid]['n']) || $this->data['equipment'][$iid]['n'] == ''){
				$this->error('你身上已经没有这个物品');
				return;
			}
			
			global $null_item;
			$item_name = $this->data['equipment'][$iid]['n'];
			if(isset($enemy->data['package'][$enemy->capacity])){
				if(isset($enemy->data['package'][0])){
					$this->error('对方包裹已满');
					return;
				}else{
					$enemy->data['package'][0] = $this->data['equipment'][$iid];
					$this->data['equipment'][$iid] = $null_item;
				}
			}else{
				$enemy->data['package'][$enemy->capacity] = $this->data['equipment'][$iid];
				$this->data['equipment'][$iid] = $null_item;
			}
			
			$this->ajax('item', array('equipment' => $this->parse_equipment()));
			$enemy->rearrange_package();
			$enemy->ajax('item', array('package' => $enemy->parse_package()));
			
			$this->feedback($enemy->name.'获得了'.$item_name);
			$enemy->notice($this->name.'给了你'.$item_name);
		}
		
		unset($this->data['action']['battle']);
		$this->update_enemy_info($enemy, true);
	}
	
	public function leave()
	{
		if(false === $this->is_alive()){
			$this->error('你已经死了');
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前没有碰到任何人');
			return;
		}
		
		$enemy = $GLOBALS['db']->select('players', '*', array('_id' => $this->action['battle']['pid']));
		$enemy = new_player($enemy[0]);
		
		if(($enemy->teamID === '-1' || $enemy->teamID !== $this->teamID) && $enemy->is_alive()){
			$this->update_enemy_info($enemy, false);
			$this->error('战斗中无法离开');
			return;
		}
		
		unset($this->data['action']['battle']);
		$this->update_enemy_info($enemy, true);
		$this->feedback('你离开了');
	}
	
	public function get_basic_info($end)
	{
		global $genderinfo, $gameinfo, $hp_status;
		
		$name = $this->name;
		$avatar = $this->icon;
		$number = $this->number.'号';
		$gender = $genderinfo[$this->gender];
		if($this->hp > $this->mhp * 0.4){
			$status = 'normal';
		}else if($this->hp > $this->mhp * 0.1){
			$status = 'attention';
		}else if($this->is_alive()){
			$status = 'dangerous';
		}else{
			$status = 'dead';
		}
		$status = '<span class="'.$status.'">'.$hp_status[$status].'</span>';
		
		return array(
			'name' => $name,
			'avatar' => $avatar,
			'number' => $number,
			'gender' => $gender,
			'status' => $status
			);
	}
	
	public function get_enemy_info(player $enemy, $end = false)
	{
		$info = $enemy->get_basic_info($end);
		if(strval($enemy->teamID) !== '-1' && $enemy->teamID === $this->teamID){
			$info['action'] = array('give', 'leave');
			$info['item'] = $this->parse_enemy_item($this);
		}else if($enemy->is_alive()){
			$info['action'] = array('attack', 'escape');
		}else{
			$info['action'] = array('strip', 'leave');
			$info['item'] = $this->parse_enemy_item($enemy);
		}
		return $info;
	}
	
	public function update_enemy_info(player $enemy, $end)
	{
		$this->ajax('battle', array(
			'enemy' => $this->get_enemy_info($enemy, $end),
			'end' => $end
			));
	}

	/**
	 * @param player $enemy
	 */
	public function found_enemy($enemy)
	{
		if(false === $enemy->is_alive()){
			$this->data['action']['battle'] = array('pid' => $enemy->_id);
			$this->feedback('你发现了'.$enemy->name.'的尸体，现在要做什么？');
		}else if(strval($enemy->teamID) !== '-1' && $enemy->teamID === $this->teamID){
			$this->data['action']['battle'] = array('pid' => $enemy->_id);
			$this->feedback('你发现了队友'.$enemy->name.'，现在要做什么？');
		}else{
			$this->data['action']['battle'] = array('pid' => $enemy->_id);
			$this->feedback('你发现了敌人'.$enemy->name.'，现在要做什么？');
		}
		$this->update_enemy_info($enemy, false);
	}
	
	protected function get_discover_threshold($mode)
	{
		//探索结果概率设定 - 数值代表遇敌可能性
		switch($mode){
			case 'search':
				$threshold = 30;
				break;
			
			case 'move':
				$threshold = 70;
				break;

			default:
				$threshold = 0;
				break;
		}
		
		return $threshold;
	}
	
	protected function enemy_found_rate(player $enemy)
	{
		if($enemy->is_alive()){
			if($enemy->_id == $this->_id){
				//自己
				return 0;
			}else{
				return $this->get_enemy_found_rate() - $enemy->get_hide_rate();
			}
		}else{
			if(sizeof($enemy->package) === 0 && $enemy->equipment['wep']['n'] === '' && $enemy->equipment['arb']['n'] === '' && $enemy->equipment['arh']['n'] === '' &&
			 $enemy->equipment['ara']['n'] === '' && $enemy->equipment['arf']['n'] === '' && $enemy->equipment['art']['n'] === '' && $enemy->money == 0){
				//空尸
				return 0;
			}else{
				return $this->get_corpse_found_rate();
			}
		}
	}
	
	protected function discover($mode = 'search'){
		global $db, $g;
		
		$threshold = $this->get_discover_threshold($mode);
		
		if($g->determine($threshold)){
			//遇敌
			$players = $db->select('players', '*', array('area' => $this->area));
			if(false === $players){
				$this->notice('什么都没有发现');
				return;
			}
			shuffle($players);
			
			foreach($players as &$player){
				$enemy = new_player($player);
				
				if($g->determine($this->enemy_found_rate($enemy))){
					//遇敌成功，进入战斗状态
					$this->found_enemy($enemy);
					//跳出循环，停止遇敌
					break;
				}else{
					//遇敌失败，垃圾回收
					unset($enemy);
				}
				
				//继续寻找下一个敌人
			}
		}else{
			//遇物品
			$items = $db->select('items', '*', array('area' => $this->area));
			if(false === $items){
				$this->notice('什么都没有发现');
				return;
			}
			$threshold = $this->get_item_found_rate();
			if($GLOBALS['g']->determine($threshold)){
				$item = $items[$GLOBALS['g']->random(0, sizeof($items) - 1)];
				unset($items);
				$db->delete('items', array('_id' => $item['_id']));
				$item = array(
					'n' => $item['itm'],
					'k' => $item['itmk'],
					'e' => $item['itme'],
					's' => $item['itms'],
					'sk' => $item['itmsk']
					);
				$this->found_item($item);
			}else{
				$this->notice('什么都没有发现');
			}
		}
	}
	
	protected function found_item($item)
	{
		if($item['k'] === 'TO'){
			//中陷阱
			$pid = isset($item['sk']['owner']) ? $item['sk']['owner'] : false;
			$damage = $this->calculate_trap_damage($item);
			$damage = $this->damage($damage, array('pid' => $pid, 'weapon' => $item['n'], 'type' => 'trap'));
			global $healthinfo;
			$this->feedback('糟糕，你中了'.$item['n'].'，失去了'.$damage.'点'.$healthinfo['hp']);
		}else{
			$this->package[0] = $item;
			$this->feedback('发现物品 '.$this->package[0]['n']);
			$this->ajax('item', array('package' => $this->parse_package()));
		}
	}
	
	protected function calculate_trap_damage(&$trap)
	{
		return round($GLOBALS['g']->random(0, floor($trap['e'] / 2)) + $trap['e'] / 2);
	}
	
	protected function get_enemy_found_rate()
	{
		global $enemy_found_rate;
		
		$find_rate = $enemy_found_rate;
		
		if(isset($modulus_find['weather'])){
			global $gameinfo;
			if(isset($modulus_find['weather'][intval($gameinfo['weather'])])){
				$find_rate *= $modulus_find['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_find['area'])){
			if(isset($modulus_find['area'][intval($this->data['area'])])){
				$find_rate *= $modulus_find['area'][intval($this->data['area'])];
			}
		}
		
		if(isset($modulus_find['pose'])){
			if(isset($modulus_find['pose'][intval($this->data['pose'])])){
				$find_rate *= $modulus_find['pose'][intval($this->data['pose'])];
			}
		}
		
		if(isset($modulus_find['tactic'])){
			if(isset($modulus_find['tactic'][intval($this->data['tactic'])])){
				$find_rate *= $modulus_find['tactic'][intval($this->data['tactic'])];
			}
		}
		
		return $find_rate;
	}
	
	protected function get_corpse_found_rate()
	{
		global $corpse_found_rate;
		return $corpse_found_rate;
	}
	
	protected function get_item_found_rate()
	{
		global $item_found_rate;
		return $item_found_rate;
	}
	
	protected function check_health($consumption = array(), $action = '')
	{
		foreach($consumption as $key => $value){
			if(true === isset($this->data[$key])){
				if($this->data[$key] < $value){
					if(isset($GLOBALS['actioninfo'][$action])){
						$action = $GLOBALS['actioninfo'][$action];
					}else{
						$action = '完成动作';
					}
					$this->error($GLOBALS['healthinfo'][$key]."不足，无法".$action);
				}
			}else{
				$this->error("无效的消耗类型");
			}
		}
		
		foreach($consumption as $key => $value){
			$this->data[$key] -= $value;
		}
		$this->ajax('health', array('hp' => $this->hp, 'sp' => $this->sp));
	}
	
	protected function daemon(array $param = array())
	{
		$param['heal_rate'] = $this->get_heal_rate();
		$param['damage_rate'] = 0;
		$param['endtime'] = time();
		foreach($this->buff as $key => &$buff){
			if(false === isset($buff['type'])){
				unset($this->data['buff'][$key]);
			}
			
			if($param['endtime'] > $buff['time'] && $buff['time'] != 0){
				$param['endtime'] = $buff['time'];
			}
		}
		
		$param['lasttime'] = $param['endtime'] - $this->data['daemontime'];
		$this->data['daemontime'] = $param['endtime'];
		
		foreach($this->buff as $key => &$buff){
			$this->buff_handler($buff, $param);
			
			if($param['endtime'] >= $buff['time'] && $buff['time'] != 0){
				$this->remove_buff($key);
			}
		}
		
		$this->auto_recover($param);
	}
	
	protected function buff_handler(&$buff, &$param)
	{
		switch($buff['type']){
			case 'poison':
				//TODO: 毒凶手信息
				break;
		}
	}
	
	protected function auto_recover($param)
	{
		//TODO: 毒凶手
		$this->heal('hp', $param['heal_rate']['hp'] * $param['lasttime']);
		$this->heal('sp', $param['heal_rate']['sp'] * $param['lasttime']);
	}
	
	protected function area_status($area)
	{
		global $gameinfo;
		
		if(array_search($area, $gameinfo['forbiddenlist']) !== false){
			return false;
		}
		
		return true;
	}
	
	protected function parse_enemy_item($enemy)
	{
		//此处不可用array_merge，原因是package数组不是关联数组，但是通常情况下没有下标0的元素，在使用array_merge后，数字下标会重排序，导致列表bug
		$items = array();
		foreach($enemy->equipment as $key => $value){
			$items[$key] = $value;
		}
		foreach($enemy->package as $key => $value){
			$items[$key] = $value;
		}
		
		foreach($items as $iid => &$item){
			if(false === isset($item['n']) || $item['n'] == ''){
				unset($items[$iid]);
				continue;
			}
			
			if(substr($item['k'], 0, 1) === 'W' && isset($item['sk']['poison'])){
				$item['n'] = '<span class="poison">'.$item['n'].'</span>';
			}
			
			if(substr($item['k'], 0, 1) === 'H' && isset($item['sk']['poison']) && isset($this->skill['Glutton'])){
				$item['n'] = '<span class="poison">'.$item['n'].'</span>';
			}
		}
		
		$this->parse_item_text($items);
		
		if($enemy->money > 0){
			$items['money'] = $enemy->money;
		}
		
		return $items;
	}
	
	protected function parse_item_text(&$items)
	{
		global $iteminfo;
		
		foreach($items as &$item){
			if(isset($iteminfo[$item['k']])){
				$k = $iteminfo[$item['k']];
			}else{
				$k = $iteminfo['default'];
			}
			
			if(isset($item['sk']['alt']) && (substr($item['k'], 0, 1) === 'W')){
				if(isset($iteminfo['W'.$item['sk']['alt']['k']])){
					$k .= ' <span class="alt">'.$iteminfo['W'.$item['sk']['alt']['k']].'</span>';
				}else{
					$k .= ' <span class="alt">'.$iteminfo['default'].'</span>';
				}
			}
			
			$item['k'] = $k;
			
			if($item['s'] == 0){
				$item['s'] = '∞';
			}
			unset($item['sk']);
		}
	}
	
	public function &__get($name)
	{
		if($name === 'data'){
			return $this->data;
		}
		if(false === isset($this->data[$name])){
			throw_error('Undefined property in player: '.$name);
		}
		return $this->data[$name];
	}
	
	public function __set($name, $value)
	{
		if($name === 'data'){
			return $this->data;
		}
		if(false === isset($this->data[$name])){
			throw_error('Undefined property in player: '.$name);
		}
		return $this->data[$name] = $value;
	}
	
	//输出相关函数
	
	public function ajax($action, $param = array())
	{
		if(intval($this->type) === GAME_PLAYER_USER){
			global $a;
			$a->action($action, $param, $this->uid);
		}
	}
	
	public function error($msg, $exit = true){
		$this->ajax('error', array('msg' => $msg, 'time' => time()));
		global $cuser;
		if($this->uid == $cuser['_id'] && $exit){
			die();
		}
		return false;
	}
	
	public function notice($msg){
		$this->ajax('notice', array('msg' => $msg, 'time' => time()));
	}
	
	public function feedback($msg){
		$this->ajax('feedback', array('msg' => $msg, 'time' => time()));
	}
	
}

?>