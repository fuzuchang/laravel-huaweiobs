<?php namespace Goodgay\HuaweiOBS;

use Obs\ObsClient;

class Factory
{

    /**
     * Map configuration array keys with OBS setters
     *
     * @var array
     */
    protected $configMappings = [
        'key'               => '',
        'secret'            => '',
        'security_token'    => '',
        'endpoint'          => '',
        'signature'         => '',
        'path_style'        => '',
        'region'            => '',
        'ssl_verify'        => '',
        'ssl.certificate_authority'     => '',
        'max_retry_count'           => '',
        'timeout'                   => '',
        'socket_timeout'            => '',
        'connect_timeout'           => '',
        'chunk_size'                => '',
        'exception_response_mode'   => '',
    ];

    /**
     * Make the OBS client for the given named configuration, or
     * the default client.
     *
     * @param array $config
     *
     * @return \Obs\ObsClient
     */
    public function make(array $config): ObsClient
    {
        return $this->buildClient($config);
    }

    /**
     * Build and configure an OBS client.
     *
     * @param array $config
     *
     * @return \Obs\ObsClient
     */
    protected function buildClient(array $config): ObsClient
    {
        $client = ObsClient::factory($config);
       
        if(config('app.debug')){
            $client->initLog(array (
                'FilePath'  => $config['log']['path'],
                'FileName'  => $config['log']['name'],
                'MaxFiles'  => $config['log']['max_files'],
                'Level'     => $config['log']['level'],
            ));
        }
        
        return $client;
    }
}
