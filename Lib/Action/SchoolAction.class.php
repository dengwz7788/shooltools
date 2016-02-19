<?php
class SchoolAction extends CommonAction{
	
    public function index(){
  
    	 $student = M("student");
    	 
    	 $data = $student->select();
    	 
    	 $ShoolSum = $errerSum = $daysum = 0;
    	 
    	 $daytime = time();
    	 
    	 $time24hour = $daytime - 24*60*60;
    	
    	 
    	 foreach($data as $key=>$value)
    	 {
    	 	 if(empty($value['truename']))
    	 	 {
    	 	 	 $errer[$value['studentid']]['addtime'] = $value['addtime'];
    	 	 	 $errer[$value['studentid']]['mark'] 	= $value['mark'];
    	 	 	 $errerSum += 1;
    	 	 }
    	 	 else
    	 	 {
    	 	 	$stulist[$value['schoolname']]['num'] += 1;
    	 	 	//$stulist[$value['schoolname']]['classname'][$value['classname']] = 1;
    	 	 	$ShoolSum += 1;
    	 	 	

    	 	 	if($value['addtime']>=$time24hour && $value['addtime']<=$daytime)
    	 	 	{
    	 	 		$daysum +=1; 
    	 	 	}
    	 	 }
    	 	 
    	 }
    	 arsort($stulist);
    	 $this->ShoolSum = $ShoolSum; //成功的数据
    	 $this->errerSum = $errerSum; //注册失败
    	 $this->daysum = $daysum;  //24小时内注册
    	 $this->Sum = $ShoolSum+$errerSum;
    	 $this->studentlist = $stulist;
    	 $this->errer = $errer;

    	 $this->display();
	}
	
	
	//获取班级信息
	public function ClassArr()
	{
		$student = M("student");
		
		$data = $student->select();
		
	    foreach($data as $key=>$value)
    	 {
    	 	 if(!empty($value['truename']))
    	 	 {
    	 	 	$stulist[$value['schoolname']][$value['classname']]['num'] += 1;
    	 	 }
    	 	 
    	 }
    	 
    	 foreach($stulist as $k=>$v)
    	 {
    	 	 $school[$k]["num"] = count($v)+1;
    	 	 $school[$k]["children"] = $v;
    	 }
 
    	 $this->school = $school;
    	 $this->display();
	}
	
	//获取班级本学期平均分
	public function getAvgGardeByClass()
	{
		$student = M("student");
		$map['studentid'] = array('eq',$_SESSION['userid']);
		
		$classname = $student->where($map)->getField("classname");
		unset($map);
		//
		$map['classname'] = array('eq',$classname);
	    $studentArr = $student->where($map)->field("studentid")->select();
	    
	    $count = count($studentArr);
	    $this->result = 1; //数据展示
	    
	    $this->hit ="{$classname}班，现在已经有<font color=red>{$count}</font>人参与,请让班级更多的人参进来，<button class='btn btn-success' id='copy-button' href='".C('SERVER_URL')."' />点击复制连接</button>";
	   
	    if($count>=15)
	    {
		    foreach($studentArr as $key=>$value)
		    {
		    	 $studentID[] = $value['studentid'];
		    }
		    
		    $Avggrade = M('avggrade');
		    $maps = $this->getCurrentTerm();
		    $maps['studentid'] = array('in',$studentID);
		    
		    $gradeArr = $Avggrade->where($maps)->order('AvgGrade desc')->select();
		    $rand = array(); //排名数组
		    foreach($gradeArr as $k=>$v)
		    {
		    	
		    	if($v['studentid'] == $_SESSION['userid'])
		    	{
		    		$pos = $k+1;
		    	}
		    	
 	
		    	if($v['AvgGrade']>=90)
		    	{
		    		 $rand['90分数段'] +=1;
		    	}
		    	elseif($v['AvgGrade']>=80 && $v['AvgGrade']<90)
		    	{
		    		$rand['80分数段'] +=1;
		    	}
		    	elseif($v['AvgGrade']>=70 && $v['AvgGrade']<80)
		    	{
		    		$rand['70分数段'] +=1;
		    	}
		    	elseif($v['AvgGrade']>=60 && $v['AvgGrade']<70)
		    	{
		    		$rand['60分数段'] +=1;
		    	}
		    	else
		    	{
		    		$rand['60分以下'] +=1;
		    	}
		    }
		   $this->title = $maps['schoolYear']['1']."学年,第".$maps['semester']['1']."学期 {$classname}平均分分布图";
		   $this->rand = $rand;
		   
		   $this->selfPos = round(($pos/$count)*100,1); //我的排名
		   
	    }
	    else
	    {
	    	$this->result = 0; //数据展示
	    }
	   $this->display();
	}
	
}