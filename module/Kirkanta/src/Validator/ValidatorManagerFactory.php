<?php

namespace Kirkanta\Validator;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

class ValidatorManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = ValidatorPluginManager::class;
}
