<?php
// 本类由系统自动生成，仅供测试用途
class CommonAction extends Action {

    public function post($param)
    {
    	$param = trim($_POST[$param]);
    	if(is_numeric($param))
    	{
    		$data = array('data'=>$param,'status'=>true);
    	}
    	else
    	{
    		$data = array('data'=>"学号是数字",'status'=>false);
    	}
    	
    	return $data;
    }
    
    /*
     * 生成返回Json
    * $status 表示状态 false|true
    * $url 返回POST再一次跳转的连接
    * $data 发送的post数据包
    */
    
    public function backpost($status,$msg,$url=null,$data=array(),$redirect=null)
    {
    	$datas = array("back"=>array("url"=>$url,"data"=>$data,"msg"=>$msg,"redirect"=>$redirect),"status"=>$status);
    	header('Content-Type:application/json; charset=utf-8');
    	echo json_encode($datas);
    	exit;
    }
    
   public function _pg($name)
   {
   	   $pg = isset($_POST[$name])?$_POST[$name]:$_GET[$name];
   	   
   	   if(strlen($pg) == 0)
		{
			return null;
		}
   	   return trim($pg);
   	   
   }
   
   public function showmsg($msg)
   {
   	  
   	   if(is_array($msg))
   	   {
   	     	header("location:".$msg['1']);
   	   }
   	   else
   	   {
   	   	 header("Content-type:text/html;charset=utf-8");
   	   	 echo $msg;
   	   	 exit;
   	   }
   	   
   }
   
   //获取本学期
   public function getCurrentTerm()
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
    
}