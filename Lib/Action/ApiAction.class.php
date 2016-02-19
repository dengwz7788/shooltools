<?php
//
class ApiAction extends Action {
    
	public function _initialize()
	{
		
		//忽略一些登录细节
		$PassAction = array('Login','auth');
		 
		 if(!in_array(ACTION_NAME, $PassAction))
		 {
		 	$Api_login_id 	= $this->_pg("Api_login_id");
		 	if(empty($Api_login_id))
		 	{
		 		$info = "连接超时，请登录";
		 		$status = false;
		 		$data = array();
		 		$this->json_back($status, $data, $info);
		 	}
		 }

	}
	
	//获取用户的所有学期
	public function getAllSchoolYear()
	{
		$avggrade = M('avggrade');
		
		$map['studentid'] = $_SESSION['UserID'];
		
		$datas = $avggrade->where($map)->field('semester,schoolYear')->order('schoolYear asc,semester asc')->select();
		
		$status = false;
		
		$num = count($datas);

		if(empty($datas))
		{
			$info = "没有数据";
			
		}
		else
		{
			$status = true;
			$info = "查询成功";
		}
		
		$data = array('num'=>$num,'data'=>$datas);
		
		$this->json_back($status, $data, $info);
	}
	
	
	//授权接口
	public function auth()
	{	
		 $userid = $this->_pg("userid");
		 $password = $this->_pg("password");
		
		 A('jwc')->getUserData($userid,$password);
	}
	
	//登录API
	public function login()
	{
          $userid = $this->_pg("userid");
          $password = $this->_pg("password");
          
          $status = false; //默认设置为false
          $data = array(); //返回的数组
          
          if(empty($userid) || empty($password))
          {
          	  $info = "帐号或密码不能为空";
          }
          else
          {
          	   if(!is_numeric($userid) && strlen($userid) == "11")
          	   {
          	   	  $info = "学号必须为纯数字，并且需要11位";
          	   }
          	   else
          	   {
	          	   	$student = M("student");
					$map['studentid'] = array('eq',$userid);
					$userData= $student->where($map)->field("wjcpassword,truename")->find();
					
					$jwcpassword = A('Summer')->aes_decode($userData['wjcpassword'],$userid);
					
					if($password === $jwcpassword)
					{
	    				 $array['Api_login_id'] = A('Summer')->aes_encode(session_id(),"SummerToys");
	    				 $_SESSION['UserID'] = $userid;
	    				 $info = "登陆成功";
	    				 $status = true;
	    				 $data = $array;
	    			}
          	   }
          }
          
         $this->json_back($status, $data, $info);
	}
	
	//保持在线
	public function KeepLogin()
	{
		$Api_login_id = $this->_pg("Api_login_id");
		
		$status = false; //默认设置为false
		$data = array(); //返回的数组
		
		if(empty($Api_login_id))
		{
			 $info = "你没有登录,没法更新";
		}
		else
		{	
			
			$Api_login_id = A('Summer')->aes_decode($Api_login_id,"SummerToys");
			
			
				if($Api_login_id == session_id())
				{
					$info = "Keep成功";
					$status = true;
				}
				else
				{
					$info = "Keep失败，可能还有登录";
				}	
		}
		
		$this->json_back($status, $data, $info);
		
	}
	
	
	//获取个人成绩
	public function getPersonGrade()
	{
		//$Api_login_id 	= $this->_pg("Api_login_id");
		$GradeList 		= $this->_pg("GradeList",false);  //默认是false,表示不获取详细成绩
		$SchoolYear 	= $this->_pg("SchoolYear");  //可以为空
  		$Semester 		= $this->_pg("Semester"); 	//可以为空

  		$status = false; //默认设置为false
  		$data = array(); //返回的数组
  		
  		if(!empty($SchoolYear))
  		{
  			 //判断学年的格式 2014-2015 
  			 $SchoolYearArr = explode("-", $SchoolYear);
  			 
  			 if(count($SchoolYearArr) != 2)
  			 {
  			 	 $info = "学年格式错误，应该是2014-2015";
  			 	 $this->json_back($status, $data, $info);
  			 }
  			 else if(strlen($SchoolYearArr['0']) != 4 || strlen($SchoolYearArr['1']) != 4)
  			 {
  			 	$info = "学年格式错误，年应该用4位数表示，例如2014";
  			 	$this->json_back($status, $data, $info);
  			 }
  			 else if($SchoolYearArr['1'] - $SchoolYearArr['0'] != 1)
  			 {
  			 	 $info = "学年格式错误，学年之间应该只差1年时间";
  			 	 $this->json_back($status, $data, $info);
  			 }
  			 else
  			 {
  			 	$map['schoolYear'] = array('eq',$SchoolYear);
  			 }
  		}
  		
  		if(!empty($Semester))
  		{
  			//判断学期的格式
  			if($Semester != 1 && $Semester != 2)
  			{
  				$info = "学期格式只能用1或者2来表示";
  				$this->json_back($status, $data, $info);
  			}
  			else
  			{
  				$map['semester'] = array('eq',$Semester);
  			}
  		}
  		
  		if(empty($Semester) || empty($SchoolYear))
  		{
  			$map = $this->getCurrentTerm();
  		}
  		
  		$map['studentid'] = array('eq',$_SESSION['UserID']);
  		
  		$GradeData = A('Grade')->_todoGrade($map);
  
  		$avggradeData = M("avggrade")->where($map)->select();

  		if(empty($GradeData))
  		{
  			$info = $SchoolYear."学年,第".$Semester."学期，成绩为空";
  		}
  		else
  		{
  			$info = "查询成功";
  			$status = true;	
  			$data = array('GradeData'=>$GradeData,"Avg"=>$avggradeData);
  		}
  		

  		$this->json_back($status, $data, $info);
	}
	
	//获取总学期绩点和平均分
	public function getAllGrade()
	{
	    
		$data = A('Grade')->getAllGrade($userid);

    	$this->json_back(true, $data, "数据获取成功");
	}
	

	//获取数据类型
	private function _pg($name,$default=NULL)
	{
		$pg = isset($_POST[$name])?$_POST[$name]:$_GET[$name];
			
		if(strlen($pg) == 0)
		{
			return $default;
		}
		return trim($pg);
			
	}
	
	//获取本学期
	private function getCurrentTerm()
	{
		$Month = date('m',time());
		if($Month<="06")
		{
			//表示是上半年
			$year = date('Y',time());
			$BeforYear = date('Y',strtotime('-1 year',time()));
			$map['schoolYear'] = array('eq',$BeforYear."-".$year);
			$map['semester'] = array('eq','1');
		}
		else
		{
			//表是下半年
			$year = date('Y',time());
			$afterYear = date('Y',strtotime('+1 year',time()));
			$map['schoolYear'] = array('eq',$year."-".$afterYear);
			$map['semester'] = array('eq','2');
		}
		 
		return $map;
	}
	
	//批量返回
	private function json_back($status,$data,$info)
	{
		echo  json_encode(array('status'=>$status,'data'=>$data,'info'=>$info));
		exit;
	}
	
	//意见反馈
	public function feedback()
	{
		$datas['truename'] = htmlspecialchars($this->_pg("Truename")); //反馈人信息
		$datas['contect']  = htmlspecialchars($this->_pg("Contect"));  //反馈人联系
		$datas['content']  = htmlspecialchars($this->_pg("Content"));  //反馈内容
		
		$status = false;
		$data = array();

		
			if(empty($datas['content']))
			{
				$info = "要反馈的内容是？";
			}
			else
			{
				$feedback = M("feedback");
				$feedback->create();
				$datas['addtime'] = time();
				if($feedback->add($datas)){
					$status = true;
					$info = "反馈成功";
					$_SESSION['reply_do'] = time()+5;
				}else{
					$info = "反馈的问题我们已经收到了,该问题已经被系统记录，请不要重新提交";
					 
				}
		}

		$this->json_back($status, $data, $info);
	}
	
}


