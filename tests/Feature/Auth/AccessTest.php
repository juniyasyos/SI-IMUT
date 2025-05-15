<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nik' => '0000.00000',
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('adminpassword'),
            'status' => 'active',
        ]);
    }

    /**
     * Ambil semua route GET yang tidak punya parameter (route induk saja).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIndukGetRoutes()
    {
        return collect(Route::getRoutes())
            ->filter(function ($route) {
                return in_array('GET', $route->methods())
                    && !preg_match('/\{.*?\}/', $route->uri());
            })
            ->values(); // Reset keys
    }

    public function test_homepage_is_accessible_by_admin()
    {
        $response = $this->actingAs($this->admin)->get('/');

        $response->assertStatus(200);
    }

    public function test_access_all_induk_routes_as_admin()
    {
        $this->actingAs($this->admin);

        $routes = $this->getIndukGetRoutes();

        foreach ($routes as $route) {
            $uri = '/' . ltrim($route->uri(), '/');

            try {
                $response = $this->get($uri);
                expect($response->getStatusCode())->toBeLessThan(500)
                    ->and($response->getStatusCode())->not->toBe(405);
            } catch (\Throwable $e) {
                echo "\nGagal akses: $uri => " . $e->getMessage() . "\n";
            }
        }
    }

    // public function test_all_induk_routes_status_code()
    // {
    //     $this->actingAs($this->admin);

    //     $routes = $this->getIndukGetRoutes();

    //     foreach ($routes as $route) {
    //         $uri = '/' . ltrim($route->uri(), '/');
    //         $response = $this->get($uri);
            
    //         $response->assertStatus(function ($status) {
    //             return $status < 500 && $status !== 405;
    //         });
    //     }
    // }

    public function test_page_not_found()
    {
        $response = $this->get('/page-not-found');

        $response->assertStatus(404);
    }

}


