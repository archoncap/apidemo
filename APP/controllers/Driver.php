<?php
class DriverController extends Yaf_Controller_Abstract {
	
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
	public function mytripAction(){
		do{	
			$pagenum        =  intval($this->getRequest()->getPost('pagenum', 1));
			$pagesize    	=  intval($this->getRequest()->getPost('pagesize', 10));
			$startpagenum	=  ($pagenum-1) * $pagesize;
			$limit			=  " LIMIT {$startpagenum}, {$pagesize} ";			
			$sortorder		=  " ORDER BY a.id DESC ";			
			
			$status         =  intval($this->getRequest()->getPost('status', 1));
			if( $status>=3 ){
				$conditions		=  " WHERE a.members_id=" . self::$user_id . " AND a.status>='3' ";	
			}else{
				$conditions		=  " WHERE a.members_id=" . self::$user_id . " AND a.status='{$status}' ";
			}
		
			$_DB	=	new Model;			
			$sql	=	"select a.* from {trip} a inner join {members} c on a.members_id=c.id " . $conditions . $sortorder . $limit;
			$rows	=	$_DB->getAll($sql);								
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'暂无记录.',
							'data'	=>	array(),
						);
				break;
			}
			
			$sql 	= "select count(*) from {trip} a " . $conditions;
			$total	= $_DB->getValue($sql);
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'我的行程',
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
	public function tripinfoAction(){
		do{	
			$order_id   =  intval($this->getRequest()->getPost('id', 1));
			$conditions	=  " WHERE a.id=" . $order_id;
			
			$_DB		=	new Model;
			$sql		=	"select * from {trip} a " . $conditions;
			$rows		=	$_DB->getRow($sql);						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['members_id']!=self::$user_id ){				
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条行程不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$sql			=	"select a.id as order_id, a.members_id,a.status,b.phone,b.type,b.showname,b.sex,b.logo,b.address,b.email from {trip_order} a  inner join {members} b on a.members_id=b.id where a.trip_id={$order_id}";
			$rows['orders']	=	$_DB->getAll($sql);
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'我的预约',
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
	public function orderinfoAction(){
		do{	
			$order_id   =  $this->getRequest()->getPost('id', 1);
			$conditions	=  " WHERE b.id=" . $order_id;
			
			$_DB	=	new Model;		
			
			$sql		=	"select a.fromcity,a.fromplace,a.tocity,a.toplace,a.money,a.startdate,a.seat,a.rest,b.status,b.id as order_id,b.members_id,b.contact,b.tel,b.bookseat,c.showname,c.sex,c.logo,c.address,c.email,c.phone from {trip} a inner join {trip_order} b on b.trip_id=a.id inner join {members} c on a.members_id=c.id " . $conditions;
			$rows		=	$_DB->getRow($sql);	
									
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
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
	 *接口名称	行程预约日志
	 *接口地址	http://api.com/user/index/
	 *接口说明	显示欢迎页图片
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function triporderlogAction(){
		do{	
			$order_id   =  intval($this->getRequest()->getPost('id', 1));
			$conditions	=  " WHERE a.id=" . $order_id;
			
			$_DB		=	new Model;
			$sql		=	"select * from {trip} a " . $conditions;
			$rows		=	$_DB->getRow($sql);						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['members_id']!=self::$user_id ){				
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条行程预约不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$sql			=	"select * from {trip_log} where order_id={$order_id}";
			$dataset		=	$_DB->getAll($sql);
			
			$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'行程预约日志',
							'data'	=>	$dataset,
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
	public function tripupdateAction(){
		do{	
			$trip_id   =  intval($this->getRequest()->getPost('id', 1));
			$conditions	=  " WHERE a.id=" . $trip_id;
			
			$_DB		=	new Model;
			$sql		=	"select * from {trip} a " . $conditions;
			$rows		=	$_DB->getRow($sql);			
						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['members_id']!=self::$user_id ){				
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条行程不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$rest   	=  intval($this->getRequest()->getPost('rest', 	 0));
			$status   	=  intval($this->getRequest()->getPost('status', 1));
			$location   =  strval($this->getRequest()->getPost('location', ''));
			
			$data	=	array(
							'id'		=>	$rows['id'],
							'rest'		=>	$rest,
							'status'	=>	$status,
						);
			if( !empty($location) ){
				$data['location']		=	$location;
			}			
			
			if( (new Table('trip'))->update($data)===FALSE ){			
				$result	=	array(
							'code'	=>	'0',
							'msg'	=>	'更新失败',
							'data'	=>	array(),
						);
			}else{
				$_DBtriporderlog	=	new Table('trip_order_log');				
				$rows	= array( 'order_id'	=>	$order_id	);
				switch($status){
					case 2:
						$rows['title']	=	'行程已开始';	break;
					case 3:
						$rows['title']	=	'行程已取消';	break;
					case 4:
						$rows['title']	=	'行程已结束';	break;	
				}
				$_DBtriporderlog->add($rows);
				
				$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'更新成功',
							'data'	=>	$data,
						);
			}
		}while(FALSE);
		
		json($result);
	}

	/**
	 *接口名称	拒绝预约
	 *接口地址	http://pinche.xinguanbio.com/driver/refuseorder
	 *接口说明	接受预约
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function refuseorderAction(){
		do{	
			$order_id   =  intval($this->getRequest()->getPost('order_id', 1));
			$conditions	=  " WHERE a.id=" . $order_id;			
			$_DB		=	new Model;
			$sql		=	"select a.*,b.members_id as driver_id from {trip_order} a INNER JOIN {trip} b on a.trip_id=b.id " . $conditions;
			$rows		=	$_DB->getRow($sql);			
						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['driver_id']!=self::$user_id ){				
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条行程定单不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$data	=	array(
							'id'		=>	$order_id,
							'status'	=>	2,
						);			
			if( (new Table('trip_order'))->update($data)===FALSE ){				
				$result	=	array(
							'code'	=>	'0',
							'msg'	=>	'更新定单状态失败',
							'data'	=>	array(),
						);
			}else{
				$_DBtriporderlog	=	new Table('trip_order_log');
				$rows	= array(
							'order_id'	=>	$order_id,
							'title'		=>	'订单已拒绝',
							'log'		=>	'司机拒绝了您的订单',
						);
				$_DBtriporderlog->add($rows);
				
				
				$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'拒绝预约成功',
							'data'	=>	$data,
						);
			}
		}while(FALSE);
		
		json($result);
	}
	
	/**
	 *接口名称	接受预约
	 *接口地址	http://pinche.xinguanbio.com/driver/acceptorder
	 *接口说明	接受预约
	 *参数 @param无
	 *返回 @return
	 *返回格式	Json
	 * @images   图片地址组
	 *
	 **/
	public function acceptorderAction(){
		do{	
			$order_id   =  intval($this->getRequest()->getPost('order_id', 1));
			$conditions	=  " WHERE a.id=" . $order_id;			
			$_DB		=	new Model;
			$sql		=	"select a.*,b.id as trip_id,b.rest,b.members_id as driver_id from {trip_order} a INNER JOIN {trip} b on a.trip_id=b.id " . $conditions;
			$rows		=	$_DB->getRow($sql);			
						
			if( !is_array($rows) || empty($rows) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'无此记录，参数错误.',
							'data'	=>	array(),
						);
				break;
			}
			
			if( $rows['driver_id']!=self::$user_id ){				
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'这条行程定单不属于您.',
							'data'	=>	array(),
						);
				break;
			}
			
			$data	=	array(
							'id'		=>	$order_id,
							'status'	=>	1,
						);			
			if( (new Table('trip_order'))->update($data)===FALSE ){			
				$result	=	array(
							'code'	=>	'0',
							'msg'	=>	'更新定单状态失败',
							'data'	=>	array(),
						);
			}else{
				$_DBtrip	=	new Table('trip');
				$trip		=	array(
									'id'	=>	$rows['trip_id'],
									'rest'	=>	$rows['rest']-$rows['bookseat'],
								);				
				$_DBtrip->update($trip);
				
				$_DBtriporderlog	=	new Table('trip_order_log');
				$rows	= array(
							'order_id'	=>	$order_id,
							'title'		=>	'司机已确认',
							'log'		=>	'请准时上车',
						);
				$_DBtriporderlog->add($rows);
				
				$result	=	array(
							'code'	=>	'1',
							'msg'	=>	'接受预约成功',
							'data'	=>	$data,
						);
			}
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
	public function tripsubmitAction(){
		do{			
			$fromcity	= addslashes($this->getRequest()->getPost('fromcity', ''));
			$fromplace	= addslashes($this->getRequest()->getPost('fromplace', ''));
			$tocity		= addslashes($this->getRequest()->getPost('tocity', ''));
			$toplace	= addslashes($this->getRequest()->getPost('toplace', ''));
			$location	= addslashes($this->getRequest()->getPost('location', ''));
			
			$startdate	= addslashes($this->getRequest()->getPost('startdate', date('Y-m-d H:i')));
			$starttime	= date('H:i', strtotime($startdate));
			
			$_DBpretrip	= new Table('pretrip');
			$pretrip	= $_DBpretrip->find("fromcity='{$fromcity}' AND tocity='{$tocity}'");
			$_DBprefare	= new Table('prefare');
			if( $prefare	= $_DBprefare->find("pretrip_id='{$pretrip['id']}' AND starton<'{$starttime}' AND endon>'{$starttime}'") ){
				$money	=	$prefare['money'];				
			}else{
				$prefare	= $_DBprefare->find("pretrip_id='{$pretrip['id']}'");
				$money	=	$prefare['money'];
			}			
			$seat	= intval(addslashes($this->getRequest()->getPost('seat', 5)));
			$rest	= intval(addslashes($this->getRequest()->getPost('rest', 5)));
						
			if( empty($fromcity) || empty($tocity) || empty($rest) ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'起始地、目的地、空余座位数不能为空.',
							'data'	=>	array(),
						);
				break;
			}
			if( $rest>$seat ){
				$result	= array(
							'code'	=>	'0',
							'msg'	=>	'空余座位数不能大于总座位数.',
							'data'	=>	array(),
						);
				break;
			}			
		
			$_DBtrip=	new Table('trip');
			$rows	=	array(							
							'members_id'=>	self::$user_id,
							'fromcity'		=>	$fromcity,
							'fromplace'		=>	$fromplace,
							'tocity'		=>	$tocity,
							'toplace'		=>	$toplace,
							'startdate'		=>	$startdate,
							'money'			=>	$money,
							'location'		=>	$location,
							
							'seat'		=>	$seat,
							'rest'		=>	$rest,							
							'status'	=>	1,
						);			
			if( $trip_id = $_DBtrip->add($rows) ){				
				$result	= array(
							'code'	=>	'1',
							'msg'	=>	'行程发布成功.',
							'data'	=>	array(
											'id'	=>	$trip_id,
										),
						);
			}else{
				$result	=	array(
							'code'	=>	'0',
							'msg'	=>	'行程发布失败',
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
