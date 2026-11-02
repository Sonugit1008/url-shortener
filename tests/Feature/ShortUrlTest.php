<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\ShortUrl;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function makeUser(string $role, ?Company $company = null): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $role)->value('id'),
            'company_id' => $company?->id,
        ]);
    }

    private function makeShortUrl(User $user, string $code): ShortUrl
    {
        return ShortUrl::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'original_url' => 'https://example.com/' . $code,
            'short_code' => $code,
        ]);
    }

    public function test_admin_can_create_short_url(): void
    {
        $admin = $this->makeUser('Admin', Company::create(['name' => 'Acme']));

        $this->actingAs($admin)
            ->post(route('short-urls.store'), ['original_url' => 'https://google.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('short_urls', [
            'user_id' => $admin->id,
            'company_id' => $admin->company_id,
            'original_url' => 'https://google.com',
        ]);
    }

    public function test_member_can_create_short_url(): void
    {
        $member = $this->makeUser('Member', Company::create(['name' => 'Acme']));

        $this->actingAs($member)
            ->post(route('short-urls.store'), ['original_url' => 'https://google.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('short_urls', ['user_id' => $member->id]);
    }

    public function test_superadmin_cannot_create_short_url(): void
    {
        $superAdmin = $this->makeUser('SuperAdmin');

        $this->actingAs($superAdmin)
            ->post(route('short-urls.store'), ['original_url' => 'https://google.com'])
            ->assertForbidden();

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_admin_can_only_see_short_urls_of_own_company(): void
    {
        $acme = Company::create(['name' => 'Acme']);
        $other = Company::create(['name' => 'Other']);

        $admin = $this->makeUser('Admin', $acme);
        $acmeMember = $this->makeUser('Member', $acme);
        $otherMember = $this->makeUser('Member', $other);

        $this->makeShortUrl($admin, 'ADMIN1');
        $this->makeShortUrl($acmeMember, 'ACME01');
        $this->makeShortUrl($otherMember, 'OTHER1');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('ADMIN1')
            ->assertSee('ACME01')
            ->assertDontSee('OTHER1');
    }

    public function test_member_can_only_see_own_short_urls(): void
    {
        $acme = Company::create(['name' => 'Acme']);

        $member = $this->makeUser('Member', $acme);
        $anotherMember = $this->makeUser('Member', $acme);

        $this->makeShortUrl($member, 'MINE01');
        $this->makeShortUrl($anotherMember, 'NOTME1');

        $this->actingAs($member)
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('MINE01')
            ->assertDontSee('NOTME1');
    }

    public function test_superadmin_can_see_short_urls_of_every_company(): void
    {
        $superAdmin = $this->makeUser('SuperAdmin');

        $this->makeShortUrl($this->makeUser('Member', Company::create(['name' => 'Acme'])), 'ACME01');
        $this->makeShortUrl($this->makeUser('Member', Company::create(['name' => 'Other'])), 'OTHER1');

        $this->actingAs($superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('ACME01')
            ->assertSee('OTHER1');
    }

    public function test_dashboard_shows_two_urls_and_view_all_shows_own_company_urls(): void
    {
        $acme = Company::create(['name' => 'Acme']);
        $admin = $this->makeUser('Admin', $acme);
        $otherMember = $this->makeUser('Member', Company::create(['name' => 'Other']));

        $this->makeShortUrl($admin, 'ACME01');
        $this->makeShortUrl($admin, 'ACME02');
        $this->makeShortUrl($admin, 'ACME03');
        $this->makeShortUrl($otherMember, 'OTHER1');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Showing 2 of total 3');

        $this->actingAs($admin)
            ->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee(['ACME01', 'ACME02', 'ACME03'])
            ->assertDontSee('OTHER1');
    }

    public function test_member_cannot_open_view_all_page(): void
    {
        $member = $this->makeUser('Member', Company::create(['name' => 'Acme']));

        $this->actingAs($member)
            ->get(route('short-urls.index'))
            ->assertForbidden();
    }

    public function test_short_url_is_publicly_resolvable_and_redirects(): void
    {
        $member = $this->makeUser('Member', Company::create(['name' => 'Acme']));
        $shortUrl = $this->makeShortUrl($member, 'PUBLIC');

        // Not logged in
        $this->get('/s/PUBLIC')->assertRedirect('https://example.com/PUBLIC');

        $this->assertEquals(1, $shortUrl->fresh()->clicks);
    }

    public function test_unknown_short_code_returns_404(): void
    {
        $this->get('/s/NOPE00')->assertNotFound();
    }
}
