<?php


namespace Hyper\Application;


use Hyper\Exception\HyperException;
use function array_key_exists;
use function array_search;

class HyperEventHook
{
    const boot = 'onBoot',
        booted = 'onBooted',
        routingStarting = 'onRoutingStarting',
        routeCreated = 'onRouteCreated',
        routingCompleted = 'onRoutingCompleted',
        renderingStarting = 'onRenderingStarting',
        renderingCompleted = 'onRenderingCompleted';

    private $definedHooks = [
        self::boot,
        self::booted,
        self::routeCreated,
        self::routingStarting,
        self::routingCompleted,
        self::renderingStarting,
        self::renderingCompleted,
    ];

    private $events = [];

    public function __construct(array $events)
    {
        foreach ($events as $event => $function) {
            if (array_search($event, $this->definedHooks) !== false) {
                $this->events[$event] = $function;
            } else (new HyperException)->throw('Unknown event hook: ' . $event);
        }
    }

    public function emit($eventName, $data = null)
    {
        if (array_key_exists($eventName, $this->events))
            $this->events[$eventName](new Event($eventName, $data));
    }
}