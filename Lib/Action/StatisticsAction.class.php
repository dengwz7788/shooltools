<?php
/*
 * 统计类
 * */
class StatisticsAction extends Action {
    public function AddUserLine(){
        
    	if(isset($_GET['Year']) && isset($_GET['Month']))
    	{
    		$Date = $_GET['Year']."-".$_GET['Month'];
    	}
    	else
    	{
    		$Date = date('Y-m');
    	}
  	
    	$Model = M();
    	
    	$StartTime = strtotime($Date);
    	
    	$EndTime = strtotime($Date." +1 month");
    	
    	$where = "addtime between {$StartTime} and {$EndTime} and";
    	
  
    	$UserData = $Model->query("select addtime,source from student where {$where} wjcpassword <> ''");

    	$Arr = array();
    
    	foreach($UserData as $key=>$value)
    	{
    		$Date = date("m-d",$value['addtime']);
    		
    		$Arr[$Date] ++;
    	}
    	
    	ksort($Arr);
    	
    	$DateArr = array();
    	$DataArr = array();
    	
    	foreach($Arr as $k=>$v)
    	{
    		$DateArr[] = $k;
    		$DataArr[] = $v;
    	}
    	
    	$this->DateStr = implode("','", $DateArr);
    	$this->DataStr = implode(",", $DataArr);
    	
    	
    	//获取月份
    	$EndMonth =  date('m',time());
    	$this->MonthArr = range("01", $EndMonth);

    	
    	$this->display();
    
    }
    
    
    //获取当天一天的24小时注册量
    public  function dayNum()
    {
    	
    	if(isset($_GET['Day']))
    	{
    		$Date = $_GET['Day'];
    	}
    	else
    	{
    		$Date = date('Y-m-d');
    	}
    	
    	

    	$Model = M();
    	 
    	$StartTime = strtotime($Date);
    	 
    	$EndTime = strtotime($Date." +1 day");
    	 
    	$where = "addtime between {$StartTime} and {$EndTime} and";
	
    	$UserData = $Model->query("select addtime,source from student where {$where} wjcpassword <> ''");
    	
    	$Arr = array();
    
    	for($i=0;$i<24;$i++)
    	{
    	    if($i<=9)
    	    {
    	    	$i = "0".$i;
    	    } 
    		$Arr[$i] = 0;
    		$HourArr[] = $i;
    	}
    
    	$this->HourStr = implode("','", $HourArr);

    	foreach($UserData as $key=>$value)
    	{
    		$Hour = date("H",$value['addtime']);
    	
    		$Arr[$Hour] ++;
    	}
    	 
    	ksort($Arr);

    	$this->HourDataStr = implode(",", $Arr);
    	
    	$this->Day = range(8, 31);
    	
    	$this->display();
    	
    }
	
}


