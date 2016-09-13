<?php
namespace Hrgruri\Saori\Generator;

class Environment
{
    private $maker;
    private $twig;
    private $paths;

    public function __construct(
        \Hrgruri\Saori\Maker $maker,
        \Twig_Environment $twig,
        array $paths
    ) {
        $this->maker    = $maker;
        $this->twig     = $twig;
        $this->paths    = $paths;
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
