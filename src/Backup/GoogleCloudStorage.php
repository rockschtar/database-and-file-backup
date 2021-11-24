<?php
/**
 * @author: StefanHelmer
 */

namespace ValidIO\DatabaseAndFileBackup\Backup;


use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use ValidIO\DatabaseAndFileBackup\Backup;

class GoogleCloudStorage extends Backup {

    /**
     * @var StorageClient
     */
    private $storage_client;

    protected function uploadToRemote(): void {

        $this->storage_client = new StorageClient(['projectId'   => $this->options['projectId'],
                                                   'keyFilePath' => $this->options['keyFilePath']]);

        $bucket = $this->storage_client->bucket($this->options['bucket']);

        $options = [];

        if ($this->config->getRemoteBackupPath() !== null) {
            $options['name'] = $this->config->getRemoteBackupPath() . DIRECTORY_SEPARATOR . $this->config->getFilename();
        }

        $bucket->upload(fopen($this->backup_file, 'rb'), $options);
    }

    protected function cleanupRemote(): void {
        if ($this->config->getKeepLastFiles() === null) {
            return;
        }

        $bucket = $this->storage_client->bucket($this->options['bucket']);

        $objects = $bucket->objects(['prefix' => $this->config->getRemoteBackupPath()]);

        $objects_sorted = [];

        foreach ($objects as $object) {
            /* @var $object StorageObject */
            $created = $object->info()['timeCreated'];
            $carbon_created = Carbon::parse($created);
            $timestamp_created = $carbon_created->getTimestamp();

            $objects_sorted[] = array('object'    => $object,
                                      'timestamp' => $timestamp_created,
                                      'date'      => $carbon_created->format('d.m.Y - H:i:s'));

        }

        usort($objects_sorted, function ($a, $b) {
            return (int)$b['timestamp'] <=> (int)$a['timestamp'];
        });
        $objects_to_delete = \array_slice($objects_sorted, $this->config->getKeepLastFiles(), \count($objects_sorted));

        foreach ($objects_to_delete as $object_to_delete) {
            $object = $object_to_delete['object'];
            /* @var $object StorageObject */
            $object->delete();
        }
    }
}