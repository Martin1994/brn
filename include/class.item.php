<?php

class item
{
	
	protected $data;
	protected $player;
	protected $id;
	
	public function __construct(player &$player, array &$data, $id)
	{
		$this->data = &$data;
		$this->player = &$player;
		$this->id = $id;
		
		return;
	}
	
	public function apply($param = array())
	{
		$k = str_split($this->k);
		
		switch($k[0]){
			case 'D':
			case 'W':
			case 'A':
				$this->equip();
				break;
			
			case 'H':
				$this->heal($k[1]);
				break;
			
			case 'G':
				$this->reload();
				break;
			
			case 'T':
				$this->set_trap();
				break;
			
			case 'Y':
				$this->special($param);
				break;
			
			default:
				$this->player->notice('unknown kind: '.$k[0]);
				break;
		}
	}
	
	//继承时先执行新功能，遇到例外再调用父类函数；继承时注意消耗物品，注意不要重复消耗物品
	protected function special($param)
	{
		$success = true;
		switch($this->data['n']){
			case '解毒剂':
				foreach($this->player->buff as $key => $buff){
					if($buff['type'] === 'poison'){
						$this->player->remove_buff($key);
					}
				}
				$this->player->feedback('饮用了解毒剂，毒状态解除了');
				break;
			
			case '毒药':
				$success = $this->apply_poison($param);
				break;
			
			default:
				$this->player->error($this->data['n'].'该怎么使用呢？', false);
				$success = false;
				break;
		}
		if($success){
			$this->consume();
		}
	}
	
	protected function apply_poison($param)
	{
		if(false === isset($param['target'])){
			$success = false;
			$items = $this->player->get_envenomable_items();
			$value = array();
			$content = array();
			foreach($items as $key => $item){
				$value[] = $key;
				$content[] = $item['n'];
			}
			$this->player->ajax('item_param', array(
				'id' => $this->id,
				'input' => array(array(
					'key' => '请选择要下毒的物品',
					'name' => 'target',
					'type' => 'radio',
					'value' => $value,
					'content' => $content
					))
				));
		}else{
			$success = true;
			$target = $param['target'];
			$envenomable = $this->player->get_envenomable_items();
			if(false === isset($envenomable[$target])){
				return $this->player->error('不能对这个物品使用毒药');
			}
			
			if($target === 'wep'){
				$item = &$this->player->data['equipment']['wep'];
			}else{
				$item = &$this->player->data['package'][intval($target)];
				if(false === isset($item['sk']['poison-applier'])){
					$item['sk']['poison-applier'] = array();
				}
				$item['sk']['poison-applier'][] = $this->player->_id;
			}
			$effect = intval($this->player->get_poison_power($item['k'], $item['e']));
			if(false === isset($item['sk']['poison'])){
				$item['sk']['poison'] = $effect;
			}else{
				$item['sk']['poison'] = $item['sk']['poison'] == 0 ? 0 : $item['sk']['poison'] + $effect;
			}
			
			switch(substr($item['k'], 0, 1)){
				case 'W':
					$this->player->feedback($item['n'].'淬毒成功，持续'.$effect.'回合');
					$this->player->ajax('item', array('equipment' => $this->player->parse_equipment()));
					break;
				
				case 'H':
					$this->player->feedback($item['n'].'下毒成功，持续'.$effect.'秒');
					break;
				
				default:
					$this->player->feedback('毒药似乎使用失败了');
					break;
			}
		}
		return $success;
	}
	
	protected function get_trap_effect()
	{
		return $this->data['e'];
	}
	
	protected function set_trap()
	{
		if($this->data['k'] !== 'TN'){
			return $this->player->error('无法设置此陷阱');
		}
		
		global $db;
		$trap = array(
			'itm' => $this->data['n'],
			'itmk' => 'TO',
			'itme' => $this->get_trap_effect(),
			'itms' => 1,
			'itmsk' => array_merge($this->data['sk'], array('owner' => $this->player->_id, 'effect' => $this->data['e'])),
			'area' => $this->player->area
			);
		$trap['itmsk']['pid'] = $this->player->_id;
		
		$db->insert('items', $trap);
		
		$this->consume();
		
		$this->player->data['proficiency']['d'] += 1;
		
		$this->player->feedback($this->data['n'].' 设置成功');
		$this->player->ajax('proficiency', array('proficiency' => $this->player->proficiency));
	}
	
	protected function reload()
	{
		while($this->player->weapon_reload($this->data['e']) && $this->consume()){}
		$this->player->ajax('item', array('equipment' => $this->player->parse_equipment(), 'package' => $this->player->parse_package()));
	}
	
	protected function equip()
	{
		$this->player->equip($this->id);
		return;
	}
	
	protected function heal($kind)
	{
		$heal_type = array('H' => 'hp', 'S' => 'sp', 'B' => 'all');
		$modulus = $this->player->get_potion_effect();
		if(isset($heal_type[$kind])){
			$type = $heal_type[$kind];
			global $healthinfo;
			if($type === 'all'){
				if($this->player->data['hp'] == $this->player->data['mhp'] && $this->player->data['sp'] == $this->player->data['msp']){
					global $healthinfo;
					$this->player->error($healthinfo['hp'].'与'.$healthinfo['sp'].'已经达到最大值，无需补充');
				}else{
					if(isset($this->data['sk']['poison'])){
						//毒
						$damage_source = array('type' => 'poison');
						if(isset($this->data['sk']['poison-applier'])){
							$damage_source['pid'] = $this->data['sk']['poison-applier'];
						}
						$damage = $this->player->damage($this->data['e'], $damage_source); //TODO: 下毒者信息
						$this->player->buff('poison', $this->data['sk']['poison']);
						$this->player->feedback('糟糕，'.$this->data['n'].'有毒，你中毒了，并失去了'.$damage.'点'.$healthinfo['hp']);
					}else{
						$hp_add = $this->player->heal('hp', $this->data['e'] * $modulus['hp']);
						$sp_add = $this->player->heal('sp', $this->data['e'] * $modulus['sp']);
						$this->player->feedback($this->data['n'].'使用成功，'.$healthinfo['hp'].'增加了'.strval($hp_add).'，').$healthinfo['sp'].'增加了'.strval($sp_add);
					}
				}
				
			}else if(isset($this->player->data[$type])){
				if($this->player->data[$type] >= $this->player->data['m'.$type]){
					$this->player->error($healthinfo[$type].'已经达到最大值，无需补充');
				}else{
					if(isset($this->data['sk']['poison'])){
						//毒
						$damage_source = array('type' => 'poison');
						if(isset($this->data['sk']['poison-applier'])){
							$damage_source['pid'] = $this->data['sk']['poison-applier'];
						}
						$damage = $this->player->damage($this->data['e'], $damage_source); //TODO: 下毒者信息
						$this->player->buff('poison', $this->data['sk']['poison']);
						$this->player->feedback('糟糕，'.$this->data['n'].'有毒，你中毒了，并失去了'.$damage.'点'.$healthinfo['hp']);
					}else{
						$point_add = $this->player->heal($type, $this->data['e'] * $modulus[$type]);
						$this->player->feedback($this->data['n'].'使用成功，'.$healthinfo[$type].'增加了'.strval($point_add));
					}
				}
			}else{
				$this->player->feedback('这补给品似乎是补充某种未知能力的');
			}
			$this->player->ajax('health', array('hp' => $this->player->hp, 'sp' => $this->player->sp));
		}else{
			$this->player->feedback('这补给品似乎没有效果');
		}
		
		$this->consume();
		return;
	}
	
	public function consume($num = 0, $rearrange = true)
	{
		$id = $this->id;
		$item = &$this->data;
		
		if($item['s'] != 0){
			//有限耐装备
			
			//装备损坏判定
			global $attrit_rate;
			if($num == 0 && isset($attrit_rate[$item['k']])){
				if($GLOBALS['g']->determine($attrit_rate[$item['k']])){
					$item['s'] --;
				}
			}else{
				if($GLOBALS['g']->determine($attrit_rate['default'])){
					$item['s'] --;
				}
			}
			
			if($item['s'] <= 0){
				//$id是数字代表是背包物品，反之为装备
				if(is_numeric($id)){
					if(isset($item['sk']['immortal'])){
						$item['s'] = 0;
					}else{
						$this->player->notice($this->data['n'].' 用光了');
						unset($this->player->package[$id]);
						if($rearrange){
							$this->player->rearrange_package();
						}
						return false;
					}
				}else{
					if($item['k'] === 'WG' && !isset($item['sk']['nonammo'])){
						//射系子弹用完
						if(false === isset($item['sk']['alt'])){
							$item['sk']['alt'] = array('k' => 'P', 'e' => intval($item['e'] / 5));
						}
						$this->player->notice($this->data['n'].' 的弹药用光了');
						$this->weapon_transform();
					}else if(isset($item['sk']['immortal'])){
						$item['s'] = 0;
					}else{
						if($item['k'] === 'WC' || $item['k'] === 'WD'){
							$this->player->notice($this->data['n'].' 用光了');
						}else{
							$this->player->notice($this->data['n'].' 损坏了');
						}
						global $null_item;
						$this->player->equipment[$id] = $null_item;
						$this->player->active_suits();
						$this->player->calculate_battle_info(); //重新计算攻防
						return false;
					}
				}
			}
		}else{
			//无限耐装备
			
			//装备损坏判定
			global $mar_rate;
			if(isset($mar_rate[$item['k']])){
				if($GLOBALS['g']->determine($mar_rate[$item['k']])){
					$item['e'] --;
				}
			}else{
				if($GLOBALS['g']->determine($mar_rate['default'])){
					$item['e'] --;
				}
			}
			
			if($item['e'] == 0){
				//$id是数字代表是背包物品，反之为装备
				if(is_numeric($id)){
					$this->player->notice($this->data['n'].' 用坏了');
					unset($this->player->package[$id]);
					if($rearrange){
						$this->player->rearrange_package();
					}
					return false;
				}else{
					$this->player->notice($this->data['n'].' 损坏了');
					global $null_item;
					$this->player->equipment[$id] = $null_item;
					$this->player->active_suits();
					$this->player->calculate_battle_info(); //重新计算攻防
					return false;
				}
			}
		}
		
		if($num > 1){
			$this->consume($num - 1, $rearrange);
		}
		
		return true;
	}
	
	public function weapon_transform($force = false)
	{
		if(substr($this->data['k'], 0, 1) !== 'W'){
			return $this->player->error('只有武器才能转换使用方法');
		}
		
		if(false === isset($this->data['sk']['alt'])){
			return $this->player->error('该武器没有第二种使用方法');
		}
		
		if(false === isset($this->data['sk']['alt']['k'])){
			return $this->player->error('这个武器的另一种使用方法似乎.. 坏掉了....'); //这真不是吐槽
		}
		
		if(!$force && $this->data['sk']['alt']['k'] === 'G' && $this->data['s'] == 0 && !isset($this->data['sk']['nonammo'])){
			return $this->player->error('枪械没有子弹了，不能切换');
		}
		
		$k = substr($this->data['k'], 1, 1);
		$this->data['k'] = 'W'.$this->data['sk']['alt']['k'];
		$this->data['sk']['alt']['k'] = $k;
		
		if(isset($this->data['sk']['alt']['e'])){
			$e = $this->data['e'];
			$this->data['e'] = $this->data['sk']['alt']['e'];
			$this->data['sk']['alt']['e'] = $e;
		}
		
		global $iteminfo;
		$this->player->notice($this->data['n'].'的类型变为了'.$iteminfo[$this->data['k']]);
		
		$this->player->calculate_battle_info();
		$this->player->ajax('item', array('equipment' => $this->player->parse_equipment()));
	}
	
	public function __get($name)
	{
		return $this->data[$name];
	}
	
}

?>