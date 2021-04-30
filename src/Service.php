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

namespace xia\migration;

//use Faker\Factory as FakerFactory;
//use Faker\Generator as FakerGenerator;
use xia\migration\command\factory\Create as FactoryCreate;
use xia\migration\command\migrate\Breakpoint as MigrateBreakpoint;
use xia\migration\command\migrate\Create as MigrateCreate;
use xia\migration\command\migrate\Rollback as MigrateRollback;
use xia\migration\command\migrate\Run as MigrateRun;
use xia\migration\command\migrate\Status as MigrateStatus;
use xia\migration\command\seed\Create as SeedCreate;
use xia\migration\command\seed\Run as SeedRun;

class Service extends \think\Service
{

    public function boot()
    {
        //$this->app->bind(FakerGenerator::class, function () {
        //    return FakerFactory::create($this->app->config->get('app.faker_locale', 'zh_CN'));
        //});

        $this->app->bind(Factory::class, function () {
            return (new Factory($this->app->make(FakerGenerator::class)))->load($this->app->getRootPath() . 'database/factories/');
        });

        $this->app->bind('migration.creator', Creator::class);

        $this->commands([
            MigrateCreate::class,
            MigrateRun::class,
            MigrateRollback::class,
            MigrateBreakpoint::class,
            MigrateStatus::class,
            SeedCreate::class,
            SeedRun::class,
            FactoryCreate::class,
        ]);
    }
}
