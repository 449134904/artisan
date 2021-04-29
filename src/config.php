<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
\think\Console::addDefaultCommands([
    "xia\\migration\\command\\migrate\\Create",
    "xia\\migration\\command\\migrate\\Run",
    "xia\\migration\\command\\seed\\Create",
    "xia\\migration\\command\\seed\\Run",
]);
