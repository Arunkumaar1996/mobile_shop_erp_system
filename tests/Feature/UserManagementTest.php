<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Branch $branch;
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'HQ Branch',
            'code' => 'HQ001',
            'status' => true,
        ]);

        $this->adminRole = Role::create([
            'name' => 'super-admin',
            'display_name' => 'Super Admin',
        ]);

        $this->adminUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_view_users_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_store_user(): void
    {
        $role = Role::create([
            'name' => 'cashier',
            'display_name' => 'Cashier',
        ]);

        $response = $this->actingAs($this->adminUser)->post('/users', [
            'branch_id' => $this->branch->id,
            'name' => 'Cashier Name',
            'username' => 'cashier1',
            'email' => 'cashier1@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role_id' => $role->id,
            'status' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'email' => 'cashier1@example.com',
            'username' => 'cashier1',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $role = Role::create([
            'name' => 'cashier',
            'display_name' => 'Cashier',
        ]);

        $targetUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Old Name',
            'username' => 'olduser',
            'email' => 'old@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);
        $targetUser->roles()->attach($role->id);

        $response = $this->actingAs($this->adminUser)->put("/users/{$targetUser->id}", [
            'branch_id' => $this->branch->id,
            'name' => 'Updated Name',
            'username' => 'updateduser',
            'email' => 'updated@example.com',
            'phone' => '9876543210',
            'role_id' => $role->id,
            'status' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'username' => 'updateduser',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Target User',
            'username' => 'target',
            'email' => 'target@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post("/users/{$targetUser->id}/toggle-status");
        $response->assertStatus(200);
        $this->assertEquals(0, $targetUser->fresh()->status);
    }

    public function test_admin_can_delete_user(): void
    {
        $targetUser = User::create([
            'branch_id' => $this->branch->id,
            'name' => 'Target User',
            'username' => 'target',
            'email' => 'target@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/users/{$targetUser->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);
    }
}
