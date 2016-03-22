<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\View;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Filter\FilterChain;
use Zend\Mvc\Console\View\Renderer;
use Zend\View\Model\ModelInterface;
use Zend\View\Resolver\ResolverInterface;

class RendererTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = new Renderer();
    }

    public function testGetEngineReturnsRendererInstance()
    {
        $this->assertSame($this->renderer, $this->renderer->getEngine());
    }

    public function testFilterChainIsNullByDefault()
    {
        $this->assertNull($this->renderer->getFilterChain());
    }

    public function testFilterChainIsMutable()
    {
        $filters = $this->prophesize(FilterChain::class)->reveal();
        $this->renderer->setFilterChain($filters);
        $this->assertSame($filters, $this->renderer->getFilterChain());
    }

    public function testRendereReportsItCanRenderTrees()
    {
        $this->assertTrue($this->renderer->canRenderTrees());
    }

    public function invalidModels()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'string'     => ['string'],
            'array'      => [['string']],
            'stdClass'   => [(object)['content' => 'string']],
        ];
    }

    /**
     * @dataProvider invalidModels
     */
    public function testRenderReturnsEmptyStringForNonViewModelArguments($model)
    {
        $this->assertSame('', $this->renderer->render($model));
    }

    public function testModelOptionsCanSetInstanceValues()
    {
        $filters = $this->prophesize(FilterChain::class)->reveal();
        $model = $this->prophesize(ModelInterface::class);
        $model->getOptions()->willReturn([
            'filterchain' => $filters,
        ]);
        $model->getVariables()->willReturn([]);
        $model->hasChildren()->willReturn(false);

        $this->assertSame('', $this->renderer->render($model->reveal()));
        $this->assertSame($filters, $this->renderer->getFilterChain());
    }

    public function testRendersModelResult()
    {
        $model = $this->prophesize(ModelInterface::class);
        $model->getOptions()->willReturn([]);
        $model->getVariables()->willReturn([
            'result' => 'RESULT',
        ]);
        $model->hasChildren()->willReturn(false);

        $this->assertSame('RESULT', $this->renderer->render($model->reveal()));
    }

    public function testRenderCanFilterResult()
    {
        $filters = $this->prophesize(FilterChain::class);
        $filters->filter('RESULT')->willReturn('FILTERED');
        $this->renderer->setFilterChain($filters->reveal());

        $model = $this->prophesize(ModelInterface::class);
        $model->getOptions()->willReturn([]);
        $model->getVariables()->willReturn([
            'result' => 'RESULT',
        ]);
        $model->hasChildren()->willReturn(false);

        $this->assertSame('FILTERED', $this->renderer->render($model->reveal()));
    }

    public function testRendersChildrenByAppendingThemToResult()
    {
        $child = $this->prophesize(ModelInterface::class);
        $child->getOptions()->willReturn([]);
        $child->getVariables()->willReturn([
            'result' => 'CHILD',
        ]);
        $child->hasChildren()->willReturn(false);

        $model = $this->prophesize(ModelInterface::class);
        $model->getOptions()->willReturn([]);
        $model->getVariables()->willReturn([
            'result' => 'RESULT',
        ]);
        $model->hasChildren()->willReturn(true);
        $model->getChildren()->willReturn([$child->reveal()]);

        $this->assertSame('RESULTCHILD', $this->renderer->render($model->reveal()));
    }
}
