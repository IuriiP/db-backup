<?php 

namespace IuriiP\DbBackup\Databases;

use IuriiP\DbBackup\Databases\DatabaseContract;
use IuriiP\DbBackup\Console;

class PostgresDatabase implements DatabaseContract 
{	
	/**
	 * @var IuriiP\DbBackup\Console
	 */
	protected $console;

	/**
	 * @var string
	 */
	protected $database;
	protected $user;
	protected $password;
	protected $host;

	/**
	 * @param IuriiP\DbBackup\Console $destinationFile
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param string $host
	 * @return IuriiP\DbBackup\Database\PostgresDatabase
	 */
	public function __construct(Console $console, $database, $user, $password, $host)
	{
		$this->console = $console;
		$this->database = $database;
		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
	}

	/**
	 * Create a database dump
	 * 
	 * @param string $destinationFile
	 * @return boolean
	 */
	public function dump($destinationFile)
	{
		$command = sprintf('PGPASSWORD=%s pg_dump -Fc --no-acl --no-owner -h %s -U %s %s > %s',
			escapeshellarg($this->password),
			escapeshellarg($this->host),
			escapeshellarg($this->user),
			escapeshellarg($this->database),
			escapeshellarg($destinationFile)
		);

		return $this->console->run($command);
	}
	
	/**
	 * Restore a database dump
	 * 
	 * @param string $sourceFile
	 * @return boolean
	 */
	public function restore($sourceFile)
	{
		$command = sprintf('PGPASSWORD=%s pg_restore --verbose --clean --no-acl --no-owner -h %s -U %s -d %s %s',
			escapeshellarg($this->password),
			escapeshellarg($this->host),
			escapeshellarg($this->user),
			escapeshellarg($this->database),
			escapeshellarg($sourceFile)
		);

		return $this->console->run($command);
	}
	
	/**
	 * @return string
	 */
	public function getFileExtension()
	{
		return 'dump';
	}
}
