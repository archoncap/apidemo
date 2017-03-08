<?php

class Model {

	protected $dbName;
	protected $modelName;
	protected $tablePrefix;
	protected $data  =  array();
	protected static $db = false;
	
	/**
     * 构造函数
     * 取得DB类的实例对象
     * @access public
     * @param string $name 表/模型名称
     * @param string $tablePrefix 表前缀
     */
    public function __construct($modelName='', $tablePrefix='') {    
		if (!self::$db){
			self::$db = Db::getInstance();
		}
		
        if(!empty($name)) {
            if(strpos($name,'.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName,$this->modelName) = explode('.',$name);
            }else{
                $this->modelName   =  $name;
            }
        }elseif(empty($this->modelName)){
				$this->modelName =   get_class();
        }
        // 设置表前缀
		if( !empty($tablePrefix) ){
			$this->tablePrefix  = $tablePrefix;
		}else{
			if( empty($this->tablePrefix) ){
				$this->tablePrefix = MYSQL_PREFIX;
			}
		}
    }
	
	/**
     * 直接使用 sql 语句获取记录（该方法不会处理关联数据表）
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
    function & getAll($sql,  $limit =array(0,20))
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        // 处理 $limit
        if (is_array($limit)) {
            list($offset, $length) = $limit;
        } else {
			$offset = 0;
            $length = $limit;            
        }
		if(!empty($limit)){
			$limitby	= " LIMIT {$offset}, {$length}";
			if (stripos($sql, ' limit ') === false)
				$sql = $sql . "{$limitby}";     
		}
		
        $rowset = self::$db->getAll($this->prependTablePrefix($sql));
        return $rowset;
    }
	
	/**
     * 直接使用 sql 语句获取记录（该方法不会处理关联数据表）
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
    function & getRow($sql)
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        
        $limitby	= " LIMIT 0, 1";
		if (stripos($sql, ' limit ') === false)
			$sql = $sql . "{$limitby}";     
		
        $rowset = self::$db->getRow($this->prependTablePrefix($sql));
        return $rowset;
    }

	/**
     * 直接使用 sql 语句获取记录（该方法不会处理关联数据表）
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
    function & getOne($sql)
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        
        $limitby	= " LIMIT 0, 1";
		if (stripos($sql, ' limit ') === false)
			$sql = $sql . "{$limitby}";     
		
        $rowset = self::$db->getValue($this->prependTablePrefix($sql));
        return $rowset;
    }
	
	/**
     * 直接使用 sql 语句获取记录（该方法不会处理关联数据表）
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
    function & getValue($sql)
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        
        $limitby	= " LIMIT 0, 1";
		if (stripos($sql, ' limit ') === false)
			$sql = $sql . "{$limitby}";     
		
        $rowset = self::$db->getValue($this->prependTablePrefix($sql));
        return $rowset;
    }
	
	/**
     * 直接使用 sql 语句获取记录（该方法不会处理关联数据表）无视limit
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
    function & query($sql, $usecache=TRUE)
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        		
        $rowset = self::$db->getAll($this->prependTablePrefix($sql), $usecache);
        return $rowset;
    }
	
	/**
     * append tablePrefix
     *
     * @param string $sql
     * @param mixed $limit
     *
     * @return array
     */
	function prependTablePrefix($sql){
		return preg_replace('/{([a-zA-Z0-9_-]+)}/', $this->tablePrefix.'$1', $sql);		
	}
	/**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param type: INSERT = 1; INSERT_IGNORE = 2; REPLACE = 3;
     * @return mixed
     */
    public function add($tableName, $data=array(), $type=1) {
		if (!self::$db)
			self::$db = Db::getInstance();
		
		$tableName	=	$this->tablePrefix.$tableName;
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     = array();
            }else{
                throw(new Exception("insert data cannot be empty."));
            }
        }
        if(false === $this->_beforeAdd($data)) {
            return false;
        }
        // 写入数据到数据库		
        $result = self::$db->insert($tableName, $data, FALSE, OBJECT_CACHE_ENABLE, $type);
        if(false !== $result ) {
			$this->cleanCache(false, true);
            $insertId   =   self::$db->Insert_ID();
            if($insertId) {
                // 自增主键返回插入ID
                $data[self::$db->getPrimaryKey($tableName)] = $insertId;
                $this->_afterAdd($data);
                return $insertId;
            }
			$this->_afterAdd($data);			
        }
        return $result;
    }

	/**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @param array $where 条件表达式
     * @return boolean
     */
    public function update($tableName, $data='', $where='', $limit=0) {
		if (!self::$db)
			self::$db = Db::getInstance();
		
		$tableName	=	$this->tablePrefix.$tableName;
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     =   array();
            }else{
                throw(new Exception("update data cannot be empty."));
            }
        }
		
		if(false === $this->_beforeUpdate($data)) {
            return false;
        }
		$primaryKey	=	self::$db->getPrimaryKey($tableName);
		switch(TRUE){
			// 根据主键删除记录
			case is_numeric($where):			
				$where = "`{$primaryKey}`={$data[$primaryKey]}";
				unset($data[$primaryKey]);
				break;
			case is_string($where):
				if(preg_match('/^\d+([,\d]+)?$/', $where)) {
					$where =  $primaryKey.' IN('.$where.')';
				}
				break;
			case is_array($where):
				$where     =  $primaryKey.' IN('.implode(',',$where).')';
				break;
		}
        if(empty($where)) {			
            if(!isset($data[$primaryKey])) {
                throw(new Exception("update conditions cannot be empty."));
            }
        }
        $result     =   self::$db->update($tableName, $data, $where, $limit, FALSE, OBJECT_CACHE_ENABLE);
        if(false !== $result) {
			$this->cleanCache(true, true);
            $this->_afterUpdate($data);
        }
        return $result;
    }

	/**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return bool
     */
    public function delete($tableName, $where='', $limit=0) {
		if (!self::$db)
			self::$db = Db::getInstance();
		
		$tableName	=	$this->tablePrefix.$tableName;
		$primaryKey	=	self::$db->getPrimaryKey($tableName);
        if(empty($where)) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录			
            if(!empty($this->data) && isset($this->data[$primaryKey])){
                return $this->delete($this->data[$primaryKey]);
            }else{
                 throw new Exception('delete conditions cannot empty.'); 
			}
        }		
		switch(TRUE){
			// 根据主键删除记录
			case is_numeric($where):			
				$where     =  $primaryKey.'='.$where;
				break;
			case is_string($where):
				if(preg_match('/^\d+([,\d]+)?$/', $where)) {
					$where =  $primaryKey.' IN('.$where.')';
				}
				break;
			case is_array($where):
				$where     =  $primaryKey.' IN('.implode(',',$where).')';
				break;
		}
		if(false === $this->_beforeDelete()) {
            return false;
        }
        $result=    self::$db->delete($tableName, $where, $limit, FALSE, OBJECT_CACHE_ENABLE);
        if(false !== $result) {
            $this->_afterDelete();
        }
		$this->cleanCache(true, true);
        return $result;
    }
	
	/**
     * 直接执行一个 sql 语句
     *
     * @param string $sql
     * @param array $inputarr
     *
     * @return mixed
     */
    public function execute($sql, $inputarr = false)
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        return self::$db->execute($this->prependTablePrefix($sql), OBJECT_CACHE_ENABLE);
    }
	
	protected function _beforeAdd() { }

	protected function _afterAdd() { }

	protected function _beforeDelete() { }

	protected function _afterDelete() { }

	protected function _beforeUpdate() { }

	protected function _afterUpdate() { }

	
	public function cleanCache($module, $search) {
		if (OBJECT_CACHE_ENABLE)
		{
			Cache::getInstance()->flush();
		}
	}
}
?>
