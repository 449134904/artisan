<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace migration\command;

use migration\Seeder;
use Phinx\Seed\AbstractSeed;
use Phinx\Util\Util;


abstract class Seed extends Command
{

    /**
     * @var array
     */
    protected $seeds;

    /**
     * @return string
     * @author : 小夏
     * @date   : 2021-04-28 16:22:58
     */
    protected function getPath(): string
    {
        return $this->getConfig('path', ROOT_PATH . 'database') . DS . 'seeds';
    }

    /**
     * @return Seeder[]|array
     * @author : 小夏
     * @date   : 2021-04-28 16:23:5
     */
    public function getSeeds(): array
    {
        if (null === $this->seeds) {
            $phpFiles = glob($this->getPath() . DS . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);
            // filter the files to only get the ones that match our naming scheme
            $fileNames = [];
            /** @var Seeder[] $seedsArr */
            $seedsArr = [];

            foreach ($phpFiles as $filePath) {
                if (Util::isValidSeedFileName(basename($filePath))) {
                    // convert the filename to a class name
                    $class             = pathinfo($filePath, PATHINFO_FILENAME);
                    $fileNames[$class] = basename($filePath);
                    // load the seed file
                    /** @noinspection PhpIncludeInspection */
                    require_once $filePath;
                    $this->throwNew(!class_exists($class), sprintf('Could not find class "%s" in file "%s"', $class, $filePath));
                    // instantiate it
                    $seed = new $class($this->input, $this->output);
                    $this->throwNew(!($seed instanceof AbstractSeed), sprintf('The class "%s" in file "%s" must extend \Phinx\Seed\AbstractSeed', $class, $filePath));
                    $seedsArr[$class] = $seed;
                }
            }

            ksort($seedsArr);
            $this->seeds = $seedsArr;
        }

        return $this->seeds;
    }

    public function throwNew($type, $message)
    {
        if ($type) {
            throw new \InvalidArgumentException($message);
        }
    }
}
