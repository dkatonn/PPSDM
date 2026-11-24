<?php

namespace Tests\Feature;

use App\Models\Kerjasama;
use App\Models\Ptn;
use App\Models\RuangLingkup;
use App\Models\Upt;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\DokumenKerjasama;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAndCooperationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_directory_lists_active_and_ended_partnerships_but_hides_drafts(): void
    {
        $activePtn = Ptn::create(['name' => 'Universitas Negeri Contoh', 'province' => 'Jawa Barat']);
        $endedPtn = Ptn::create(['name' => 'Universitas Lama', 'province' => 'Jawa Timur']);
        $draftPtn = Ptn::create(['name' => 'Universitas Draft', 'province' => 'Banten']);

        Kerjasama::create(['ptn_id' => $activePtn->id, 'title' => 'Kolaborasi aktif', 'agreement_type' => 'ka', 'status' => 'aktif', 'starts_at' => today()->subMonth(), 'ends_at' => today()->addYear()]);
        Kerjasama::create(['ptn_id' => $endedPtn->id, 'title' => 'Kolaborasi selesai', 'agreement_type' => 'pks', 'status' => 'berakhir', 'ends_at' => today()->subDay()]);
        Kerjasama::create(['ptn_id' => $draftPtn->id, 'title' => 'Kolaborasi draft', 'agreement_type' => 'pks', 'status' => 'draft']);

        $this->get('/')->assertOk()->assertSee('Universitas Negeri Contoh')->assertSee('Universitas Lama')->assertDontSee('Universitas Draft');
        $this->get('/mitra/'.$activePtn->id)->assertOk()->assertSee('Kolaborasi aktif');
        $this->get('/mitra/'.$draftPtn->id)->assertNotFound();
    }

    public function test_admin_can_create_cooperation_with_many_upts_and_scopes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ptn = Ptn::create(['name' => 'PTN Demo', 'province' => 'DKI Jakarta']);
        $uptA = Upt::create(['name' => 'UPT Jakarta', 'province' => 'DKI Jakarta', 'status' => 'operasional']);
        $uptB = Upt::create(['name' => 'UPT Bogor', 'province' => 'Jawa Barat', 'status' => 'operasional']);
        $scope = RuangLingkup::create(['name' => 'Magang']);

        $this->actingAs($admin)->post(route('kerjasama.store'), [
            'ptn_id' => $ptn->id,
            'title' => 'Program Magang Karantina',
            'agreement_type' => 'pks',
            'status' => 'aktif',
            'starts_at' => today()->toDateString(),
            'upt_ids' => [$uptA->id, $uptB->id],
            'scope_ids' => [$scope->id],
        ])->assertRedirect();

        $agreement = Kerjasama::firstOrFail();
        $this->assertCount(2, $agreement->upt);
        $this->assertSame('Magang', $agreement->ruangLingkup->first()->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'kerjasama.created']);
        $this->actingAs($admin)->get(route('kerjasama.show', $agreement))->assertOk()->assertSee('Program Magang Karantina');
    }

    public function test_internal_module_pages_render_for_an_authenticated_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ptn = Ptn::create(['name' => 'PTN Halaman', 'province' => 'Jawa Tengah']);
        $upt = Upt::create(['name' => 'UPT Halaman', 'province' => 'Jawa Tengah', 'status' => 'operasional']);
        $scope = RuangLingkup::create(['name' => 'Perkuliahan']);
        $agreement = Kerjasama::create(['ptn_id' => $ptn->id, 'title' => 'Kerja Sama Halaman', 'agreement_type' => 'ka', 'status' => 'aktif']);
        $agreement->upt()->attach($upt);
        $agreement->ruangLingkup()->attach($scope);

        foreach ([
            route('dashboard'), route('ptn.index'), route('ptn.create'), route('ptn.show', $ptn),
            route('upt.index'), route('upt.create'), route('scope.index'), route('kerjasama.index'),
            route('kerjasama.create'), route('kerjasama.show', $agreement), route('kerjasama.edit', $agreement),
            route('documents.index'), route('implementation.index'), route('monitoring.index'), route('reports.index'),
            route('notifications.index'),
            route('profile.edit'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_scheduled_command_creates_idempotent_six_month_reminders(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ptn = Ptn::create(['name' => 'PTN Pengingat', 'province' => 'Banten']);
        Kerjasama::create(['ptn_id' => $ptn->id, 'title' => 'Kerja Sama Berakhir', 'agreement_type' => 'pks', 'status' => 'aktif', 'ends_at' => today()->addMonthsNoOverflow(6)]);

        $this->artisan('sipena:remind-expiring')->assertSuccessful();
        $this->artisan('sipena:remind-expiring')->assertSuccessful();

        $this->assertSame(1, Notifikasi::where('user_id', $user->id)->count());
    }

    public function test_admin_can_import_excel_compatible_csv_ptn_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $file = UploadedFile::fake()->createWithContent('ptn.csv', "Nama PTN,Jenis,Provinsi,Kabupaten/Kota\nUniversitas CSV,Universitas,Jawa Barat,Bandung\n");

        $this->actingAs($admin)->post(route('ptn.import'), ['file' => $file])->assertRedirect();
        $this->assertDatabaseHas('ptn', ['name' => 'Universitas CSV', 'province' => 'Jawa Barat', 'city' => 'Bandung']);
    }

    public function test_uploaded_agreement_documents_are_downloaded_through_authenticated_routes(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ptn = Ptn::create(['name' => 'PTN Arsip', 'province' => 'Bali']);
        $agreement = Kerjasama::create(['ptn_id' => $ptn->id, 'title' => 'Dokumen privat', 'agreement_type' => 'pks', 'status' => 'aktif']);
        $file = UploadedFile::fake()->createWithContent('pks.pdf', "%PDF-1.4\nDokumen uji");

        $this->actingAs($admin)->post(route('documents.store', $agreement), ['type' => 'pks', 'number' => 'PKS-001', 'file' => $file])->assertRedirect();
        $document = DokumenKerjasama::firstOrFail();
        Storage::disk('local')->assertExists($document->path);
        $this->get(route('documents.download', $document))->assertOk();

        auth()->logout();
        $this->get(route('documents.download', $document))->assertRedirect(route('login'));
    }

    public function test_only_documents_explicitly_marked_public_are_shown_on_public_ptn_page(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ptn = Ptn::create(['name' => 'PTN Dokumen Publik', 'province' => 'Jawa Barat']);
        $agreement = Kerjasama::create(['ptn_id' => $ptn->id, 'title' => 'Kerja Sama dengan Dokumen', 'agreement_type' => 'pks', 'status' => 'aktif', 'starts_at' => today(), 'ends_at' => today()->addYear()]);

        $this->actingAs($admin)->post(route('documents.store', $agreement), [
            'type' => 'pks', 'number' => 'PKS-PUB-01', 'is_public' => '1',
            'file' => UploadedFile::fake()->createWithContent('pks-publik.pdf', "%PDF-1.4\nPublik"),
        ])->assertRedirect();
        $publicDocument = DokumenKerjasama::latest('id')->firstOrFail();

        $this->post(route('documents.store', $agreement), [
            'type' => 'ia', 'number' => 'IA-INT-01',
            'file' => UploadedFile::fake()->createWithContent('ia-internal.pdf', "%PDF-1.4\nInternal"),
        ])->assertRedirect();
        $privateDocument = DokumenKerjasama::latest('id')->firstOrFail();

        $this->get(route('public.ptn.show', $ptn))->assertOk()->assertSee('pks-publik.pdf')->assertDontSee('ia-internal.pdf');
        $this->get(route('public.documents.download', $publicDocument))->assertOk();
        $this->get(route('public.documents.download', $privateDocument))->assertNotFound();
    }

    public function test_ptn_created_in_internal_form_can_become_a_public_partner(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->post(route('ptn.store'), [
            'name' => 'Universitas Mitra Demo',
            'type' => 'Universitas',
            'province' => 'Jawa Barat',
        ])->assertRedirect(route('ptn.index'));

        $ptn = Ptn::where('name', 'Universitas Mitra Demo')->firstOrFail();
        $this->get(route('kerjasama.create', ['ptn_id' => $ptn->id, 'status' => 'aktif']))
            ->assertOk()->assertSee('value="aktif" selected', false);

        $this->actingAs($admin)->post(route('kerjasama.store'), [
            'ptn_id' => $ptn->id,
            'title' => 'Kerja Sama Aktif Universitas Mitra Demo',
            'agreement_type' => 'pks',
            'status' => 'aktif',
            'starts_at' => today()->toDateString(),
        ])->assertRedirect();

        $this->get('/')->assertOk()->assertSee('Universitas Mitra Demo');
    }

    public function test_registered_upt_coordinates_appear_as_public_map_markers(): void
    {
        Upt::create(['name' => 'UPT Titik Peta', 'province' => 'Jawa Barat', 'city' => 'Bandung', 'status' => 'operasional', 'latitude' => -6.9147, 'longitude' => 107.6098]);

        $this->get('/')->assertOk()->assertSee('upt-map')->assertSee('UPT Titik Peta');
    }

    public function test_future_active_agreement_is_published_with_upcoming_status_and_dates(): void
    {
        $ptn = Ptn::create(['name' => 'PTN Mulai Besok', 'province' => 'DKI Jakarta']);
        $agreement = Kerjasama::create(['ptn_id' => $ptn->id, 'title' => 'Kerja Sama Mulai Besok', 'agreement_type' => 'pks', 'status' => 'aktif', 'starts_at' => today()->addDay(), 'ends_at' => today()->addYear()]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('ptn.show', $ptn))
            ->assertOk()->assertSee('masih di masa depan')->assertSee($agreement->starts_at->format('d/m/Y'));
        $this->get('/')->assertOk()->assertSee($ptn->name)->assertSee('Akan datang')->assertSee('Tersisa');
    }
}
