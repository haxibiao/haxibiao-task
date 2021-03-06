<?php

namespace Haxibiao\Task\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class ReviewFlow extends Resource
{
    public static $model  = 'Haxibiao\Task\ReviewFlow';
    public static $title  = 'name';
    public static $search = [
        'id', 'name',
    ];

    public static $group = '任务中心';

    public static function label()
    {
        return '任务模板';
    }

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('模板名称', 'name'),
            Text::make('任务检查类', 'review_class'),
            Code::make('任务检查函数类', 'check_functions')
                ->json(JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE)
                ->showOnIndex(),
            Select::make('类型', 'type')->options([
                \App\ReviewFlow::ADMIN_USER_SCOPE  => '运营选用',
                \App\ReviewFlow::NORMAL_USER_SCOPE => '用户可选用',
            ])->displayUsingLabels()->withMeta(['type' => 0]),
            Text::make('是否需要任务发布者检查', 'need_owner_review')->default(0),
            Text::make('是否需要官方人员检查', 'need_offical_review')->default(0),
            DateTime::make('开始时间', 'updated_at')->hideFromIndex(),
            DateTime::make('创建时间', 'created_at')->hideFromIndex(),

        ];

    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
