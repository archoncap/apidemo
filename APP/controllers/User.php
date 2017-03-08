<?php
class UserController extends Yaf_Controller_Abstract {
	
	private static $user_id;	
	/**
	 *
	 * 初始化验证
	 *
	 **/
	public function init(){
	
		/***验证提交方式***/
		if( $this->getRequest()->getMethod()!='POST' ){
			$result	= array(
						'code'	=>	'0',
						'msg'	=>	'调用方式错误',
						'data'	=>	array(),
					);
			json($result);
		}			
			
		/***验证登陆***/
		$token	  = addslashes($this->getRequest()->getPost('token', NULL));
		if( (self::$user_id = self::checklogin($token))==FALSE ){
			$result	= array(
				'code'	=>	'0',
				'msg'	=>	'用户未登陆，请先登陆吧',
				'data'	=>	array(),
			);
			json($result);
		}		
		
	}
	
	/**
	 *接口名称	用户中心首页
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function indexAction(){
		do{			
			$_DBmem	=	new Table('members');
			$rows	=	$_DBmem->find(self::$user_id);	
			
			$rows['lasttime']= date('Y-m-d H:i', $rows['lasttime']);
			
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到该用户,数据异常.',
							'data'	=>	array(),
						);
				break;
			}
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'用户中心',
							'data'	=>	$rows,
						);
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	个人资料
	 *接口地址	http://api.com/user/info/
	 *接口说明	显示个人资料
	 *参数 @param
	 * @token		登陆令牌
	 *返回 @return
	 * @rows
	 *
	 **/
	public function infoAction(){
		do{			
			$_DBmem	=	new Table('members');
			$rows	=	$_DBmem->find(self::$user_id, NULL, 'phone,type,showname,sex,logo,address,email,status,addtime,logintimes,lasttime');
			
			$rows['lasttime']= date('Y-m-d H:i', $rows['lasttime']);
			
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'未找到该用户,数据异常.',
							'data'	=>	array(),
						);
				break;
			}
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'个人资料',
							'data'	=>	$rows,
						);
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	实名认证
	 *接口地址	http://api.com/user/consummate/
	 *接口说明	新注册用户完善个人信息
	 *参数 @param
	 * @realname 	姓名
	 * @card_id 	身份证号
	 * @email		邮箱
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function authAction(){
		do{		
			$_DBmem	=	new Table('members');
			$me		=	$_DBmem->find(self::$user_id);
			if( !$me ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'查找用户失败',
						'data'	=>	array(),
					);
					break;
			}
			if( empty($me['showname']) || empty($me['shenfenzheng']) || empty($me['shenfenzheng_img'])  || empty($me['auto_img'])  || empty($me['jiashizheng_img']) ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'用户姓名、驾驶证、车辆照片、或身份证未填写或者照片未上传，请在完善信息后提交.',
						'data'	=>	array(),
					);
					break;
			}
			if( $me['status']==2 ){
					$result	= array(
						'code'	=>	'0',
						'msg'	=>	'认证已通过，无需重复认证.',
						'data'	=>	array(),
					);
					break;
			}			
			
			$rows		=	array(
								'id'		=>	self::$user_id,								
								'status'	=>	1,
							);
			if ($_DBmem->update($rows)===FALSE) {
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'提交申请认证信息失败.',
							'data'	=>	array(),
						);
			}else{
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'提交申请认证成功,请等待审核.',
							'data'	=>	array(
											'status'=>	1,
										),
						);
			}
		}while(FALSE);

		json($result);
	}	
	
	/**
	 *接口名称	完善信息
	 *接口地址	http://api.com/user/consummate/
	 *接口说明	新注册用户完善个人信息
	 *参数 @param
	 * @realname 	姓名
	 * @card_id 	身份证号
	 * @email		邮箱
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function consummateAction(){

		do{			
			$showname = addslashes($this->getRequest()->getPost('showname', NULL));
			$address  = addslashes($this->getRequest()->getPost('address', NULL));
			$email	  = addslashes($this->getRequest()->getPost('email', NULL));
			$shenfenzheng	= addslashes($this->getRequest()->getPost('shenfenzheng', NULL));
			$autobrand	= addslashes($this->getRequest()->getPost('autobrand', NULL));
			$autovin	= addslashes($this->getRequest()->getPost('autovin', NULL));
					
			if( $showname==NULL ){

						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'请输入您的姓名',
							'data'	=>	array(),
						);
						break;
			}
			
			$_DBmem	=	new Table('members');
			$rows		=	array(
								'id'		=>	self::$user_id,								
								'showname'	=>	$showname,
								'address'	=>	$address,
								'email'		=>	$email,
								'autobrand'	=>	$autobrand,
								'autovin'	=>	$autovin,
							);
			if ($_DBmem->update($rows)===FALSE) {
						$result	= array(
							'code'	=>	'0',
							'msg'	=>	'更新用户信息失败.',
							'data'	=>	array(),
						);
			}else{
						$result	= array(
							'code'	=>	'1',
							'msg'	=>	'用户信息更新成功.',
							'data'	=>	array(
											'status'=>	1,
										),
						);
			}
		}while(FALSE);

		json($result);
	}
		
	/**
	 *接口名称	上传头像
	 *接口地址	http://api.com/user/uploadphoto/
	 *接口说明	上传图片，更新用户头像
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadphotoAction(){

		do{			
			$type	= addslashes($this->getRequest()->getPost('type', NULL));
			$files	= $this->getRequest()->getPost('logo', NULL);
			if( $files==NULL || $type==NULL ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = './' . $config['application']['uploadpath'] . '/logo/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$sysusers	=	new userModel();
			$rows		=	array(
								'id'	=>	self::$user_id,
								'logo'	=>	$photourl,
							);
			if ($sysusers->updateinfo($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'更新用户头像更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'用户头像更新成功.',
					'data'	=>	array(
									'status'	=>	1,
									'photourl'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	上传身份证
	 *接口地址	http://api.com/user/uploadphoto/
	 *接口说明	上传图片，更新用户头像
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadshenfenzhengAction(){

		do{			
			$type	= addslashes($this->getRequest()->getPost('type', NULL));
			$files	= $this->getRequest()->getPost('shenfenzheng', NULL);
			if( $files==NULL || $type==NULL ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = './' . $config['application']['uploadpath'] . '/shenfenzheng/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$sysusers	=	new userModel();
			$rows		=	array(
								'id'				=>	self::$user_id,
								'shenfenzheng_img'	=>	$photourl,
							);
			if ($sysusers->updateinfo($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'身份证更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'身份证更新成功.',
					'data'	=>	array(
									'status'			=>	1,
									'shenfenzheng_img'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	上传身份证
	 *接口地址	http://api.com/user/uploadphoto/
	 *接口说明	上传图片，更新用户头像
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadjiashizhengAction(){

		do{			
			$type	= addslashes($this->getRequest()->getPost('type', NULL));
			$files	= $this->getRequest()->getPost('jiashizheng', NULL);
			if( $files==NULL || $type==NULL ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = './' . $config['application']['uploadpath'] . '/jiashizheng/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$sysusers	=	new userModel();
			$rows		=	array(
								'id'				=>	self::$user_id,
								'jiashizheng_img'	=>	$photourl,
							);
			if ($sysusers->updateinfo($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'驾驶证更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'驾驶证更新成功.',
					'data'	=>	array(
									'status'			=>	1,
									'jiashizheng_img'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
	
	/**
	 *接口名称	上传汽车照片
	 *接口地址	http://api.com/user/uploadphoto/
	 *接口说明	上传图片，更新用户头像
	 *参数 @param
	 * @logo 		图片文件
	 * @token		登陆标记
	 *返回 @return	
	 * @status		更新状态
	 **/
	public function uploadautoAction(){

		do{			
			$type	= addslashes($this->getRequest()->getPost('type', NULL));
			$files	= $this->getRequest()->getPost('auto', NULL);
			if( $files==NULL || $type==NULL ){
						$result	= array(
								'code'	=>	'0',
								'msg'	=>	'图片类型或者内容为空',
								'data'	=>	array(),
							);
						break;
			}
			
			$config	  = Yaf_Registry::get('config');
			$filename = 'logo-t' . time() . '.' . $type;				
			$descdir  = './' . $config['application']['uploadpath'] . '/auto/' . date('Ym') . '/';
			if( !is_dir($descdir) ){ mkdir($descdir, 0777, TRUE); }
			$realpath = $descdir . $filename;				
			
			if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $files, $base64result)){			 
			  if (file_put_contents($realpath, base64_decode(str_replace(' ', '+', str_replace($base64result[1], '', $files))))){
				$newfile = str_replace('./', '', $realpath);
			  }else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			  }
			}elseif (file_put_contents($realpath, base64_decode(str_replace(' ', '+', $files)))){
				$newfile = str_replace('./', '', $realpath);
			}else{
				$result	= array(
								'code'	=>	'0',
								'msg'	=>	'储存图片出错.',
								'data'	=>	array(),
							);
				break;
			}
			$photourl	=	$config['application']['scheme'] . '://' . $_SERVER['HTTP_HOST']  . '/' . $newfile;
			$sysusers	=	new userModel();
			$rows		=	array(
								'id'				=>	self::$user_id,
								'auto_img'			=>	$photourl,
							);
			if ($sysusers->updateinfo($rows)===FALSE) {
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'汽车照片更新失败.',
					'data'	=>	array(),
				);		
			}else{
				$result	= array(
					'code'	=>	'1',
					'msg'	=>	'汽车照片更新成功.',
					'data'	=>	array(
									'status'	=>	1,
									'auto_img'	=>	$photourl,
								),
				);
			}
					
		}while(FALSE);

		json($result);
	}
		
	/**
	 *接口名称	我的消息
	 *接口地址	http://api.com/user/message/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messageAction(){		
		$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
        $pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
		
		$rows 		= (new Table('message'))->findAll("receive_user='".self::$user_id."'", NULL, array($pagenum-1, $pagesize), 'id,name,status,type,content,addtime');
		$counter	= (new Table('message'))->findCount("receive_user='".self::$user_id."'");
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										
										'total'		=>	$counter,
										'pagenum'	=>	$pagenum,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($counter/$pagesize),
										'list'		=>	(array)$rows,
									),
					);
		json($result);
	}
	
	/**
	 *接口名称	未读消息数
	 *接口地址	http://api.com/user/newmessagenum/
	 *接口说明	列出我的消息
	 *参数 @param
	 * @token		登陆标记
	 *返回 @return
	 * @num			未读消息条数
	 *
	 **/
	public function newmessagenumAction(){	
	
		$counter	= (new Table('message'))->findCount("receive_user='".self::$user_id."' AND status=0");
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'未读消息条数',
						'data'	=>	array(										
										'num'	=>	$counter,
									),
					);
		json($result);
	}
	
	
	/**
	 *接口名称	消息删除
	 *接口地址	http://api.com/user/messagedelete/
	 *接口说明	删除我的消息
	 *参数 @param
	 * @id   		消息ID
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function messagedeleteAction(){		
		do{	
			$id         =  intval($this->getRequest()->getPost('id',  0));
			$all        =  intval($this->getRequest()->getPost('all', 0));
			
			if($id==0 && $all==0){			
				$result	= array(
					'code'	=>	'0',
					'msg'	=>	'参数异常.',
					'data'	=>	array(),
				);
				break;				
			}
			if($id>0){
				$rows 		= (new Table('message'))->delete("receive_user='".self::$user_id."' AND id='{$id}'");
			}elseif($all==1){
				$rows 		= (new Table('message'))->delete("receive_user='".self::$user_id."'");
			}
			
			if($rows){
				$result		= array(
							'code'	=>	'1',
							'msg'	=>	'消息删除成功',
							'data'	=>	array(										
											'status'	=> 1,
										),
						);
			}else{
				$result		= array(
							'code'	=>	'1',
							'msg'	=>	'消息删除失败',
							'data'	=>	array(),
						);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	我的积分
	 *接口地址	http://api.com/user/points/
	 *接口说明	列出我的积分记录
	 *参数 @param
	 * @status   	整数  0:未读  1：已读
	 * @pagenum		页码 
	 * @pagesize	每页数量
	 * @token		登陆标记
	 *返回 @return
	 * @list		消息列表
	 *
	 **/
	public function pointsAction(){
		$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
        $pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
		
		$creditNum	= (new Table('credit'))->find("user_id='".self::$user_id."'");		
		if( empty($creditNum) ){
			$rows	= array( 'user_id'=>self::$user_id, 'value'=>0, 'op_user'=>0, 'addtime'=>time(), 'addip'=>getIp() );
			$_DBcredit->add($rows);
			$creditNum['value']= 0;
		}
		
		$creditLog	= (new Table('credit_log'))->findAll("user_id='".self::$user_id."'", NULL, array($pagenum-1, $pagesize), 'id,type_id,value,remark,addtime');
		$counter	= (new Table('credit_log'))->findCount("user_id='".self::$user_id."'");
		
		if(is_array($creditLog) && !empty($creditLog)){
			$creditType	=	new Table('credit_type');
			foreach($creditLog as $k=>$v){
					$type	=	$creditType->find($v['type_id']);
					$creditLog[$k]['type']	=	$type['name'];
			}
		}
		
		$result		= array(
						'code'	=>	'1',
						'msg'	=>	'数据读取成功',
						'data'	=>	array(
										'credit'	=>	$creditNum['value'],
										'total'		=>	$counter,
										'pagenum'	=>	$pagenum,
										'pagesize'	=>	$pagesize,
										'totalpage'	=>	ceil($counter/$pagesize),
										'list'		=>	(array)$creditLog,
									),
					);
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
}
