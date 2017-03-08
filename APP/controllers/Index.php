<?php
/********************************************
                   _ooOoo_
                  o8888888o
                  88" . "88
                  (| -_- |)
                  O\  =  /O
               ____/`---'\____
             .'  \\|     |//  `.
            /  \\|||  :  |||//  \
           /  _||||| -:- |||||-  \
           |   | \\\  -  /// |   |
           | \_|  ''\---/''  |   |
           \  .-\__  `-`  ___/-. /
         ___`. .'  /--.--\  `. . __
      ."" '<  `.___\_<|>_/___.'  >'"".
     | | :  `- `.;`\ _ /`;.`/ - ` : | |
     \  \ `-.   \_ __\ /__ _/   .-` /  /
======`-.____`-.___\_____/___.-`____.-'======
                   `=---='
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         佛祖保佑       BUG永无
/********************************************/
/**
 
 * @name IndexController
 
 * @author slayer.hover
 
 * @desc   默认控制器
 
 */
class IndexController extends Yaf_Controller_Abstract {	
	private $_DB = NULL;
	public function init(){
		if( $_DB==NULL )
			$this->_DB	=new Model;
	}
	
	/** 
	* 构造函数
	* 
	* @param  空
	* 	
	* @return 空
	*/
	public function indexAction(){					
		$server = new Yar_Server($this);	
		$server->handle();
	}
	
	/** 
	* 构造函数
	* 
	* @param  $type 整型 类型（1: 类型a 2：类型b 3:类型c 4:类型d 5:类型e）
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function getSchoolList($options=array(), $authcode='123456'){		
		$this->auth(__FUNCTION__.implode('',$options), $authcode);
		
		$sql = "select * from xc_school a inner join xc_user b on a.user_id=b.user_id";
		return $this->_DB->getRow($sql);
	}
	
	/** 
	* 构造函数
	* 
	* @param  $type 整型 类型（1: 类型a 2：类型b 3:类型c 4:类型d 5:类型e）
	* 	
	* @return $rows 数组 返回对应的数据包
	*/
	public function getData(array $options=['authcode'=>'123456']){	
		$this->auth(__FUNCTION__.implode('',$options), $options['authcode']);
		
		$sql = "select * from xc_school a inner join xc_user b on a.user_id=b.user_id";
		return $this->_DB->getRow($sql);
	}
	
	
	/** 
	* 验证数字签名
	* 
	* @param  $authcode 字符型
	* 	
	* @return bool型 成功true, 失败false
	*/
	private function auth($decodewords='abcdefg', $authcode='123456'){		
		if( strcmp($this->authcode($decodewords, 'ENCODE'), $authcode)!==0 ){
			throw(new Exception('数字签名有误，请核对'));
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
	private function authcode($string, $operation, $key = '') {
		$authorization='dhxc9385dbc36c077a2e8bec942dd38';
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
	
}
