<?php

namespace MorningTrain\LaravelSupport\Eloquent\Tree;

use Illuminate\Database\Eloquent\Model;

trait Tree
{

    public function getNodePathKey()
    {
        return isset($this->nodePathKey) ? $this->nodePathKey : 'path';
    }

    /*
     -------------------------------
     Relation constructors
     -------------------------------
     */

    protected function hasParentNode()
    {
        return new HasParentNode($this);
    }

    protected function hasChildNodes()
    {
        return new HasManyNodes($this, 'child');
    }

    protected function hasAncestors()
    {
        return new HasManyNodes($this, 'ancestor');
    }

    protected function hasDescendants()
    {
        return new HasManyNodes($this, 'descendant');
    }

    /*
     -------------------------------
     Accessors
     -------------------------------
     */

    public function getParentIdAttribute()
    {
        if ($this->isRoot()) {
            return null;
        }

        $pathKey = $this->getNodePathKey();
        $ancestors = explode('/', $this->$pathKey);
        return array_pop($ancestors);
    }

    /*
     -------------------------------
     Relationships
     -------------------------------
     */

    public function parentNode()
    {
        return $this->hasParentNode();
    }

    public function childNodes()
    {
        return $this->hasChildNodes();
    }

    public function ancestors()
    {
        return $this->hasAncestors();
    }

    public function descendants()
    {
        return $this->hasDescendants();
    }

    /*
     -------------------------------
     Helpers
     -------------------------------
     */

    public function isRoot()
    {
        $pathKey = $this->getNodePathKey();
        return $this->$pathKey === '/';
    }

    public function isLeaf()
    {
        return $this->childNodes()->count() === 0;
    }

    public function setAsRoot()
    {
        $pathKey = $this->getNodePathKey();
        $this->$pathKey = '/';
        $this->save();
    }

    public function addChild(Model $model)
    {
        $pathKey = $this->getNodePathKey();
        $primaryKey = $this->getKeyName();

        $model->$pathKey = $this->$pathKey . '/' . $this->$primaryKey;
        $model->save();

        return $model;
    }

    public function removeChild(Model $model)
    {
        $pathKey = $this->getNodePathKey();
        $primaryKey = $this->getKeyName();
        $parent = $model->parentNode();

        if ($parent && ($parent->$primaryKey === $this->$primaryKey)) {
            $model->setAsRoot();
        }

        return $model;
    }

    public function isParentOf(Model $child)
    {
        $primaryKey = $this->getKeyName();
        return $child->parent_id === $this->$primaryKey;
    }

    public function isChildOf(Model $parent)
    {
        return $parent->isParentOf($this);
    }

    public function isAncestorOf(Model $descendant)
    {
        $primaryKey = $this->getKeyName();
        $pathKey = $this->getNodePathKey();
        $ancestors = explode(',', $descendant->$pathKey);

        return in_array($this->$primaryKey, $ancestors);
    }

    public function isDescendantOf(Model $ancestor)
    {
        return $ancestor->isAncestorOf($this);
    }

    /*
     -------------------------------
     Scopes
     -------------------------------
     */

    public function scopeRoot($q, $is_root = true)
    {
        return $q->where($this->getNodePathKey(), $is_root ? '=' : '<>', '/');
    }

    public function scopeLeaf($q, $is_leaf = true)
    {
        return $is_leaf ?
            $q->whereDoesntHave('childNodes') :
            $q->whereHas('childNodes');
    }

    public function scopeParentOf($q, Model $child)
    {
        $pathKey = $this->getNodePathKey();

        // If root, return an impossible query
        if ($child->isRoot()) {
            return $q->where('1', '=', '2');
        }

        $ancestors = explode('/', $child->$pathKey);
        $parent_id = array_pop($ancestors);

        return $q->where($this->getKeyName(), $parent_id);
    }

    public function scopeChildOf($q, Model $parent)
    {
        $pathKey = $this->getNodePathKey();
        $ancestorId = $parent->id;
        return $q->where($pathKey, 'LIKE', "%/$ancestorId");
    }

    public function scopeAncestorOf($q, Model $descendant)
    {
        $pathKey = $this->getNodePathKey();

        // Compute ancestor ids
        $references = collect(explode('/', $descendant->$pathKey))->filter(function ($reference) {
            return strlen($reference) > 0;
        });

        return $q->whereIn($this->getKeyName(), $references->all());
    }

    public function scopeDescendantOf($q, Model $ancestor)
    {
        $pathKey = $this->getNodePathKey();
        $ancestorId = $ancestor->id;

        return $q
            ->childOf($ancestor)
            ->orWhere($pathKey, 'LIKE', "%/$ancestorId/%");
    }

}