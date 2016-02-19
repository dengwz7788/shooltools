<?php
/*
 * 批量去获取成绩
 * */
class BatchAction extends Action {
    public function index(){

       //获取所有存密码的情况
       $UserData = A('User')->getAllStudent();
       
       //先登录
       $url = C('School_url')."default2.aspx";
       
       $log = M('acquirelog');
       
       $log->create();
       
       foreach($UserData as $key=>$value)
       {
          	 $post = A("jwc")->GetPost($value['studentid'],$value['wjcpassword']);
          	 
          	 $data['studentid'] = $value['studentid'];
     	
          	 $result =  A("jwc")->curl($url, $url, $post, "");
          	 
          	 $data['addtime'] =  time();
          	 
          	 $data['status'] = false;

          	 if(strlen($result) > 6223)
          	 {
          	 	 //表示登录成功
          	 	 $fileDir = A('jwc')->_getPersonResult($data['studentid'],$value['truename']);
          	 	 
          	 	 if(A('jwc')->filesize($fileDir)) {
          	 	      //对采集的数据进行分析
          	 	      A("Analysis")->_saveGradeList($data['studentid']);
          	 	      $data['result'] =  "数据分析成功入库";
          	 	      $data['status'] = true;
          	 	      
          	 	 }else {
          	 	 	$data['result'] =  "成绩采集失败";
          	 	 }
          	 }
          	 else
          	 {
          	 	 //表示登录失败	
          	 	$data['result'] = "教务系统密码错误";
          	 }
          	 
          	 $log->add($data,array(),true);
       }
        
      
	}
	
	
}


