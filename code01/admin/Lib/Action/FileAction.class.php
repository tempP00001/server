<?php 
// +----------------------------------------------------------------------
// | EaseTHINK 易想团购系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.easethink.com All rights reserved.
// +----------------------------------------------------------------------

class FileAction extends CommonAction{
	public function do_upload()
	{
		if(intval($_REQUEST['upload_type'])==0)
		$result = $this->uploadFile();
		else
		$result = $this->uploadImage();
		if($result['status'] == 1)
		{
			$list = $result['data'];
			if(intval($_REQUEST['upload_type'])==0)
			$file_url = ".".$list[0]['recpath'].$list[0]['savename'];
			else
			$file_url = ".".$list[0]['bigrecpath'].$list[0]['savename'];
			$html = '<html>';
			$html.= '<head>';
			$html.= '<title>Insert Image</title>';
			$html.= '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
			$html.= '</head>';
			$html.= '<body>';
			$html.= '<script type="text/javascript">';
			$html.= 'parent.parent.KE.plugin["image"].insert("' . $_POST['id'] . '", "' . $file_url . '","' . $_POST['imgTitle'] . '","' . $_POST['imgWidth'] . '","' . $_POST['imgHeight'] . '","' . $_POST['imgBorder'] . '","' . $_POST['align'] . '");';
			$html.= '</script>';
			$html.= '</body>';
			$html.= '</html>';
			echo $html;
		}
		else
		{
			echo "<script>alert('".$result['info']."');</script>";
		}
	}
	public function do_upload_img()
	{
		if(intval($_REQUEST['upload_type'])==0)
		$result = $this->uploadFile();
		else
		$result = $this->uploadImage();
		if($result['status'] == 1)
		{
			$list = $result['data'];
			if(intval($_REQUEST['upload_type'])==0)
			$file_url = ".".$list[0]['recpath'].$list[0]['savename'];
			else
			$file_url = ".".$list[0]['bigrecpath'].$list[0]['savename'];
			$html = '<html>';
			$html.= '<head>';
			$html.= '<title>Insert Image</title>';
			$html.= '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
			$html.= '</head>';
			$html.= '<body>';
			$html.= '<script type="text/javascript">';
			//$html.='alert("'.$_POST['id'].'");';
			//$html.='alert(parent.parent.document.getElementById("'.$_POST['id'].'").value);';
			//$html.='parent.parent.document.getElementById("'.$_POST['id'].'").value="'.$file_url.'";';
			$html.= 'parent.parent.KE.plugin["upload_image"].insert("' . $_POST['id'] . '", "' . $file_url . '","' . $_POST['imgTitle'] . '","' . $_POST['imgWidth'] . '","' . $_POST['imgHeight'] . '","' . $_POST['imgBorder'] . '","' . $_POST['align'] . '");';
			$html.= '</script>';
			$html.= '</body>';
			$html.= '</html>';
			echo $html;
		}
		else
		{
			echo "<script>alert('".$result['info']."');</script>";
		}
	}

	
	public function deleteImg()
	{
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$file = $_REQUEST['file'];
		$file = explode("..",$file);
		@unlink(get_real_path().$file[4]);		
		save_log(l("DELETE_SUCCESS"),1);
		$this->success(l("DELETE_SUCCESS"),$ajax);
	}
}
?>