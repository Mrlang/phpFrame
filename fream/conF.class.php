<?php
class conF{


	public function display(){
		$className = get_class($this);//获取当前控制器名
		$class = new ReflectionClass($className);
		$classPath = $class->getFileName();
		preg_match("/\/app\/(.*?)\/controller/", $classPath,$result);
		$module = $result[1];
		$relName = preg_replace('/Controller/', '', $className);
		$action = debug_backtrace()[1]['function'];
		$viewFilePath = './app/'.$module.'/view/'.$relName.'/'.$action.'.html';  // 获取html模板的位置
		$test = new parser(array($viewFilePath), $action.'.php', true); //模板引擎生成缓存文件
		require_once('./app/'.$module.'/cache/'.$action.'.php');
	}


	public function D($host = 'localhost', $user = 'root', $password = '123456', $db_name='mactest'){
		try{
			return Mysql::get_instance($host, $user, $password, $db_name);
		}catch(Exception $ex){
			throw new Exception("数据链接字段有错", 1);
		}
	}
}
