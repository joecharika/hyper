<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Extension;

use Twig\NodeVisitor\OptimizerNodeVisitor;

final class OptimizerExtension extends AbstractExtension
{
    private $optimizers;

    public function __construct($optimizers = -1)
    {
        $this->optimizers = $optimizers;
    }

    public function getNodeVisitors()
    {
        return [new OptimizerNodeVisitor($this->optimizers)];
    }
}

class_alias('Twig\Extension\OptimizerExtension', 'Twig_Extension_Optimizer');
