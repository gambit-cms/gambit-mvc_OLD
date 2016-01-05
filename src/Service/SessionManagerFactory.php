<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Gambit\Mvc\Service;

use Zend\Session\Service\SessionManagerFactory as BaseSessionManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class SessionManagerFactory extends BaseSessionManagerFactory
{
    
    /**
     * Create session manager object
     *
     * Will consume any combination (or zero) of the following services, when
     * present, to construct the SessionManager instance:
     *
     * - Zend\Session\Config\ConfigInterface
     * - Zend\Session\Storage\StorageInterface
     * - Zend\Session\SaveHandler\SaveHandlerInterface
     *
     * The first two have corresponding factories inside this namespace. The
     * last, however, does not, due to the differences in implementations, and
     * the fact that save handlers will often be written in userland. As such
     * if you wish to attach a save handler to the manager, you will need to
     * write your own factory, and assign it to the service name
     * "Zend\Session\SaveHandler\SaveHandlerInterface", (or alias that name
     * to your own service).
     *
     * You can configure limited behaviors via the "session_manager" key of the
     * Config service. Currently, these include:
     *
     * - enable_default_container_manager: whether to inject the created instance
     *   as the default manager for Container instances. The default value for
     *   this is true; set it to false to disable.
     * - validators: ...
     *
     * @param  ServiceLocatorInterface    $services
     * @return SessionManager
     * @throws ServiceNotCreatedException if any collaborators are not of the
     *         correct type
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (!$services->has('Zend\Session\SaveHandler\SaveHandlerInterface')) {
            // Gambit uses DbTableGateway save handler
            $services->setFactory('Zend\Session\SaveHandler\SaveHandlerInterface', 'Gambit\Mvc\Service\SessionSaveHandlerFactory');
        }

        $config = $services->get('Config');
        if (isset($config['session_config'])) {
            if (!$services->has('Zend\Session\Config\ConfigInterface')) {
                $services->setFactory('Zend\Session\Config\ConfigInterface', 'Zend\Session\Service\SessionConfigFactory');
            }
        }
        
        if (isset($config['session_storage'])) {
            if (!$services->has('Zend\Session\Storage\StorageInterface')) {
                $services->setFactory('Zend\Session\Storage\StorageInterface', 'Zend\Session\Service\StorageFactory');
            }
        }

        return parent::createService($services);
    }
}
