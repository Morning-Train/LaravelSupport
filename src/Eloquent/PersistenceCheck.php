<?php

namespace MorningTrain\Laravel\Support\Eloquent;

trait PersistenceCheck
{

    /**
     * @var bool
     */
    protected $wasNew;

    /**
     * Boots our trait
     */
    protected static function bootPersistenceCheck()
    {
        static::saving(function (Model $model) {
            if ($model->isNew()) {
                $model->wasNew = true;
            }
        });
    }

    /*
     -------------------------------
     Helpers
     -------------------------------
     */

    public function isNew()
    {
        return is_null($this->getKey());
    }

    public function wasNew()
    {
        return is_bool($this->wasNew) ? $this->wasNew : $this->isNew();
    }

}