<?php
class CacheXcacheService extends CacheService
{

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct()
    {
        if ( !function_exists('xcache_info') ) {
            throw_exception(L('_NOT_SUPPERT_').':Xcache');
        }
        $this->type = strtoupper(substr(__CLASS__,6));
		$this->expire = 36000;
    }

    /**
     +----------------------------------------------------------
     * 读取缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name)
    {
    	if(app_conf("CACHE_ON")==0)return false;
    	$var_name = md5($name);    	
    	global $$var_name;
    	if($$var_name)
    	{
    		return $$var_name;
    	}
   		if (xcache_isset($name)) {
   			$data = xcache_get($name);
    		$$var_name = $data;    	
			return $data;
		}
        return false;
    }

    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name, $value,$expire='')
    {
    	if(app_conf("CACHE_ON")==0)return false;
		if(empty($expire)) {
			$expire = $this->expire ;
		}
		$this->log_names($name);
		return xcache_set($name, $value, $expire);
    }

    /**
     +----------------------------------------------------------
     * 删除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name)
    {
		return xcache_unset($name);
    }
    
    
    public function clear()
    {
		$names = $this->get_names();
		foreach($names as $name)
		{
			$this->rm($name);
		}
		$this->del_name_logs();
    }

}//类定义结束
?>