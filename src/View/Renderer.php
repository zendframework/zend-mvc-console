<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Console\View;

use Zend\Filter\FilterChain;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Renderer\TreeRendererInterface;
use Zend\View\Resolver\ResolverInterface;

/**
 * Render console view models.
 *
 * Note: all non-public variables in this class are prefixed with "__". This is
 * to mark them as part of the internal implementation, and thus prevent
 * conflict with variables injected into the renderer.
 */
class Renderer implements RendererInterface, TreeRendererInterface
{
    /**
     * @var FilterChain
     */
    protected $__filterChain;

    /**
     * Constructor.
     *
     * @todo handle passing helper manager, options
     * @todo handle passing filter chain, options
     * @todo handle passing variables object, options
     * @todo handle passing resolver object, options
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($config = [])
    {
        $this->init();
    }

    /**
     * Set the script resolver.
     *
     * No-op.
     *
     * @param ResolverInterface $resolver
     * @return void
     */
    public function setResolver(ResolverInterface $resolver)
    {
    }

    /**
     * Return the template engine object.
     *
     * Returns the object instance, as it is its own template engine.
     *
     * @return self
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Allow custom object initialization when extending the Renderer.
     *
     * Triggered by {@link __construct() the constructor} as its final action.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set filter chain     o use for post-filtering script content.
     *
     * @param FilterChain $filters
     */
    public function setFilterChain(FilterChain $filters)
    {
        $this->__filterChain = $filters;
    }

    /**
     * Retrieve filter chain for post-filtering script content.
     *
     * @return null|FilterChain
     */
    public function getFilterChain()
    {
        return $this->__filterChain;
    }

    /**
     * Recursively processes all ViewModels and returns output.
     *
     * @param string|ModelInterface $model A ViewModel instance.
     * @param null|array|\Traversable $values Values to use when rendering. If
     *     none provided, uses those in the composed variables container.
     * @return string Console output.
     */
    public function render($model, $values = null)
    {
        if (! $model instanceof ModelInterface) {
            return '';
        }

        $result = '';
        foreach ($model->getOptions() as $setting => $value) {
            $method = 'set' . $setting;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            unset($method, $setting, $value);
        }

        $values = $model->getVariables();

        if (isset($values['result']) && ! isset($this->__filterChain)) {
            // append the result verbatim
            $result .= $values['result'];
        }

        if (isset($values['result']) && isset($this->__filterChain)) {
            // filter and append the result
            $result .= $this->getFilterChain()->filter($values['result']);
        }

        if ($model->hasChildren()) {
            // recursively render all children
            foreach ($model->getChildren() as $child) {
                $result .= $this->render($child, $values);
            }
        }

        return $result;
    }

    /**
     * @see Zend\View\Renderer\TreeRendererInterface
     * @return bool
     */
    public function canRenderTrees()
    {
        return true;
    }
}
