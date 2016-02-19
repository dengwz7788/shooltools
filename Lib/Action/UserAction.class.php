<?php
// 本类由系统自动生成，仅供测试用途
class UserAction extends CommonAction {
    public function CheckUser(){
    	
    	$userid 	= $this->_pg('userid');
    	$password 	= $this->_pg('password');
    	
    	if(!empty($userid) && !empty($password))
    	{
    		//判断是否为数字
    		if(!is_numeric($userid))
    		{
    			$msg = "学号错误";
    		}
    		else if(strlen($userid) != 11)
    		{
    			$msg = "学号错误";
    		}
    		else {
    			
    			//登陆验证
    			$student = M("student");
    			$map['studentid'] = array('eq',$userid);
    			$password2= $student->where($map)->getField("password");
    			if(sha1($password) === $password2)
    			{
    				 $_SESSION['userid'] = $userid;
    				 $_SESSION['auth'] = "Summer_".time();
    				 $msg = "登陆成功";
    				 $this->backpost(true, $msg);
    			}
    			
    		}
    	}
    	else
    	{
    		$msg = "学号或密码错误";
    	}
    	$this->backpost(false, $msg);
	}
	
	public function JwcCheckUser($userid,$password){

		if(!empty($userid) && !empty($password))
		{
			//判断是否为数字
			if(!is_numeric($userid) || strlen($userid) != 11)
			{
				$msg = "学号错误";
			} else {
				 
				//登陆验证
				$student = M("student");
				$map['studentid'] = array('eq',$userid);
				$userData= $student->where($map)->field("wjcpassword,truename")->find();
		
				$jwcpassword = A('Summer')->aes_decode($userData['wjcpassword'],$userid);
				
				if($password === $jwcpassword)
				{
					if(!empty($userData['truename']))
					{
						$_SESSION['userid'] = $userid;
						$_SESSION['auth'] = "Summer_".time();
						return array(true,"登录成功");
					}
					else
					{
						return array(false,"重新采集");
					}
				
				}
			}
		}
		else
		{
			$msg = "学号或密码错误";
		}
		return array(false,$msg);
	}
	
	//记录登陆错误
	public function LoginLog($userid,$mark)
	{
		$student = M("student");
		$map['studentid'] = array('eq',$userid);
		$student->where($map)->setField("mark",$mark);
	}
	
	public function addUserInfo()
	{
		$telephone 	= $this->_pg('telephone');
		$password 	= $this->_pg('password');
		
		if(empty($password))
		{
			$msg = "查询密码不能为空";
			$this->backpost(false, $msg);
		}
		else
		{
			$data['password'] = sha1($password);
			$data['telephone'] = floatval($telephone);
			
			$Mode = M("student");
			$map['studentid'] = $_SESSION['userid'];
			$Mode->where($map)->save($data); 
			$msg = "信息补全成功";
			$this->backpost(true,$msg,"",array(),__APP__."/index.php/Grade/index");
		}

	}
	
	public function Graduates()
	{
		$userid = $this->_pg("Studentid");
		if(empty($userid))
		{
			$this->showmsg("请输入学号");
		}
		
		$graduates = M("graduates");
		
		$map['a.studentid'] = array('eq',$userid);
		
		$data = $graduates->alias('a')->where($map)
		->join('student as b on a.studentid=b.studentid')
		->join('post as c on c.PostID=a.Post')
		->join('company as d on d.company=a.LastCompany')
		->field("b.truename as name,b.garde,b.classname,d.companyname,d.companyaddress,d.companysize,d.companytype,c.postname,c.Description	,a.EnterDate,a.Websites")
		->find();

        $this->data = $data;
		
		$this->display();
	}
	

    public function AddGraduates()
    {
    	$graduates = M("graduates");
    	
    	$map['a.studentid'] = array('eq',$_SESSION['userid']);
    	
    	$data = $graduates->alias('a')->where($map)
    	->join('student as b on a.studentid=b.studentid')
    	->join('post as c on c.PostID=a.Post')
    	->join('company as d on d.company=a.LastCompany')
    	->field("b.truename as name,b.garde,b.classname,d.companyname,d.companyaddress,d.companysize,d.companytype,c.postname,c.posttype,c.Description,a.Email,a.Weibo,a.QQ,a.EnterDate,a.Websites")
    	->find();

    	$this->data = $data;
    	
    	
    	$this->display();
    }
    
    //保存信息
    public function saveGraduates()
    {
     
    	$userid = $this->_pg('userid');
    	
    	if(empty($userid))
    	{
    		$msg = "请先登录教务系统关联信息";
    		$this->showmsg($msg);
    	}
    	
    	//添加职位信息
    	$CompanyData = $this->_pg('company');
    	
    	//将职位信息写入数据库
    	if( empty($CompanyData['companyname']) )
    	{
    		$msg = "公司名字不能为空";	
    	}
    	else
    	{
    		$company = M("company");
    		$map['companyname'] = array('eq',$CompanyData['companyname']);
    		$companyid = $company->where($map)->getField("company");
    		if(empty($companyid))
    		{
    			$company->create();
    			$companyid = $company->add($CompanyData);
    		}
    		
    		//对职位进行处理
    		$PostData =  $this->_pg("post");
    		
    		if( empty($PostData['PostName']) || empty($PostData['PostType']) )
    		{
    			$msg = "职位名称或者职位分类不能为空";
    		}
    		else
    		{
    			$Post = M("Post");
    			unset($map);
    			$map['PostName'] = array('eq',$CompanyData['PostName']);
    			$PostID = $Post->where($map)->getField("PostID");
    			if(empty($PostID))
    			{
    				$Post->create();
    				$PostID = $Post->add($PostData,array(),true);
    			}
    			
    			$userinfo =  $this->_pg("userinfo");
    			
    			//数据都完整了，更新最新的数据
    			$userinfo['LastCompany'] = $companyid;
    			$userinfo['Post'] = $PostID;
    			$userinfo['UpdateTime'] = time();
    			$userinfo['Studentid'] = $userid;
    			
    			$graduates = M("graduates");
    			$graduates->create();
    			if($graduates->add($userinfo,array(),true))
    			{
    				$msg = array("信息录取成功",__APP__."/index.php/User/Graduates/Studentid/".$userid);
    			}
    			else
    			{
    				$msg = "数据录取失败";
    			}
     		}
    		
    		
    	}
    	
    	$this->showmsg($msg);
   
    }
    
    //删除数据
    public function del()
    {
    	$studentid = $this->_pg("studentid");
    	$student = M("student");
    
    	$map['studentid'] = array('eq',$studentid);
    	
    	$map['mark'] = array('neq',null);
    
    	$result = $student->where($map)->delete();
    
    	if($result)
    	{
    		$this->success("数据删除成功");
    	}
    }
    
    //获取姓名
    public function getTruenameByUserid($userid)
    {
   	
    	$student = M("student");
    
    	$map['studentid'] = array('eq',$userid);
    
    	$truename= $student->where($map)->getfield("truename");
    		
    	$truename = A('Summer')->aes_decode($truename,$userid);

    	return $truename;
    		
    }
    
    //获取所有存有密码的学生
    public function getAllStudent()
    {
    	$Model = M();
    	
    	$UserData = $Model->query("select studentid,wjcpassword,truename from student where wjcpassword <> '' limit 0,10");
    	
    	foreach($UserData as $key=>&$value)
    	{
    		$value['wjcpassword'] = A('Summer')->aes_decode($value['wjcpassword'],$value['studentid']);
    		$value['truename'] = A('Summer')->aes_decode($value['truename'],$value['studentid']);
    	}
    	
    	unset($value);
   
    	return $UserData;
    }
}