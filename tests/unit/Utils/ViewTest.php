<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;

/**
 * Class ViewTest
 * @package TheFrosty\WpUtilities\Tests\Utils
 */
#[CoversClass(View::class)]
#[Group('utils')]
class ViewTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/wp-utilities-views';
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }
        file_put_contents($this->directory . '/hello.php', '<?php echo $name;');
        file_put_contents(sys_get_temp_dir() . '/wp-utilities-outside.php', '<?php echo "outside";');
    }

    public function testRenderRetrieveAndSharedVars(): void
    {
        $view = new View();
        $view->addPath($this->directory);
        $view->setVars((object)['name' => 'Ada']);

        $expected = realpath($this->directory . '/hello.php');
        $this->assertSame(['name' => 'Ada'], $view->getViewData());
        $this->assertSame($expected, $view->get('hello'));
        $this->assertSame($expected, $view->get('hello.php'));
        $this->assertSame('Grace', $view->retrieve('hello', ['name' => 'Grace']));
        $this->assertSame(['name' => 'Grace'], $view->getViewData());
    }

    public function testMissingViewThrows(): void
    {
        $view = new View();
        $view->addPath($this->directory);

        $this->expectException(RuntimeException::class);
        $view->render('missing');
    }

    public function testViewDataCannotOverrideTheIncludedFile(): void
    {
        $view = new View();
        $view->addPath($this->directory);

        // Previously extract() ran in load()'s scope and `viewPath` replaced the file being included.
        $output = $view->retrieve('hello', [
            'name' => 'Ada',
            'viewPath' => sys_get_temp_dir() . '/wp-utilities-outside.php',
            'file' => sys_get_temp_dir() . '/wp-utilities-outside.php',
        ]);

        $this->assertSame('Ada', $output);
    }

    public function testViewNamesCannotTraverseOutOfTheViewPaths(): void
    {
        $view = new View();
        $view->addPath($this->directory);

        $this->assertNull($view->get('../wp-utilities-outside'));
        $this->expectException(RuntimeException::class);
        $view->render('../wp-utilities-outside');
    }
}
