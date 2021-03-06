<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Tests\Events;

use Whoa\Contracts\Settings\Packages\EventSettingsInterface;
use Whoa\Events\Exceptions\EventNotFoundException;
use Whoa\Events\SimpleEventEmitter;
use Whoa\Tests\Events\Data\Events\OrderCreatedEvent;
use Whoa\Tests\Events\Data\Events\OrderUpdatedEvent;
use Whoa\Tests\Events\Data\Events\UserCreatedEvent;
use Whoa\Tests\Events\Data\Events\NoHandlerEvent;
use Whoa\Tests\Events\Data\Events\UserUpdatedEvent;
use Whoa\Tests\Events\Data\EventSettings;
use Whoa\Tests\Events\Data\Subscribers\GenericSubscribers;
use Whoa\Tests\Events\Data\Subscribers\OrderSubscribers;
use Whoa\Tests\Events\Data\Subscribers\UserSubscribers;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Whoa\Tests\Events
 */
class SimpleEventEmitterTest extends TestCase
{
    /** Method name */
    public const PUBLIC_STATIC_NAME = 'publicStaticHandler';

    /** Method name */
    public const PUBLIC_NON_STATIC_NAME = 'publicNonStaticHandler';

    /**
     * @var bool
     */
    private static bool $publicStaticCalled = false;

    /**
     * @var bool
     */
    private bool $publicNonStaticCalled = false;

    /**
     * Stub for public static handler.
     * @return bool
     */
    public static function publicStaticHandler(): bool
    {
        static::$publicStaticCalled = true;

        return false;
    }

    /**
     * Stub for public static handler.
     * @return bool
     */
    public function publicNonStaticHandler(): bool
    {
        $this->publicNonStaticCalled = true;

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetCalledFlags();
    }

    /**
     * Test basic event operations.
     * @return void
     */
    public function testBasicSubUnSub()
    {
        $emitter = new SimpleEventEmitter();
        $this->assertTrue($emitter->enableCancelling()->isCancellingEnabled());
        $this->assertFalse($emitter->disableCancelling()->isCancellingEnabled());

        $emitter->subscribe('event1', [self::class, self::PUBLIC_STATIC_NAME]);
        $emitter->subscribe('event2', [$this, self::PUBLIC_NON_STATIC_NAME]);

        $this->assertEquals([
            ['event1' => true, 'event2' => true],
            ['event1' => [[self::class, self::PUBLIC_STATIC_NAME],],]
        ], $emitter->getData());

        $emitter->emit('event1');
        $this->assertTrue(static::$publicStaticCalled);
        $this->assertFalse($this->publicNonStaticCalled);

        $emitter->emit('event2');
        $this->assertTrue($this->publicNonStaticCalled);

        $this->resetCalledFlags();

        $emitter->unSubscribe('event1', [self::class, self::PUBLIC_STATIC_NAME]);
        $emitter->unSubscribe('event2', [$this, self::PUBLIC_NON_STATIC_NAME]);

        $emitter->emit('event1');
        $emitter->emit('event2');
        $this->assertFalse(static::$publicStaticCalled);
        $this->assertFalse($this->publicNonStaticCalled);
    }

    /**
     * Test cancelling event propagation.
     */
    public function testCancellingPropagation()
    {
        $eventName = 'event1';
        $emitter = (new SimpleEventEmitter())->disableCancelling();
        $emitter->subscribe($eventName, [self::class, self::PUBLIC_STATIC_NAME]);
        $emitter->subscribe($eventName, [$this, self::PUBLIC_NON_STATIC_NAME]);

        $emitter->emit($eventName);
        $this->assertTrue(static::$publicStaticCalled);
        $this->assertTrue($this->publicNonStaticCalled);

        $this->resetCalledFlags();
        $emitter->enableCancelling();

        $emitter->emit($eventName);
        $this->assertTrue(static::$publicStaticCalled);
        $this->assertFalse($this->publicNonStaticCalled);
    }

    /**
     * Test using cache for static handlers.
     */
    public function testCacheForStaticHandlers()
    {
        $eventName = 'event1';
        $emitter1 = (new SimpleEventEmitter())->disableCancelling();
        $emitter1->subscribe($eventName, [self::class, self::PUBLIC_STATIC_NAME]);
        $emitter1->subscribe($eventName, [$this, self::PUBLIC_NON_STATIC_NAME]);
        $emitter1->subscribe($eventName, self::class . '::' . self::PUBLIC_STATIC_NAME);

        $cache = $emitter1->getData();

        $emitter2 = (new SimpleEventEmitter())->disableCancelling()->setData($cache);

        $emitter2->emit($eventName);
        $this->assertTrue(static::$publicStaticCalled);
        $this->assertFalse($this->publicNonStaticCalled);
    }

    /**
     * Test emitting non-existing event should fail.
     */
    public function testEmitNonExistingEvent()
    {
        $this->expectException(EventNotFoundException::class);

        (new SimpleEventEmitter())->emit('event1');
    }

    /**
     * Test invalid subscribers are handled correctly.
     * @throws ReflectionException
     */
    public function testCheckInvalidSubscribers1()
    {
        $this->assertFalse($this->callCheckSubscribers(['whatever']));
        $this->assertFalse($this->callCheckSubscribers(['some_even_name' => 'rubbish_insteadof_method_list']));
        $this->assertFalse($this->callCheckSubscribers(['some_even_name' => ['rubbish_insteadof_method']]));
        $this->assertFalse($this->callCheckSubscribers(['some_even_name' => [[self::class, 'invalid_method']]]));
    }

    /**
     * Test event dispatch.
     * @throws ReflectionException
     */
    public function testEventDispatch()
    {
        $appConfig = [];
        $cacheData = (new EventSettings())->get($appConfig)[EventSettingsInterface::KEY_CACHED_DATA];

        // it has 4 sections for each non-abstract event we have described
        $this->assertCount(2, $cacheData);
        $this->assertCount(9, $cacheData[0]);
        $this->assertCount(4, $cacheData[1]);

        // now do some actual event testing
        $events = (new SimpleEventEmitter())->setData($cacheData);

        // 1
        $this->resetSubscribers();
        $events->dispatch(new OrderCreatedEvent());
        $this->assertTrue(GenericSubscribers::isOnCreated());
        $this->assertFalse(GenericSubscribers::isOnUpdated());
        $this->assertTrue(OrderSubscribers::isOnOrder());
        $this->assertTrue(OrderSubscribers::isOnBaseOrder());
        $this->assertTrue(OrderSubscribers::isOnOrderCreated());
        $this->assertFalse(OrderSubscribers::isOnOrderUpdated());
        $this->assertFalse(UserSubscribers::isOnUser());
        $this->assertFalse(UserSubscribers::isOnBaseUser());
        $this->assertFalse(UserSubscribers::isOnUserCreated());
        $this->assertFalse(UserSubscribers::isOnUserUpdated());

        // 2
        $this->resetSubscribers();
        $events->dispatch(new OrderUpdatedEvent());
        $this->assertFalse(GenericSubscribers::isOnCreated());
        $this->assertTrue(GenericSubscribers::isOnUpdated());
        $this->assertTrue(OrderSubscribers::isOnOrder());
        $this->assertTrue(OrderSubscribers::isOnBaseOrder());
        $this->assertFalse(OrderSubscribers::isOnOrderCreated());
        $this->assertTrue(OrderSubscribers::isOnOrderUpdated());
        $this->assertFalse(UserSubscribers::isOnUser());
        $this->assertFalse(UserSubscribers::isOnBaseUser());
        $this->assertFalse(UserSubscribers::isOnUserCreated());
        $this->assertFalse(UserSubscribers::isOnUserUpdated());

        // 3
        $this->resetSubscribers();
        $events->dispatch(new UserCreatedEvent());
        $this->assertTrue(GenericSubscribers::isOnCreated());
        $this->assertFalse(GenericSubscribers::isOnUpdated());
        $this->assertFalse(OrderSubscribers::isOnOrder());
        $this->assertFalse(OrderSubscribers::isOnBaseOrder());
        $this->assertFalse(OrderSubscribers::isOnOrderCreated());
        $this->assertFalse(OrderSubscribers::isOnOrderUpdated());
        $this->assertTrue(UserSubscribers::isOnUser());
        $this->assertTrue(UserSubscribers::isOnBaseUser());
        $this->assertTrue(UserSubscribers::isOnUserCreated());
        $this->assertFalse(UserSubscribers::isOnUserUpdated());

        // 4
        $this->resetSubscribers();
        $events->dispatch(new UserUpdatedEvent());
        $this->assertFalse(GenericSubscribers::isOnCreated());
        $this->assertTrue(GenericSubscribers::isOnUpdated());
        $this->assertFalse(OrderSubscribers::isOnOrder());
        $this->assertFalse(OrderSubscribers::isOnBaseOrder());
        $this->assertFalse(OrderSubscribers::isOnOrderCreated());
        $this->assertFalse(OrderSubscribers::isOnOrderUpdated());
        $this->assertTrue(UserSubscribers::isOnUser());
        $this->assertTrue(UserSubscribers::isOnBaseUser());
        $this->assertFalse(UserSubscribers::isOnUserCreated());
        $this->assertTrue(UserSubscribers::isOnUserUpdated());

        // check sending event with no handlers works fine
        $this->resetSubscribers();
        $events->dispatch(new NoHandlerEvent());
        $this->assertFalse(GenericSubscribers::isOnCreated());
        $this->assertFalse(GenericSubscribers::isOnUpdated());
        $this->assertFalse(OrderSubscribers::isOnOrder());
        $this->assertFalse(OrderSubscribers::isOnBaseOrder());
        $this->assertFalse(OrderSubscribers::isOnOrderCreated());
        $this->assertFalse(OrderSubscribers::isOnOrderUpdated());
        $this->assertFalse(UserSubscribers::isOnUser());
        $this->assertFalse(UserSubscribers::isOnBaseUser());
        $this->assertFalse(UserSubscribers::isOnUserCreated());
        $this->assertFalse(UserSubscribers::isOnUserUpdated());
    }

    /**
     * Reset flags.
     */
    private function resetCalledFlags()
    {
        static::$publicStaticCalled = false;
        $this->publicNonStaticCalled = false;
    }

    /**
     * @param array $subscribers
     * @return bool
     * @throws ReflectionException
     */
    private function callCheckSubscribers(array $subscribers): bool
    {
        $method = new ReflectionMethod(SimpleEventEmitter::class, 'checkAllSubscribersAreStatic');
        $method->setAccessible(true);
        return $method->invoke(new SimpleEventEmitter(), $subscribers);
    }

    /**
     * @return void
     */
    private function resetSubscribers()
    {
        GenericSubscribers::reset();
        OrderSubscribers::reset();
        UserSubscribers::reset();
    }
}
