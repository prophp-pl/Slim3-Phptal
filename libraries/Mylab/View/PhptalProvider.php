<?php

namespace Mylab\View;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @copyright (c) 2015, MyLab (http://mylab.pl)
 * @version $Id: 1 2015-11-21 13:17:47 $;
 */
class PhptalProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['view'] = function($c) {
            $settings = $c->get('settings');
            $view = new PhptalView($settings['view']);
            return $view;
        };
    }

}
