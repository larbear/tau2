<?php
/**
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use PHPUnit\Framework\TestCase;
use Theyak\Tau\View;

final class ViewTest extends TestCase
{

    private $root;


    public function testShouldRenderView()
    {
        $view = new View();
        $s = $view->renderToString("tests/views/helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testShouldWorkWithVariables()
    {
        $view = new View();
        $s = $view->render("tests/views/hellovariable", ['name' => 'Variable']);
        $this->expectOutputString("<div>Hello Variable!</div>");
    }


    public function testShouldWorkWithAssignedVariables()
    {
        $view = new View();
        $view->assign('name', 'Variable');
        $s = $view->render("tests/views/hellovariable");
        $this->expectOutputString("<div>Hello Variable!</div>");
    }


    public function testShouldWorkWithMultipleAssignedVariables()
    {
        $view = new View();
        $view->assign('name', 'Variable');
        $view->assign('noun', 'life');
        $s = $view->render("tests/views/hellomulti");
        $this->expectOutputString("<div>Hello Variable, how's life?</div>");
    }


    public function testShouldWorkWithMultipleVariables()
    {
        $view = new View();
        $s = $view->render("tests/views/hellomulti", [
            'name' => 'Variable',
            'noun' => 'life',
        ]);
        $this->expectOutputString("<div>Hello Variable, how's life?</div>");
    }

    public function testShouldNotWorkWithMultipleBadVariables()
    {
        $view = new View();
        $s = $view->render("tests/views/hellomulti", [
            'nam' => 'Variable',
            'nou' => 'life',
        ]);
        $this->expectOutputString("<div>Hello , how's ?</div>");
    }


    public function testShouldWorkWithMultipleAssignedVariablesAsArray()
    {
        $view = new View();
        $view->assign([
            'name' => 'Variable',
            'noun' => 'life',
        ]);
        $s = $view->render("tests/views/hellomulti");
        $this->expectOutputString("<div>Hello Variable, how's life?</div>");
    }


    public function testShouldOverrideAssignedVairable()
    {
        $view = new View();
        $view->assign('name', 'Variable');
        $s = $view->render("tests/views/hellovariable", ['name' => 'Override']);
        $this->expectOutputString("<div>Hello Override!</div>");
    }


    public function testShouldRegisterAndUseMinimizeBlock()
    {
        $view = new View();
        $view->render("tests/views/ShouldBlockAndMinimize");
        $this->expectOutputString("<div><span></span></div><div>\n        Should not minimize\n    </div>");
    }


    public function testShouldUseFolders()
    {
        $view = new View(['folders' => ['tests/views']]);
        $s = $view->renderToString("helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testShouldUsePaths()
    {
        $view = new View(['paths' => ['tests/views']]);
        $s = $view->renderToString("helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testShouldConvertObjectToArray()
    {
        $opts = new stdClass();
        $opts->folders = ['tests/views'];
        $view = new View($opts);
        $s = $view->renderToString("helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testShouldUsePathsAsString()
    {
        $view = new View(['paths' => 'tests/views']);
        $s = $view->renderToString("helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testNotFound()
    {
        $view = new View();
        $s = $view->renderToString('badfile');
        $this->assertEquals("", $s);
    }


    public function testNotFoundWithDebug()
    {
        $expected = "Checking for ./badfile.phtml\n";
        $expected .= "Template not found: badfile\n";
        $view = new View(['debug' => true]);
        $s = $view->renderToString('badfile');
        $this->assertEquals($expected, $s);
    }


    public function testShouldUseDefaultTemplate()
    {
        $view = new View([
            'defaultTemplate' => 'tests/views/helloworld'
        ]);
        $s = $view->renderToString('badfile');
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testShouldFailInvalidTemplate()
    {
        $this->expectException(TypeError::class);
        $view = new View();
        $s = $view->render(new stdClass());
    }


    public function testShouldFailInvalidDefaultTemplate()
    {
        $this->expectException(TypeError::class);
        $view = new View(['defaultTemplate' => new stdClass()]);
        $s = $view->render(new stdClass());
    }


    public function testShouldErrorOnNullTemplate()
    {
        $this->expectException(TypeError::class);
        $view = new View();
        $s = $view->render();
    }


    public function testDefaultExtensionPhtml()
    {
        $view = new View(['extension' => null]);
        $s = $view->renderToString("tests/views/helloworld");
        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testNoExtension()
    {
        $view = new View(['extension' => false]);
        $s = $view->renderToString("tests/views/helloworld");
        $this->assertEquals("", $s);
    }


    public function testNoExtensionWithExtensionIncludedInFilename()
    {
        $view = new View(['extension' => false]);
        $s = $view->renderToString("tests/views/helloworld.phtml");

        $this->assertEquals("<div>Hello World!</div>", $s);
    }


    public function testSearchMultiplePaths()
    {
        $expected = "Checking for tests/helloworld.phtml\n";
        $expected .= "Checking for tests/views/helloworld.phtml\n";
        $expected .= '<div>Hello World!</div>';

        $view = new View(['paths' => ['tests/', 'tests/views/'], 'debug' => true]);
        $s = $view->renderToString("helloworld");
        $this->assertEquals($expected, $s);
    }


    public function testUsePathById()
    {
        $expected = "Checking for tests/views/helloworld.phtml\n";
        $expected .= '<div>Hello World!</div>';

        $view = new View(['paths' => ['a' => 'tests/', 'b' => 'tests/views/'], 'debug' => true]);
        $s = $view->renderToString('b::helloworld');
        $this->assertEquals($expected, $s);
    }


    public function testShouldHandleMissingEndBlock()
    {
        $this->expectException(Exception::class);
        $view = new View();
        $s = $view->renderToString('tests/views/missingEndBlock');
        $this->assertEquals("", $s);
    }


    public function testHelper()
    {
        $view = new View();
        $view->registerHelper('ucfirst', function ($x) {
            return ucfirst($x);
        });
        $view->assign('name', 'variable');
        $s = $view->render('tests/views/helper');
        $this->expectOutputString("<div>Hello Variable!</div>");
    }


    public function testBadHelper()
    {
        $this->expectException(Exception::class);
        $view = new View();
        $view->assign('name', 'variable');
        $s = $view->render('tests/views/helper');

        // expectException doesn't seem to clear phpUnit's output buffer
        ob_end_clean();
    }


    public function testEmbedAndGetFile()
    {
        $expected = "File: ./tests/views/header.phtml\n";
        $expected .= "File: ./tests/views/body.phtml";
        $view = new View();
        $view->render('tests/views/body');
        $this->expectOutputString($expected);
    }
}
