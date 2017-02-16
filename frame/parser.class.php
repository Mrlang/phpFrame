<?php
date_default_timezone_set('PRC');
class parser {
	private $file_arr;      //数组：需要编译的页面
	private $target_file;	//生成缓存文件的存放路径
	private $file_content;
	private $flag = false;
	private $exp_js = '/<script[\s]+?src=[\'\"]([\s\S]+?)[\'\"]>[\s\S]*?<\/script>/';

	function __construct ($file_arr, $target_file, $b_js = false) {//是否将外链js绑定进页面
		$this->target_file = $target_file;
		if (!Cache_file::check_cache($file_arr,$target_file)) {  //检查缓存
			$this->file_arr = $file_arr;
			$this->joinContent();	//将$file_arr数组中指定的多个文件的内容拼接在一起存在$this->file_content.
			$b_js == true ? $this->joinJs() : 0;     //引入js文件具体内容
			$this->replaceTags();  //进行模版中自定义标签的编译替换
			$this->flag = true;
		}
	}

	private function joinContent () {	//该方法用于拼接文件模板文件
		foreach ($this->file_arr as $value) {
			$value = file_get_contents($value);
			$this->file_content .= $value;
		}
	}

	private function joinJs(){
		$handle = function($match){
			$jsFileName = $match[1];
			$jsFileContent = file_get_contents($jsFileName);
			return '<script>'.$jsFileContent.'</script>';
		};
		$this->file_content = preg_replace_callback($this->exp_js, $handle, $this->file_content);
	}

	//进行模版中自定义标签的编译替换
	private function replaceTags () {
		$temp = new Mould($this->file_content);
		if(!file_exists('app/home/cache')){
			mkdir('app/home/cache', 0775);
		}
		file_put_contents('app/home/cache/'.$this->target_file,$temp->replace());
	}
}

class Mould {
	private $variable = '/\{(\$[^\s]+?)\}/';
	private $foreach_begin = '/<foreach\s+([\S]+)\s+as\s+([\S]+)>{1}/';
	private $foreach_end = '/<\/each>/';

	private $temp_for_sec = '/<each[\s\S]+?>/';    //开头
	private $for_sec = '/[^\s=,;<>=0-9][^\,s=;<>=]*/';  //开头
	private $file_content;
	function __construct ($file_content) {
		$this->file_content = $file_content;
	}

	public function replace () {
		$this->match_var();
		$this->match_foreach();
		$this->match_for();
		return $this->file_content;
	}

	function match_var () {
		$this->file_content = preg_replace_callback(
			$this->variable,
			function ($match) {
				return '<?php echo ('.$match[1].'); ?>';
			},
			$this->file_content
		);
	}

	function match_foreach () {
		$this->file_content = preg_replace_callback(
			$this->foreach_begin,
			function ($match) {
				return '<?php foreach( $'.$match[1].' as $'.$match[2].')  {  ?>';
			},
			$this->file_content
		);
		$this->file_content = preg_replace_callback(
			$this->foreach_end,
			function ($match) {
				return '<?php }  ?>';
			},
			$this->file_content
		);
	}

	function match_for () {
		preg_match_all(
			$this->temp_for_sec
			,$this->file_content
			,$match_arr
		);
		for($i = 0,$len = count($match_arr[0]);$i < $len;$i++){
			$strk[] = preg_replace_callback(
				$this->for_sec,
				function ($march) {
					return '$'.$march[0];
				},
				substr($match_arr[0][$i],5,strlen($match_arr[0][$i]) - 6)
			);
			$strk[$i] = '<?php for('.$strk[$i].') {?>';
		}
		if($i == 0){
			return;
		}
		$str_arr = preg_split(
			$this->temp_for_sec,
			$this->file_content
		);
		$str = '';
		for($i = 0,$len = count($strk);$i < $len;$i++){
			$str .= $str_arr[$i];
			$str .= $strk[$i];
		}
		$str .= $str_arr[$i];
		$this->file_content = $str;
	}
}

class Cache_file {
	static private $file_arr;
	static private $target_file;
	static private $cache_arr;

	static public function check_cache ($file_arr,$target_file) {
		$flag01 = false;
		$flag02 = true;
		self::$file_arr = $file_arr;
		self::$target_file = $target_file;
		if(!file_exists('app/home/cache')){  //如果不存在就创建文件夹
			mkdir('app/home/cache', 0777);
		}
		if(!file_exists('app/home/cache/cache.json')){ //创建缓存说明文件
			$arr = array(
				'target_files'=>array($target_file),
				'model_files'=>array()
			);
			foreach ($file_arr as $value) {       //为每一个缓存文件添加说明
				$arr['model_files'][] = array(
					'name'=>$value,
					'last_change'=>self::get_change_time($value)
				);
			}
			$fopen = fopen('app/home/cache/cache.json','wb ');
			fwrite($fopen,json_encode($arr));			//将说明写入josn文件
			fclose($fopen);
		}
		self::$cache_arr = json_decode(file_get_contents('app/home/cache/cache.json'),true);//读取缓存记录文件，获取缓存文件数组的相关信息
		if(!(array_search(self::$target_file, self::$cache_arr['target_files']) > -1)){
			//如果缓存记录中没有该文件就handle
			self::handle_cache(array(self::$target_file),'target_files');
			$flag02 = false;
		}
		if (!file_exists('app/home/cache/'.$target_file)) {
			$flag02 = false;
		}
		$temp_arr = array();
		for ($i = count(self::$file_arr) - 1;$i > -1;$i--) {
			for ($j = count(self::$cache_arr['model_files']) - 1;$j > -1;$j--) {
				if ($file_arr[$i] == self::$cache_arr['model_files'][$j]['name']) {
					if (self::get_change_time(self::$file_arr[$i]) == self::$cache_arr['model_files'][$j]['last_change']) {
						unset(self::$file_arr[$i]);
					}
				}
			}
		}
		if (count(self::$file_arr) == 0) {
			$flag01 = true;
		}
		self::handle_cache(self::$file_arr,'model_files');
		file_put_contents('app/home/cache/cache.json', json_encode(self::$cache_arr));
		return $flag01&&$flag02;
	}

	static private function handle_cache ($file_arr,$target) {
		foreach ($file_arr as $i => $value) {
			$flag = false;
			if($target == 'target_files'){
				self::$cache_arr['target_files'][] = $file_arr[$i];
			}else{
				for ($j = count(self::$cache_arr['model_files']) - 1;$j > -1;$j--) {
					if ($file_arr[$i] == self::$cache_arr['model_files'][$j]['name']) {
						self::$cache_arr['model_files'][$j]['last_change'] = self::get_change_time($file_arr[$i]);
						$flag = true;
						break;
					}
				}
				if (!$flag) {
					self::$cache_arr['model_files'][] = array('name'=>$file_arr[$i],'last_change'=>self::get_change_time($file_arr[$i]));
				}
			}
		}

	}

	static private function get_change_time ($file) {
		$timer = filemtime($file);
		return date("Y-m-d H:i:s",$timer);
	}
}
