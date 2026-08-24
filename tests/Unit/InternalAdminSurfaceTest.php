<?php

namespace Tests\Unit;

use App\Http\Middleware\InitializePlugins;
use App\Http\Routes\V2\AdminRoute;
use App\Services\Plugin\PluginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class InternalAdminSurfaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.internal_free_mode' => true,
            'app.settings_cache_store' => 'array',
        ]);
        app()->forgetScopedInstances();
    }

    public function test_internal_admin_routes_keep_nodes_but_remove_machines_plugins_and_themes(): void
    {
        $router = new Router(app('events'), app());
        (new AdminRoute())->map($router);

        $uris = collect($router->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->values();

        $this->assertTrue($uris->contains(fn ($uri) => str_contains($uri, 'server/manage')));
        $this->assertTrue($uris->contains(fn ($uri) => str_contains($uri, 'server/group')));
        $this->assertFalse($uris->contains(fn ($uri) => str_contains($uri, 'server/machine')));
        $this->assertFalse($uris->contains(fn ($uri) => str_contains($uri, '/plugin')));
        $this->assertFalse($uris->contains(fn ($uri) => str_contains($uri, '/theme')));
    }

    public function test_internal_requests_do_not_initialize_plugins(): void
    {
        /** @var PluginManager&MockInterface $pluginManager */
        $pluginManager = Mockery::mock(PluginManager::class);
        $pluginManager->shouldNotReceive('initializeEnabledPlugins');

        $middleware = new InitializePlugins($pluginManager);
        $response = $middleware->handle(
            Request::create('/api/v1/user/info'),
            fn () => response('ok')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }
}
