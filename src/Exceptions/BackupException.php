<?php
/**
 * @author: StefanHelmer
 */

namespace ValidIO\DatabaseAndFileBackup\Exceptions;

class BackupException extends \Exception {

    /**
     * GoogleCloudStorageBackupException constructor.
     * @param \Throwable|null $previous
     */
    public function __construct(\Throwable $previous = null) {
        $message = $previous === null ? '' : $previous->getMessage();
        parent::__construct($message, 0, $previous);
    }
}