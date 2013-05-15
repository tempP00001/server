<?php
class CacheFileService extends CacheService
{//类定义开始

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	private $dir;
    public function __construct()
    {
        $this->dir = APP_ROOT_PATH."app/Runtime/data_caches/";
        $this->init();

    }

    /**
     +----------------------------------------------------------
     * 初始化检查
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function init()
    {
        $stat = @stat($this->dir);

        // 创建项目缓存目录
        if (!is_dir($this->dir)) {
            if (!  mkdir($this->dir))
                return false;
             chmod($this->dir, 0777);
        }
    }

    private function filename($name)
    {
        $name	=	md5($name);
        $filename	=  $name.'.php';
       
        return $this->dir.$this->prefix.$filename;
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
        $filename   =   $this->filename($name);    
        if(file_exists($filename))    
        {
		    if (!$handle = fopen($filename, 'r')) {
			     return false;
			}			
			$content = fread($handle, filesize ($filename));				
	    	fclose($handle);
        }
        else
        {
        	return false;
        }
        if( false !== $content) { 
        	$content    =   unserialize($content);
        	$$var_name  = $content;
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 -1 为永久
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name,$value,$expire='')
    {
    	if(app_conf("CACHE_ON")==0)return false;
        $filename   =   $this->filename($name);
        $data   =   serialize($value);     
        
	    if (!$handle = fopen($filename, 'w')) {
			return false;
		}
			
			    
		if (fwrite($handle, $data) === FALSE) {
			return false;
		}
			
	    fclose($handle);
	    
        return true;
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
    	if(app_conf("CACHE_ON")==0)return false;
        return unlink($this->filename($name));
    }

    /**
     +----------------------------------------------------------
     * 清除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function clear()
    {
    	$this->del_name_logs();
        $path   =  $this->dir;
        if ( $dir = opendir( $path ) )
        {
            while ( $file = readdir( $dir ) )
            {
                $check = is_dir( $file );
                if ( !$check )
                    unlink( $path . $file );                 
            }
            closedir( $dir );
            return true;
        }
        
    }
    


}//类定义结束
?>