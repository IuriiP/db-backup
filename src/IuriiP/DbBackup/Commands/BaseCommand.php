<?php 

namespace IuriiP\DbBackup\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use IuriiP\DbBackup\DatabaseBuilder;
use IuriiP\DbBackup\ConsoleColors;
use IuriiP\DbBackup\Console;

class BaseCommand extends Command 
{
	/**
	 * @var IuriiP\DbBackup\DatabaseBuilder
	 */
	protected $databaseBuilder;

	/**
	 * @var IuriiP\DbBackup\ConsoleColors
	 */
	protected $colors;
	
	/**
	 * @var IuriiP\DbBackup\Console
	 */
	protected $console;

	/**
	 * @param IuriiP\DbBackup\DatabaseBuilder $databaseBuilder
	 * @return IuriiP\DbBackup\Commands\BaseCommand
	 */
	public function __construct(DatabaseBuilder $databaseBuilder)
	{
		parent::__construct();

		$this->databaseBuilder = $databaseBuilder;
		$this->colors = new ConsoleColors();
		$this->console = new Console();
	}

	/**
	 * @return IuriiP\DbBackup\Databases\DatabaseContract
	 */
	public function getDatabase($database)
	{
		$database = $database ? : Config::get('database.default');
		$realConfig = Config::get('database.connections.' . $database);

		return $this->databaseBuilder->getDatabase($realConfig);
	}
	
	/**
	 * @return string
	 */
	protected function getDumpsPath()
	{
		return Config::get('db-backup.path');
	}

	/**
	 * @return boolean
	 */
	public function enableCompression()
	{
		return Config::set('db-backup.compress', true);
	}

	/**
	 * @return boolean
	 */
	public function disableCompression()
	{
		return Config::set('db-backup.compress', false);
	}

	/**
	 * @return boolean
	 */
	public function isCompressionEnabled()
	{
		return Config::get('db-backup.compress');
	}

	/**
	 * @return boolean
	 */
	public function isCompressed($fileName)
	{
		return pathinfo($fileName, PATHINFO_EXTENSION) === "gz";
	}
}
