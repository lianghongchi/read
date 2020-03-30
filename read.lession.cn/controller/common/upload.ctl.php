<?php
/**
 * 上传图片
 *
 * @authName    上传图片
 * @note        上传图片
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-24
 *
 */

class common_upload_controller extends controller_lib
{

    /**
     * 是否自定义权限控制
     */
    protected $_isCustomAuthControl = true;

    /**
     * 上传图片类
     */
    public function upload_action()
    {
        $upload = new ueditor_uploader_lib();
        $result = $upload->upload('file',$_GET['path']);
        if($result){
            $arr = [
                'code'=>0,
                'msg'=>'上传成功',
                'data'=>[
                    'src'=>'http://cdn.nextjoy.com/'.$_GET['path'].'/'.$upload->getFileName(),
                ]
            ];
        }else{
            $arr = [
                'code'=>400,
                'msg'=>$upload->getErrorMsg(),
                'data'=>[
                    'src'=>'',
                ]
            ];
        }

        echo json_encode($arr);

    }


}
