<?php

namespace App\Providers;

use App\Attributes\Filter;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use ReflectionMethod;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->callAfterResolving('router', function ($router, $app) {
            $router->matched($this->assign(...));
        });
    }

    private function assign(RouteMatched $matched): void
    {
        $route = $matched->route;

        $method = new ReflectionMethod($route->getController(), $route->getActionMethod());
        $instance = $method->getAttributes(Filter::class)[0]->newInstance();

        $route->setParameter('filtered', $instance->run($matched->request));
    }
}
