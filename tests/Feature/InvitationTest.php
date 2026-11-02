<?php

namespace Tests\Feature;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Mail::fake();
    }

    private function makeUser(string $role, ?Company $company = null): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $role)->value('id'),
            'company_id' => $company?->id,
        ]);
    }

    public function test_superadmin_can_invite_admin_in_new_company(): void
    {
        $superAdmin = $this->makeUser('SuperAdmin');

        $this->actingAs($superAdmin)
            ->post(route('superadmin.invite-client'), [
                'company_name' => 'Acme',
                'email' => 'admin@acme.com',
            ])
            ->assertRedirect();

        $company = Company::where('name', 'Acme')->firstOrFail();
        $invitation = Invitation::where('email', 'admin@acme.com')->firstOrFail();

        $this->assertEquals($company->id, $invitation->company_id);
        $this->assertEquals('Admin', $invitation->role->name);
        Mail::assertSent(InvitationMail::class);
    }

    public function test_superadmin_dashboard_shows_two_clients_and_view_all_shows_all(): void
    {
        $superAdmin = $this->makeUser('SuperAdmin');

        foreach (['Client A', 'Client B', 'Client C'] as $name) {
            Company::create(['name' => $name]);
        }

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('Showing 2 of total 3');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.clients'))
            ->assertOk()
            ->assertSee(['Client A', 'Client B', 'Client C']);
    }

    public function test_admin_can_invite_member_in_own_company(): void
    {
        $acme = Company::create(['name' => 'Acme']);
        $admin = $this->makeUser('Admin', $acme);

        $this->actingAs($admin)
            ->post(route('admin.invite-member'), [
                'name' => 'New Member',
                'email' => 'member@acme.com',
                'role' => 'Member',
            ])
            ->assertRedirect();

        $invitation = Invitation::where('email', 'member@acme.com')->firstOrFail();

        $this->assertEquals($acme->id, $invitation->company_id);
        $this->assertEquals('Member', $invitation->role->name);
    }

    public function test_member_cannot_invite_users(): void
    {
        $member = $this->makeUser('Member', Company::create(['name' => 'Acme']));

        $this->actingAs($member)
            ->post(route('admin.invite-member'), [
                'name' => 'Someone',
                'email' => 'someone@acme.com',
                'role' => 'Member',
            ])
            ->assertForbidden();
    }

    public function test_invited_user_can_accept_invitation(): void
    {
        $acme = Company::create(['name' => 'Acme']);

        $invitation = Invitation::create([
            'company_id' => $acme->id,
            'role_id' => Role::where('name', 'Member')->value('id'),
            'email' => 'member@acme.com',
            'token' => 'test-token',
            'expires_at' => now()->addDay(),
        ]);

        $this->post(route('invitations.accept', $invitation->token), [
            'name' => 'New Member',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/');

        $user = User::where('email', 'member@acme.com')->firstOrFail();

        $this->assertEquals($acme->id, $user->company_id);
        $this->assertTrue($user->isMember());
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertAuthenticatedAs($user);
    }
}
