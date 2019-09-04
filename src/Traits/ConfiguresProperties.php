<?php

namespace MorningTrain\Laravel\Support\Traits;

use Illuminate\Support\Arr;

trait ConfiguresProperties
{

    protected function getConfigurableProperties()
    {
        return isset($this->configurable) && is_array($this->configurable) ? $this->configurable : [];
    }

    public function config(array $config)
    {
        foreach (Arr::only($config, $this->getConfigurableProperties()) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

}
