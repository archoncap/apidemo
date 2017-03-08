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
 * @desc   公用控制器
 */
class PublicController extends Yaf_Controller_Abstract {
	
	/**
	 *接口名称	APP欢迎页
	 *接口地址	http://api.com/public/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function indexAction(){
		$rows	= (new Table('images'))->findAll('type=0 AND status=1', 'id asc', 5, 'url,title,links');
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'欢迎使用中原顺风车',
							'data'	=>	array(
											'images'	=>	$rows,
										),
						);
		json($result);
	}
	
	/**
	 *接口名称	APP版本号
	 *接口地址	http://api.com/public/version/
	 *接口说明	显示APP当前版本号
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function versionAction(){		
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'APP当前版本号',
							'data'	=>	array(
											'version'	=>	'1.0.1',
											'link'		=>	'http://api.xinguanbio.com/uploads/apk/app-release.apk',
											'remark'	=>	"1. 更新版本至1.0.1",
										),
						);
		json($result);
	}
	
	/**
	 *接口名称	弹出公告页
	 *接口地址	http://api.com/public/pop/
	 *接口说明	显示弹出公告页
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @notice  公告页
	 * @novice	新手页
	 *
	 **/
	public function popAction(){
		$notice	= (new Table('images'))->find('type=1 AND status=1', 'id desc', 'name,litpic,links');		
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'客户端公告页',
							'data'	=>	array(
											'notice' =>	$notice,
										),
						);
		json($result);
	}
		
	/***生成验证码图片***/
	public function yzcodeAction(){

		Captcha::generate(3);

	}
		
	/**
	 *接口名称	轮播图
	 *接口地址	http://api.com/public/scrollimg/
	 *接口说明	显示轮播图
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   轮播图片地址组
	 *
	 **/
	public function scrollimgAction(){
		$rows	= (new Table('images'))->findAll('type=1 AND status=1', 'sortorder desc', 4, 'url, title, links');		
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'主页轮播图片',
							'data'	=>	array(
											'images'	=>	$rows,
										),
						);
		json($result);
	}
		
	/**
	 *接口名称	主页内容
	 *接口地址	http://api.com/public/home/
	 *接口说明	新手体验标、随机推荐项目
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @tiyan  		体验标
	 * @recommend	推荐标
	 *
	 **/
	public function homeAction(){
		$images	= (new Table('images'))->findAll('type=1 AND status=1', 'sortorder desc', 4, 'url, title, links');
		
		$rows	= (new Model)->getAll("SELECT a.*,b.showname,b.sex,b.logo,b.address,b.email,b.phone from {trip} a inner join {members} b on a.members_id=b.id ORDER BY a.id DESC LIMIT 0,10");
				
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'APP主页内容',
							'data'	=>	array(											
											'images'=>	$images,
											'list'	=>	$rows,
										),
						);
		json($result);
	}
	
	
	/**
	 *接口名称	搜索行程
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function searchAction(){
		do{	
			$startdate  =  date('Y-m-d', strtotime($this->getRequest()->getPost('startdate', date('Y-m-d'))));
			$fromcity   =  $this->getRequest()->getPost('fromcity', '');
			$tocity   	=  $this->getRequest()->getPost('tocity', '');
			
			$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
			$pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
			$startpagenum	=  ($pagenum-1) * $pagesize;
			$limit			=  " LIMIT {$startpagenum}, {$pagesize} ";			
			$sortorder		=  " ORDER BY a.id DESC ";			
			$conditions		=  " WHERE a.status=1 AND a.fromcity like '%{$fromcity}%' AND a.tocity like '%{$tocity}%' AND left(a.startdate,10)='{$startdate}' ";			
			
			$_DB	=	new Model;					
			$rows	= $_DB->getAll("SELECT a.*,b.showname,b.sex,b.logo,b.address,b.email,b.phone,b.autobrand,b.autovin from {trip} a inner join {members} b 
											on a.members_id=b.id " . $conditions . $sortorder . $limit );
			$total	= $_DB->getValue("SELECT count(*) from {trip} a " . $conditions);											
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到相关行程.',
							'data'	=>	array(),
						);
				break;
			}
						
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'找到的相关行程.',
							'msg'	=>	' ',
							'data'	=>	array(
											'fromcity'	=>	$fromcity,
											'tocity'	=>	$tocity,
											'pagenum'	=>	$pagenum,
											'pagesize'	=>	$pagesize,											
											'total'		=>	$total,
											'totalpage'	=>	ceil($total/$pagesize),
											'list' 		=>	$rows,
										)
						);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	提示消息
	 *接口地址	http://api.com/public/tips/
	 *接口说明	提示消息
	 *参数 @param
	 *返回 @return
	 * @caption   	  提示消息
	 *
	 **/
	public function tipsAction(){
		if( $this->getRequest()->getMethod()!='POST' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);
			json($result);
		}
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'caption'	=>	'工作日固定发标时间在 09:00、14:00、18:00，其余时间与周末随机发标',
									),
					);
		json($result);
	}
		
	/***关于我们***/
	public function aboutusAction(){		
		$myconfig	=	Yaf_Application::app()->getConfig();

		$smarty = new Smarty_Adapter(null, $myconfig->smarty);
				
		Yaf_Dispatcher::getInstance()->setView($smarty);
				
		$this->_view->display('public/aboutus.html');
    }
	
	/***服务协议***/
	public function serviceAction(){		
		$myconfig	=	Yaf_Application::app()->getConfig();

		$smarty = new Smarty_Adapter(null, $myconfig->smarty);
				
		Yaf_Dispatcher::getInstance()->setView($smarty);
				
		$this->_view->display('public/service.html');
    }
		
	/**
	 *接口名称	选择省
	 *接口地址	http://api.com/public/selectprovince/
	 *接口说明	返回省列表
	 *参数 @param
	 * 空
	 *返回 @return
	 * @id		省ID
	 * @title	省名称
	 **/
	public function selectcityAction(){			
		$rows	= (new Table('zone_city'))->findAll('provincecode=410000');		
		$result	= array(
						'code'	=>	'1',
						'msg'	=>	'城市列表.',
						'data'	=>	array(
										'city'	=>	$rows
									),
					);		
		json($result);
	}
	
	/**
	 *接口名称	选择市
	 *接口地址	http://api.com/public/selectcity/
	 *接口说明	返回市列表
	 *参数 @param
	 * @province_id	省ID
	 *返回 @return
	 * @id		市ID
	 * @title	市名称
	 **/
	public function selectareaAction(){
		do{
			$id		= addslashes($this->getRequest()->getPost('citycode', 	'410100'));
			if( empty($id) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'传递参数错误',
							'data'	=>	array(),
						);
				break;
			}
			
			$rows	= (new Table('zone_area'))->findAll("citycode='{$id}'");
			$result	= array(
							'code'	=>	'1',
							'msg'	=>	'区域列表.',
							'data'	=>	array(
											'area'	=>	$rows
										),
						);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	发送短信
	 *接口地址	http://api.com/public/sendmsg/
	 *接口说明	发送验证码短信
	 *参数 @param
	 * @phone    手机号码 
	 *返回 @return
	 *返回格式	Json
	 * @code   验证码
	 *
	 **/
	public function sendmsgAction(){
		do{
			if( $this->getRequest()->getMethod()!='POST' ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'调用方式错误',
							'data'	=>	array(),
						);
				break;
			}
			$phone		= addslashes($this->getRequest()->getPost('phone', 	''));
			if( empty($phone) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'手机号必填',
							'data'	=>	array(),
						);
				break;
			}
			/***测试环境,delete me***/			
			$config	  = Yaf_Registry::get('config');
			$debug	  = $config['application']['debug'];			
			if( $debug ){
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
											'status'	=>	1,
											'phone'		=>	$phone,
											'code'		=>	'111111',
										),
						);
				break;
			}
			/***测试环境，不发短信***/
			
			$url 	= 'http://www.sendcloud.net/smsapi/send';
			$rand 	= rand(1111,9999);
			$param 	= array(
				'smsUser' 	=> 'sms_web', 
				'templateId'=> '2158',
				'msgType' 	=> '0',
				'phone' 	=> $phone,
				'vars' 		=> '{"%code%":"'.$rand.'"}'
			);
			
			$myCache	= Cache::getInstance();
			$myCache->set('msg_'.$phone, $rand, 300);
			
			$sParamStr = "";
			ksort($param);
			foreach ($param as $sKey => $sValue) {
				$sParamStr .= $sKey . '=' . $sValue . '&';
			}
			$sParamStr = trim($sParamStr, '&');
			$smskey = 'Jsspj9dNGOGBVYZnxHgz3WUJYQoiY7Tj';
			$sSignature = md5($smskey."&".$sParamStr."&".$smskey);
			$param['signature'] = $sSignature;
			$data = http_build_query($param);
			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type:application/x-www-form-urlencoded',
					'content' => $data

			));
			$context = stream_context_create($options);
			$result  = json_decode(file_get_contents($url, FILE_TEXT, $context), TRUE);
			
			if( $result['statusCode']=='200' ){
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'短信发送成功',
							'data'	=>	array(
											'status'	=>	1,
											'phone'		=>	$phone,
											'code'		=>	$rand,
										),
						);
				break;
			}else{
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'短信发送失败，请重试.',
							'data'	=>	array(),
						);
				break;
			}
		}while(FALSE);
		
		json($result);
	}
		
	/**
	 *接口名称	APP注册
	 *接口地址	http://api.com/public/register/
	 *接口说明	APP客户端注册
	 *POST参数 @param
	 * @phone    	手机号码
	 * @password  	登陆密码
	 * @repassword	重复密码
	 * @invite	  	邀请码
	 *返回 @return
	 * @token   	令牌
	 *
	 **/
	public function registerAction() {
		do{
			if( $this->getRequest()->getMethod()!='POST' ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'调用方式错误',
							'data'	=>	array(),
						);
				break;
			}
			$type		= addslashes($this->getRequest()->getPost('type', 	0));
			$phone		= addslashes($this->getRequest()->getPost('phone', 	''));
			$password	= addslashes($this->getRequest()->getPost('password', 	''));
			$repassword	= addslashes($this->getRequest()->getPost('repassword',''));
			if( empty($phone) || empty($password) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'手机号或密码必填',
							'data'	=>	array(),
						);
				break;
			}
			if(!preg_match("/^1[34578]{1}\d{9}$/", $phone)){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'手机号格式有误',
							'data'	=>	array(),
						);
				break;
			} 	
			if( $password!=$repassword ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'重复密码不一致',
							'data'	=>	array(),
						);
				break;
			}
			$_DBuser	=	new Table('members');
			$myUser		=	$_DBuser->find("`phone`='{$phone}'");
			if( !empty($myUser) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'此手机号已存在，请直接登陆.',
							'data'	=>	array(),
						);
				break;
			}							
			$rows		=	array(
							'type'			=>	$type,
							'phone'			=>	$phone,
							'password'		=>	md5($password),							
							'addtime'		=>	time(),
						);
			$lastId = $_DBuser->add($rows);
			if ($lastId) {				
				/***设置登陆token***/
				$sysusers =new userModel();
				if( $token=$sysusers->setUserLogin($phone, $password) ){					
					$result	= array(
							'code'	=>	'1',
							'msg'	=>	'注册成功，感谢您的使用.',
							'data'	=>	array(
											'token'				=>	$token,
										),
					);
					break;
				}
			} else {
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'用户注册失败',
							'data'	=>	array(),
						);
			}			
		}while(FALSE);
		
		json($result);	
	}
	
		
	/**
	 *接口名称	找回密码，修改密码，重置密码
	 *接口地址	http://api.com/user/resetpwd/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/	
	public function resetpwdAction(){
		do{	
			$phone		= $this->getRequest()->getPost('phone', NULL);
			$yzcode		= $this->getRequest()->getPost('yzcode', NULL);
			$password 	= $this->getRequest()->getPost('password', NULL);
			$repassword = $this->getRequest()->getPost('repassword', NULL);
			
			if( empty($phone) || empty($yzcode) ){

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'手机号及验证码不能为空',
							'data'	=>	array(),
						);
						break;
			}
			
			$myCache 		= Cache::getInstance();
			if(!$myCache->exists('msg_'.$phone) || $myCache->get('msg_'.$phone)!=$yzcode){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'验证码不正确.',
							'data'	=>	array(),
						);
						break;
			}
			
			if( $password==NULL  || $repassword==NULL){

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'密码未填',
							'data'	=>	array(),
						);
						break;
			}			
			if($password!=$repassword){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'重复密码不一致',
							'data'	=>	array(),
						);
						break;
			}			
			$sysusers	=	new userModel();			
			$myuser		=	$sysusers->getUsers("phone='{$phone}'");		
			if( $myuser==FALSE ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到对应的手机号.',
							'data'	=>	array(),
						);
						break;
			}
			$rows		=	array(
								'user_id'	=>	$myuser[0]['user_id'],
								'password'	=>	md5($repassword),
							);
			if ($sysusers->updateinfo($rows)!==FALSE) {
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'更新密码成功.',
							'data'	=>	array(
											'status'	=> 1,
										),
						);			
						break;
			}else{

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'更新失败.',
							'data'	=>	array(),
						);
			}								

		}while(FALSE);

		json($result);
	}	
		
	/**
	 *接口名称	APP登陆
	 *接口地址	http://api.com/public/login/
	 *接口说明	生成token，用户登陆
	 *参数 @param
	 * @username 	用户名
	 * @password 	密码
	 *返回 @return	
	 * @token   	登陆标记
	 * @status		登陆状态
	 **/
	public function loginAction(){
		do{
			if( $this->getRequest()->getMethod()!='POST' ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'调用方式错误',
							'data'	=>	array(),
						);
				break;
			}
			
			$username = addslashes($this->getRequest()->getPost('phone', NULL));

			$password = $this->getRequest()->getPost('password', NULL);

			if( $username==NULL || $password==NULL ){

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'用户名或者密码为空',
							'data'	=>	array(),
						);
						break;
			}

			$sysusers =new userModel();

			if ($sysusers->checkUsername($username)==FALSE) {
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到匹配用户名.',
							'data'	=>	array(),
						);			
						break;
			}		

			$myCache 		= Cache::getInstance();
			$try_times_key	= 'login_'.$username;
			if ($sysusers->checkPassword($username, $password)==FALSE){
				if(!$myCache->exists($try_times_key)){
					$try_times = 1;
					$myCache->set($try_times_key, 1, 600);			
				}else{
					$try_times = $myCache->get($try_times_key);
					$myCache->set($try_times_key, $try_times+1, 600);
				}				
				if($try_times>10){					
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'重试次数过多了， 10分钟后再重试吧.',
							'data'	=>	array(),
					);					
				}else{
					$result	= array(
							'code'	=>	'0',
							'msg'	=>	'密码有误.',
							'data'	=>	array(),
					);
				}
				break;
			}

			if( $token=$sysusers->setUserLogin($username, $password) ){
						$myCache->delete($try_times_key);
						$userid	= $myCache->get($token);
						$rows	= (new userModel)->getUser($userid);

						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'登陆成功.',
							'data'	=>	array(
											'token'		=>	$token,
											'userinfo'	=>	array(
															'user_id'		=>	$rows['id'],
															'type'			=>	$rows['type'],
															'phone'			=>	strval($rows['phone']),
															'showname'		=>	strval($rows['showname']),
															'sex'			=>	strval($rows['sex']),
															'logo'			=>	strval($rows['logo']),														
														)
										),
						);
			}else{

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'登陆失败.',
							'data'	=>	array(),
						);
			}								

		}while(FALSE);

		json($result);
	}	

	/**
	 *接口名称	生成二维码
	 *接口地址	http://api.com/public/qrcode/
	 *接口说明	
	 *参数 @param
	 * @token 		网址
	 *返回 @return	
	 * @image		图片		
	 **/
	public function qrcodeAction(){
	
		if( $this->getRequest()->getMethod()!='GET' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);				
			json($result);
		}
		include APPLICATION_PATH . "/qrcode/phpqrcode.php";
		
		$errorLevel = "L";
		$size 		= "4";
		$url		= $this->getRequest()->get('url', 	'https://www.zzwms.com');			
		
		QRcode::png($url, false, $errorLevel, $size);			
	
	}
	
	/**
	 *接口名称	验证token是否有效
	 *接口地址	http://api.com/public/checktoken/
	 *接口说明	验证token
	 *参数 @param
	 * @token 		登陆标识
	 *返回 @return	
	 * @status		token状态
	 **/
	public function checktokenAction(){
		do{
			if( $this->getRequest()->getMethod()!='POST' ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'调用方式错误',
							'data'	=>	array(),
						);
				break;
			}			
			$token	  = addslashes($this->getRequest()->getPost('token', NULL));
			if( $token==NULL ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'参数为空',
							'data'	=>	array(),
						);
						break;
			}			
			if( (self::checklogin($token))==FALSE ){
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'token无效.',
					'data'	=>	array(),
				);				
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'token有效.',
					'data'	=>	array(
									'status' =>	1,
								),
				);
			}
		}while(FALSE);

		json($result);
	}

	/**
	 *私有方法	验证登陆
	 *方法说明	验证token，返回用户ID
	 *参数 @param
	 * @token 	标记
	 *返回 @return	
	 * @user_id   	成功返回用户ID
	 * FALSE		失败返回FALSE
	 **/
	private static function checklogin($token){
		$myCache 		= Cache::getInstance();
		if( !$myCache->exists($token) ){
			return FALSE;
		}else{
			//$myCache->expire($token, 86400);
			return $myCache->get($token);
		}
	}	
		
		
	/**
	 *接口名称	退出登陆
	 *接口地址	http://api.com/public/logout/
	 *接口说明	清除token，退出登陆
	 *参数 @param无
	 *返回 @return无
	 **/
	public function logoutAction(){	
		do{
			if( $this->getRequest()->getMethod()!='POST' ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'调用方式错误',
							'data'	=>	array(),
						);
				break;
			}			
			$token	  = addslashes($this->getRequest()->getPost('token', NULL));
			if( $token==NULL ){
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'参数为空',
							'data'	=>	array(),
						);
						break;
			}		
			
			$myCache = Cache::getInstance();
			if( $myCache->exists($token) ){				
			
				$myCache->delete($token);
			}			
			$result	= array(
				'code'	=>	'1',
				'msg'	=>	'安全退出.',
				'data'	=>	array(
								'status' =>	1,
							),
			);
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	行程列表
	 *接口地址	http://api.com/public/tripall/
	 *接口说明	
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function tripallAction(){
		if( $this->getRequest()->getMethod()!='POST' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);
			json($result);
		}
		
		$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
        $pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
		$startpagenum	=  ($pagenum-1) * $pagesize;
		$limit			=  " LIMIT {$startpagenum}, {$pagesize} ";
		
		$status			=  addslashes($this->getRequest()->getPost('status', 	1));
		$conditions		=  " WHERE a.status={$status} ";
		$sortorder		=  " ORDER BY a.id DESC ";
			
		
		$_DB	= new Model;
		$sql 	= "SELECT a.*,b.showname,b.phone from {trip} a inner join {members} b on a.members_id=b.id {$conditions} {$sortorder} {$limit}";		
		$rows	= $_DB->getAll($sql);
				
		$sql 	= "SELECT count(*) from {trip} a inner join {members} b on a.members_id=b.id {$conditions}";
		$total	= $_DB->getValue($sql);
		
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'行程列表',
							'data'	=>	array(				
											'status'	=>	$status,
											'pagenum'	=>	$pagenum,
											'pagesize'	=>	$pagesize,											
											'total'		=>	$total,
											'totalpage'	=>	ceil($total/$pagesize),
											'list' 		=>	$rows,
										),
						);
		json($result);
	}
	
	/**
	 *接口名称	行程详情
	 *接口地址	http://api.com/public/tripview/
	 *接口说明	
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 *
	 **/
	public function tripviewAction(){
		if( $this->getRequest()->getMethod()!='POST' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);
			json($result);
		}
				
		$id				=  addslashes($this->getRequest()->getPost('id', 	1));
		$conditions		=  " WHERE a.id={$id} ";
		
		$_DB	= new Model;
		$sql 	= "SELECT a.*,b.showname,b.phone from {trip} a inner join {members} b on a.members_id=b.id {$conditions}";		
		$rows	= $_DB->getRow($sql);
				
		$result	= array(
							'code'	=>	'1',
							'msg'	=>	'行程详情',
							'data'	=>	array(															
											'data' 	=>	$rows,
										),
						);
		json($result);
	}
	
	
	
	/**
	 *接口名称	选择出发城市
	 *接口地址	http://api.com/public/selectprovince/
	 *接口说明	返回省列表
	 *参数 @param
	 * 空
	 *返回 @return
	 * @id		省ID
	 * @title	省名称
	 **/
	public function selectfromcityAction(){			
		$rows	= (new Model)->getAll("select fromcity from {pretrip} group by fromcity");
		$result	= array(
						'code'	=>	'1',
						'msg'	=>	'出发城市列表.',
						'data'	=>	array(
										'city'	=>	$rows
									),
					);		
		json($result);
	}
	
	/**
	 *接口名称	选择到达城市
	 *接口地址	http://api.com/public/selectprovince/
	 *接口说明	返回省列表
	 *参数 @param
	 * 空
	 *返回 @return
	 * @id		省ID
	 * @title	省名称
	 **/
	public function selecttocityAction(){
		do{
			$fromcity	= addslashes($this->getRequest()->getPost('fromcity', 	''));
			if( empty($fromcity) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'出发城市参数错误',
							'data'	=>	array(),
						);
				break;
			}
			$conditions	= "fromcity='{$fromcity}'";
			$rows	= (new Model)->getAll("select tocity from {pretrip} where {$conditions} group by tocity");
			$result	= array(
							'code'	=>	'1',
							'msg'	=>	'到达城市列表.',
							'data'	=>	array(
											'city'	=>	$rows
										),
						);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	选择到达城市
	 *接口地址	http://api.com/public/selectprovince/
	 *接口说明	返回省列表
	 *参数 @param
	 * 空
	 *返回 @return
	 * @id		省ID
	 * @title	省名称
	 **/
	public function selectfareAction(){
		do{
			$fromcity	= addslashes($this->getRequest()->getPost('fromcity', 	''));
			$tocity		= addslashes($this->getRequest()->getPost('tocity', 	''));
			if( empty($fromcity) || empty($tocity)  ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'出发城市或者到达城市参数错误',
							'data'	=>	array(),
						);
				break;
			}
			$conditions	= " pretrip_id in (select id from {pretrip} where fromcity='{$fromcity}' and tocity='{$tocity}') ";
			$rows	= (new Table('prefare'))->findAll($conditions);
			$result	= array(
							'code'	=>	'1',
							'msg'	=>	'预定义行程价格表.',
							'data'	=>	array(
											'fare'	=>	$rows
										),
						);
		}while(FALSE);
		
		json($result);
	}
	
}
