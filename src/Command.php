<?php


namespace xia\migration;


use InvalidArgumentException;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Adapter\AdapterInterface;
use think\Config;
use think\Db;
use think\Exception;

abstract class Command extends \think\console\Command
{
    protected $database = 'database';

    /**
     * @return AdapterInterface
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-04-28 14:01:28
     */
    public function getAdapter(): AdapterInterface
    {

        if (isset($this->adapter)) {
            return $this->adapter;
        }

        $options = $this->getDbConfig();

        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        $this->adapter = $adapter;

        return $adapter;
    }

    /**
     * 获取数据库配置
     * @return array
     * @throws Exception
     * @author : 小夏
     * @date   : 2021-04-27 10:52:15
     */
    protected function getDbConfig(): array
    {
        $config = Db::connect($this->database)->getConfig();

        if ($config['deploy'] == 0) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'host'         => $config['hostname'],
                'name'         => $config['database'],
                'user'         => $config['username'],
                'pass'         => $config['password'],
                'port'         => $config['hostport'],
                'charset'      => $config['charset'],
                'table_prefix' => $config['prefix'],
                'version_order'=>$config['version_order'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'host'         => explode(',', $config['hostname'])[0],
                'name'         => explode(',', $config['database'])[0],
                'user'         => explode(',', $config['username'])[0],
                'pass'         => explode(',', $config['password'])[0],
                'port'         => explode(',', $config['hostport'])[0],
                'charset'      => explode(',', $config['charset'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
                'version_order' => explode(',', $config['version_order'])[0],
            ];
        }

        $dbConfig['default_migration_table'] = $this->getConfig('table', $dbConfig['table_prefix'] . 'migrations');

        return $dbConfig;
    }

    protected function getConfig($name, $default = null)
    {
        $config = Config::get('migration');
        return $config[$name] ?? $default;
    }

    protected function verifyMigrationDirectory($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" does not exist', $path));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf('Migration directory "%s" is not writable', $path));
        }
    }
}
