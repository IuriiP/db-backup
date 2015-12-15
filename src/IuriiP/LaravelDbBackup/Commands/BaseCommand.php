<?php 

namespace IuriiP\LaravelDbBackup\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use IuriiP\LaravelDbBackup\DatabaseBuilder;
use IuriiP\LaravelDbBackup\ConsoleColors;
use IuriiP\LaravelDbBackup\Console;

class BaseCommand extends Command 
{
	/**
	 * @var IuriiP\LaravelDbBackup\DatabaseBuilder
	 */
	protected $databaseBuilder;

	/**
	 * @var IuriiP\LaravelDbBackup\ConsoleColors
	 */
	protected $colors;
	
	/**
	 * @var IuriiP\LaravelDbBackup\Console
	 */
	protected $console;

	/**
	 * @param IuriiP\LaravelDbBackup\DatabaseBuilder $databaseBuilder
	 * @return IuriiP\LaravelDbBackup\Commands\BaseCommand
	 */
	public function __construct(DatabaseBuilder $databaseBuilder)
	{
		parent::__construct();

		$this->databaseBuilder = $databaseBuilder;
		$this->colors = new ConsoleColors();
		$this->console = new Console();
	}

	/**
	 * @return IuriiP\LaravelDbBackup\Databases\DatabaseContract
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
