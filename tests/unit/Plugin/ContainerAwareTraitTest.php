<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerInterface;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function is_callable;

#[CoversClass(Container::class)]
#[CoversTrait(ContainerAwareTrait::class)]
class ContainerAwareTraitTest extends TestCase
{

     /**
      * Test setContainer returns $this for fluent interface.
      */
    public function testSetContainerReturnsSelf(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $this->assertSame($subject, $subject->setContainer($container));
     }

     /**
      * Test getContainer returns null by default.
      */
    public function testGetContainerReturnsNullByDefault(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $this->assertNull($subject->getContainer());
     }

     /**
      * Test getContainer returns the set container.
      */
    public function testGetContainerReturnsSetContainer(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $subject->setContainer($container);
         $this->assertSame($container, $subject->getContainer());
     }

     /**
      * Test setContainer with null.
      */
    public function testSetContainerWithNull(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $subject->setContainer($container);
         $this->assertNotNull($subject->getContainer());

         $subject->setContainer();
         $this->assertNull($subject->getContainer());
     }

     /**
      * Test __get proxies to container->get().
      */
    public function testGetProxiesToContainerGet(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $container['greeting'] = 'hello';
         $subject->setContainer($container);

         $this->assertSame('hello', $subject->getContainer()['greeting']);
     }

     /**
      * Test __get returns false when container is not set.
      */
    public function testGetReturnsFalseWhenNoContainer(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $this->assertFalse($subject->someService);
     }

     /**
      * Test __isset returns true when container has the service.
      */
    public function testIssetReturnsTrueWhenContainerHasService(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $container['service'] = 'value';
         $subject->setContainer($container);

         $this->assertTrue($subject->__isset('service'));
     }

     /**
      * Test __isset returns false when container does not have the service.
      */
    public function testIssetReturnsFalseWhenContainerDoesNotHaveService(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $container['service'] = 'value';
         $subject->setContainer($container);

         $this->assertFalse(isset($subject->getContainer()['missingService']));
     }

     /**
      * Test __isset returns false when container is not set.
      */
    public function testIssetReturnsFalseWhenNoContainer(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $this->assertFalse(isset($subject->getContainer()['service']));
     }

     /**
      * Test __call invokes a callable container service.
      */
    public function testCallInvokesCallableContainerService(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $container['greet'] = static function () {
            return 'hello';
         };
         $subject->setContainer($container);

         $this->assertSame('hello', $subject->getContainer()['greet']);
     }

     /**
      * Test __call returns false when the container service is not callable.
      */
    public function testCallReturnsFalseWhenServiceNotCallable(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $container['value'] = 'not a callable';
         $subject->setContainer($container);

         $this->assertFalse(is_callable($subject->getContainer()['value']));
     }

     /**
      * Test __call returns false when container is not set.
      */
    public function testCallReturnsFalseWhenNoContainer(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $this->assertFalse($subject->someMethod());
     }

     /**
      * Test __call returns false when container service does not exist.
      */
    public function testCallReturnsFalseWhenServiceMissing(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $subject->setContainer($container);

         $this->expectException(UnknownIdentifierException::class);
         $this->assertFalse($subject->getContainer()['missingService']);
     }

     /**
      * Test container implements ContainerInterface.
      */
    public function testContainerIsContainerInterface(): void
     {
         $subject = new class {
            use ContainerAwareTrait;
         };

         $container = new Container();
         $subject->setContainer($container);

         $this->assertInstanceOf(ContainerInterface::class, $subject->getContainer());
     }
}
