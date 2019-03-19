<?php

namespace MorningTrain\LaravelSupport\Traits;

trait ConfiguresProperties
{

    protected function getConfigurableProperties()
    {
        return isset($this->configurable) && is_array($this->configurable) ? $this->configurable : [];
    }

    public function config(array $config)
    {
        foreach (array_only($config, $this->getConfigurableProperties()) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

}