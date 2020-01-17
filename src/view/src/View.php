<?php

namespace Mix\View;

/**
 * Class View
 * @package Mix\View
 * @author liu,jian <coder.keda@gmail.com>
 */
class View
{

    /**
     * @var string
     */
    public $dir = '';

    /**
     * @var string
     */
    public $layout = '';

    /**
     * View constructor.
     * @param string $layout
     * @param string $dir
     */
    public function __construct(string $dir, string $layout = '')
    {
        $this->dir    = $dir;
        $this->layout = $layout;
    }

    /**
     * 渲染视图 (包含布局)
     * @param string $name
     * @param array $data
     * @return string
     */
    public function render(string $name, array $data = []): string
    {
        $layout = $this->layout;
        if (!$layout) {
            $renderer = new Renderer();
            return $renderer->render($this->dir, $name, $data);
        }
        $renderer        = new Renderer();
        $data['content'] = $renderer->render($this->dir, $name, $data);
        return $renderer->render($this->dir, "layouts.{$layout}", $data);
    }

}
