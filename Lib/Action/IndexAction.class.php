<?php
//
class IndexAction extends Action {
    public function index(){

    	 $this->display();
	}
	
	//获取cpu的空闲百分比
	private function get_cpufree(){
		$cmd =  "top -n 1 -b -d 0.1 | grep 'Cpu'";//调用top命令和grep命令
		$lastline = exec($cmd,$output);
	 
		preg_match('/(\S+)%id/',$lastline, $matches);//正则表达式获取cpu空闲百分比
		$cpufree = $matches[1];
		return $cpufree;
	}
	//获取内存空闲百分比
	private function get_memfree(){
		$cmd =  'free -m';//调用free命令
		$lastline = exec($cmd,$output);
	 
		preg_match('/Mem:\s+(\d+)/',$output[1], $matches);
		$memtotal = $matches[1];
		preg_match('/(\d+)$/',$output[2], $matches);
	    $memfree = round($matches[1]*100.0/$memtotal,2);
	 
		return $memfree;
	}
	 
	public function mysqlStatus()
	{
		 //页面请求开始时间戳
		 $StartTime = microtime(TRUE); 
		 
		 //CUP
		 $this->Cpu = $this->get_cpufree();
		 
		 //内存
		 $this->memfree = $this->get_memfree();
		
		 $M = M();
		 
		 $data = $M->query("show status");

		 foreach($data as $key=>$value)
		 {
		 	$statueData[$value['Variable_name']] = $value['Value'];  
		 }
		 
		
		 
		 //请求的索引的
		 $InfoData['KeyRead']['Name'] = "搜索请求";
		 
		 $KeyRead['Key_read_requests'] 	 = $statueData['Key_read_requests'];
		 $KeyRead['Key_reads']		   	 = $statueData['Key_reads'];
		 $KeyRead['索引内存命中率'] 		 = round($KeyRead['Key_reads']/$KeyRead['Key_read_requests'],2);
		 $KeyRead['参考意见'] = "索引内存命中率在0.1以下都很好（每1000个请求有一个直接读硬盘），如果key_cache_miss_rate在 0.01以下的话，key_buffer_size分配的过多，可以适当减少。";
		 
		 $InfoData['KeyRead']['Data'] = $KeyRead;
		 
		 
		 //临时表
		 $InfoData['Tmp']['Name'] = "临时表[仅对Myisam引擎有用]";
		 
		 $tmp['Created_tmp_disk_tables'] 	= $statueData['Created_tmp_disk_tables'];
		 $tmp['Created_tmp_files'] 			= $statueData['Created_tmp_files'];
		 $tmp['Created_tmp_tables'] 		= $statueData['Created_tmp_tables'];
		 $tmp['临时表内存和硬盘的比例'] 		= round($tmp['Created_tmp_disk_tables']/$tmp['Created_tmp_tables'],2);
		 
		 $tmp['参考意见'] = "临时表内存和硬盘的比例理想值小于0.25";
		 
		 $InfoData['Tmp']['Data'] = $tmp;
		 
		 
		 //进程使用情况
		 $InfoData['Threads']['Name'] = "进程使用情况";
		 
		 $Threads['Threads_cached'] 	= $statueData['Threads_cached'];
		 $Threads['Threads_connected'] 	= $statueData['Threads_connected'];
		 $Threads['Threads_created'] 	= $statueData['Threads_created'];
		 $Threads['Threads_running '] 	= $statueData['Threads_running '];
		 
		 $Threads['参考意见'] = "如果我们在MySQL服务器配置文件中设置了thread_cache_size，当客户端断开之后，服务器处理此客户的线程将会缓存起来以响应下一个客户而不是销毁（前提是缓存数未达上限）。Threads_created表示创建过的线程数，如果发现Threads_created值过大的话，表明 MySQL服务器一直在创建线程，这也是比较耗资源，可以适当增加配置文件中thread_cache_size值，查询服务器";
		 	
		 $InfoData['Threads']['Data'] = $Threads;
		 	 
		 //查询缓存
		 $InfoData['Qcache']['Name'] = "查询缓存";
		 
		 $Qcache['Qcache_free_blocks'] 		= $statueData['Qcache_free_blocks']."";
		 $Qcache['Qcache_free_memory'] 		= $statueData['Qcache_free_memory'];
		 $Qcache['Qcache_queries_in_cache'] = $statueData['Qcache_queries_in_cache'];
		 $Qcache['Qcache_hits'] 			= $statueData['Qcache_hits'];
		 $Qcache['Qcache_inserts'] 			= $statueData['Qcache_inserts'];
		 $Qcache['Qcache_lowmem_prunes'] 	= $statueData['Qcache_lowmem_prunes'];
		 $Qcache['Qcache_not_cached'] 		= $statueData['Qcache_not_cached']."";
		 $Qcache['Qcache_total_blocks'] 	= $statueData['Qcache_total_blocks'];
		 $Qcache['查询缓存碎片率'] 			= $statueData['Qcache_free_blocks']/$statueData['Qcache_total_blocks']; 
		 $Qcache['查询缓存利用率'] 			= ($statueData['query_cache_size'] - $statueData['Qcache_free_memory'])/$statueData['query_cache_size'];
		 $Qcache['查询缓存命中率'] 			= ($statueData['Qcache_hits'] - $statueData['Qcache_inserts'])/$statueData['Qcache_hits'];
		 
		 $Qcache['参考意见'] = "Qcache_free_blocks:数目大说明可能有碎片<br/>Qcache_not_cached:不适合进行缓存的查询的数量<br/>Qcache_lowmem_prunes:缓存出现内存不足并且必须要进行清理以便为更多查询提供空间的次数。这个数字最好长时间来看如果这个数字在不断增长，就表示可能碎片非常严重，或者内存很少。(上面的 free_blocks和free_memory可以告诉您属于哪种情况)";
		 
		 $InfoData['Qcache']['Data'] = $Qcache;
		 
		 
		 //表扫描情况
		 $InfoData['Handler']['Name'] = "表扫描情况";
		 
		 $Handler['Handler_read_first'] 	= $statueData['Handler_read_first'];
		 $Handler['Handler_read_key'] 		= $statueData['Handler_read_key'];
		 $Handler['Handler_read_next'] 		= $statueData['Handler_read_next'];
		 $Handler['Handler_read_prev'] 		= $statueData['Handler_read_prev'];
		 $Handler['Handler_read_rnd'] 		= $statueData['Handler_read_rnd'];
		 $Handler['Handler_read_rnd_next'] 	= $statueData['Handler_read_rnd_next'];
		 $Handler['Com_select'] 			= $statueData['Com_select'];
		 $Handler['表扫描率'] 				= $statueData['Handler_read_rnd_next']/$statueData['Com_select'];
		 
		 $Handler['参考意见'] = "如果表扫描率超过4000，说明进行了太多表扫描，很有可能索引没有建好，增加read_buffer_size值会有一些好处，但最好不要超过8MB。";
		 
		 $InfoData['Handler']['Data'] = $Handler;
		 
		 
		 //表锁情况
		 $InfoData['Lock']['Name'] = "表锁情况";
		 $Lock['Table_locks_immediate'] = $statueData['Table_locks_immediate'];
		 $Lock['Table_locks_waited'] = $statueData['Table_locks_waited'];
		 $Lock['锁等待率'] = round($Lock['Table_locks_immediate']/$Lock['Table_locks_waited'],2);
		 $Lock['参考意见'] = "如果锁等待率 > 5000，最好采用InnoDB引擎，因为InnoDB是行锁而MyISAM是表锁，对于高并发写入的应用InnoDB效果会好些.";

		 $InfoData['Lock']['Data'] = $Lock;
		 
		 
		 //打开表的情况
		 $InfoData['OpenTable']['Name'] = "打开数据表的情况";
		 $OpenTable['Open_tables'] 		= $statueData['Open_tables'];
		 $OpenTable['Opened_tables'] 	= $statueData['Opened_tables'];
		 $OpenTable['打开表的频率'] 		= round($OpenTable['Open_tables']/$OpenTable['Opened_tables'],2);


		 $InfoData['OpenTable']['Data'] = $OpenTable;
		 
		 $this->InfoData = $InfoData;
		 
		 //获取时间
		 $this->Uptime = $statueData['Uptime'];
		 
		 $endTime  = microtime(TRUE);
		 
		 $this->diff = $endTime-$StartTime;
		 
		 $this->display();
		 
	}
	
// 	//判断加密数据库里面的数据
// 	public function Batch()
// 	{
// 		$Student = M("student");
		
// 		$Data = $Student->field("wjcpassword,cardno,telephone,truename,studentid")->select();
		
// 		foreach($Data as $key=>$value)
// 		{
// 		  if(!empty($value['truename']))
// 		  {
// 		  	 foreach($value as $k=>$v)
// 		  	 {
// 		  	 	if($k !== "studentid")
// 		  	 	{
// 		  	 		$tmp[$k] =  A("Summer")->aes_encode($v,$value['studentid']);
// 		  	 	}
// 		  	 }
// 		  	 $Datas[$value['studentid']] = $tmp;
// 		  }
// 		}
		
// 		//将数据存入数据库
// 		foreach($Datas AS $K=>$V)
// 		{
// 			$map['studentid'] = array('eq',$K);
// 			$Student->where($map)->save($V);
// 		}
// 	}

// 	private function scannerdir()
// 	{
// 		$dir = SITE_PATH."/TMP/HTML/";
		
// 		if(is_dir($dir))
// 		{
// 			$handle = opendir($dir);
// 			while (false !== ($entry = readdir($handle))) {
// 				if(is_numeric($entry))
// 				{
// 					$studentdir[] = $entry;
// 				}
// 			}
// 		}
// 		return $studentdir;
// 	}
	
// 	public function saveInformation($student,$Key)
// 	{
// 		$dir = SITE_PATH."/TMP/HTML/";

// 		foreach($student as $value)
// 		{
// 			$dirs =  $dir.$value."/Results/Results_all.html";
			
// 			if($this->filesize($dirs))
// 			{
// 				$html = file_get_html($dirs);
// 				$e = $html->find('table#tbXsxx');
// 				$i=0;
// 				foreach($e as $k=>$v)
// 				{
// 					  $tmp = preg_split("/\s+/i", $v->children(1)->plaintext);
					 
// 					 //处理数据
// 					 foreach($tmp as $vv)
// 					 {
// 					 	if(!empty($vv))
// 					 	{
// 					 		$tmps = preg_split("/:|：/i", $vv);
// 					 		$tmpd[$Key[$i]] = $tmps['1'];
// 					 		$i++;
// 					 	}
					 
// 					 }
					 
					 
// 					 $tmp = preg_split("/\s+/i", $v->children(2)->plaintext);
					 
// 					 foreach($tmp as $vv)
// 					 {
// 					 	if(!empty($vv))
// 					 	{
// 					 		$tmps = preg_split("/:|：/i", $vv);
// 					 		$tmpd[$Key[$i]] = $tmps['1'];
// 					 		$i++;
// 					 	}
// 					 }
	

// 					 //把个人信息塞数据库
// 					 $student = M('student');
// 					 $student->create();
// 					 $student->add($tmpd,array(),true);

// 					 //再把成绩塞进数据库
// 					 A('Analysis')->saveGradeList($value);
// 				}
				
		
// 			}
	
// 		}

// 	}
	
// 	public function go()
// 	{
// 		ini_set('memory_limit', '1024M');
// 		set_time_limit(0);
// 		$now = memory_get_usage();
// 		$allStudent = $this->scannerdir();
// 		import("@.ORG.SimpleHtmlDom");       //导入数据抓取类
// 		$Key = array("studentid","truename","schoolname","depname","classname");
// 		$count = count($allStudent);
// 		for($i=0;$i<$count;$i=$i+100)
// 		{
// 			$student = array_slice($allStudent, $i,100);
// 			$this->saveInformation($student,$Key);
// 		}

// 		$end = memory_get_usage();
// 		echo ($end-$now)/1024;
// 	}
	
	
// 	//判断文件大学
// 	private function filesize($filedir)
// 	{
// 		$filesize=filesize($filedir);
// 		if($filesize<2024){    //小于5K
// 			return false;
// 		}else{         //大于5K
// 			return true;
// 		}
		
		
// 	}
	
	function vdump()
	{
		dump($_SERVER);
	}
	
}


