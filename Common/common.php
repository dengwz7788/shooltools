<?php
// 获取客户端IP地址

function toDate($time,$format='Y-m-d H:i:s')
{
	if( empty($time)) {
		return '';
	}
    $format = str_replace('#',':',$format);
	return date($format,$time);
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length=100, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
            if ($suffix && strlen($str)>$length)
                return mb_substr($str, $start, $length, $charset);
        else
                 return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
            if ($suffix && strlen($str)>$length)
                return iconv_substr($str,$start,$length,$charset);
        else
                return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice;
    return $slice;
}

//获取置顶数据
function getOneData($value,$table,$field,$result,$extra="08")
{
   $table = M($table);
   if(empty($value))
   {
      return NULL;
   }
   $where[$field] = array("eq",$value);
   return $table->where($where)->getField($result);
}

//获取状态
function getStatus($value,$result)
{
   if(!isset($value))
   {
      return "未知";
   }
   else
   {
	if(is_string($result))
	{
	   $temp = explode(",",$result);
	   
	   //处理数组，$num的数量表示判断的数量
	   $num = count($temp);
	
	  if($num >= $value)
	  {
	   return $temp[$value];   
	  }
	  else
	  {
	    return "数据异常";
	  }
	}
}
}

//处理数据，判断数据必须为0,1,2
function checkNum($value,$max)
{
    $value = trim($value);
    if(is_numeric($value))
	{
	    if($value <= $max && $value >=0)
		{
		   return $value; 
		}
		else
		{
		   return 0;
		}
	}
	else
	{
	   return 0;
	}
}
?>