<?php
namespace Slim\Flash;

use Pimple\ServiceProviderInterface;

/**
 * Flash messages
 */
class Messages implements \Pimple\ServiceProviderInterface
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
     * @var null|array|ArrayAccess
     */
    protected $storage;

    /**
     * Message storage key
     *
     * @var string
     */
    protected $storageKey = 'slimFlash';

    /**
     * Create new Flash messages service provider
     *
     * @param null|array|ArrayAccess $storage
     */
    public function __construct(&$storage = null)
    {
        // Set storage
        if (is_array($storage) || $storage instanceof \ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new \RuntimeException('Flash messages middleware failed. Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new \InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }

        // Load messages from previous request
        if (isset($this->storage[$this->storageKey]) && is_array($this->storage[$this->storageKey])) {
            $this->fromPrevious = $this->storage[$this->storageKey];
        }
        $this->storage[$this->storageKey] = [];
    }

    /**
     * Register service provider
     *
     * @param  \Pimple\Container $container
     */
    public function register(\Pimple\Container $container)
    {
        $container['flash'] = $this;
    }

    /**
     * Add flash message
     *
     * @param string $message Message to show on next request
     */
    public function addMessage($message)
    {
        $this->storage[$this->storageKey][] = (string)$message;
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
}