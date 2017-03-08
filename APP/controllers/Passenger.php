<?php
class PassengerController extends Yaf_Controller_Abstract {
	
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
	public function myorderAction(){
		do{	
			$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
			$pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
			$startpagenum	=  ($pagenum-1) * $pagesize;
			$limit			=  " LIMIT {$startpagenum}, {$pagesize} ";			
			$sortorder		=  " ORDER BY b.addtime DESC ";			
			$conditions		=  " WHERE b.members_id=" . self::$user_id;
			
			$_DB	=	new Model;			
			$sql	=	"select a.*,b.id as order_id,b.contact,b.tel,b.bookseat from {trip} a inner join {trip_order} b on b.trip_id=a.id " . $conditions . $sortorder . $limit;						
			$rows	=	$_DB->getAll($sql);
						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'暂无记录.',
							'data'	=>	array(),
						);
				break;
			}
			
			$sql 	= "select count(*) from {trip_order} b " . $conditions;
			$total	= $_DB->getValue($sql);
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'我的预约',
							'data'	=>	array(				
											'pagenum'	=>	$pagenum,
											'pagesize'	=>	$pagesize,											
											'total'		=>	$total,
											'totalpage'	=>	ceil($total/$pagesize),
											'list' 		=>	$rows,
										),
						);
		}while(FALSE);
		
		json($result);
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
	public function orderinfoAction(){
		do{	
			$order_id   =  intval($this->getRequest()->getPost('id', 1));
			$conditions	=  " WHERE b.id=" . $order_id;
			
			$_DB	=	new Model;		
			
			$sql		=	"select a.fromcity,a.fromplace,a.tocity,a.toplace,a.money,a.startdate,a.seat,a.rest,b.status,b.id as order_id,b.members_id as ordermembers_id,b.contact,b.tel,b.bookseat,c.autobrand,c.autovin,c.showname,c.sex,c.logo,c.address,c.email,c.phone from {trip} a inner join {trip_order} b on b.trip_id=a.id inner join {members} c on a.members_id=c.id " . $conditions;
			$rows		=	$_DB->getRow($sql);	
									
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['ordermembers_id']!=self::$user_id ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条订单不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$sql			=	"select * from {trip_order_log} where order_id={$order_id}";
			$rows['log']	=	$_DB->getAll($sql);
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'预约详情',
							'data'	=>	$rows,
						);
		}while(FALSE);
		
		json($result);
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
	public function ordersubmitAction(){
		do{			
			$trip_id= addslashes($this->getRequest()->getPost('id', NULL));
			
			$contact	=addslashes($this->getRequest()->getPost('contact', NULL));
			$tel		=addslashes($this->getRequest()->getPost('tel', NULL));
			$bookseat	=addslashes($this->getRequest()->getPost('bookseat', NULL));
		
			$_DBtriporder	=	new Table('trip_order');
			$rows	=	array(
							'trip_id'	=>	$trip_id,
							'members_id'=>	self::$user_id,							
							'contact'	=>	$contact,
							'tel'		=>	$tel,
							'bookseat'	=>	$bookseat,
						);			
			if( $_DBtriporder->findCount("trip_id='{$trip_id}' AND members_id='".self::$user_id."'")>0 ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'请勿重复提交.',
							'data'	=>	array(),
						);
				break;
			}
			
			$_DBtrip	=	new Table('trip');
			$trip		=	$_DBtrip->find($trip_id);
			if( $trip['rest']<$bookseat ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'剩余空坐数不足.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $order_id = $_DBtriporder->add($rows) ){
				
				$_DBtriporderlog	=	new Table('trip_order_log');
				$rows	= array(
							'order_id'	=>	$order_id,
							'title'		=>	'订单已提交',
							'log'		=>	'请等待司机确认',
						);
				$_DBtriporderlog->add($rows);
				
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'预约提交成功.',
							'data'	=>	array(
											'id'	=>	$order_id,
										),
						);
			}else{
				$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'预约提交失败',
							'data'	=>	array(),
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
}
