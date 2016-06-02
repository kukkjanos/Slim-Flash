<?php
namespace Slim\Flash;

use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;
use Illuminate\Session\SessionManager;

/**
 * Flash messages
 */
class Messages
{
    /**
     * Messages from previous request
     *
     * @var string[]
     */
    protected $fromPrevious = [];

    /**
     * Messages for next request
     *
     * @var string[]
     */
    protected $forNext = [];

    /**
     * Message storage
     *
     * @var null|array|ArrayAccess|SessionManager
     */
    protected $storage;

    /**
     * Message storage Type
     *
     * @var boolean
     */
    protected $storageTypeIsSessionManager = false;

    /**
     * Message storage key
     *
     * @var string
     */
    protected $storageKey = 'slimFlash';

    /**
     * Create new Flash messages service provider
     *
     * @param null|array|ArrayAccess|SessionManager $storage
     * @throws RuntimeException if the session cannot be found
     * @throws InvalidArgumentException if the store is not array-like
     */
    public function __construct(&$storage = null)
    {
        // Set storage type
        if ($storage instanceof SessionManager)
            $this->storageTypeIsSessionManager = true;

        // Set storage
        if (is_array($storage) || $storage instanceof ArrayAccess || $this->storageTypeIsSessionManager) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new RuntimeException('Flash messages middleware failed. Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }

        // Load messages from previous request
        if ($this->storageTypeIsSessionManager) {
            if (is_array($this->storage->get($this->storageKey))) {
                $this->fromPrevious = $this->storage->get($this->storageKey);
            }
            $this->storage->set($this->storageKey, []);
        }
        else {
            if (isset($this->storage[$this->storageKey]) && is_array($this->storage[$this->storageKey])) {
                $this->fromPrevious = $this->storage[$this->storageKey];
            }
            $this->storage[$this->storageKey] = [];
        }
    }

    /**
     * Add flash message
     *
     * @param string $key The key to store the message under
     * @param mixed  $message Message to show on next request
     */
    public function addMessage($key, $message)
    {
        //Create Array for this key
        if ($this->storageTypeIsSessionManager) {
            $tmpArray = $this->storage->get($this->storageKey);
            if ($tmpArray !== null && !isset($tmpArray[$key])) {
                $tmpArray[$key] = array();
            }
            $tmpArray[$key] = $message;
            $this->storage->set($this->storageKey, $tmpArray);
        }
        else {
            if (!isset($this->storage[$this->storageKey][$key])) {
                $this->storage[$this->storageKey][$key] = array();
            }

            //Push onto the array
            $this->storage[$this->storageKey][$key][] = $message;
        }
    }

    /**
     * Get flash messages
     *
     * @return array Messages to show for current request
     */
    public function getMessages()
    {
        return $this->fromPrevious;
    }

    /**
     * Get Flash Message
     *
     * @param string $key The key to get the message from
     * @return mixed|null Returns the message
     */
    public function getMessage($key)
    {
        //If the key exists then return all messages or null
        return (isset($this->fromPrevious[$key])) ? $this->fromPrevious[$key] : null;
    }
}
