<?php
namespace Gambit\Mvc\Session\SaveHandler;

use Zend\Mvc\Exception\InvalidArgumentException;
use Zend\Session\SaveHandler\SaveHandlerInterface;

/**
 * Dummy save handler.
 * Just makes use of another save handler or the internal php save handler.
 */
class DummySaveHandler implements SaveHandlerInterface
{
    protected $sessionHandler = null;

    public function __construct($saveHandler=null)
    {
        if ($saveHandler === null) {
            $this->sessionHandler = new \SessionHandler();
        } elseif ($saveHandler instanceof SaveHandlerInterface) {
            $this->sessionHandler = $saveHandler;
        } elseif ($saveHandler instanceof \SessionHandlerInterface) {
            $this->sessionHandler = $saveHandler;
        } else {
            throw new InvalidArgumentException(
                'saveHandler must implement \Zend\Session\SaveHandler\SaveHandlerInterface or \SessionHandlerInterface.'
            );
        }
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $savePath
     * @param string $name
     */
    public function open($savePath, $name)
    {
        return $this->sessionHandler->open($savePath, $name);
    }

    /**
     * Close Session - free resources
     *
     */
    public function close()
    {
        return $this->sessionHandler->close();
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id)
    {
        return $this->sessionHandler->read($id);
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data)
    {
        return $this->sessionHandler->write($id, $data);
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id)
    {
        return $this->sessionHandler->destroy($id);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
        $this->sessionHandler->gc($maxlifetime);
    }
}