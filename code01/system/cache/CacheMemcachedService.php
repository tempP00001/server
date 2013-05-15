<?php

class CacheMemcachedService extends CacheService
{

	private $mem;
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct()
    {
		$this->mem = new Memcache;
		$memcache_config = app_conf("MEMCACHE_HOST");
		$memcache_config = explode(":",$memcache_config);
		$host = $memcache_config[0];
		$port = $memcache_config[1]?$memcache_config[1]:'11211'; //默认端口为11211
		$this->mem->connect($host, $port);   //此处为memcache的连接主机与端口 
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
    	$data = $this->mem->get($name);
    	$$var_name = $data;
        return $data;
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
    public function set($name, $value)
    {
    	if(app_conf("CACHE_ON")==0)return false;
		$this->log_names($name);
		return $this->mem->set($name,$value,0,36000);
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
		return $this->mem->delete($name);
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