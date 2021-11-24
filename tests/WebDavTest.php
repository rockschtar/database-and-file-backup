<?php

namespace ValidIO\DatabaseAndFileBackup\Tests;


use PHPUnit\Framework\TestCase;
use ValidIO\DatabaseAndFileBackup\Backup\WebDAV;
use ValidIO\DatabaseAndFileBackup\Models\Config;

class WebDavTest extends TestCase
{

    public function testBackup(): void
    {
        $config = Config::create();
        $config->setFilename('backup.zip');
        $config->setLocalBackupPath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test-backup-', true));
        $config->setPaths([__DIR__ . '/test-data']);
        $config->setRemoteBackupPath('WebDavBackup');


        $webDavBackup->doBackup();
    }

}
