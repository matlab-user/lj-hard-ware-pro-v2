<?php 
class logger{
	public $path = './logger.log'; //默认值文件
	public $mode = 'a'; //默认追加写
	public $content = '默认值：空'; //默认内容是 空
	public $tag = '空';
	
	// 使用方式
	// $log = new logger();
	// $log->write('this is content', 'test');
	public function write($content, $tag=null, $path=null, $mode=null) {
		if(!empty($path))
			$this->path = $path;
	
		if(!empty($mode))
			$this->mode = $mode;
	
		if(!empty($content))
			$this->content = $content;
	
		if(!empty($tag))
			$this->tag = $tag;
	
		$handle = fopen($this->path, $this->mode);
	
		if($handle){
			fwrite($handle, "[" . $this->tag . "]" . $this->content . "\r\n");
			
			fclose($handle);
		}
	
		
	}
	
}

	
?>