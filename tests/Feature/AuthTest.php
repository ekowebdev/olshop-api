<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login()
    {
        User::create([
            'name' => 'Testing',
            'username' => 'testing',
            'email'=> 'test@mail.com',
            'password' => bcrypt('123456')
        ]);
        $response = $this->postJson('/api/id/login', [
            'username' => 'testing',
            'password' => '123456',
        ]);
        $response->assertStatus(200);
        $this->assertArrayHasKey('access_token', $response->json()['data']);
    }
}
