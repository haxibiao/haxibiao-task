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
