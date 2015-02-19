<?php
interface IChloroComet
{
	// 构造函数
	public function __construct(array $config);
	
	// 设置触发本次脚本的玩家，该玩家的消息将不会推送而是随着本次脚本一起返回
	public function set_self($hash, $ajax);
	
	// 获取在一个推送对象待推送消息列表中的所有消息
	public function query($hash);
	
	// 清除所有推送对象
	public function clear_all();
	
	// 向一个推送对象的待推送消息列表中添加一条消息
	public function add($hash, array $value);
	
	// 删除一个推送对象待推送消息列表中的所有内容
	public function clear($hash, $delete);
	
	// 创建一个推送对象
	public function create($hash);
}
?>