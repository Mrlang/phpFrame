<?php
class userController extends conF{
	public function userInfo($name, $pass){
		echo '$name is '.$name.', $pass is '.$pass."<br>";
		print_r($_GET);
		$this->display();
	}
	public function lalala(){
		echo 'lalala';
	}
}
