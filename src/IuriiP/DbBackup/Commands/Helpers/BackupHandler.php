<?php namespace IuriiP\DbBackup\Commands\Helpers;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use AWS;
use Config;
use File;

use IuriiP\DbBackup\Commands\Helpers\BackupFile;
use IuriiP\DbBackup\ConsoleColors;

class BackupHandler {

	/**
	 * @var IuriiP\DbBackup\ConsoleColors
	 */
	protected $colors;

	/**
	 * @param IuriiP\DbBackup\ConsoleColors $colors
	 * @return IuriiP\DbBackup\Commands\Helpers\BackupHandler
	 */
	public function __construct( $colors )
	{
		$this->colors = $colors;
	}

	/**
	 * @param boolean $status
	 * @return string
	 */
	public function errorResponse( $status )
	{
		return $this->consoleResponse( 'Database backup failed. %s', $status, 'red' );
	}

	/**
	 * @param string $filenameArg
	 * @param string $filePath
	 * @param string $fileName
	 * @return string
	 */
	public function dumpResponse( $filenameArg, $filePath, $fileName )
	{
		$message = 'Database backup was successful. %s was saved in the dumps folder';
		$param = $fileName;

		if ( $filenameArg )
		{
			$message = 'Database backup was successful. Saved to %s';
			$param = $filePath;
		}

		return $this->consoleResponse( $message, $param );
	}

	/**
	 * @return string
	 */
	public function s3DumpResponse()
	{
		return $this->consoleResponse( 'Upload complete.' );
	}

	/**
	 * @return string
	 */
	public function localDumpRemovedResponse()
	{
		return $this->consoleResponse( 'Removed dump as it\'s now stored on S3.' );
	}

	/**
	 * @param string $message
	 * @param mixed $param
	 * @param string $color
	 * @return string
	 */ 
	private function consoleResponse( $message, $param = null, $color = 'green' )
	{
		$coloredString = $this->colors->getColoredString( "\n" . $message . "\n", $color );

		return $param ? sprintf( $coloredString, $param ) : $coloredString;
	}
}
