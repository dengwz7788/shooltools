<?php
// 本类由系统自动生成，仅供测试用途
class AnalysisAction extends CommonAction {
	
    public function savePersonInfomation(){

    	$userid = $_POST['userid'];
    	
    	$filedir = SITE_PATH."/TMP/HTML/".$userid."/infomation";

    	$filename = "infomation.html";
    	
    	import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
    	
    	$html = file_get_html($filedir."/".$filename);
 
    	$e = $html->find('table.formlist tr');
    	unset($html);
    	foreach($e as $k=>$v)
    	{
    	
    		$temp = preg_split("/\s+/i", $v->children(1)->plaintext);
    		$temp2 = preg_split("/\s+/i", $v->children(3)->plaintext);
    	
    		$data[] = $temp["0"];
    		$data[] = $temp2["0"];
    	
    	}
    	unset($e);
    	//过滤获取到我想要的信息
    	$datas = $this->filterData($data);
    	
    	$student = M("student");

    	$where['studentid'] = array("eq",$userid);
    	
    	$result = $student->where($where)->save($datas);
    	
    	if($result)
    	{
    		$msg = "个人信息收集成功，马上处理您的成绩  -- 4/6";
    		$post['userid'] = $userid;
    		$post['truename'] = $datas['truename_nocode'];
    		$this->backpost(true,$msg,__APP__."/index.php/jwc/getPersonResult",$post);
    	}
    	else
    	{

    		$msg = "登录失败，密码或学号错误";
    		$this->backpost(false,$msg);
    	}
  
    }
    
    //解析教务系统信息
    public function GetTrueName()
    {
    	$userid = $this->_pg("Studentid");
    	//把数据存入数据
     	$filedir = SITE_PATH."/TMP/HTML/".$userid."/login";
	  
	  	$filename = "login.html";
    	 
    	import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
    	 
    	$html = file_get_html($filedir."/".$filename);

    	$truenameStr = $html->find('span#xhxm',0)->plaintext;

    	$truename = msubstr($truenameStr,0,-3);

    	if(isset($truename) && !empty($truename))
    	{
    		$data["userid"] = $userid;
    		$data["truename"] = $truename;
    		$msg = "登录成功  ----2/6";
    		$_SESSION["userid"] = $data['userid'];
    		
    		$this->backpost(true,$msg,__APP__."/index.php/jwc/getPersonInfomation",$data);
    	} else{
    		$msg = "账号或密码错误,请联系教务处";
    		A('User')->LoginLog($userid,$msg);
    		$this->backpost(false,$msg);
    	}
    }
      
    public  function saveGradeList()
    {
    	$userid = $_POST['userid'];
    	//后期需要分析的文件
    	
    	$this->_saveGradeList($userid);
    	
        $this->backpost(true,"成绩入库  --- 6/6");

    }
    
    public  function _saveGradeList($userid)
    {
    	$dir = SITE_PATH."/TMP/HTML/".$userid."/Results/Results_all.html";
    	 
    	import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
    	
    	$html = file_get_html($dir);
    	
    	$e = $html->find('table#DataGrid1 tr');
    	
    	unset($html);
    	foreach($e as $k=>$v)
    	{
    	
    		$year = $v->children(0)->plaintext;
    		$semester = $v->children(1)->plaintext;
    		$courseId = $v->children(2)->plaintext;
    		$courseName = $v->children(3)->plaintext;
    		$courseType = $v->children(4)->plaintext;
    		$credit = $v->children(6)->plaintext;
    		$courseGrade = $v->children(11)->plaintext;
    		$makeupGrade = $v->children(12)->plaintext;
    		$isAgain = $v->children(13)->plaintext;
    		$school = $v->children(14)->plaintext;
    		$mark = $v->children(15)->plaintext;
    	
    	
    		//数据入库,先进修课程学习
    		if(is_numeric($courseId) && $school != "&nbsp;")
    		{
    			$studeyCorse[$courseId] = array('id'=>$courseId,'name'=>$courseName,'type'=>$courseType,'credit'=>$credit,'schoolname'=>$school,'addtime'=>time());
    		}
    	
    		//分析成绩
    		if(is_numeric($courseId))
    		{
    			$studyGrade[$courseId] =  $this->studyGrade($userid, $year, $courseId, $courseGrade, $courseName, $makeupGrade, $isAgain, $semester,$mark);
    		}
    	}
    	 
    	//批量插入数据
    	$course = D("course");
    	$course->create();
    	
    	foreach($studeyCorse as $key=>$value)
    	{
    		$course->add($value,array(),true);
    	}
    	
    	$grade = D("grade");
    	$map['studentid'] = array('eq',$userid);
    	$grade->where($map)->delete(); //删除之前的数据

    	$grade->create();
    	
    	foreach($studyGrade as $key=>$value)
    	{
    		$grade->add($value,array(),true);
    	}
    	
    	A("Grade")->GetAvgAndPointByStudentid($userid);
    }
    
    private function filterData($data)
    {
    	//获取到想要的全部信息进行打包存储
    	if(empty($data))
    	{
    		return false;
    	}
    	else
    	{
    		//获取真实姓名
    		$temp["truename"] = A("Summer")->aes_encode($data[2],$data[0]);
    		$temp["truename_nocode"] = $data[2];
    		
    		//获取性别
    		if($data[6] == "男")
    		{
    			$temp["sex"] = 1;
    		}
    		else {
    			$temp["sex"] = 0;
    		}
    			
    		//获取入学时间
    		$temp["beginsyear"] = $data[7];
    		//获取生日
    		$temp["birth"] = $data[8];
    		//获取身份证号码
    		$temp["cardno"] = A("Summer")->aes_encode($data[21],$data[0]);
    		//学历层次
    		$temp["level"] = $data[23];
    		//获取学院
    		$temp["schoolname"] = $data[24];
    		//获取班级
    		$temp["classname"] = $data[32];
    		//获取班级
    		$temp["depname"] = $data[28];
    		//获取所在年级
    		$temp["garde"] = $data[40];
    			
    		//获取添加时间
    		$temp["addtime"] = time();
    			
    	}
    
    	return $temp;
    }
     
   //新增学生信息入数据库
   public function addNewStudent($userid,$password,$source="WEB")
   {
   	    $student = M("student");
   	   
   	     $map['studentid'] = array('eq',$userid);
   	   
   	   	  try {
   	   	  	
   	   	  	$student->create();
   	   	  	
   	   	  	$data['studentid'] = $userid;
   	   	  	$data['source'] = $source;
   	   	  	$data['wjcpassword'] = A('Summer')->aes_encode($password,$userid);
   	   	  	$data['addtime'] = time();
   	   	  	
   	   	  	if($student->add($data,array(),true))
   	   	  	{
   	   	  		return array('status'=>true,'msg'=>'数据已经存入进数据库');
   	   	  	}
   	   	  	
   	   	  } catch (Exception $e) {
   	   	  	
   	   	  	return array('status'=>false,'msg'=>$e->getMessage());

   	   }  	   
   }
   
   
   private function studyGrade($studentid,$year,$courseId,$grade,$courseName,$makeup,$isAgain,$semester,$mark)
   {

   	$data["courseid"] = $courseId;
   	$data["studentid"] = $studentid;
   	$data["schoolYear"] = $year;
   	$data["semester"] = $semester;
   	$data["coursename"] = $courseName;
   	$data["grade"] = $grade;

   	if($makeup == "&nbsp;")
   	{
   		$makeup = 0;
   	}
   	$data["makeup"] = $makeup;
   	
   	if($mark == "&nbsp;")
   	{
   		$mark = 0;
   	}
   	$data["mark"] = $mark;
   	
   	if($isAgain == "是")
   	{
   		//未参与重修
   		$isAgain = 1;
   	}
   	else
   	{
   		$isAgain = 0;
   	}
   	$data["isAgain"] = $isAgain;
   	$data["addtime"] = time();

   	return $data;
   }
   
   
}