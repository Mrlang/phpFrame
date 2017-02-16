<?php
class conF{
	private $moduleName = '';
	private $controllerName = '';

	public function display($pageName=''){
		$pageName=='' ? $pageName=debug_backtrace()[1]['function'] : 0;
		$viewFilePath=$this->getViewFilePath($pageName);  // 获取html模板的位置
		new parser(array($viewFilePath), $pageName.'.php', true); //模板引擎生成缓存文件
		// $test = 'this variable test';
		require_once($this->getCacheFilePath($pageName));
	}


	public function D($host = 'localhost', $user = 'root', $password = '123456', $db_name='mactest'){
		try{
			return Mysql::get_instance($host, $user, $password, $db_name);
		}catch(Exception $ex){
			throw new Exception("数据链接字段有错", 1);
		}
	}

	private function getViewFilePath($pageName){
		$className = get_class($this);    //获取实例对象对用的类名
		$classObject = new ReflectionClass($className);  //获取class对象
		$classPath = $classObject->getFileName();   // "/Users/Mr_liang/Sites/phpFrame/app/home/controller/indexController.class.php"
		preg_match("/\/app\/(.*?)\/controller/",$classPath, $result);
		$this->moduleName = $result[1];
		$this->controllerName = preg_replace('/Controller/', '', $className);
		return './app/'.$this->moduleName.'/view/'.$this->controllerName.'/'.$pageName.'.html';
	}

	private function getCacheFilePath($pageName){
		return './app/'.$this->moduleName.'/cache/'.$pageName.'.php';
	}
}
