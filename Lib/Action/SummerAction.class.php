<?php
// 加密类
class SummerAction extends Action {

	public function _initialize()
	{
		$this->publicKey= <<<KEY
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDGEjyTyvBeZiBjk+li1LhBXnEI
fRw8Ur91Mcz0GNuYoXlPOw/LJnULDEb9wIngLKBgcutKQyneugsrBZ0oJHlb0n9/
YJkCvZXaELgzNulAQgTa5G6e51cK4WctbK3kaS0z37Gh7b4Gz3wQ/4xgGIjrWw3i
atDrG2uPeF9qrWJ9YQIDAQAB
-----END PUBLIC KEY-----
KEY;
	
		$this->privateKey = <<<KEY
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDGEjyTyvBeZiBjk+li1LhBXnEIfRw8Ur91Mcz0GNuYoXlPOw/L
JnULDEb9wIngLKBgcutKQyneugsrBZ0oJHlb0n9/YJkCvZXaELgzNulAQgTa5G6e
51cK4WctbK3kaS0z37Gh7b4Gz3wQ/4xgGIjrWw3iatDrG2uPeF9qrWJ9YQIDAQAB
AoGAWaZhcsd0+lUhLdEB3rDHYRb89EmkYQ1XCRVsXcNuiWv0u07RPDMA4OpCq5Hd
FfE8+cqqAqfZqKzuZx+YXVX88y3Ae0EL9pDmmtwgFg6heogx1JmgrEDd/g6nIteA
oFswmjhEqTnmEmuJNGrQO2Ao+BuockVxJl9NkW78JmiE0X0CQQDhwB7IIoEL6xik
5N2TyZM8q8FK0wFOmdeb2rlmacePN0rBHhgTAKXMlZvMCKZdJMtY57ObZLsPsc7+
K6v2N9LnAkEA4Jyj8ROmbPfwLrBn/NS0BdqmodJQuw8/vth25sdc/A8mkXJF67IY
rzCTN5mGIwVCONfUQyap6zCgq4TmlxpsdwJAU61Gu6AufSBzTguXJgR9kuuHBhkY
Tu4vQRHdztw+oBM6nkJtYf3HdwGtcf9yyuushBO+O0cnHzYlJ4lAgE9I2QJARkI5
NevDn0pfIvujS37tYPdoMC4tepXmhrgHWWD7QQ0sL0rjfDqVZhd3tWicgM/gSw+Y
IfkyJZNsbrp/trxZOQJBAMm7bE4wr/xE4hz8ehwOStR4u2Jurql9GSqvujznFhix
ETqBBKhmkS/Jfzs8HmFDO6WHFoLS9NsNSZlPBMUJROs=
-----END RSA PRIVATE KEY-----
KEY;
	
		$this->iv = "6543210987654321";
	}
	
	
	public function inputPass($pass){
		 
		return $pass;
		 
	}
	
	
	
	
	/**
	 * 公钥加密
	 *
	 * @param string 明文
	 * @return string 密文（base64编码）
	 */
	public function publickey_encode($sourcestr)
	{
	
		$pubkeyid = openssl_get_publickey($this->publicKey);
	
		if (openssl_public_encrypt($sourcestr, $crypttext, $pubkeyid))
		{
			return base64_encode("".$crypttext);
		}
		return false;
	}
	
	/**
	 * 公钥解密
	 *
	 * @param string 密文 (base64编码)
	 * @return string 明文
	 */
	public function publickey_decode($crypttext)
	{
		$pubkeyid = openssl_get_publickey($this->publicKey);
		$crypttext = base64_decode($crypttext);
		if (openssl_public_decrypt($crypttext, $sourcestr, $pubkeyid))
		{
			return "".$sourcestr;
		}
		return false;
	}
	
	/**
	 * 私钥加密
	 *
	 * @param string 明文
	 * @return string 密文 (base64编码)
	 */
	public function privatekey_encode($sourcestr)
	{
	
		$prikeyid = openssl_get_privatekey($this->privateKey);
		 
		if (openssl_private_encrypt($sourcestr, $crypttext, $prikeyid))
		{
			return base64_encode("".$crypttext);
		}
		return false;
	}
	
	/**
	 * 私钥解密
	 *
	 * @param string 密文（二进制格式且base64编码）
	 * @return string 明文
	 */
	public function privatekey_decode($crypttext)
	{
	
		$prikeyid = openssl_get_privatekey($this->privateKey);
		$crypttext = base64_decode($crypttext);
		if (openssl_private_decrypt($crypttext, $sourcestr, $prikeyid))
		{
			return "".$sourcestr;
		}
		return false;
	}
	
	public function gen_aes_key()
	{
		return substr(md5(mt_rand(0, 16)), 0, 16);
	}
	
	
	
	public function aes_encode($sourcestr, $key)
	{
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $sourcestr, MCRYPT_MODE_CBC, $this->iv));
	}
	
	public function aes_decode($crypttext, $key)
	{
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($crypttext), MCRYPT_MODE_CBC,$this->iv), "\0");
	}
	
	
	public function index()
	{
		$data = array('0'=>array('truename'=>'邓蔚之','staffid'=>'GZ1'),'1'=>array('truename'=>'邓蔚之','staffid'=>'GZ1'));
	
		$datas = array('member'=>$data);
	
		//$datas = '12345676';
	
		//$json = json_encode($datas);
	
		//dump($json);
	
		$key = '13301920416';
	
		//dump($key);
		 header('Content-type:text/html;charset=utf-8');
		//echo $a;
		$a= 'ymWeyfKooH2FCVA10xywYQ==';
		$b = $this->aes_decode($a,$key);
		echo "\n";
		// $json = json_decode($b);
		dump($b);
	}
}