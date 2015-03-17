<?php

class item_bra extends item
{
	
	protected function special($param)
	{
		$success = true;
		switch($this->data['n']){
			
			case '雷达':
				$success = $this->apply_radar();
				break;
			
			case '电池':
				$success = $this->apply_battery($param);
				break;
			
			case '凸眼鱼':
				$success = $this->apply_flounder();
				break;
			
			case '针线包':
				$success = $this->apply_sewing();
				$this->player->calculate_battle_info();
				$this->player->ajax('item', array('equipment' => $this->player->parse_equipment()));
				
				break;
			
			case '天候棒':
				$success = $this->apply_weatherod();
				break;
			
			case '消音器':
				$success = $this->apply_silencer();
				break;
			
			case '御神签':
				$success = $this->apply_dice();
				break;
			
			case '移动PC':
				$success = $this->apply_hacker();
				break;
			
			case '■DeathNote■':
				$success = $this->apply_deathnote($param);
				break;
			
			case '游戏解除钥匙':
				//TODO: 插入news
				$GLOBALS['g']->game_end('eliminate', $this->player);
				break;
			
			default:
				if(strpos($this->data['n'], '钉') !== false){
					$success = $this->apply_nail();
					$this->player->calculate_battle_info();
					$this->player->ajax('item', array('equipment' => $this->player->parse_equipment()));
				}else if(strpos($this->data['n'], '磨刀石') !== false){
					$success = $this->apply_whetstone();
					$this->player->calculate_battle_info();
					$this->player->ajax('item', array('equipment' => $this->player->parse_equipment()));
				}else{
					parent::special($param);
					return; //此处一定要返回，否则会重复消耗物品
				}
				break;
		}
		
		if($success){
			$this->consume();
		}
	}
	
	protected function apply_nail()
	{
		if($this->player->equipment['wep']['n'] == ''){
			
			$this->player->error('没有装备武器');

			return false;
			
		}else if($this->player->equipment['wep']['k'] === 'WP' && (strpos($this->player->equipment['wep']['n'], '棍') !== false || strpos($this->player->equipment['wep']['n'], '棒') !== false)){
			
			if(determine(80)){
				
				if(strpos($this->player->equipment['wep']['n'], '钉') === false){
					$this->player->feedback($this->player->equipment['wep']['n'].'变成了'.$this->data['n'].$this->player->equipment['wep']['n']);
					$this->player->equipment['wep']['n'] = $this->data['n'].$this->player->equipment['wep']['n'];
				}
				$this->player->equipment['wep']['e'] += $this->data['e'];
				$this->player->feedback($this->data['n'].'使用成功，'.$this->player->equipment['wep']['n'].'的效果增加了'.$this->data['e'].'，变成了'.$this->player->equipment['wep']['e']);
				
			}else{
				
				$this->player->equipment['wep']['e'] -= ceil($this->data['e'] / 2);
				$this->player->error($this->data['n'].'使用失败，'.$this->player->equipment['wep']['n'].'的效果减少了'.ceil($this->data['e'] / 2).'，变成了'.$this->player->equipment['wep']['e'], false);
				
				if($this->player->equipment['wep']['e'] <= 0){
					$this->player->feedback($this->player->equipment['wep']['n'].'损坏了');
					global $null_item;
					$this->player->equipment['wep'] = $null_item;
					$this->player->calculate_battle_info(); //重新计算攻防
				}
				
			}
			
			return true;
		}else{
			$this->player->error('不能对'.$this->player->equipment['wep']['n'].'使用'.$this->data['n']);
			return false;
		}
	}
	
	protected function apply_sewing()
	{
		if($this->player->equipment['arb']['n'] != ''){
			$this->player->equipment['arb']['e'] += $this->data['e'];
			$this->player->feedback('使用了针线包，'.$this->player->equipment['arb']['n'].'的强度增加了'.$this->data['e'].'，变成了'.$this->player->equipment['arb']['e']);
			return true;
		}else{
			$this->player->error('没有装备防具');
			return false;
		}
	}
	
	protected function apply_whetstone()
	{
		
		if($this->player->equipment['wep']['n'] == ''){
			
			$this->player->error('没有装备武器');
			
			return false;
			
		}else if($this->player->equipment['wep']['k'] === 'WK'){
			
			if(determine(80)){
				
				$this->player->equipment['wep']['e'] += $this->data['e'];
				$this->player->feedback($this->data['n'].'使用成功，'.$this->player->equipment['wep']['n'].'的效果增加了'.$this->data['e'].'，变成了'.$this->player->equipment['wep']['e']);
				
			}else{
				
				$this->player->equipment['wep']['e'] -= ceil($this->data['e'] / 2);
				$this->player->error($this->data['n'].'使用失败，'.$this->player->equipment['wep']['n'].'的效果减少了'.ceil($this->data['e'] / 2).'，变成了'.$this->player->equipment['wep']['e'], false);
				
				if($this->player->equipment['wep']['e'] <= 0){
					$this->player->feedback($this->player->equipment['wep']['n'].'损坏了');
					global $null_item;
					$this->player->equipment['wep'] = $null_item;
					$this->player->calculate_battle_info(); //重新计算攻防
				}
				
			}
			
			return true;
		}else{
			$this->player->error('不能对'.$this->player->equipment['wep']['n'].'使用'.$this->data['n']);
			return false;
		}
	}
	
	protected function apply_hacker()
	{
		if($this->data['s'] == 0){
			$this->player->error($this->data['n'].' 没电了，无法使用', false);
			return false;
		}
		
		if(determine(in_array('Hacker', $this->player->skill) ? 95 :25)){
			global $map;
			$all_map = array();
			$target_map = array();
			foreach($map as $mid => $map_name){
				$all_map[] = $mid;
				if($mid !== 0){
					$target_map[] = $mid;
				}
			}
			
			$GLOBALS['g']->gameinfo['forbiddenlist'] = array();
			$GLOBALS['g']->moving_NPC($all_map, $target_map);
			$GLOBALS['a']->action('area_info', $GLOBALS['g']->get_areainfo(), true);
			$this->player->feedback('Hacking成功，所有禁区解开了！');
		}else{
			$this->player->feedback('Hacking失败了……');
		}
		
		if(determine(5)){
			$this->player->error($this->data['n'].' 的电路烧毁了', false);
		}
		
		if(determine(3)){
			$this->player->error('滴滴滴……警报的声响！？', false);
			$this->player->sacrifice(array('source' => 'hack'));
		}
		
		return true;
	}
	
	protected function apply_deathnote($param)
	{
		if(false === isset($param['target'])){
			$this->player->ajax('item_param', array(
				'id' => $this->id,
				'input' => array(array(
					'key' => '请输入要使用的对象',
					'name' => 'target',
					'type' => 'text'
					))
				));
			return false;
		}else{
			$this->player->feedback($param['target'].' 写在了 '.$this->data['n'].' 的扉页上，烧成了灰烬');
			$target = $GLOBALS['db']->select('players', array('name' => $param['target'], type => GAME_PLAYER_USER));
			if(!$target){
				return true; //使用失败也消失
			}else{
				$deceased = new_player($target[0]);
				$deceased->sacrifice(array('pid' => $this->player->_id, 'source' => 'custom:死亡笔记')); //TODO
			}
			return true;
		}
	}
	
	protected function apply_weatherod()
	{
		$weather = random($GLOBALS['normal_weather'], sizeof($GLOBALS['weatherinfo']) - 1);
		$GLOBALS['g']->gameinfo['weather'] = $weather;
		$this->player->feedback($this->data['n'].' 使用成功，天气变成了'.$GLOBALS['weatherinfo'][$weather]);
		$GLOBALS['a']->action('weather', array('name' => $GLOBALS['weatherinfo'][$weather]), true);
		return true;
	}
	
	protected function apply_silencer()
	{
		if($this->player->equipment['wep']['n'] == ''){
			
			$this->player->error('没有装备武器');
			
			return false;
			
		}else if(isset($this->player->equipment['wep']['sk']['silent'])){
			
			$this->player->error($this->player->equipment['wep']['n'].'已经装备了消音器');
			
			return false;
			
		}else if($this->player->equipment['wep']['k'] === 'WG'){
			
			$this->player->data['equipment']['wep']['sk']['silent'] = true;
			$this->player->data['equipment']['wep']['n'] .= '-消';
			
			$this->player->feedback($this->player->equipment['wep']['n'].'装上了消音器');
			
			return true;
			
		}else{
			$this->player->error('不能对'.$this->player->equipment['wep']['n'].'使用'.$this->data['n']);
			return false;
		}
	}
	
	protected function apply_dice()
	{
		$dice = random(0, 99);
		if($dice < 20){
			$up = 5;
			$log = '是大吉！要有什么好事发生了！';
		}elseif($dice < 40){
			$up = 3;
			$log = '中吉吗？感觉还不错！';
		}elseif($dice < 60){
			$up = 1;
			$log = '小吉吗？有跟无也没有什么分别';
		}elseif($dice < 80){
			$up = -1;
			$log = '凶，真是不吉利。';
		}else{
			$up = -3;
			$log = '大凶？总觉得有什么可怕的事快要发生了';
		}
		
		$uphp = random(0, abs($up)) * ($up > 0 ? 1 : -1);
		$upatt = random(0, abs($up)) * ($up > 0 ? 1 : -1);
		$updef = random(0, abs($up)) * ($up > 0 ? 1 : -1);
		
		if($up > 0){
			$log .= '【命】+'.$uphp.' 【攻】+'.$upatt.' 【防】+'.$updef;
		}else{
			$log .= '【命】-'.-$uphp.' 【攻】-'.-$upatt.' 【防】-'.-$updef;
		}
		
		$this->player->mhp += $uphp;
		$this->player->att += $upatt;
		$this->player->def += $updef;
		
		$this->player->calculate_battle_info();
		$this->player->feedback('使用了御神签，'.$log);
		
		$GLOBALS['a']->action('max_health', array('mhp' => $this->player->mhp, 'msp' => $this->player->msp));
		
		return true;
	}
	
	protected function apply_battery($param)
	{
		$avaliable = array();
		$avaliable_name = array();
		foreach($this->player->package as $iid => $pitem){
			if($pitem['n'] == '移动PC' || $pitem['n'] == '雷达'){
				$avaliable[] = $iid;
				$avaliable_name[] = $pitem['n'];
			}
		}
		
		if(sizeof($avaliable) === 0){
			$this->player->error($this->data['n'].' 该怎么使用呢？');
			return false;
		}
		
		if(sizeof($avaliable) === 1){
			$param['target'] = $avaliable[0];
		}
		
		if(false === isset($param['target'])){
			$this->player->ajax('item_param', array(
				'id' => $this->id,
				'input' => array(array(
					'key' => '请选择要充电的物品',
					'name' => 'target',
					'type' => 'radio',
					'value' => $avaliable,
					'content' => $avaliable_name
					))
				));
			return false;
		}else{
			if($this->player->package[$param['target']]['n'] !== '移动PC' && $this->player->package[$param['target']]['n'] !== '雷达'){
				$this->player->error($this->player->package[$param['target']]['n'].' 不能充电');
				return false;
			}
			
			$this->player->data['package'][$param['target']]['s'] += $this->data['e'];
			$this->player->feedback($this->data['n'].' 使用成功， '.$this->player->package[$param['target']]['n'].'的耐久变成了 '.$this->player->package[$param['target']]['s']);
			return true;
		}
	}
	
	protected function apply_flounder()
	{
		global $db, $a, $g;
		$db->update('players', array('area' => 99), array('hp' => array('$lte' => 0)), false);
		$a->action('notice', array('msg' => '突然刮起了一阵怪风，把地上的尸体都吹走了！', 'time' => time()));
		$g->insert_news('flounder', array('caster' => $this->player->name));
		$this->player->feedback('凸眼鱼 使用成功');
		return true;
	}
	
	protected function apply_radar()
	{
		global $db, $map, $a;
		
		$opponents = $db->select('players', array('area'), array('type' => GAME_PLAYER_USER));
		
		$radar = array();
		for($i = 0; $i < sizeof($map); $i ++){
			$radar[] = 0;
		}
		
		foreach($opponents as $opponent){
			$radar[$opponent['area']] ++;
		}
		
		$a->action('radar', array('result' => $radar));
		$this->player->feedback($this->data['n'].' 使用成功');
		
		return true;
	}
	
}

?>