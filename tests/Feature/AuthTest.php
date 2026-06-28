<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Test Branch',
            'code' => 'TB001',
            'status' => true,
        ]);

        $this->user = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Active User',
            'username' => 'activeuser',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'login' => 'active@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'active@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $suspendedUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Suspended User',
            'username' => 'suspendeduser',
            'email' => 'suspended@example.com',
            'password' => bcrypt('password'),
            'status' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'suspended@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
