<?php

namespace KikCMS\Services\Base;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;

class BaseServices extends FactoryDefault
{
    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct();
        
        $this->setShared('config', $config);
        $this->setShared('applicationConfig', $config->get('application'));
        $this->setShared('databaseConfig', $config->get('database'));

        $this->bindServices();
    }
    
    protected function bindServices()
    {
        $reflection = new \ReflectionObject($this);
        $methods = $reflection->getMethods();
        
        foreach ($methods as $method) {

            if ((strlen($method->name) > 10) && (strpos($method->name, 'initShared') === 0)) {
                $this->set(lcfirst(substr($method->name, 10)), $method->getClosure($this));
                continue;
            }
            
            if ((strlen($method->name) > 4) && (strpos($method->name, 'init') === 0)) {
                $this->set(lcfirst(substr($method->name, 4)), $method->getClosure($this));
            }

        }
    }

    /**
     * @return Config
     */
    protected function getDatabaseConfig()
    {
        return $this->get('config')->get('database');
    }

    /**
     * @return Config
     */
    protected function getApplicationConfig()
    {
        return $this->get('config')->get('application');
    }
}