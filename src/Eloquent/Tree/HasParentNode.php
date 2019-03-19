<?php

namespace MorningTrain\Laravel\Support\Eloquent\Tree;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HasParentNode extends Relation
{

    public function __construct(Model $parent)
    {
        $this->parent = $parent;
        parent::__construct($parent->newQuery(), $parent);
    }

    protected function getRelationTableAlias()
    {
        return $this->parent->getTable() . 'TreeNodes';
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->parentOf($this->parent);
        }
    }

    public function addEagerConstraints(array $models)
    {
        foreach ($models as $node) {
            $this->query->orWhere(function ($q) use ($node) {
                return $q->parentOf($node);
            });
        }
    }

    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $primaryKey = $this->parent->getKeyName();
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$primaryKey] = $result;
        }

        foreach ($models as $model) {
            $parent_id = $model->parent_id;
            if (isset($dictionary[$parent_id])) {
                $model->setRelation($relation, $dictionary[$parent_id]);
            }
        }

        return $models;
    }

    public function getResults()
    {
        return $this->query->first();
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $parentTable = $this->parent->getTable();
        $primaryKey = $this->parent->getKeyName();
        $pathKey = $this->parent->getNodePathKey();
        $relationTable = $this->getRelationTableAlias();
        $this->related->setTable($relationTable);

        return $query
            ->from("$parentTable as $relationTable")
            ->select($columns)
            ->whereRaw("`$parentTable`.`$pathKey` LIKE CONCAT('%/', `$relationTable`.`$primaryKey`)");
    }

}