<?php namespace Goodgay\HuaweiOBS;

use ObsV3\ObsClient;
use Goodgay\HuaweiOBS\Traits\Upload;
use Goodgay\HuaweiOBS\Traits\Download;
use Goodgay\HuaweiOBS\Traits\Objects;

class Client {

    use Upload,Download,Objects;

    protected $obs      = null;
    protected $bucket   = '';

    public function __construct(ObsClient $obsClient)
    {
        $this->obs      = $obsClient;
        $this->bucket   = config("hwobs.bucket");
    }

    public function obs()
    {
        return $this->obs;
    }

}