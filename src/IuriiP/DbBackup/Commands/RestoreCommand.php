<?php 

namespace IuriiP\DbBackup\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;

class RestoreCommand extends BaseCommand 
{

	/**
	 * @var string
	 */
	protected $name = 'db:restore';
	protected $description = 'Restore a dump from `app/storage/dumps`';
	protected $database;

	/**
	 * @return void
	 */
	public function fire()
	{		
		$this->database = $this->getDatabase( $this->input->getOption('database') );

		$fileName = $this->argument('dump');

		if ( $this->option('last-dump') )
		{
			$fileName = $this->lastBackupFile();

			if ( ! $fileName )
			{
				return $this->line(
					$this->colors->getColoredString("\n".'No backups have been created.'."\n",'red')
				);
			}
		}
		
		if ( $fileName )
		{
			return $this->restoreDump($fileName);
		}

		$this->listAllDumps();
	}

	/**
	 * @param string $fileName
	 * @return void
	 */
	protected function restoreDump($fileName)
	{
		$sourceFile = $this->getDumpsPath() . $fileName;

		if ( $this->isCompressed($sourceFile) )
		{
			$sourceFile = $this->uncompress($sourceFile);
		}

		$status = $this->database->restore( $this->getUncompressedFileName( $sourceFile ) );
		
		if ( $this->isCompressed($sourceFile) )
		{
			$this->uncompressCleanup($this->getUncompressedFileName($sourceFile));
		}

		if ($status === true)
		{
			return $this->line(
				sprintf($this->colors->getColoredString("\n".'%s was successfully restored.'."\n",'green'), $fileName)
			);
		}

		$this->line(
			$this->colors->getColoredString("\n".'Database restore failed.'."\n",'red')
		);
	}

	/**
	 * @return void
	 */
	protected function listAllDumps()
	{
		$finder = new Finder();
		$finder->files()->in( $this->getDumpsPath() );

		if ( $finder->count() === 0 )
		{
			return $this->line(
				$this->colors->getColoredString("\n".'You haven\'t saved any dumps.'."\n",'brown')
			);
		}

		$this->line($this->colors->getColoredString("\n".'Please select one of the following dumps:'."\n",'white'));

		$finder->sortByName();
		$count = count($finder);

		$i=0;
		foreach ($finder as $dump)
		{
			$i++;
			$fileName = $dump->getFilename();
			if( $i === ( $count-1 ) ) $fileName .= "\n";

			$this->line( $this->colors->getColoredString( $fileName ,'brown') );
		}
	}

	/** 
	 * Uncompress a GZip compressed file
	 * 
	 * @param string $fileName      Relative or absolute path to file
	 * @return string               Name of uncompressed file (without .gz extension)
	 */ 
	protected function uncompress($fileName)
	{
		$fileNameUncompressed = $this->getUncompressedFileName($fileName);
		$command = sprintf('gzip -dc %s > %s', $fileName, $fileNameUncompressed);
		if ($this->console->run($command) !== true)
		{
			$this->line($this->colors->getColoredString("\n".'Uncompress of gzipped file failed.'."\n",'red'));
		}

		return $fileNameUncompressed;
	}

	/**
	 * Remove uncompressed files 
	 * 
	 * Files are temporarily uncompressed for usage in restore. We do not need these copies
	 * permanently.
	 * 
	 * @param string $fileName      Relative or absolute path to file
	 * @return boolean              Success or failure of cleanup
	 */ 
	protected function cleanup($fileName)
	{
		$status = true;
		$fileNameUncompressed = $this->getUncompressedFileName($fileName);
		if ($fileName !== $fileNameUncompressed)
		{
			$status = File::delete($fileName);
		}

		return $status;
	}

	/**
	 * Retrieve filename without Gzip extension
	 * 
	 * @param string $fileName      Relative or absolute path to file
	 * @return string               Filename without .gz extension
	 */ 
	protected function getUncompressedFileName($fileName)
	{
		return preg_replace('"\.gz$"', '', $fileName);
	}

	/**
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['dump', InputArgument::OPTIONAL, 'Filename of the dump']
		];
	}

	/**
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to restore to'],
			['last-dump', true, InputOption::VALUE_NONE, 'The last dump stored'],
		];
	}

	/**
	 * @return string
	 */
	private function lastBackupFile()
	{
		$finder = new Finder();
		$finder->files()->in( $this->getDumpsPath() );

		$lastFileName = '';

		foreach ($finder as $dump)
		{
			$filename = $dump->getFilename();
			$filenameWithoutExtension = $this->filenameWithoutExtension( $filename );
			if ( (int) $filenameWithoutExtension > (int) $this->filenameWithoutExtension( $lastFileName ) )
			{
				$lastFileName = $filename;
			}
		}

		return $lastFileName;
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	private function filenameWithoutExtension( $filename )
	{
		return preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filename );
	}
}
