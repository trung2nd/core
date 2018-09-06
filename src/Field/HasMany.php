<?php

namespace Terranet\Administrator\Field;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\View;
use Terranet\Administrator\Contracts\Module;
use Terranet\Administrator\Field\Traits\WorksWithModules;
use Terranet\Administrator\Modules\Faked;
use Terranet\Administrator\Scaffolding;
use Terranet\Administrator\Services\CrudActions;
use Terranet\Administrator\Traits\Module\HasColumns;

class HasMany extends Generic
{
    use WorksWithModules;

    /** @var string */
    protected $icon = 'list-ul';

    /** @var Builder */
    protected $query;

    /**
     * @param \Closure $query
     *
     * @return $this
     */
    public function withQuery(\Closure $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string $icon
     *
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return array
     */
    protected function onIndex(): array
    {
        $relation = call_user_func([$this->model, $this->id]);
        $module = $this->firstWithModel($relation->getRelated());

        // apply a query
        if ($this->query) {
            $relation = call_user_func_array($this->query, [$relation]);
        }

        $count = $relation->count();

        $related = $relation->getRelated();

        if ($module = $this->firstWithModel($related)) {
            $url = route('scaffold.view', [
                'module' => $module->url(),
                $related->getKeyName() => $related->getKey(),
                $relation->getForeignKeyName() => $this->model->getKey(),
            ]);
        }

        return [
            'icon' => $this->icon,
            'module' => $module,
            'count' => $count,
            'url' => $url ?? null,
        ];
    }

    /**
     * @return array
     */
    protected function onView(): array
    {
        $relation = call_user_func([$this->model, $this->id]);
        $module = $this->firstWithModel($related = $relation->getRelated());

        if (!$module) {
            // Build a runtime module
            $module = Faked::make($related);
        }
        $columns = $module->columns()->each->disableSorting();
        $actions = $module->actionsManager();

        return [
            'module' => $module ?? null,
            'columns' => $columns ?? null,
            'actions' => $actions ?? null,
            'items' => $relation ? $relation->getResults() : null,
        ];
    }
}