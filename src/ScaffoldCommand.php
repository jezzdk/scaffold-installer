<?php

namespace Jezzdk\Scaffold\Console;

use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use ZipArchive;

class ScaffoldCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Bedrock application with Sage')
            ->addArgument('project', InputArgument::REQUIRED, 'The directory name of the project')
            ->addArgument('theme', InputArgument::REQUIRED, 'The directory name of the theme')
            ->addOption('dist', 'd', InputOption::VALUE_OPTIONAL, 'Installs a specific release', '1.12.8')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! extension_loaded('zip')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        $name = $input->getArgument('project');

        $directory = $name && $name !== '.' ? getcwd().'/'.$name : getcwd();

        if (! $input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        $output->writeln('<info>Crafting application...</info>');

        $this->download($zipFile = $this->makeFilename(), $input->getOption('dist'))
             ->extract($zipFile, $directory)
             ->moveFiles($directory)
             ->cleanUp($zipFile);

        $composer = $this->findComposer();

        $commands = [
            $composer.' install',
            $composer.' run-script post-root-package-install',
            'composer create-project roots/sage web/app/themes/'.$input->getArgument('theme')
        ];

        if ($input->getOption('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value.' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                return $value.' --quiet';
            }, $commands);
        }

        $process = Process::fromShellCommandline(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if ($process->isSuccessful()) {
            $output->writeln('<comment>Application ready! Build something amazing.</comment>');
        }

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'docker-compose.yml', $directory.DIRECTORY_SEPARATOR.'docker-compose.yml');

        return 0;
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd().'/scaffold_'.md5(time().uniqid()).'.zip';
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @param  string  $version
     * @return $this
     */
    protected function download($zipFile, $version = 'master')
    {
        $response = (new Client)->get('https://github.com/roots/bedrock/archive/'.$version.'.zip');

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     * @return $this
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $response = $archive->open($zipFile, ZipArchive::CHECKCONS);

        if ($response === ZipArchive::ER_NOZIP) {
            throw new RuntimeException('The zip file could not download. Verify that you are able to access: https://github.com/roots/bedrock/archive/master.zip');
        }

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd().'/composer.phar';

        if (file_exists($composerPath)) {
            return '"'.PHP_BINARY.'" '.$composerPath;
        }

        return 'composer';
    }

    /**
     * Move files up one level
     *
     * @param string $directory
     * @return $this
     */
    protected function moveFiles($directory)
    {
        $filesystem = new Filesystem();

        if ($subDirectories = $filesystem->directories($directory)) {
            if (sizeof($subDirectories) === 1) {
                $subDirectory = array_pop($subDirectories);

                $filesystem->copyDirectory($subDirectory, $directory);

                $filesystem->deleteDirectory($subDirectory);
            }
        }

        return $this;
    }
}
