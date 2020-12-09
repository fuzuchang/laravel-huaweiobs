# Laravel-HuaWeiOBS

An easy way to use the [official php sdk client](https://support.huaweicloud.com/sdk-php-devg-obs/obs_28_0100.html) in your Laravel or Lumen applications.

- [Installation and Configuration](#installation-and-configuration)
  - [Laravel](#laravel)
    - [Alternative configuration method via .env file](#alternative-configuration-method-via-env-file)
  - [Lumen](#lumen)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [Bugs, Suggestions and Support](#bugs-suggestions-and-support)
- [Copyright and License](#copyright-and-license)



## Installation and Configuration

Install the current version of the `goodgay/huaweiobs` package via composer:

```sh
composer require goodgay/huaweiobs
```

### Laravel

The package's service provider will automatically register its service provider.

Publish the configuration file:

```sh
php artisan vendor:publish --provider="Goodgay\HuaweiOBS\HWOBSServiceProvider"
```

##### Alternative configuration method via .env file

After you publish the configuration file as suggested above, you may configure OBS
by adding the following to your application's `.env` file (with appropriate values):
  
```ini
HWOBS_KEY=
HWOBS_SECRET=
HWOBS_ENDPOINT=
HWOBS_BUCKET=
```


### Lumen

If you work with Lumen, please register the service provider and configuration in `bootstrap/app.php`:

```php
$app->register(Goodgay\HuaweiOBS\HWOBSServiceProvider::class);
$app->configure('hwobs');

```

Manually copy the configuration file to your application.



## Usage

The `HWobs` facade is just an entry point into the [php-obs sdk](https://github.com/huaweicloud/huaweicloud-sdk-php-obs),
so previously you might have used:

```php

use ObsV3\ObsClient;
$obsClient = ObsClient::factory ( [
		'key' => $ak,
		'secret' => $sk,
		'endpoint' => $endpoint,
		'socket_timeout' => 30,
		'connect_timeout' => 10
] );

$resp = $obsClient -> listObjects(['Bucket' => $bucketName]);
foreach ( $resp ['Contents'] as $content ) {
    printf("\t%s etag[%s]\n", $content ['Key'], $content ['ETag']);
}
printf("\n");
    
```

You can now replace those last two lines with simply:

```php
use Goodgay\HuaweiOBS\HWobs;

$return = HWobs::all();

//or

$return = HWobs::obs()->listObjects(['Bucket' => $bucketName]);
```

Lumen users who wish to use Facades can do so by editing the 
`bootstrap/app.php` file to include the following:

```php
$app->withFacades(true,[
     Goodgay\HuaweiOBS\HWobs::class  => 'Hwobs'
]);
```



## Advanced Usage

Because the package is a wrapper around the official php-obs sdk, you can 
do pretty much anything with this package. 

To upload:

```php
$resp = HWobs::putText("object-name","some content");
$resp = HWobs::putFile("object-name","./some.txt");
```

To download:

```php
$resp = HWobs::getText("object-name");
$resp = HWobs::getStream("object-name");
$resp = HWobs::getFile("object-name",'save_path.txt');
```

To manage objects:

```php
$resp = HWobs::getMetadata("object-name");
$resp = HWobs::delete("object-name");
$resp = HWobs::all();
$resp = HWobs::deleteMulti(['object-name1','object-name2']);
```




## Bugs, Suggestions and Support

Special thanks to 
[Visual Studio Code](https://code.visualstudio.com/?from=goodgay/huaweiobs) for their 
Open Source License Program ... and the excellent IDE, of course!

Please use [Github](https://github.com/fuzuchang/laravel-huaweiobs) for reporting bugs, 
and making comments or suggestions.
 

## Copyright and License

[laravel-huaweiobs](https://github.com/fuzuchang/laravel-huaweiobs)
was written by [fuzuchang](https://github.com/fuzuchang/laravel-huaweiobs) and is released under the 
[MIT License](LICENSE.md).

Copyright (c) 2020 fuzuchang