<?php
namespace Gambit\Mvc;

use Locale;
use Zend\Mvc\Application as BaseApplication;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\ServiceManager\ServiceManager;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorInterface;

class Application extends BaseApplication
{   
    /**
     * Application locale.
     * 
     * @var string
     */
    protected $locale = null;

    /**
     * Bootstrap the application
     *
     * Defines and binds the MvcEvent, and passes it the request, response, and
     * router. Attaches the ViewManager as a listener. Triggers the bootstrap
     * event.
     *
     * @param array $listeners List of listeners to attach.
     * @return Application
     */
    public function bootstrap(array $listeners = array())
    {
        $serviceManager = $this->serviceManager;
        $events         = $this->events;
    
        $listeners = array_unique(array_merge($this->defaultListeners, $listeners));
    
        foreach ($listeners as $listener) {
            $events->attach($serviceManager->get($listener));
        }
    
        // Setup MVC Event
        $this->event = $event  = new MvcEvent();
        $event->setTarget($this);
        $event->setApplication($this)
              ->setRequest($this->request)
              ->setResponse($this->response)
              ->setRouter($serviceManager->get('Router'))
              ->setLocale($this->getLocale());
    
        // Trigger bootstrap events
        $events->trigger(MvcEvent::EVENT_BOOTSTRAP, $event);

        return $this;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Zend\Mvc\Application::getMvcEvent()
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return parent::getMvcEvent();
    }

    /**
     * Get the running locale.
     * 
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            $locale = null;

            if ($this->serviceManager->has('translator')) {
                $translator = $this->serviceManager->get('translator');
                if ($translator instanceof MvcTranslator) {
                    $translator = $translator->getTranslator();
                }

                if (method_exists($translator, 'getLocale')) {
                    $locale = $translator->getLocale();
                }
            }

            if ($locale === null) {
                if (!extension_loaded('intl')) {
                    throw new Exception\ExtensionNotLoadedException(sprintf(
                        '%s component requires the intl PHP extension',
                        __NAMESPACE__
                    ));
                }
                $locale = Locale::getDefault();
            }

            $this->locale = $locale;
        }
        return $this->locale;
    }

    /**
     * Static method for quick and easy initialization of the Application.
     *
     * If you use this init() method, you cannot specify a service with the
     * name of 'ApplicationConfig' in your service manager config. This name is
     * reserved to hold the array from application.config.php.
     *
     * The following services can only be overridden from application.config.php:
     *
     * - ModuleManager
     * - SharedEventManager
     * - EventManager & Zend\EventManager\EventManagerInterface
     *
     * All other services are configured after module loading, thus can be
     * overridden by modules.
     *
     * @param array $configuration
     * @return Application
     */
    public static function init($configuration = array())
    {
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $serviceManager = new ServiceManager(new Service\ServiceManagerConfig($smConfig));
        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->get('ModuleManager')->loadModules();
    
        $listenersFromAppConfig     = isset($configuration['listeners']) ? $configuration['listeners'] : array();
        $config                     = $serviceManager->get('Config');
        $listenersFromConfigService = isset($config['listeners']) ? $config['listeners'] : array();
    
        $listeners = array_unique(array_merge($listenersFromConfigService, $listenersFromAppConfig));
    
        return $serviceManager->get('Application')->bootstrap($listeners);
    }

    /**
     * Set the application locale.
     * 
     * @param string $locale
     * @return Application
     */
    public function setLocale($locale)
    {
        $locale = Locale::canonicalize($locale);
        if (strcmp($locale, $this->locale) === 0) {
            return $this;
        }
        
        $event = $this->getMvcEvent();
        $event->setLocale($locale);

        $eventManager = $this->getEventManager();
        $eventManager->trigger(MvcEvent::EVENT_LOCALE_CHANGED, $event);
        $locale = $event->getLocale();

        if (strcmp($locale, $this->locale) === 0) {
            return $this;
        }

        $this->locale = $locale;
        
        // Change the default translators if present.
        $serviceManager = $this->getServiceManager();

        // Default MvcTranslator
        if ($serviceManager->has('Translator')) {
            $translator = $serviceManager->get('Translator');
            if ($translator instanceof MvcTranslator) {
                $translator = $translator->getTranslator();
            }

            if (method_exists($translator, 'setLocale')) {
                $translator->setLocale($locale);
            }
        }

        // Router translator
        if ($serviceManager->has('Router')) {
            $router = $serviceManager->get('Router');
            if ($router instanceof TranslatorAwareInterface) {
                $translator = $router->getTranslator();
                if (($translator instanceof TranslatorInterface) && (method_exists($translator, 'setLocale'))) {
                    $translator->setLocale($locale);
                }
            }
        }

        return $this;
    }
}