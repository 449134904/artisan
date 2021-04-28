<?php


namespace xia\migration\command;


use InvalidArgumentException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\ProxyAdapter;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\MigrationInterface;
use Phinx\Util\Util;
use think\console\input\Option as InputOption;
use think\Exception;


abstract class Migrate extends Command
{
    /**
     * @var array
     */
    protected $migrations;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addOption('--config', null, InputOption::VALUE_REQUIRED, 'The database config name', 'database');
    }

    /**
     * @param \think\console\Input $input
     * @param \think\console\Output $output
     * @author : 小夏
     * @date   : 2021-04-27 14:57:49
     */
    protected function initialize(\think\console\Input $input, \think\console\Output $output)
    {
        $this->database = $input->getOption('config');
    }

    /**
     * @return string
     * @author : 小夏
     * @date   : 2021-04-28 16:02:21
     */
    protected function getPath(): string
    {
        return $this->getConfig('path', ROOT_PATH . 'database') . DS . 'migrations' . ($this->database !== 'database' ? DS . $this->database : '');
    }

    /**
     * @param MigrationInterface $migration
     * @param string $direction
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-04-28 16:02:17
     */
    protected function executeMigration(MigrationInterface $migration, string $direction = MigrationInterface::UP)
    {
        $this->output->writeln('');
        $this->output->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . ($direction === MigrationInterface::UP ? 'migrating' : 'reverting') . '</comment>');

        // Execute the migration and log the time elapsed.
        $start = microtime(true);

        $startTime = time();
        $direction = ($direction === MigrationInterface::UP) ? MigrationInterface::UP : MigrationInterface::DOWN;
        $migration->setAdapter($this->getAdapter());

        // begin the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->beginTransaction();
        }

        // Run the migration
        if (method_exists($migration, MigrationInterface::CHANGE)) {
            if ($direction === MigrationInterface::DOWN) {
                // Create an instance of the ProxyAdapter so we can record all
                // of the migration commands for reverse playback
                /** @var ProxyAdapter $proxyAdapter */
                $proxyAdapter = AdapterFactory::instance()->getWrapper('proxy', $this->getAdapter());
                $migration->setAdapter($proxyAdapter);
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $migration->change();
                $proxyAdapter->executeInvertedCommands();
                $migration->setAdapter($this->getAdapter());
            } else {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $migration->change();
            }
        } else {
            $migration->{$direction}();
        }

        // commit the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->commitTransaction();
        }

        // Record it in the database
        $this->getAdapter()
            ->migrated($migration, $direction, date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', time()));

        $end = microtime(true);

        $this->output->writeln(' ==' . ' <info>' . $migration->getVersion() . ' ' . $migration->getName() . ':</info>' . ' <comment>' . ($direction === MigrationInterface::UP ? 'migrated' : 'reverted') . ' ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    /**
     * @return array
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-04-28 16:01:43
     */
    protected function getVersionLog(): array
    {
        return $this->getAdapter()->getVersionLog();
    }

    /**
     * @return array
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-04-28 16:01:52
     */
    protected function getVersions(): array
    {
        return $this->getAdapter()->getVersions();
    }

    /**
     * @return Migrator[]|array
     * @author : 小夏
     * @date   : 2021-04-28 16:01:55
     */
    protected function getMigrations(): array
    {
        if (null === $this->migrations) {
            $phpFiles = glob($this->getPath() . DS . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);

            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var Migrator[] $versions */
            $versions = [];

            foreach ($phpFiles as $filePath) {

                if (Util::isValidMigrationFileName(basename($filePath))) {
                    $version = Util::getVersionFromFileName(basename($filePath));
                    $this->throwNew(isset($versions[$version]), isset($versions[$version]) ? sprintf('Duplicate migration - "%s" has the same version as "%s"', $filePath, $versions[$version]->getVersion()) : '');
                    // convert the filename to a class name
                    $class = Util::mapFileNameToClassName(basename($filePath));
                    $this->throwNew(isset($fileNames[$class]), sprintf('Migration "%s" has the same name as "%s"', basename($filePath), $fileNames[$class]));


                    $fileNames[$class] = basename($filePath);

                    // load the migration file
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    $this->throwNew(!class_exists($class), sprintf('Could not find class "%s" in file "%s"', $class, $filePath));


                    $this->input  = new Input();
                    $this->output = new Output();

                    $migration = new $class('production', $version, $this->input, $this->output);
                    $this->throwNew(!($migration instanceof AbstractMigration), sprintf('The class "%s" in file "%s" must extend \Phinx\Migration\AbstractMigration', $class, $filePath));

                    $versions[$version] = $migration;
                }
            }

            ksort($versions);
            $this->migrations = $versions;
        }

        return $this->migrations;
    }

    public function throwNew($type, $message)
    {
        if ($type) {
            throw new InvalidArgumentException($message);
        }
    }
}

