<?php
/**
 * @author: StefanHelmer
 */

namespace ValidIO\DatabaseAndFileBackup\Backup;


use Carbon\Carbon;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use ValidIO\DatabaseAndFileBackup\Backup;
use ValidIO\DatabaseAndFileBackup\Exceptions\BackupException;


/**
 * Class SFTP
 * @desc See http://flysystem.thephpleague.com/docs/adapter/sftp/ for documentation
 * @package ValidIO\DatabaseAndFileBackup\Backup
 */
class SFTP extends Backup {

    /**
     * @throws BackupException
     */
    protected function uploadToRemote(): void {

        $default_options = ['host'          => 'example.com',
                            'port'          => 22,
                            'username'      => 'username',
                            'password'      => 'password',
                            'privateKey'    => 'path/to/or/contents/of/privatekey',
                            'root'          => '/path/to/root',
                            'timeout'       => 10,
                            'directoryPerm' => 0755];

        $adapter = new SftpAdapter($this->options);
        $filesystem = new Filesystem($adapter);
        $path = $this->config->getRemoteBackupPath() . DIRECTORY_SEPARATOR . $this->config->getFilename();

        $filesystem->put($path, fopen($this->backup_file, 'rb'));

    }

    /**
     * @throws FileNotFoundException
     */
    protected function cleanupRemote(): void {
        if ($this->config->getKeepLastFiles() === null) {
            return;
        }
        $adapter = new SftpAdapter($this->options);
        $filesystem = new Filesystem($adapter);

        $files = $filesystem->listContents($this->config->getRemoteBackupPath());

        foreach ($files as $file) {

            $created = $file['timestamp'];
            $carbon_created = Carbon::createFromTimestamp($created);
            $timestamp_created = $carbon_created->getTimestamp();

            $objects_sorted[] = array('object'    => $file,
                                      'timestamp' => $timestamp_created,
                                      'date'      => $carbon_created->format('d.m.Y - H:i:s'));

        }

        usort($objects_sorted, function ($a, $b) {
            return (int)$b['timestamp'] <=> (int)$a['timestamp'];
        });

        $objects_to_delete = \array_slice($objects_sorted, $this->config->getKeepLastFiles(), \count($objects_sorted));

        foreach ($objects_to_delete as $object_to_delete) {
            $filesystem->delete($object_to_delete['object']['path']);
        }

    }
}