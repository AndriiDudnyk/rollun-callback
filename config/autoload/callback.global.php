<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 11:26
 */

use rollun\actionrender\Factory\ActionRenderAbstractFactory;
use rollun\actionrender\Factory\MiddlewarePipeAbstractFactory;
use rollun\actionrender\Renderer\ResponseRendererAbstractFactory;
use rollun\callback\Callback\Interruptor\Factory\AbstractMultiplexerFactory;
use rollun\callback\Callback\Interruptor\Factory\CronMultiplexerFactory;
use rollun\callback\Example;
use rollun\callback\Middleware\CronReceiver;
use rollun\callback\Middleware\Factory\LazyLoadAbstractFactory;

return [
    'dependencies' => [
        'invokables' => [
            'cronSecMultiplexer' => Example\CronSecMultiplexer::class,
            'httpCallback' =>
                \rollun\callback\Middleware\HttpInterruptorAction::class,
        ],
        'factories' => [
        ],
        'abstract_factories' => [
            \rollun\callback\Callback\Interruptor\Factory\MultiplexerAbstractFactory::class,
            \rollun\callback\Middleware\Factory\LazyLoadAbstractFactory::class
        ]
    ],

    AbstractMultiplexerFactory::KEY_MULTIPLEXER => [
        CronMultiplexerFactory::KEY_CRON => [
            CronMultiplexerFactory::KEY_CLASS => Example\CronMinMultiplexer::class,//not require
            CronMultiplexerFactory::KEY_SECOND_MULTIPLEXER_SERVICE => 'cronSecMultiplexer', //not require
            //CronMultiplexerFactory::KEY_INTERRUPTERS_SERVICE => [] not require
        ]
    ],

    ResponseRendererAbstractFactory::KEY_RESPONSE_RENDERER => [
        'webhookJsonRender' => [
            ResponseRendererAbstractFactory::KEY_ACCEPT_TYPE_PATTERN => [
                //pattern => middleware-Service-Name
                '/application\/json/' => \rollun\actionrender\Renderer\Json\JsonRendererAction::class,
            ]
        ]
    ],

    ActionRenderAbstractFactory::KEY_AR_SERVICE => [
        'webhookActionRender' => [
            ActionRenderAbstractFactory::KEY_AR_MIDDLEWARE => [
                ActionRenderAbstractFactory::KEY_ACTION_MIDDLEWARE_SERVICE => 'webhookLazyLoad',
                ActionRenderAbstractFactory::KEY_RENDER_MIDDLEWARE_SERVICE => 'webhookJsonRender'
            ]
        ]
    ],

    LazyLoadAbstractFactory::KEY_LAZY_LOAD => [
        'webhookLazyLoad' => [
            LazyLoadAbstractFactory::KEY_DIRECT_FACTORY =>
                \rollun\callback\Middleware\Factory\InterruptorDirectFactory::class
        ]
    ],

    MiddlewarePipeAbstractFactory::KEY_AMP => [
    ],
];
