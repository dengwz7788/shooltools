<?php
// 本类由系统自动生成，仅供测试用途
class StudentAction extends CommonAction {
     
	//获取个人信息
	public function Userinfo()
	{
		 $student = M('student');
		 
		 $map['studentid'] = array('eq',$_SESSION['userid']);
		 
		 $UserInfo = $student->where($map)->field('studentid,truename,classname,schoolname,depname,garde,level,telephone,source')->find();
		 
		 $this->TrueName = A("Summer")->aes_decode($UserInfo['truename'],$UserInfo['studentid']);
		 
		 $this->UserInfo = $UserInfo;
		
		 $this->display();
	}
	
	//保存信息
	public function SaveUserinfo()
	{
		$student = M('student');
			
		$map['studentid'] = array('eq',$_SESSION['userid']);
		
		$field = $this->_pg("name");
		
		if($field == "telephone")
		{
			 //验证号码是否正确
			 if(!preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $this->_pg("value")))
			 {
			 	 exit;
			 }
			 
		}
		
		
		$data[$field] = $this->_pg("value");
		
		$student->where($map)->save($data);

		exit;
	}
}