<?php

class Table {

	protected $dbName;
	protected $tableName;
	protected $tablePrefix;
	protected $primaryKey;
	protected $fields=	array();
	protected $data  =  array();
	protected static $db = false;
	
	/**
     * 构造函数
     * 取得DB类的实例对象
     * @access public
     * @param string $tableName 表/模型名称
     * @param string $tablePrefix 表前缀
     */
    public function __construct($tableName='', $tablePrefix='', $primaryKey='') {    
		if (!self::$db){
			self::$db = Db::getInstance();
		}
		
        if(!empty($tableName)) {
            if(strpos($tableName,'.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName,$this->tableName) = explode('.',$tableName);
            }else{
                $this->tableName   =  $tableName;
            }
        }else{
			if(empty($this->tableName))
				throw(new Exception("Table name cannot be empty."));
        }
		
		// 设置表前缀
		if( !empty($tablePrefix) ){
			$this->tablePrefix  = $tablePrefix;
		}else{
			if( empty($this->tablePrefix) ){
				$this->tablePrefix = MYSQL_PREFIX;
			}
		}
		$this->tableName	= $this->tablePrefix.$this->tableName;
		
		$tbexist 		=	self::$db->getValue("SHOW TABLES LIKE '{$this->tableName}'");		
		if( empty($tbexist) ){
				throw(new Exception("Table {$this->tableName} not exists."));
		}
		$this->fields	=   self::$db->getFields($this->tableName);
		
		// 设置主键
		$this->primaryKey = empty($primaryKey) ? self::$db->getPrimaryKey($this->tableName) : $primaryKey;		
		if( empty($this->primaryKey) ){
			if( in_array('id', $this->fields) ){
				$this->primaryKey	=	'id';
			}else{
				throw(new Exception("Table primaryKey cannot be empty."));
			}
		}
    }
	
	/**
     * 返回符合条件的第一条记录及所有关联的数据，查询没有结果返回 false
     *
     * @param mixed $conditions
     * @param string $sort
     * @param mixed $fields
     * @param mixed $queryLinks
     *
     * @return array
     */
    public function & find($conditions, $sort = '', $fields = '*'){	
		if (!self::$db)
			self::$db = Db::getInstance();
		
		if(is_numeric($conditions)){
            $whereby= " WHERE `{$this->primaryKey}`={$conditions} ";
        }else{
			$whereby= ($conditions!='') ? " WHERE {$conditions} " : '';
		}		
		
        // 处理排序		
        $sortby = ($sort!='') ? " ORDER BY {$sort} " : '';
		// 处理字段
		$fieldsby = ($fields=='*'||empty($fields)) ? implode(',', array_map(function($obj){return "`{$obj}`";}, $this->fields)) : $fields;
        // 处理 $limit        
		$limitby	= " LIMIT 0, 1";
		
        $sql = "SELECT {$fieldsby} FROM {$this->tableName} {$whereby} {$sortby} {$limitby}";		
        $rowset = self::$db->getRow($this->prependTablePrefix($sql));			
        return $rowset;
    }

	/**
     * 查询所有符合条件的记录及相关数据，返回一个包含多行记录的二维数组，失败时返回 false
     *
     * @param mixed $conditions
     * @param string $sort
     * @param mixed $limit
     * @param mixed $fields
     *
     * @return array
     */
    public function & findAll($conditions = null, $sort = null, $limit ='', $fields = '*')
    {
		if (!self::$db)
			self::$db = Db::getInstance();
		
		switch(TRUE){
			// 根据主键查询记录
			case is_numeric($conditions):			
				$whereby= " WHERE `{$this->primaryKey}`={$conditions} ";
				break;
			case is_string($conditions):
				if(preg_match('/^\d+([,\d]+)?$/', $conditions)) {
					$whereby =  " WHERE {$this->primaryKey} IN ({$conditions})";
				}else{
					$whereby = ($conditions!='') ? " WHERE {$conditions} " : '';
				}
				break;
			case is_array($conditions):
				$whereby     =  " WHERE {$this->primaryKey} IN (".implode(',',$conditions).")";
				break;
			default:
				$whereby	 =	'';
		}
		
        // 处理排序		
        $sortby = ($sort!='') ? " ORDER BY {$sort} " : '';
		if( !empty($limit) ){
			// 处理 $limit
			if(is_array($limit)) {
				list($offset, $length) = $limit;
			}elseif(is_integer($limit)) {			
				$offset = 0;
				$length = $limit;            
			}else{
				list($offset, $length)	= explode(',', $limit);
			}
			$limitby	= $length>0 ? " LIMIT {$offset}, {$length}" : "";
		}else{
			$limitby	= "";
		}
		// 处理字段
		$fieldsby = ($fields=='*'||empty($fields)) ? implode(',', array_map(function($obj){return "`{$obj}`";}, $this->fields)) : $fields;		
        $sql = "SELECT {$fieldsby} FROM {$this->tableName} {$whereby} {$sortby} {$limitby}";			

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
    function & findBySql($sql,  $limit ='')
    {
		if (!self::$db)
			self::$db = Db::getInstance();
        
		if( !empty($limit) ){
			// 处理 $limit
			if(is_array($limit)) {
				list($offset, $length) = $limit;
			}elseif(is_integer($limit)) {			
				$offset = 0;
				$length = $limit;            
			}else{
				list($offset, $length)	= explode(',', $limit);
			}
			$limitby	= $length>0 ? " LIMIT {$offset}, {$length}" : "";
			
			if (stripos($sql, ' limit ') === false)
				$sql = $sql . "{$limitby}";      
		}		
        $rowset = self::$db->getAll($this->prependTablePrefix($sql));			
        return $rowset;
    }
	
	/**
     * 统计符合条件的记录的总数
     *
     * @param mixed $conditions
     * @param string|array $fields
     *
     * @return int
     */
    function findCount($conditions = null, $fields = null)
    {		
		if (!self::$db)
			self::$db = Db::getInstance();
		$whereby= ($conditions!='') ? " WHERE {$conditions} " : '';
        if (is_null($fields)) {
            $fields = $this->primaryKey;
        }
        $sql = "SELECT COUNT({$fields}) FROM {$this->tableName}{$whereby}";		
		
        return (int)self::$db->getValue($this->prependTablePrefix($sql));
    }

	/**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param type: INSERT = 1; INSERT_IGNORE = 2; REPLACE = 3;
     * @return mixed
     */
    public function add($data=array(), $type=1) {
		if (!self::$db)
			self::$db = Db::getInstance();
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
        $result = self::$db->insert($this->tableName, $data, FALSE, OBJECT_CACHE_ENABLE, $type);
        if(false !== $result ) {
			$this->cleanCache(false, true);
            $insertId   =   self::$db->Insert_ID();
            if($insertId) {
                // 自增主键返回插入ID
                $data[$this->primaryKey] = $insertId;
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
    public function update($data='', $where='', $limit=0) {
		if (!self::$db)
			self::$db = Db::getInstance();
		
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
		switch(TRUE){
			// 根据主键删除记录
			case is_numeric($where):			
				$where = "`{$this->primaryKey}`='{$where}'";
				if( isset($data[$this->primaryKey]) ){ unset($data[$this->primaryKey]); }
				break;
			case is_string($where):
				if(preg_match('/^\d+([,\d]+)?$/', $where)) {
					$where =  $this->primaryKey.' IN('.$where.')';
				}
				break;
			case is_array($where):
				$where     =  $this->primaryKey.' IN('.implode(',',$where).')';
				break;
		}
        if(empty($where)) {			
            if(!isset($data[$this->primaryKey])) {
                throw(new Exception("update conditions cannot be empty."));
            }else{
				$where = "`{$this->primaryKey}`={$data[$this->primaryKey]}";
				unset($data[$this->primaryKey]);
			}
        }
		
        $result     =   self::$db->update($this->tableName, $data, $where, $limit, FALSE, OBJECT_CACHE_ENABLE);
        if(false !== $result) {
			$this->cleanCache(true, true);
            $this->_afterUpdate($data);
			return (int)self::$db->getValue('SELECT ROW_COUNT()');
        }
        return false;
    }

	/**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return bool
     */
    public function delete($where='', $limit=0) {
		if (!self::$db)
			self::$db = Db::getInstance();
        if(empty($where)) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if(!empty($this->data) && isset($this->data[$this->primaryKey])){
                return $this->delete($this->data[$this->primaryKey]);
            }else{
                 throw new Exception('delete conditions cannot empty.'); 
			}
        }		
		switch(TRUE){
			// 根据主键删除记录
			case is_numeric($where):			
				$where	   = "`{$this->primaryKey}`='{$where}'";
				break;
			case is_string($where):
				if(preg_match('/^\d+([,\d]+)?$/', $where)) {
					$where =  $this->primaryKey.' IN('.$where.')';
				}
				break;
			case is_array($where):
				$where     =  $this->primaryKey.' IN('.implode(',',$where).')';
				break;
		}
		if(false === $this->_beforeDelete()) {
            return false;
        }
        $result=    self::$db->delete($this->tableName, $where, $limit, FALSE, OBJECT_CACHE_ENABLE);
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
