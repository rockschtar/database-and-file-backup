<?php

namespace ValidIO\DatabaseAndFileBackup\Backup;

use Carbon\Carbon;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use ValidIO\DatabaseAndFileBackup\Backup;

class WebDAV extends Backup
{

    private function filesystem() : Filesystem
    {
        $client = new Client(
            [
                'baseUri' => $this->options['baseUri'],
                'userName' => $this->options['userName'],
                'password' => $this->options['password']
            ]
        );

        $adapter = new WebDAVAdapter($client);
        return new Filesystem($adapter);
    }

    protected function uploadToRemote(): void
    {
        $path = $this->config->getRemoteBackupPath() . DIRECTORY_SEPARATOR . $this->config->getFilename();
        $this->filesystem()->put($path, file_get_contents($this->backup_file));
    }

    protected function cleanupRemote(): void
    {
        if ($this->config->getKeepLastFiles() === null) {
            return;
        }

        $filesystem = $this->filesystem();

        $files = $filesystem->listContents($this->config->getRemoteBackupPath());

        foreach ($files as $file) {

            $created = $file['timestamp'];
            $carbon_created = Carbon::createFromTimestamp($created);
            $timestamp_created = $carbon_created->getTimestamp();

            $objects_sorted[] = array('object'    => $file,
                'timestamp' => $timestamp_created,
                'date'      => $carbon_created->format('d.m.Y - H:i:s'));

        }

        usort($objects_sorted, static function ($a, $b) {
            return (int)$b['timestamp'] <=> (int)$a['timestamp'];
        });

        $objects_to_delete = \array_slice($objects_sorted, $this->config->getKeepLastFiles(), \count($objects_sorted));

        foreach ($objects_to_delete as $object_to_delete) {
            $filesystem->delete($object_to_delete['object']['path']);
        }
    }
}
