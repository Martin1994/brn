<?php
//TODO: 将推送逻辑改为脚本结束时ajax类统一将需要推送的内容发送至comet类
class chloroajax
{
	
	private $stack;
	private $comet;
	
	public function __construct($c = false)
	{
		$this->stack = array();
		$this->comet = $c;
		return;
	}
	
	/**
	 * 发送ajax命令
	 * @param string $action 命令名
	 * @param array|boolean $param 命令参数（形如: Array('p1' => 'v1', 'p2' => 2)）
	 * @param array|boolean $comet 发送对象：
	 *                                 数组内容为userID，$exception键存在则代表全员发送且不发送$exception内的userID；
	 *                                 true代表全员发送；
	 *                                 默认值false代表发送触发本次脚本的玩家
	 * @param boolean $unshift 是否将本条消息插入到消息队列的第一条
	 */
	public function action($action, $param = false, $comet = false, $unshift = false)
	{
		if($param === false or is_array($param) === false){
			$param = array();
		}
		$data = array('action' => $action, 'param' => $param);

		if($comet === false){
			$this->push($data, $unshift);
		}else{
			//PHP5.3如果不判断数组类型只要$comet不是数组会恒返回true
			if(is_array($comet) && isset($comet['$exception'])){
				$this->comet->add_all($data, $comet['$exception']);
			}else if($comet === true) {
				$this->comet->add_all($data, array());
			}else{
				$this->comet->add($comet, $data);
			}
		}
		return;
	}
	
	public function push($data = false, $unshift = false)
	{
		if($data === false or is_array($data) === false){
			$data = array();
		}
		if($unshift === true){
			array_unshift($this->stack, $data);
		}else{
			array_push($this->stack, $data);
		}
		return;
	}
	
	public function flush()
	{
		echo json_encode($this->stack);
		$this->stack = array();
		exit();
		return;
	}
	
	public function clear()
	{
		return $this->stack = array();
	}
	
	public function __destruct(){
		if(sizeof($this->stack) !== 0){
			echo json_encode($this->stack);
		}
		return;
	}
}

?>