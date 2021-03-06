<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace rollun\callback\Queues\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use ReputationVIP\QueueClient\Adapter\MemoryAdapter;
use rollun\callback\Queues\QueueClient;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of QueueClient
 *
 * Config example:
 *
 * <code>
 *  [
 *      QueueClientAbstractFactory::class => [
 *          'requestedServiceName1' => [
 *              'delay' => 30, // in seconds,
 *              'name' => 'testQueue',
 *              'adapter' => SqsAdapter,
 *          ],
 *          'requestedServiceName2' => [
 *
 *          ],
 *      ]
 *  ]
 * </code>
 *
 * Class QueueClientAbstractFactory
 * @package rollun\callback\Queues\Factory
 */
class QueueClientAbstractFactory implements AbstractFactoryInterface
{
    const KEY_CLASS = 'class';

    const KEY_DEFAULT_CLASS = QueueClient::class;

    const KEY_DELAY = 'delay';

    const KEY_NAME = 'name';

    const KEY_ADAPTER = 'adapter';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName] ?? [];

        if (empty($serviceConfig)) {
            return false;
        }

        if (isset($serviceConfig[self::KEY_CLASS])
            && !is_a($serviceConfig[self::KEY_CLASS], self::KEY_DEFAULT_CLASS, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return QueueClient
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (!isset($serviceConfig[self::KEY_ADAPTER]) || !$container->has($serviceConfig[self::KEY_ADAPTER])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_ADAPTER . "'");
        }

        if (!isset($serviceConfig[self::KEY_NAME])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_NAME . "'");
        }

        $adapter = $container->get($serviceConfig[self::KEY_ADAPTER]);
        $delay = $serviceConfig[self::KEY_DELAY] ?? 0;
        $queueName = $serviceConfig[self::KEY_NAME];
        $class = $serviceConfig[self::KEY_CLASS] ?? self::KEY_DEFAULT_CLASS;

        return new $class($adapter, $queueName, $delay);
    }

    public static function createSimpleQueueClient(): QueueClient
    {
        return new QueueClient(new MemoryAdapter(), sha1(openssl_random_pseudo_bytes(1024)));
    }
}
