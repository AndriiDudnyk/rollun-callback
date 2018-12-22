<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\test\Queues;

use PHPUnit\Framework\TestCase;
use ReputationVIP\QueueClient\Adapter\MemoryAdapter;
use rollun\callback\Callback\Example\CallMe;
use rollun\callback\Callback\Extractor;
use rollun\callback\Callback\Interrupter\Job;
use rollun\callback\Callback\Interrupter\Process;
use rollun\callback\Queues\Message;
use rollun\callback\Queues\QueueClient;
use rollun\callback\Queues\QueueInterface;

class ExtractorTest extends TestCase
{
    /** @var Extractor*/
    protected $object;

    /** @var QueueInterface */
    protected $queue;

    protected $config;

    protected $queueName;

    public function setUp()
    {
        $queueName = 'test_extractor';
        $this->queue = new QueueClient(new MemoryAdapter(), $queueName);
    }

    public function providerType()
    {
        $stdObject = (object)['prop' => 'Hello '];
        //function
        return [
            [
                [
                    function ($val) {
                        return 'Hello ' . $val;
                    },
                ],
                "World"
            ],
            [
                [
                    new Process(function ($val) use ($stdObject) {
                        return $stdObject->prop . $val;
                    }),
                ],
                "World"
            ],
            [
                [
                    new Process(new CallMe()),
                ],
                "World"
            ],
            [
                [
                    new Process([new CallMe(), 'staticMethod']),
                ],
                "World"
            ],
            [
                [
                    '\\' . CallMe::class . '::staticMethod'
                ],
                "World"
            ],
        ];
    }

    public function addInQueue(array $callbacks, $value)
    {
        foreach ($callbacks as $callback) {
            $job = new Job($callback, $value);
            $this->queue->addMessage(Message::createInstance($job->serializeBase64()));
        }
    }

    /**
     * @param $callbacks
     * @param $value
     * @dataProvider providerType
     */
    public function testExtractQueue(array $callbacks, $value)
    {
        $this->object = new Extractor($this->queue);

        $this->addInQueue($callbacks, $value);

        $i = 0;
        while ($this->object->extract()) {
            $i++;
        };
        $this->assertEquals(count($callbacks), $i);
    }
}
