<?php

namespace MorningTrain\Laravel\Support\Eloquent\Tree;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use \Exception;

class HasManyNodes extends Relation
{

    protected $relationType;

    public function __construct(Model $parent, $relationType)
    {
        if (!in_array($relationType, ['child', 'ancestor', 'descendant'])) {
            throw new Exception('Invalid relation type.');
        }

        $this->parent = $parent;
        $this->relationType = $relationType;

        parent::__construct($parent->newQuery(), $parent);
    }

    protected function getRelationTableAlias()
    {
        return $this->parent->getTable() . 'TreeNodes';
    }

    protected function getRelationTypeMethod()
    {
        return lcfirst(Str::studly($this->relationType)) . 'Of';
    }

    protected function getRelationTypeMatchMethod()
    {
        return 'is' . Str::studly($this->relationType) . 'Of';
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            $method = $this->getRelationTypeMethod();
            $this->query->$method($this->parent);
        }
    }

    public function addEagerConstraints(array $models)
    {
        $method = $this->getRelationTypeMethod();

        foreach ($models as $node) {
            $this->query->orWhere(function ($q) use ($node, $method) {
                return $q->$method($node);
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
        $method = $this->getRelationTypeMatchMethod();

        foreach ($results as $result) {
            foreach ($models as $model) {
                if ($result->$method($model)) {
                    $model->setRelation($relation, $result);
                }
            }
        }

        return $models;
    }

    public function getResults()
    {
        return $this->query->get();
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $parentTable = $this->parent->getTable();
        $primaryKey = $this->parent->getKeyName();
        $pathKey = $this->parent->getNodePathKey();
        $relationTable = $this->getRelationTableAlias();
        $this->related->setTable($relationTable);

        $query
            ->from("$parentTable as $relationTable")
            ->select($columns);

        switch ($this->relationType) {
            case 'child':
                $query->whereRaw("`$relationTable`.`$pathKey` LIKE CONCAT('%/', `$parentTable`.`$primaryKey`)");
                break;

            case 'ancestor':
                $query
                    ->whereRaw("`$parentTable`.`$pathKey` LIKE CONCAT('%/', `$relationTable`.`$primaryKey`)")
                    ->orWhereRaw("`$parentTable`.`$pathKey` LIKE CONCAT('%/', `$relationTable`.`$primaryKey`, '/%')");
                break;

            case 'descendant':
                $query
                    ->whereRaw("`$relationTable`.`$pathKey` LIKE CONCAT('%/', `$parentTable`.`$primaryKey`)")
                    ->orWhereRaw("`$relationTable`.`$pathKey` LIKE CONCAT('%/', `$parentTable`.`$primaryKey`, '/%')");
                break;
        }

        return $query;
    }

}