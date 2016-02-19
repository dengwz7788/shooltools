<?php
// 本类由系统自动生成，仅供测试用途
class GradeAction extends CommonAction {
    public function index(){

    	$data = array();
    	if(isset($_SESSION['userid'])){
		    $map = $this->getCurrentTerm(); //判断今年年的学年
		    $map['studentid'] = array("eq",$_SESSION['userid']);
		    $this->data = $this->_todoGrade($map);    
		    $this->UserData = $this->getAllGrade();
		    $this->display();
       }
      
    }
    
    //获取总学期绩点和平均分
    public function getAllGrade()
    {

    	$map['studentid'] = array("eq",$_SESSION['userid']);
    	
    	//获取真实姓名
    	$truename = A('User')->getTruenameByUserid($_SESSION['userid']);
 
    	$data = A('Grade')->_todoGrade($map);
    	
    	$nopassNum = 0;

    	foreach($data as $key=>$value)
    	{
    		if(($value['grade'] < 60 && $value['makeup'] < 60) && $value['type'] != "校公选")
    		{
    			$nopass[] = $value;
    			$nopassNum ++;
    		}
    	}
    	 
    	$UserData = A('Grade')->SummaryGrade($data);
    	 
    
    	return  array('GradePoint'=>$UserData['0'],'AvgGrade'=>$UserData['1'],'NopassNum'=>$nopassNum,'Truename'=>$truename,'Nopass'=>$nopass);

    }
    
    //获取暂时还没有通过的课程
    public function nopass()
    {
    
    	$map['studentid'] = array('eq',$_SESSION['userid']);
    
    	$data = $this->_todoGrade($map);

    	$nopass = array();
    
    	foreach($data as $key=>$value)
    	{
    		if(($value['grade'] < 60 && $value['makeup'] < 60) && $value['type'] != "校公选")
    		{  
    		    	$nopass[] = $value;	
    		
    		}
    	}
    	
    	$map['b.type'] = array('eq',"校公选");
    	
    	$this->SchoolPublic = $this->_todoGrade($map);
    	
    	$this->nopass = $nopass;
    	
    	$this->display();
    }
    
    
    //处理成绩
    public function _todoGrade($map)
    {
    	$Grade = M("Grade");
    	
    	$data = $Grade->alias('a')->join('course as b on b.id=a.courseid')->where($map)->order('grade desc')->select();
    	
    	if(!empty($data))
    	{
    		$this->changeStringtoNum($data);
    	
    		foreach($data as $key=>&$value)
    		{
    			if($value['grade'] >=60 )
    			{
    				$value['gradeText'] = "通过";
    			}
    			else
    			{
    				$value['gradeText'] = "挂科";
    			}
    	
    			if($value['isAgain'] == 1)
    			{
    				$value['isAgainText'] = "重修";
    			}
    			else
    			{
    				$value['isAgainText'] = "期末考";
    			}
    	
    			if($value['makeup']>0)
    			{
    				$value['isAgainText'] = "补考";
    				$value['grade'] .= "(补考:".$value['makeup'].")";
    				
    				if($value['makeup'] >= 60)
    				{
    					$value['mark'] = "补考通过";
    				}
    			}
    	
    		}
    	
    	}
    	
    	return $data;
    }
    	
	
	//获取个人成绩分析
	public function GardeList()
	{
		$map['studentid'] = array("eq",$_SESSION['userid']);
		
		$avggrade = M("avggrade");
		
		$Data = $avggrade->where($map)->order("schoolYear desc,semester desc")->select();
		
		$Grade = M("Grade");
		$Max = 0 ;
		$Mix = 100;
		foreach($Data as $key=>&$value)
		{
		   if($value['AvgGrade']>=$Max)
		   {
		   	   $Max = $value['AvgGrade'];
		   }

		   if($value['AvgGrade']<=$Mix)
		   {
		   	 $Mix = $value['AvgGrade'];
		   }
		
		   $map['schoolYear']  =  $value['schoolYear'];
		   $map['semester'] 	= $value['semester'];
		   
		   $data = $Grade->alias('a')->join('course as b on b.id=a.courseid')->where($map)->order('grade desc')->select();
		   
		   $this->changeStringtoNum($data);
		   
		   foreach($data as $key=>&$v)
		   {
		   	if($v['grade'] >=60 )
		   	{
		   		$v['gradeText'] = "通过";
		   	}
		   	else
		   	{
		   		$v['gradeText'] = "挂科";
		   	}
		   
		   	if($v['isAgain'] == 1)
		   	{
		   		$v['isAgainText'] = "重修";
		   	}
		   	else
		   	{
		   		$v['isAgainText'] = "期末考";
		   	}
		   
		   	if($v['makeup']>0)
		   	{
		   		$v['isAgainText'] = "补考";
		   		$v['grade'] .= "(补考:".$v['makeup'].")";
		   		if($v['makeup'] >= 60)
		   		{
		   			$v['mark'] = "补考通过";
		   		}
		   	 }
		   }
		   unset($v);

		   $value['GradeList'] = $data;
		   
		}
		unset($value);
	
		$this->Data = $Data;
		$this->Max = $Max;
		$this->Mix = $Mix;
		
		$this->display();
	}
	
	//数据转行
	public function changeStringtoNum(&$data)
	{
		foreach($data as $key=>&$value)
		{
			if(!is_numeric( $value['grade'] ) )
			{
				switch( $value['grade'] )
				{
					case '优秀':
						$grade = 95;
						break;
					case '良好':
						$grade = 85;
						break;
					case '中等':
						$grade = 75;
						break;
					case '及格':
						$grade = 65;
						break;
					case '不及格':
						$grade = 65;
						break;
				}
				$value['grade'] = $grade;
			}
		}
		unset($value);
	}
	
	//计算平均分
	public function SummaryGrade($GradeList)
	{
	
		$TotalCredit = 0;
		$GradePoint = 0;
		$num = 0;
		foreach($GradeList as $key=>$value)
		{
	
			if(is_numeric($value['grade']))
			{
				if($value['grade'] < 60)
				{
					//表示没有及格,判断是否已经补考
					if($value['makeup'] != '0')
					{
						$grade = $value['makeup'];
						$credit = $value['credit'];
					}
				}
				else
				{
					$grade = $value['grade'];
					$credit = $value['credit'];
				}
			}
			else
			{
				switch( $value['grade'] )
				{
					case '优秀':
						$grade = 95;
						$credit = $value['credit'];
						break;
					case '良好':
						$grade = 85;
						$credit = $value['credit'];
						break;
					case '中等':
						$grade = 75;
						$credit = $value['credit'];
						break;
					case '及格':
						$grade = 65;
						$credit = $value['credit'];
						break;
					case '不及格':
						if($value['makeup'] != '0')
						{
							$grade = $value['makeup'];
						}
						else
						{
							$grade = 0;
						}
						$credit = $value['credit'];
						break;
				}
			}
			$GradePoint += (($grade-50)/10)*$credit;
			$totalCredit += $credit;
				
			$sum += $grade;
			$num ++;
		}
	
		return array(round($GradePoint/$totalCredit,2),round($sum/$num,2));
	}
	
	
	//根据学生成绩和学号，计算他大学每一个学期的平均成绩和绩点
	public function GetAvgAndPointByStudentid($studentid)
	{
		$student = M("student");
		$map['studentid'] = array('eq',$studentid);
	
		$Grade = M("Grade");
		$GradeList = $Grade->alias('a')->join('course as b on b.id=a.courseid')->where($map)->select();

		foreach ($GradeList as $key=>$value)
		{
			$GradeLists[$value['schoolYear']."|".$value['semester']][] = $value;
		}

		$avggrade = M('avggrade');
		//获取平均分和绩点
		foreach($GradeLists as $key=>$value)
		{
			$expArr = explode("|", $key);
			$Arr = $this->SummaryGrade($value);
			$data['studentid'] = $studentid;
			$data['semester'] = $expArr['1'];
			$data['schoolYear'] = $expArr['0'];
			$data['AvgGrade'] = $Arr['1'];
			$data['GradePoint'] = $Arr['0'];
			$data['UpdateDate'] = time();

			$avggrade->create();
			$avggrade->add($data,array(),true);
		}	
	}
	

}