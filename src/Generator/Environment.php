<?php
namespace Taniko\Saori\Generator;

class Environment
{
    const SIZE = 10;
    private $size;
    private $maker;
    private $twig;
    private $paths;

    public function __construct(
        \Taniko\Saori\Maker $maker,
        \Twig_Environment $twig,
        array $paths
    ) {
        $this->maker    = $maker;
        $this->twig     = $twig;
        $this->paths    = $paths;
        $size = $this->maker->theme('size') ?? self::SIZE;
        $this->size = (is_int($size) && $size > 0) ? $size : self::SIZE;
    }

    /**
     * get property of Environment or Maker
     * @param  string $name
     * @throws LogicException if failed getting property
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->$name)) {
            $result = $this->$name;
        } else {
            try {
                $result = $this->maker->$name;
            } catch (\LogicException $e) {
                throw new \LogicException("undefined property({$name})\n");
            }
        }
        return $result;
    }
}
