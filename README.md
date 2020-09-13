# haxibiao/task

> haxibiao/task 是哈希表内部系统架构重构出来的任务系统

## 导语


## 环境要求
1. 依赖 User, Contribute, Gold... 还有很多，待重构
2. TODO: 答题产品和工厂APP产品还有一些差异，需要持续重构这个系统来兼容。

## 安装步骤

1. `composer.json`改动如下：
在`repositories`中添加 vcs 类型远程仓库指向 
`http://code.haxibiao.cn/packages/haxibiao-task` 
1. 执行`composer require haxibiao/task`
2. 执行`php artisan task:install && composer dump`
3. 给app/User.php 添加 use PlayWithTasks
4. 执行`php artisan migrate`
5. 执行`php artisan db:seed --class=ReviewFlowsSeeder` 先执行 
6. 执行`php artisan db:seed --class=TaskSeeder` (答赚的DZTasksSeeders, 答妹用DMTasksSeeders)
7. 完成

### 如何完成更新？
> 远程仓库的composer package发生更新时如何进行更新操作呢？
1. 执行`composer update haxibiao/task`
2. 执行`php artisan task:install`

## GQL接口说明

## Api接口说明

### 更新日志
**1.2**

_Released on 2020-09-11_

2020-09-01~2020-09-11

- 区分新老用户有趣小视频奖励 ([#993e3033](http://code.haxibiao.cn/packages/haxibiao-task/commit/993e3033b3017dadf7dbef25a54099999ea76552))
- 添加采集视频任务检查 ([#571a8b4d](http://code.haxibiao.cn/packages/haxibiao-task/commit/571a8b4d0842a9e854f19aa3da6557f86141ed29))
- 检查视频发布任务状态 ([#d540607e](http://code.haxibiao.cn/packages/haxibiao-task/commit/d540607e4b6d2f456d77cbbaf07e76161e2ff471))
- 新增检查任务（评论 检查个性签名，绑定微信） ([#8c79b832](http://code.haxibiao.cn/packages/haxibiao-task/commit/8c79b8325b55825427e81c8608e21ad8d5a3ce7b))
- 修复点赞任务检查 ([#4f31ded6](http://code.haxibiao.cn/packages/haxibiao-task/commit/4f31ded6c818697ab99bb7286a11044f6cdab4d2))
- 修复初次进入任务界面看广告倒数计时问题  ([#702169da](http://code.haxibiao.cn/packages/haxibiao-task/commit/702169da8f46c78aa703654746ea15a0b4750ffa))