<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
\think\Console::addDefaultCommands([
    "migration\\command\\migrate\\Create",
    "migration\\command\\migrate\\Run",
    "migration\\command\\seed\\Create",
    "migration\\command\\seed\\Run",
]);
