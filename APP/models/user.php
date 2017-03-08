<?php
class userModel extends Table{
	
	protected $tableName = 'members';
	protected $primaryKey= 'id';		protected $tablePrefix='pc_';
		
	public function checkUsername($username) {
		$conditions	=	"phone='".addslashes($username)."'";
		
		return $this->findCount($conditions)>0 ? TRUE : FALSE;
	}

	public function checkPassword($username, $password) {
		$conditions	=	"(phone='".addslashes($username)."') AND password='". md5($password)."'";
		return $this->findCount($conditions)>0 ? TRUE : FALSE;
	}

	public function setUserLogin($username, $password){
		$conditions	=	"(phone='".addslashes($username)."') AND password='". md5($password)."'";
		if( $user = $this->find($conditions) ){			$lasttime	= time();
			$rows	= array(
							'id'			=>	$user['id'],
							'logintimes'	=>	intval($user['logintimes'])+1,
							'lasttime'		=>	$lasttime,
						);
			$this->update($rows);			$token	= 'auth' . md5($user['id'].$user['phone'].$user['type'].$user['logintimes'].$lasttime);			Cache::getInstance()->set($token, $user['id'], 0);
			return $token;
		}else{
			return FALSE;
		}
	}	public function updateinfo($rows){		$myinfo	=	array();		foreach($rows as $k=>$v){			if( !empty($v) ){				$myinfo[$k]	=	$v;			}		}		return $this->update($myinfo);	}		
	public function getUsers($conditions, $sort=NULL, $startpagenum=0,$pageSize=10){
		$rows	=	$this->findAll($conditions, $sort, array($startpagenum,$pageSize));
				return $rows;
	}		public function getUser($id){				$result	=	$this->find($id);				return	$result;			}
	
}
