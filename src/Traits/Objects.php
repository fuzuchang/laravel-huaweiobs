<?php namespace Goodgay\HuaweiOBS\Traits;

use Obs\ObsClient;

trait Objects {
    /**
     * 获取对象属性，包括对象长度，对象MIME类型，对象自定义元数据等信息
     *
     * @param string $objectName
     * @return array
     */
    public function getMetadata(string $objectName)
    {
        return $this->obs->getObjectMetadata([
            'Bucket'    => $this->bucket,
            'Key'       => $objectName,
        ]);
    }

    /**
     * 举出桶里的对象。
     * 
     * @return array
     */
    public function all()
    {
        
        $resp = $this->obs->listObjects ([
            'Bucket'    => $this->bucket
        ]);
        return $resp ['Contents']??[];
    }

    /**
     * 删除单个对象
     *
     * @param string $objectName
     * @return void
     */
    public function delete(string $objectName)
    {
        return $this->obs->deleteObject([
            'Bucket'    => $this->bucket,
            'Key'       => $objectName,
        ]);
    }

    /**
     * 删除多个对象
     *
     * @param array $objects
     * @param boolean $quiet
     * @return array
     */
    public function deleteMulti(array $objects,bool $quiet = false)
    {

        $tmp = [];

        foreach($objects as $k => $v){
            $tmp[$k] = [
                'Key'       => $v,
                'VersionId' => null 
            ];
        }

        return $this->obs->deleteObject([
            'Bucket'    => $this->bucket,
            // 设置为verbose模式
            'Quiet'     => false,
            'Objects'   => $tmp
        ]);
    }
}