# Database and File Backup

Simple MySQL & file backup library for

Supports [Google Cloud Storage](https://cloud.google.com/storage/) & FTP/SFTP

Contributors
-------------
* [Stefan Helmer](https://gitlab.validio.de/stefan)

Prerequisites
-------------
 * PHP7.1 or greater
 * Composer (http://getcomposer.org)

## Installing

Add ValidIO Custom Repository to your composer.json file:

```
"repositories": [
    {
      "type": "composer",
      "url": "https://satis.validio.de"
    }
```

Add library to your project
```
composer require validio/database-and-file-backup
```

Example MySQL & file backup to Google Cloud


```php
    $paths = [];
    $paths[] = realpath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads');
    $paths[] = realpath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins');

    $options = [];
    $options['bucket'] = 'some-bucket-name';
    $options['projectId'] = 'my-project-id';
    $options['keyFilePath'] = \dirname(__DIR__, 6) . DIRECTORY_SEPARATOR . 'google-service-account.json';

    $config = Config::create();
    $config->setPaths($paths);
    $config->setRemoteBackupPath(gethostname() . DIRECTORY_SEPARATOR . $home_url['host']);
    $config->setLocalBackupPath(\dirname(__DIR__, 6) . DIRECTORY_SEPARATOR . uniqid('backup', false));
    $backup = new GoogleCloudStorage($config, $options);
    try {
        $backup->doBackup();
    } catch(BackupException $e) {
      // do something
    }
```

## Built With

* [PHP](http://php.net) - Programming language
* [Composer](https://getcomposer.org/) - Dependency Management

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://gitlab.validio.de/validio/database-and-file-backup/tags). 
