<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

namespace rollun\callback\Queues\Factory;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use ReputationVIP\QueueClient\PriorityHandler\ThreeLevelPriorityHandler;
use rollun\callback\Queues\Adapter\FileAdapter;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of FileAdapter
 *
 * Config example:
 *
 * <code>
 *  [
 *      FileAdapterAbstractFactory::class => [
 *          'requestedServiceName1' => [
 *              'priorityHandler' => 'priorityHandlerServiceName',
 *              'storageDirPath' => 'path/to/directory', // default 'data/queues',
 *              'timeInFlight' => 30
 *          ],
 *          'requestedServiceName2' => [
 *
 *          ],
 *      ]
 *  ]
 * </code>
 *
 * Class FileAdapterAbstractFactory
 * @package rollun\callback\Queues\Factory
 */
class FileAdapterAbstractFactory implements AbstractFactoryInterface
{
    const KEY_STORAGE_DIR_PATH = 'storageDirPath';

    const KEY_PRIORITY_HANDLER = 'priorityHandler';

    const KEY_TIME_IN_FLIGHT = 'timeInFlight';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return !empty($container->get('config')[self::class][$requestedName]);
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return FileAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceConfig = $container->get('config')[self::class][$requestedName];

        if (isset($serviceConfig[self::KEY_PRIORITY_HANDLER])) {
            if (!$container->has($serviceConfig[self::KEY_PRIORITY_HANDLER])) {
                throw new InvalidArgumentException("Invalid option '" . self::KEY_PRIORITY_HANDLER . "'");
            } else {
                $priorityHandler = $container->get($serviceConfig[self::KEY_PRIORITY_HANDLER]);
            }
        } else {
            $priorityHandler = $container->get(ThreeLevelPriorityHandler::class);
        }

        if (!isset($serviceConfig[self::KEY_STORAGE_DIR_PATH])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_STORAGE_DIR_PATH . "'");
        }

        if (!isset($serviceConfig[self::KEY_TIME_IN_FLIGHT])) {
            throw new InvalidArgumentException("Invalid option '" . self::KEY_TIME_IN_FLIGHT . "'");
        }

        $storageDirPath = $serviceConfig[self::KEY_STORAGE_DIR_PATH];
        $timeInFlight = $serviceConfig[self::KEY_TIME_IN_FLIGHT];

        return new FileAdapter($storageDirPath, $timeInFlight, $priorityHandler);
    }
}
