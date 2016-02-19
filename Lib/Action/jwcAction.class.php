<?php
/**
 * 教务系统对接类，主要要负责跟教务系统数据对接
 * **/
class jwcAction extends CommonAction {
  
  public function LoginFormData()
  {
  		$url = C('School_url')."default2.aspx";
	  
  		$response = $this->curl($url, $url, '','',"get");
	  	
	  	$filedir = SITE_PATH."/TMP/HTML/login";
	  	 
	  	$filename = "login_base.html";
	  	 
	  	$this->saveHtml($response, $filedir, $filename);
	  	
	  	$postdata = $this->getData($filedir."/".$filename);
	  	
	  	foreach($postdata as $key=>$value)
	  	{
	  		 $post[$key] = !empty($value['value'])?$value['value']:'';
	  	}
	  	
	  	$post['RadioButtonList1'] = iconv('utf-8', 'gb2312', '学生');
	  	$post['Button1'] = iconv('utf-8', 'gb2312', '登录');
	  	
	  	return $post;
  }
  
  
	
  public function GetPost($userid="",$password="")
  {
  	$post  = $this->LoginFormData();
  				
  	if(empty($userid) || empty($password))
  	{
  		$post['txtUserName'] = $this->_pg("userid");
  		$post['TextBox2'] = $this->_pg("password");
  	}
  	else
  	{
  		$post['txtUserName'] = $userid;
  		$post['TextBox2'] = $password;
  	}

  	return $post;
  	 
  }
/**
 * 获取用户的信息，并且保存起来
 *
 **/
  public function getUserData($userid="",$password=""){

  	$url = C('School_url')."default2.aspx";
  	
  	$post = $this->GetPost($userid="",$password="");
  	
  	//判断用户是否已经登陆
	  	if(is_numeric($post['txtUserName']))
	  	{
	  	
	  		if($this->setTime())
	  		{
	  			$this->curl_Login($url,$post);
	  		}
	  		else
	  		{
	  			//判断用户是否已经成了过
	  			$result = A('User')->JwcCheckUser($post['txtUserName'],$post['TextBox2']);
	  			
	  			if(!$result['0'])
	  			{
	  				$this->curl_Login($url,$post);
	  			}
	  			else
	  			{
	  				$msg = "数据已经存在，直接跳转到查询成功";
	  				$this->backpost(true,$msg,"",array(),__APP__."/index.php/Grade/index");
	  			}
	  		}
	  	}
	  	else
	  	{
	  		$msg = "登录失败，学号是数字";
	  		$this->backpost(false,$msg);
	  	}

	}
	
	/*
	设置查询时间,在这个时间断能会去更新数据
	 每年的5.1-7.1, 11-次年2月
	*/
	private function setTime()
	{
		$Month = date('n');
		
		if($Month >= 5 && $Month <= 7)
		{
			  return true;
		}
		else if($Month>=11 && $Month<=2)
		{
			 return true;
		}
		
		return false;
		
	}

	//登录教务系统
	public function curl_login($URL,$POST)
	{
	
	  $result =  $this->curl($URL, $URL, $POST, "");

	  $filedir = SITE_PATH."/TMP/HTML/".$POST['txtUserName']."/login";
	  
	  $filename = "login.html";
	  
	  $this->saveHtml($result, $filedir, $filename);
	
	  if($this->filesize($filedir."/".$filename))
	  {
	  	$msg = "已经去登陆了教务系统，稍等解析你的个人信息 --- 1/6";
	  	$data['Studentid'] = $POST['txtUserName'];
	  	$result = A('Analysis')->addNewStudent($POST['txtUserName'],$POST['TextBox2']);
	  	$this->backpost(true,$msg,__APP__."/index.php/Analysis/GetTrueName",$data);
	  }
	  else {
	  	$msg = "登陆失败";	
	  	
	  	$this->backpost(false,$msg);
	  }
	}
	
	//获取个人信息
	public function getPersonInfomation(){

		  $truename = $this->_pg("truename");
		  $userid 	= $this->_pg("userid");

       	  $filedir = SITE_PATH."/TMP/Cookies";
          $cookie_file =  $filedir."/cookie.txt";
    	 //获取个人信息
    	 $url = C('School_url').C('Get_info').$userid."&xm=".$truename."&gnmkdm=N121501";
    	 $url2 = C('School_url')."xs_main.aspx?xh=".$userid;
    	
    	 $infomation = $this->curl($url, $url2, "", $cookie_file);

    	 $filedir = SITE_PATH."/TMP/HTML/".$userid."/infomation";
    	 
    	 $filename = "infomation.html";
    	 
    	 $this->saveHtml($infomation, $filedir, $filename);
    	 
    	 if($this->filesize($filedir."/".$filename))
    	 {
    	 	$msg = "信息个人信息采集成功，准备写入数据库 --- 3/6";
    	 	$data['userid'] = $userid;
	
    	 	$this->backpost(true,$msg,__APP__."/index.php/Analysis/savePersonInfomation",$data);
    	 }
    	 else {
    	 	$msg = "数据采集失败,可能是教务系统密码错误或者没有参加指定的教学获取，请联系教务处";
    	 	A('User')->LoginLog($userid,$msg);
    	 	$this->backpost(false,$msg);
    	 }
    	 
   }

	//获取个人成绩
	public  function getPersonResult()
	{
		$userid = $_POST['userid'];
		$truename = $_POST['truename'];
		
		$fileDir = $this->_getPersonResult($userid, $truename);
		
		if($this->filesize($fileDir))
		{
			$msg = "个人成绩收集成功，准备写入数据库  --- 5/6";
			$data['userid'] = $userid;
			 
			$this->backpost(true,$msg,__APP__."/index.php/Analysis/saveGradeList",$data);
		}
		else {
			$msg = "数据采集失败";
			$this->backpost(false,$msg);
		}

	}
	
    public function _getPersonResult($userid,$truename)
    {
    	$filedir = SITE_PATH."/TMP/Cookies";
    		
    	$cookie_file =  $filedir."/cookie.txt";
    	
    	$ch2 = curl_init();
    	//获取个人信息
    	$url2 = C('School_url').C('Get_garde').$userid."&xm=".urlencode($truename)."&gnmkdm=N121605";
    	
    	curl_setopt($ch2, CURLOPT_URL, $url2);
    	curl_setopt($ch2, CURLOPT_HEADER, 0); //不返回header部分
    	curl_setopt($ch2, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
    	curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch2, CURLOPT_FOLLOWLOCATION,1);
    	curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookie_file);
    	curl_setopt($ch2, CURLOPT_REFERER, C('School_url')."xs_main.aspx?xh=".$userid);
    	$infomation = curl_exec($ch2);
    	$filedir = SITE_PATH."/TMP/HTML/".$userid."/Results";
    		
    	$filename = "Results.html";
    		
    	$this->saveHtml($infomation, $filedir, $filename);
    		
    	$sf = $filedir."/".$filename;
    		
    	$data = $this->getData($sf);
    		
    	$allowArray = array("__EVENTARGUMENT","__EVENTTARGET","__VIEWSTATEGENERATOR","__VIEWSTATE");
    		
    	foreach($data as $key=>$value)
    	{
    		if(in_array($key, $allowArray))
    		{
    			$arr2[] = $key."=".urlencode($value['value']);
    		}
    	}
    		
    	$arr2[] = "ddlxn=%C8%AB%B2%BF";
    	$arr2[] = "ddlxq=%C8%AB%B2%BF";
    	$arr2[] = "btnCx=+%B2%E9++%D1%AF+";
    		
    	$fields =  implode("&", $arr2);
  	
    	$url = C('School_url').C('Get_garde').$userid."&xm=".urlencode($truename)."&gnmkdm=N121605";
    		
    	$infomation = $this->curl($url, $url, $fields, $cookie_file);
    		
    	$filedir = SITE_PATH."/TMP/HTML/".$userid."/Results";
    		
    	$filename = "Results_all.html";
    		
    	$this->saveHtml($infomation, $filedir, $filename);
    	
    	return $filedir."/".$filename;
    }
	
	private function getData($htmnldom)
	{
	
		import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
	
		$html = file_get_html($htmnldom);
	
		$e = $html->find("input");
	
		foreach($e as $a)
		{
			$data[$a->name]['value']  = $a->value;
			$data[$a->name]['type']  = $a->type;
	
		}
	
		$data["RadioButtonList1"]['value'] = '学生';
		$data["RadioButtonList1"]['type'] = "hidden";
	
		$data["lbLanguage"]['value'] = '';
		$data["lbLanguage"]['type'] = "hidden";
	
		$data["Button1"]['type'] = "hidden";
		$data["Button1"]['value'] = '登录';
		unset($data["Button2"]);
	
		$data["txtUserName"]['text']  = "用户名";
		$data["TextBox2"]['text']  = "密码";
		$data["txtSecretCode"]['text']  = "验证码";
	
		return $data;
	}
 /**
  * curl
  * **/
  public function curl($url,$url2,$fields,$cookie_file,$method="post")
	{

		$filedir = SITE_PATH."/TMP/Cookies";
	
		if(!$this->mkdirs($filedir))
		{
			echo "目录创建失败";
			exit;
		}
	
		$cookie =  $filedir."/cookie.txt";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
		
		if(strtolower($method) == "post")
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			
			if(strlen($cookie_file)>0)
			{
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
			}
			else
			{
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
			}
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_REFERER,$url2);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
	
   public function saveHtml($infomation,$filedir,$filename)
	{
	
		if(!$this->mkdirs($filedir))
		{
			return 0;
		}
	
		$sf =  $filedir."/".$filename;
		$fp=fopen($sf,"w"); //写方式打开文件
		fwrite($fp,$infomation); //存入内容
		fclose($fp); //关闭文件
	}

	//判断文件大学
	public function filesize($filedir)
	{
		$filesize=filesize($filedir);
		if($filesize<2024){    //小于5K
			return false;
		}else{         //大于5K
			return true;
		}
		
	}
	
	//创建目录
	private function mkdirs($dir)
	{
		if(!is_dir($dir))
		{
			if(!$this->mkdirs(dirname($dir))){
				return false;
			}
			if(!mkdir($dir,0777)){
				return false;
			}
		}
		return true;
	}
	
	//数据补录
	public function Backtrack()
	{
		$studentid = $this->_pg("studentid");
		
		$student = M("student");
		
		$map['studentid'] = array('eq',$studentid);
		
		$jwcpassword = $student->where($map)->getfield("wjcpassword");
		
		$student->where($map)->delete();
		
		$this->redirect("/index.php/Index/index/password/{$jwcpassword}/studentid/{$studentid}");
	
	}
	
}