<?php
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

class GoodsTypeAttrAction extends CommonAction{
	public function __construct()
	{
		parent::__construct();
		$this->assign("jumpUrl",u("Index/main"));
		$this->error(l("NO_SUPPORT_THIS_FUNC"));
	}	
}
?>