<?php
/**
 * chenzhidong
 * 2013-4-26
 */

define('_RBACCookieKey_', 'RBACUser');
define('_EXPIRETIME_', 86000);

define('_COOKIE_KEY_', 'f6j5r@ziqpei&vkjapip19lo6kl8t4');
define('_COOKIE_IV_', 'x6j5r@ziqpei&vkjapip19lo6kl8t3');

define('_RIJNDAEL_KEY_', 'u9487u4598y749824$Ry^%&I*i*Or4t4');
define('_RIJNDAEL_IV_',  'af4u7697u76978u7985677u498764551');

define('LOG_DIR', APPLICATION_PATH . '/log/');

define('APP_KEY', '');
define('APP_SECRET', '');

function pSQL($string, $htmlOK = false) {
	static $db = false;
	if (!$db)
		$db = Db::getInstance();

	return $db->escape($string, $htmlOK);
}

function bqSQL($string) {
	return str_replace('`', '\`', pSQL($string));
}

/**
 * 输出变量的内容，通常用于调试
 *
 * @package Core
 *
 * @param mixed $vars 要输出的变量
 * @param string $label
 * @param boolean $return
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}

function json($vars)
{
	header("Content-type: application/json");		
	/***加密
	if(is_array($vars['data']) && !empty($vars['data'])){
		$_cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);		
		$vars['data']	=	$_cipherTool->encrypt(json_encode($vars['data']));
	}***/
	$data = updateNull($vars);	
    echo json_encode($data);
	exit;
}

function updateNull(& $onearr){
	foreach($onearr as $k=>$v){
		if(is_array($v)){
			$onearr[$k]	=	updateNull($v);
		}else{
			if($v===NULL){
				$onearr[$k] = '';
			}
		}
	}
	return $onearr;
}

function getIp(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	   return $_SERVER['HTTP_CLIENT_IP']; 
	}elseif(!empty($_SERVER['HTTP_X_FORVARDED_FOR'])){
	   return $_SERVER['HTTP_X_FORVARDED_FOR'];
	}elseif(!empty($_SERVER['REMOTE_ADDR'])){
	   return $_SERVER['REMOTE_ADDR'];
	}else{
	   return "未知IP";
	}
}
function getHost(){
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
 && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	return $http_type.$_SERVER['HTTP_HOST'];

}

function url($controller='index', $action='index', $args=array()){
	$router = Yaf_Dispatcher::getInstance()->getRouter();
	$urls	= array(
						':c'=>$controller,
						':a'=>$action
				);

	if( !empty($args) && is_array($args) ){
		foreach($args	as $k=>$v){
			$args[$k]	=	strval($v);
		}
	}
		
	$url	= $router->getRoute($router->getCurrentRoute())->assemble($urls, $args);
	$suffix = Yaf_Registry::get('config')->application->suffix;
	if(!empty($suffix)) {
		if( substr($url, -1, 1)=='/' ){
			$url= substr($url,0,strlen($url)-1).'.'.$suffix;
		}else{
			if(preg_match('#(\/?\?)#', $url)){
				$url = preg_replace('#(\/?\?)#', '.'.$suffix.'?', $url);
			}else{
				$url.= '.'.$suffix;
			}
		}
	}	
	return $url;
}

function get_index(){//以时间获取唯一订单值，返回为年月日时分秒毫秒
	$time = explode (" ", microtime ());//当前时间
	$time_micro = $time [0] * 10000;//毫秒
	$time_ymd = date('YmdHis',$time[1]);//年月日时分秒
	$time2 = explode ( ".", $time_micro );
	$time3= $time_ymd.substr($time2[0],0,3);
	return $time3;
}

//获得时间天数
function get_times($data=array()){
	if (isset($data['time']) && $data['time']!=""){
		$time = $data['time'];//时间
	}elseif (isset($data['date']) && $data['date']!=""){
		$time = strtotime($data['date']);//日期
	}else{
		$time = time();//现在时间
	}
	if (isset($data['type']) && $data['type']!=""){
		$type = $data['type'];//时间转换类型，有day week month year
	}else{
		$type = "month";
	}
	if (isset($data['num']) && $data['num']!=""){
		$num = $data['num'];
	}else{
		$num = 1;
	}
	if ($type=="month"){
		$month = date("m",$time);
		$year = date("Y",$time);
		$_result = strtotime("$num month",$time);
		$_month = (int)date("m",$_result);
		if ($month+$num>12){
			$_num = $month+$num-12;
			$year = $year+1;
		}else{
			$_num = $month+$num;
		}

		if ($_num!=$_month){

			$_result = strtotime("-1 day",strtotime("{$year}-{$_month}-01"));
		}
	}else{
		$_result = strtotime("$num $type",$time);
	}
	if (isset($data['format']) && $data['format']!=""){
		return date($data['format'],$_result);
	}else{
		return $_result;
	}
}

/**
 * 跳转
 *
 * @param      $url
 * @param null $headers
 */
function redirect($url) {
	echo "<script>top.location.href='{$url}';</script>";
	exit;
	/* if (!empty($url))
	{
		if ($headers)
		{
			if (!is_array($headers))
				$headers = array($headers);

			foreach ($headers as $header)
				header($header);
		}

		header('Location: ' . $url);
		exit;
	} */
}
/***发个短信***/
function sendSMS($user_id,$content,$time='',$mid='')
{
	$user = (new Table('user'))->find("user_id='{$user_id}' AND phone_status=1");
	if(is_array($user) && $user['phone']!=''){
		$SMS_SIGNATURE	= '【豫商贷】';
		PostSmsJianZhou($user['phone'],$content.$SMS_SIGNATURE);
	}else{
		return array('status'=>false,'messages'=>'用户手机未绑定');
	}	
}
function PostSmsJianZhou($mobile,$str){//发送短信接口
	$uid = 'sdk_yushangdai';//发送短信用户名
	$pwd = '******';//密码
	$http = 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage';
	$data = array
		(
		'account'=>$uid,					//用户账号
		'password'=>$pwd,	//MD5位32密码
		'destmobile'=>$mobile,				//号码
		'msgText'=>$str,			//内容 utf-8编码，则需转码iconv('gbk','utf-8',$content); 如果是gbk则无需转码
		'sendDateTime'=>''		//定时发送
		);
	$re= postSMS($http,$data);
	if( trim($re) > 0 )
	{
		return array('status'=>true,'messages'=>'发送成功');
	}
	else 
	{
		return array('status'=>false,'messages'=>'发送失败');
	}
}
function postSMS($url,$data='')
{
	$row = parse_url($url);
	$host = $row['host'];
	$port = isset($row['port']) ? $row['port']:80;
	$file = $row['path'];
	$post = "";
	while (list($k,$v) = each($data)) 
	{
		$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
	}
	$post = substr( $post , 0 , -1 );
	$len = strlen($post);
	$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		$receive = '';
		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;		
		fwrite($fp, $out);
		while (!feof($fp)) {
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n",$receive);
		unset($receive[0]);
		return implode("",$receive);
	}
}

/**
 *采集函数
 *
 */
function curl_data($url,$postdata='',$pre_url='http://www.baidu.com',$proxyip=false,$compression='gzip, deflate'){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT,5);
		
		$client_ip	= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		$x_ip		= rand(1,254).'.'.rand(1,254).'.'.rand(1,254).'.'.rand(1,254);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$x_ip,'CLIENT-IP:'.$client_ip));//构造IP				
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if($postdata!=''){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}
		$pre_url = $pre_url ? $pre_url : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		curl_setopt($ch, CURLOPT_REFERER, $pre_url);
		if($proxyip){
			curl_setopt($ch, CURLOPT_PROXY, $proxyip);
		}		
		if($compression!='') {	
			curl_setopt($ch, CURLOPT_ENCODING, $compression);	
		}
		
		//Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; c8650 Build/GWK74) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1s		
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11'); 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
}

/***上传文件到七牛cdn***/
function uploadToCDN($filePath, $cdnfileName){
		require_once  APPLICATION_PATH . '/library/Qiniu/functions.php';
    			
		// 需要填写你的 Access Key 和 Secret Key
		$accessKey = 'jHYFRjlEXA_iiuLrBXZyr7dD2FMyy6Nfo20PKBlc';
		$secretKey = 'sLkQV3m7UHNlFU-7gEmezvg4N0WZUtcbOkVK5uV3';

		// 构建鉴权对象
		$auth = new Qiniu_Auth($accessKey, $secretKey);
		// 要上传的空间
		$bucket = 'cnwhy';

		// 生成上传 Token
		$token = $auth->uploadToken($bucket);

		// 上传到七牛后保存的文件名
		$key = $cdnfileName;

		// 初始化 UploadManager 对象并进行文件的上传
		$uploadMgr = new Qiniu_Storage_UploadManager();

		// 调用 UploadManager 的 putFile 方法进行文件的上传
		list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
		if ($err !== null) {
			return false;
		} else {
			return 'http://o748t1241.bkt.clouddn.com/' . $ret['key'];
		}
}


/**
 * 加密/解密字符串
 *
 * @param  string     $string    原始字符串
 * @param  string     $operation 操作选项: DECODE：解密；其它为加密
 * @param  string     $key       混淆码
 * @return string     $result    处理后的字符串
 */
function authcode($string, $operation, $key = '') {
	$authorization='ysd9385dbc36c077a2e8bec942dd38';
	$key = md5($key ? $key : $authorization);
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = array();
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}	
}