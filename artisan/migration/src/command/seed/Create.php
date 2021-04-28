<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace migration\command\seed;

use migration\command\Seed;
use InvalidArgumentException;
use Phinx\Util\Util;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument as InputArgument;


class Create extends Seed
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('seed:create')
            ->setDescription('Create a new database seeder')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the seeder?')
            ->setHelp(sprintf('%sCreates a new database seeder%s', PHP_EOL, PHP_EOL));
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return void
     * @author : 小夏
     * @date   : 2021-04-28 16:25:43
     */
    protected function execute(Input $input, Output $output)
    {
        $path = $this->getPath();

        if (!file_exists($path) && $this->output->confirm($this->input, 'Create seeds directory? [y]/n')) {
            mkdir($path, 0755, true);
        }

        $this->verifyMigrationDirectory($path);

        $path = realpath($path);

        $className = $input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf('The seed class name "%s" is invalid. Please use CamelCase format', $className));
        }

        // Compute the file path
        $filePath = $path . DS . $className . '.php';

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" already exists', basename($filePath)));
        }

        // inject the class names appropriate to this seeder
        $contents = file_get_contents($this->getTemplate());
        $classes  = [
            '$className' => $className
        ];
        $contents = strtr($contents, $classes);

        if (false === file_put_contents($filePath, $contents)) {
            throw new InvalidArgumentException(sprintf('The file "%s" could not be written to', $path));
        }

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }

    protected function getTemplate(): string
    {
        return __DIR__ . '/../stubs/seed.stub';
    }
}
