<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * Ensures that user can register
     *
     * @return void
     */
    public function test_user_can_register()
    {
        // send request with creds
        $response = $this->postJson('api/user/register', [
            'name' => "MaestroError",
            'email' => "maestro@example.com",
            "password" => "12345678",
            "password_confirmation" => "12345678",
        ]);

        // check status
        $response->assertStatus(201)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['token', 'user'])
        );
    
    }

    /**
     * Ensures that user can login and get api token
     *
     * @return void
     */
    public function test_user_can_login()
    {
        // create user with custom password
        $customPassword = "12345678";
        $user = User::factory([
            "password" => bcrypt($customPassword),
        ])->create();

        // make login request
        $response = $this->postJson('api/user/login', [
            "email" => $user->email,
            "password" => $customPassword,
        ]);

        // check status and json
        $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['token', 'user'])
        );
    
    }

    /**
     * Tests if user can reach protected route 
     * delete tokens and logout
     *
     * @return void
     */
    public function test_user_can_reach_protected_route_and_logout() 
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')->postJson('api/user/logout')->assertOk();
    }
}
