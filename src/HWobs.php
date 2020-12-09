<?php 
namespace Goodgay\HuaweiOBS;

use Illuminate\Support\Facades\Facade;


/**
 * Class Facade
 *
 * @package Goodgay\HuaweiOBS
 */
class HWobs extends Facade
{

    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return 'hwobs';
    }
}
