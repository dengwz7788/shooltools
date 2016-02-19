<?php
//
class ScheduleAction extends CommonAction {
    public function index(){
  
    	 $this->display();
	}
	
	public function getSchedule()
	{
		$userid = "14495200433";
		$truename = "姜循";
		
		$filedir = SITE_PATH."/TMP/Cookies";
		$cookie_file =  $filedir."/cookie.txt";
		//获取个人信息
		$url = C('School_url').C('Get_schedule').$userid."&xm=".$truename."&gnmkdm=N121601";
		
		$url2 = C('School_url')."xs_main.aspx?xh=".$userid;
		 
		$infomation = A('jwc')->curl($url, $url2, "", $cookie_file);
		
		$filedir = SITE_PATH."/TMP/HTML/".$userid."/Schedule";
		
		$filename = "Schedule.html";
		
		A('jwc')->saveHtml($infomation, $filedir, $filename);
	}
	
	//解析课表
	public function jxSchedule()
	{
		$userid = "14495200433";
		
		$filedir = SITE_PATH."/TMP/HTML/".$userid."/Schedule";
		
		$filename = "Schedule.html";
		
		import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
		 
		$html = file_get_html($filedir."/".$filename);

		$htmlnode = $html->find('table#Table1 tbody tr td option[selected=selected]');

		$head = array('schoolYear','semester','grade','schoolname','depname','classname');
		
		$i=0;
		foreach($htmlnode as $k=>$v)
		{
			$table[$head[$i]] = $v->plaintext;
			$i++;
		}

		
		$e = $html->find('table.blacktab tbody tr td');
		
		
		$Sum = 0;
		
		$classNameArr = array('第1节','第2节','第3节','第4节','第5节','第6节','第7节','第8节','第9节','第10节');
	
		foreach($e as $k=>$v)
		{	 	
			$tmp = preg_split("/\s+/i", $v->plaintext);	 
			if(count($tmp) == 1){
				 $data[$Sum] = $tmp['0'];
				 
				 if(in_array($tmp['0'], $classNameArr))
				 {
				 	$pos[] = $Sum;
				 }
				 
			}else{
				 $data[$Sum] = $tmp;
				  
			}
			
			$Sum ++;
		}
		unset($e);
		
		$pos[] = count($data);
	
		$poscount = count($pos);
		
		$classnum = 1;
		
		for ($i=0;$i<$poscount-1;$i++)
		{
			$startpos = $pos[$i];
			
			$endpos = $pos[$i+1];
			
			for($j=$startpos;$j<$endpos;$j++)
			{
				if($data[$j] == "&nbsp;")
				{
					$result[$classnum][] = NULL;
				}
				else
				{
					$result[$classnum][] = $data[$j];
				}
			
			}
			
			$classnum ++;
			
		}
		
		$html->clear();
		unset($html);
		$table['data'] = json_encode($result);
		
		$table['addtime'] = time();
		
		$schedule = M('schedule');
		$schedule->create();
		$schedule->add($table,array(),true);
		
	}
	
	public function showSchedule()
	{
		$schedule = M('schedule');
		
		$map["classname"] = array('eq','冶金工程1404');
		
		$data = json_decode($schedule->where($map)->getField("data"));

		foreach($data as $key=>$classArr)
		{
			$classNum = array_shift($classArr);
			foreach($classArr as $k=>$v)
			{
				 if( $v !== "下午" && $v !== "晚上" )
				 {
				 	 if(empty($v))
				 	 {
				 	 	$datapost[$classNum][] = "&nbsp;";
				 	 }
				 	 else
				 	 {
				 	    if(is_array($v))
				 	    {
				 	    	$datapost[$classNum][] = implode("<br/>", $v);
				 	    }
				 
				 	 }
				 }

			}
			
			//小于7节课，不全
			$pos = count($datapost[$classNum]);
	
		
			if( $pos < 7)
			{
				$diff = 7 - $pos;
				$datapost[$classNum] = array_merge($datapost[$classNum], array_fill($pos,$diff,"&nbsp;"));
			}

			
		}
		
		$this->DataArr = $datapost;

		$this->display();
	}

}


