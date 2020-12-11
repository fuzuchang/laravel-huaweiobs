<?php

namespace Goodgay\HuaweiOBS;

use GuzzleHttp\Psr7\Utils;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;
use League\Flysystem\Util\MimeType;
use ObsV3\ObsClient as Client;
use ObsV3\ObsException;

class HwobsAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait;

    const RES_FILE      = 'file';
    const RES_STREAM    = 'stream';

    protected $client;
    protected $bucket;

    /**
     * 构造client
     *
     * @param Client $client
     * @param string $prefix
     */
    public function __construct(Client $client, string $prefix = '', string $bucket = '')
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->setPathPrefix($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, self::RES_FILE);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource, self::RES_STREAM);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, self::RES_FILE);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->upload($path, $resource, self::RES_STREAM);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath): bool
    {
        $path = $this->applyPathPrefix($path);
        $newPath = $this->applyPathPrefix($newPath);

        try {
            $this->copy($path, $newPath);
        } catch (ObsException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath): bool
    {
        $path = $this->applyPathPrefix($path);
        $newpath = $this->applyPathPrefix($newpath);

        try {
            $tr = $this->client->copyObject([
                'Bucket'    => $this->bucket,
                'Key'       => $path,
                'CopySource'=> $newpath
            ]);
        } catch (ObsException $e) {
            return false;
        }
        return true;
    }

    protected function acl($path)
    {
        return $this->client->getObjectAcl([
            'Bucket'    => $this->bucket,
            'Key'       => $path,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path): bool
    {
        $location = $this->applyPathPrefix($path);

        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $location
            ]);
        } catch (ObsException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname): bool
    {
        return $this->delete($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        $path = $this->applyPathPrefix($dirname);
        try {
            $this->mkdir($path);
            return true;
        } catch (ObsException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        return $this->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $path = $this->applyPathPrefix($path);
        try {
            $resp = $this->client->getObject([
                'Bucket'        => $this->bucket,
                'Key'           => $path,
                'SaveAsStream'  => true
            ]);
        } catch (ObsException $e) {
            return false;
        }

        $object['stream'] = $object['contents'] = Utils::copyToString($resp['Body']);
        unset($resp['Body']);
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false): array
    {
        $location = $this->applyPathPrefix($directory);

        try {
            $data = [
                'Bucket' => $this->bucket,
                'Prefix' => $location,
            ];
            $result = $this->client->listObjects($data);
        } catch (ObsException $e) {
            return [];
        }

        $entries = $result['Contents']??[];

        return array_map(function ($entry) {
            $path = $this->removePathPrefix($entry['Key']);
            return $this->normalizeResponse($entry, $path);
        }, $entries);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $path = $this->applyPathPrefix($path);
        try {
            $object = $this->client->getObjectMetadata([
                'Bucket'    => $this->bucket,
                'Key'       => $path,
            ]);
        } catch (ObsException $e) {
            return false;
        }

        if (($object['ContentLength']??-1) < 0) {
            return false;
        }

        $metadata = [
            'Key'           => $path,
            'LastModified'  => $object['LastModified'],
            'Size'          => $object['ContentLength'],
        ];

        return $this->normalizeResponse($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return ['mimetype' => MimeType::detectByFilename($path)];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($path): string
    {
        $path = parent::applyPathPrefix($path);

        return trim($path, '/');
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $path
     * @param resource|string $contents
     * @param string $mode
     *
     * @return array|false file metadata
     */
    protected function upload(string $path, $contents, string $mode)
    {
        $path = $this->applyPathPrefix($path);

        try {
            $data = [];
            $data['Bucket'] = $this->bucket;
            $data['Key']    = $path;
            switch ($mode) {
                case self::RES_FILE:
                    $data['SourceFile'] = $contents;
                break;
                case self::RES_STREAM:
                    $data['Body'] = $contents;
                break;
            }
            $this->client->putObject($data);
        } catch (ObsException $e) {
            return false;
        }
        return true;
    }

    /**
     * 创建文件夹
     *
     * @param string $path
     * @return void
     */
    protected function mkdir(string $path)
    {
        $dir = \dirname($path);
        if ($dir && $dir !== '.') {
            return $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key'   => "$dir/"
            ]);
        }
        return [];
    }

    protected function normalizeResponse(array $response): array
    {
        $normalizedPath = ltrim($this->removePathPrefix($response['Key']), '/');

        $normalizedResponse = ['path' => $normalizedPath];

        if (isset($response['LastModified'])) {
            $normalizedResponse['timestamp'] = strtotime($response['LastModified']);
        }

        if (isset($response['Size'])) {
            $normalizedResponse['size'] = $response['Size'];
            $normalizedResponse['bytes'] = $response['Size'];
        }
        $type = (mb_substr($normalizedResponse['path'], -1, 1) === '/' ? 'dir' : 'file');
        $normalizedResponse['type'] = $type;
        return $normalizedResponse;
    }

    public function getTemporaryLink(string $path, int $expiration = 1800): string
    {
        $resp = $this->client->createSignedUrl([
            'Method' => 'GET',
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Expires' => $expiration
        ]);
        return $resp['SignedUrl']??'';
    }

    public function getTemporaryUrl(string $path, int $expiration = 1800): string
    {
        return $this->getTemporaryLink($path, $expiration);
    }

    public function getUrl(string $path): string
    {
        return $this->getTemporaryLink($path, 3600);
    }

    public function getVisibility($path)
    {
        return false;
    }
}
