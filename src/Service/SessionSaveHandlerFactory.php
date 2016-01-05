<?php
namespace Gambit\Mvc\Service;

use Gambit\Mvc\Session\SaveHandler\DummySaveHandler;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;

class SessionSaveHandlerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator->has('Zend\Db\Adapter\Adapter')) {
            $adapter        = $serviceLocator->get('Zend\Db\Adapter\Adapter');
            $tableGateway   = new TableGateway('sessions', $adapter);
            $options        = new DbTableGatewayOptions();
            $sessionHandler = new DbTableGateway($tableGateway, $options);
        } else {
            $sessionHandler = new DummySaveHandler();
        }

        return $sessionHandler;
    }
}