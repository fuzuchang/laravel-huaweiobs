<?php namespace Goodgay\HuaweiOBS\Traits;

use ObsV3\ObsClient;

trait Upload {
    /**
     * 文本上传
     * 1.用于直接上传字符串
     * 2.使用resource或GuzzleHttp\Psr7\StreamInterface作为对象的数据源
     * 3.创建文件夹实际上是创建了一个大小为0且对象名以“/”结尾的对象
     *
     * @param string $objectName
     * @param mixed $content
     * @return array
     */
    public function putText(string $objectName, $content)
    {
        return $this->obs->putObject([
            'Bucket'    => $this->bucket,
            'Key'       => $objectName,
            'Body'      => $content
        ]);
    }

    /**
     * 文件上传使用本地文件作为对象的数据源。
     *
     * @param string $objectName
     * @param string $content
     * @return array
     */
    public function putFile(string $objectName, string $sourceFile)
    {
        return $this->obs->putObject([
            'Bucket'    => $this->bucket,
            'Key'       => $objectName,
            'SourceFile'=> $sourceFile
        ]);
    }
}