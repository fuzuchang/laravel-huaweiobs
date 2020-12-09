<?php namespace Goodgay\HuaweiOBS\Traits;

use Obs\ObsClient;

trait Download {
    /**
     * 文本下载
     *
     * @param string $objectName
     * @return void
     */
    public function getText(string $objectName)
    {
        $resp = $this->obs->getObject([
            'Bucket'    => $this->bucket,
            'Key'       => $objectName,
        ]);

        echo $resp['Body']??'';
        exit();
    }

    /**
     * 流式下载
     *
     * @param string $objectName
     * @return void
     */
    public function getStream(string $objectName)
    {
        $resp = $this->obs->getObject([
            'Bucket'        => $this->bucket,
            'Key'           => $objectName,
            'SaveAsStream'  => true
        ]);

        while(!$resp['Body'] -> eof()){
            echo $resp['Body'] -> read(65536);
        }
        exit();
    }


    /**
     * 文件下载
     *
     * @param string $objectName
     * @return void
     */
    public function getFile(string $objectName,string $filepath)
    {
        $resp = $this->obs->getObject([
            'Bucket'        => $this->bucket,
            'Key'           => $objectName,
            'SaveAsFile'    => $filepath
        ]);
    }
}