<?php
namespace Gambit\Mvc;

use Locale;
use Zend\Mvc\MvcEvent as BaseMvcEvent;

class MvcEvent extends BaseMvcEvent
{
    const EVENT_LOCALE_CHANGED      = 'locale.changed';
    
    /**
     * The current locale.
     * 
     * @var string
     */
    protected $locale = null;

    /**
     * {@inheritDoc}
     * @see \Zend\Mvc\MvcEvent::getApplication()
     * @return Application
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * Get the application locale.
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set application locale
     *
     * @param  string $locale
     * @return MvcEvent
     */
    public function setLocale($locale)
    {
        $locale = Locale::canonicalize($locale);
        $this->setParam('locale', $locale);
        $this->locale = $locale;
        return $this;
    }
}