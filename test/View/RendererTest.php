<?php
/**
 * @link      http://github.com/zendframework/zend-mvc-console for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\View;

use PHPUnit\Framework\TestCase;
use Zend\Filter\FilterChain;
use Zend\View\Model\ModelInterface;

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

    public function testAppendsChildrenToEachOtherAndThenAppendsParentResult()
    {
        $child1 = $this->prophesize(ModelInterface::class);
        $child1->getOptions()->willReturn([]);
        $child1->getVariables()->willReturn([
            'result' => 'CHILD1',
        ]);
        $child1->hasChildren()->willReturn(false);

        $child2 = $this->prophesize(ModelInterface::class);
        $child2->getOptions()->willReturn([]);
        $child2->getVariables()->willReturn([
            'result' => 'CHILD2',
        ]);
        $child2->hasChildren()->willReturn(false);

        $model = $this->prophesize(ModelInterface::class);
        $model->getOptions()->willReturn([]);
        $model->getVariables()->willReturn([
            'result' => 'RESULT',
        ]);
        $model->hasChildren()->willReturn(true);
        $model->getChildren()->willReturn([
            $child1->reveal(),
            $child2->reveal(),
        ]);

        $this->assertSame('CHILD1CHILD2RESULT', $this->renderer->render($model->reveal()));
    }
}
