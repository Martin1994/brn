<?php
//TODO: �������߼���Ϊ�ű�����ʱajax��ͳһ����Ҫ���͵����ݷ�����comet��
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
	 * ����ajax����
	 * param: $action String ������
	 *        $param Array �������������: Array('p1' => 'v1', 'p2' => 2)��
	 *        $comet Array/Boolean ���Ͷ���
	 *                                 ��������ΪuserID��$exception�����������ȫԱ�����Ҳ�����$exception�ڵ�userID��
	 *                                 true����ȫԱ���ͣ�
	 *                                 Ĭ��ֵfalse�����ʹ������νű������
	 *        $unshift Boolean �Ƿ񽫱�����Ϣ���뵽��Ϣ���еĵ�һ��
	 * return: null
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
			//PHP5.3������ж���������ֻҪ$comet���������㷵��true
			if(is_array($comet) && isset($comet['$exception'])){
				$this->comet->add_all($data, $comet['$exception']);
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