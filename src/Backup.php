<?php
/**
 * @author: StefanHelmer
 */

namespace ValidIO\DatabaseAndFileBackup;


use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use ValidIO\DatabaseAndFileBackup\Exceptions\BackupException;
use ValidIO\DatabaseAndFileBackup\Models\Config;
use ZipArchive;

abstract class Backup {

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var array
     */
    protected array $options;

    /**
     * @var string
     */
    protected string $backup_file;

    /**
     * @var \ZipArchive
     */
    protected ZipArchive $backup_zip;

    /**
     * WordPressGoogleCloudStorageBackup constructor.
     * @param Config $config
     * @param array $options
     */
    public function __construct(Config $config, array $options = []) {
        $this->config = $config;
        $this->options = $options;
    }

    /**
     * @throws BackupException
     */
    public function doBackup(): void {
        try {
            $this->bootstrap();
            $this->doFileBackup();
            $this->doDatabaseBackup();
            $this->finalize();
            $this->uploadToRemote();
            $this->cleanupRemote();
        } catch (Exception $exception) {
            throw new BackupException($exception);
        } finally {
            $this->cleanupLocal();
        }
    }

    /**
     *
     * @throws \RuntimeException
     */
    private function bootstrap(): void {

        if (!file_exists($this->config->getLocalBackupPath()) && !mkdir($this->config->getLocalBackupPath(), 0777, true) && !is_dir($this->config->getLocalBackupPath())) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->config->getLocalBackupPath()));
        }

        $this->backup_file = $this->config->getLocalBackupPath() . DIRECTORY_SEPARATOR . $this->config->getFilename();
        $this->backup_zip = new ZipArchive();
        $this->backup_zip->open($this->backup_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    }

    private function doFileBackup(): void {

        if (!$this->config->isBackupFiles()) {
            return;
        }

        $paths = $this->config->getPaths();

        foreach ($paths as $path) {
            $this->doPathBackup($path);
        }


    }

    /**
     * @param string $path
     */
    private function doPathBackup(string $path): void {
        /** @var \SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::LEAVES_ONLY);

        $base_path = $path;
        $base_dir = basename($path);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $target_path = $base_dir . str_replace($path, '', $filePath);
                $this->backup_zip->addFile($filePath, $target_path);
            }
        }
    }

    private function doDatabaseBackup(): void {

        if (!$this->config->isBackupDatabase()) {
            return;
        }

        $mysql = MySql::create()
                      ->setDbName($this->config->getDbName())
                      ->setUserName($this->config->getDbUser())
                      ->setPassword($this->config->getDbPassword());

        if ($this->config->getDbHost() !== null) {
            $mysql->setHost($this->config->getDbHost());
        }

        $filename = $this->config->getDbName() . '.sql.gz';
        $db_backup_file = $this->config->getLocalBackupPath() . DIRECTORY_SEPARATOR . $filename;
        $mysql->useCompressor(new GzipCompressor());
        $mysql->dumpToFile($db_backup_file);
        $this->backup_zip->addFile($db_backup_file, $filename);
    }

    /**
     *
     */
    private function finalize(): void {
        $this->backup_zip->close();
    }

    abstract protected function uploadToRemote(): void;

    abstract protected function cleanupRemote(): void;

    /**
     * @param null $target
     */
    private function cleanupLocal($target = null): void {

        if (empty($target)) {
            $target = $this->config->getLocalBackupPath();
        }

        if (is_dir($target)) {
            $files = glob($target . DIRECTORY_SEPARATOR . '*', GLOB_MARK);

            foreach ($files as $file) {
                $this->cleanupLocal($file);
            }

            rmdir($target);
        } elseif (is_file($target)) {
            unlink($target);
        }

    }
}
