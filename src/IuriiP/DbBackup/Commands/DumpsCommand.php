<?php namespace IuriiP\DbBackup\Commands;

use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;

class DumpsCommand extends BaseCommand {

    /**
     * @var string
     */
    protected $name = 'db:dumps';
    protected $description = 'List of dumps from `app/storage/dumps`';
    protected $database;

    /**
     * @return void
     */
    public function fire() {
        $this->database = $this->getDatabase($this->input->getOption('database'));

        $this->listAllDumps();
    }

    /**
     * @return void
     */
    protected function listAllDumps() {
        $finder = new Finder();
        $finder->files()->in($this->getDumpsPath());

        if ($finder->count() === 0) {
            return $this->line(
                            $this->colors->getColoredString("\n" . 'You haven\'t saved any dumps.' . "\n", 'brown')
            );
        }

        $finder->sortByName();
        $count = count($finder);

        $i = 0;
        foreach ($finder as $dump) {
            $i++;
            $fileName = $dump->getFilename();
            if ($i === ( $count - 1 )) {
                $fileName .= "\n";
            }

            $this->line($this->colors->getColoredString($fileName, 'brown'));
        }
    }

    /**
     * Retrieve filename without Gzip extension
     * 
     * @param string $fileName      Relative or absolute path to file
     * @return string               Filename without .gz extension
     */
    protected function getUncompressedFileName($fileName) {
        return preg_replace('"\.gz$"', '', $fileName);
    }

    /**
     * @return array
     */
    protected function getArguments() {
        return [
        ];
    }

    /**
     * @return array
     */
    protected function getOptions() {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to restore to'],
        ];
    }

}
