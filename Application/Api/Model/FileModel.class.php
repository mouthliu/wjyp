<?php
namespace Api\Model;
/**
 * 文件处理
 * Class FileModel
 * @package Api\Model
 */
class FileModel extends BaseModel {

    /**
     * 获取一张图片的绝对路径
     * @param int $file_id
     * @param string $default_path
     * @return string
     */
    public function getOneFilePath($file_id = 0,$default_path = ''){

        if($file_id){
            $path = $this->where(array('id'=>$file_id))->getField('path');
            $absolute_path = $path?C('API_URL').$path:$default_path;
        }else{
            $absolute_path = $default_path;
        }
        return $absolute_path;
    }

    /**
     * 获取多张图片绝对地址
     * @param array $file_arr
     * @param string $picture_key
     * @param $default_path
     * @return array
     */
    public function getArrayFilePath($file_arr = array(),$picture_key = 'path',$default_path=''){
        if($file_arr){
            $picture_arr = array();
            foreach($file_arr as $k =>$v){
                $path = $this->where(array('id'=>$v))->getField('path');
                $picture_arr[$k][$picture_key] = $path?C('API_URL').$path:$default_path;
            }
        }else{
            $picture_arr = array();
        }
        return $picture_arr;
    }
}