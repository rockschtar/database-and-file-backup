<?php
/**
 * @author: StefanHelmer
 */

namespace ValidIO\DatabaseAndFileBackup\Models;

class Config {

    /**
     * @var array
     */
    private $paths = [];
    /**
     * @var int
     */
    private $keep_last_files = null;
    /**
     * @var string|null
     */
    private $remote_backup_path;
    /**
     * @var string
     */
    private $local_backup_path;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var bool
     */
    private $backup_files = true;
    /**
     * @var bool
     */
    private $backup_database = true;
    /**
     * @var string|null
     */
    private $db_name;
    /**
     * @var string|null
     */
    private $db_user;
    /**
     * @var string|null
     */
    private $db_password;
    /**
     * @var string|null
     */
    private $db_host;

    private function __construct() {

        if(\defined('DB_NAME')) {
            $this->db_name = DB_NAME;
        }

        if(\defined('DB_USER')) {
            $this->db_user = DB_USER;
        }

        if(\defined('DB_PASSWORD')) {
            $this->db_password = DB_PASSWORD;
        }

        if(\defined('DB_HOST')) {
            $this->db_host = DB_HOST;
        }
        $datetime = new \DateTime();
        $this->filename = gethostname() . '-' . $datetime->format('d-m-Y_H-i-s') . '.zip';
        $this->local_backup_path = sys_get_temp_dir();
    }

    public static function create(): Config {
        return new self();
    }

    /**
     * @return array
     */
    public function getPaths(): array {
        return $this->paths;
    }

    /**
     * @param array $paths
     * @return Config
     */
    public function setPaths(array $paths): Config {
        $this->paths = $paths;
        return $this;
    }

    /**
     * @return int
     */
    public function getKeepLastFiles(): ?int {
        return $this->keep_last_files;
    }

    /**
     * @desc Set how many backup files should kept
     * @param int $keep_last_files
     * @return Config
     */
    public function setKeepLastFiles(int $keep_last_files): Config {
        $this->keep_last_files = $keep_last_files;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDbHost(): ?string {
        return $this->db_host;
    }

    /**
     * @param null|string $db_host
     * @return Config
     */
    public function setDbHost(?string $db_host): Config {
        $this->db_host = $db_host;
        return $this;
    }

    /**
     * @desc The backup file filename
     * @return string
     */
    public function getFilename(): string {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return Config
     */
    public function setFilename(string $filename): Config {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRemoteBackupPath(): ?string {
        return $this->remote_backup_path;
    }

    /**
     * @param null|string $remote_backup_path
     * @return Config
     */
    public function setRemoteBackupPath(?string $remote_backup_path): Config {
        $this->remote_backup_path = $remote_backup_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalBackupPath(): string {
        return $this->local_backup_path;
    }

    /**
     * @param string $local_backup_path
     * @return Config
     */
    public function setLocalBackupPath(string $local_backup_path): Config {
        $this->local_backup_path = $local_backup_path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBackupFiles(): bool {
        return $this->backup_files;
    }

    /**
     * @desc Enable or disable file backup
     * @param bool $backup_files
     * @return Config
     */
    public function setBackupFiles(bool $backup_files): Config {
        $this->backup_files = $backup_files;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBackupDatabase(): bool {
        return $this->backup_database;
    }

    /**
     * @desc Enable or disable database backup
     * @param bool $backup_database
     * @return Config
     */
    public function setBackupDatabase(bool $backup_database): Config {
        $this->backup_database = $backup_database;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDbName(): ?string {
        return $this->db_name;
    }

    /**
     * @param null|string $db_name
     * @return Config
     */
    public function setDbName(?string $db_name): Config {
        $this->db_name = $db_name;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDbUser(): ?string {
        return $this->db_user;
    }

    /**
     * @param null|string $db_user
     * @return Config
     */
    public function setDbUser(?string $db_user): Config {
        $this->db_user = $db_user;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDbPassword(): ?string {
        return $this->db_password;
    }

    /**
     * @param null|string $db_password
     * @return Config
     */
    public function setDbPassword(?string $db_password): Config {
        $this->db_password = $db_password;
        return $this;
    }

}