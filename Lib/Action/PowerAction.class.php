<?php
//
class PowerAction extends Action {
    public function index(){
    	
    	//获取个人信息
    	$this->url  = "http://218.75.197.120:8021/XSCK/Login_Students.aspx";
    	
    	$this->display();
	}
	
	public function staggerer()
	{
		$this->display();
	}
	

}


