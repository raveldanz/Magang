<?php

namespace Tests\Feature\Chat;

use App\Models\AuditLog;
use App\Models\StudentProfile;
use App\Models\University;
use App\Models\User;

/**
 * Info Kontak chat: email, telepon & data studi hanya untuk pihak yang punya hubungan magang langsung.
 */
class ChatContactDetailsTest extends ChatTestCase
{
    private User $dosenA2;

    protected function setUp(): void
    {
        parent::setUp();

        StudentProfile::create([
            'user_id' => $this->studentA->id,
            'nim' => '22050974001',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'Sistem Informasi',
            'semester' => 5,
            'phone' => '0812-3456-7890',
        ]);
        University::where('code', 'UNESA')->update(['phone' => '(031) 99424930', 'email' => 'humas@unesa.ac.id']);
        $this->dosenA->update(['phone' => '0813 1111 2222']);

        // Dosen sekampus yang BUKAN DPL studentA: boleh chat, tetapi tidak melihat data pribadi
        $this->dosenA2 = User::factory()->create(['name' => 'Dosen Lain', 'role' => 'dosen', 'university_id' => $this->studentA->university_id]);
    }

    private function contactDetails(User $viewer, User $other): array
    {
        return $this->actingAs($viewer)
            ->postJson(route('chat.start'), ['user_id' => $other->id])
            ->assertOk()
            ->json('conversation.contact.details');
    }

    public function test_mentor_sees_mentee_phone_email_and_study_data(): void
    {
        $details = $this->contactDetails($this->mentorX, $this->studentA);

        $this->assertTrue($details['visible']);
        $this->assertSame($this->studentA->email, $details['email']);
        $this->assertSame('0812-3456-7890', $details['phone']);
        $this->assertSame('https://wa.me/6281234567890', $details['whatsapp_url']);
        $this->assertContains(['label' => 'NIM', 'value' => '22050974001'], $details['fields']);
        $this->assertContains(['label' => 'Program Studi', 'value' => 'Sistem Informasi'], $details['fields']);
        $this->assertSame('Kontak Resmi Kampus', $details['institution']['label']);
    }

    public function test_student_sees_own_dpl_phone_from_account_settings(): void
    {
        $details = $this->contactDetails($this->studentA, $this->dosenA);

        $this->assertTrue($details['visible']);
        $this->assertSame('0813 1111 2222', $details['phone']);
        $this->assertSame('https://wa.me/6281311112222', $details['whatsapp_url']);
    }

    public function test_campus_lecturer_without_supervision_cannot_see_private_data(): void
    {
        $details = $this->contactDetails($this->dosenA2, $this->studentA);

        $this->assertFalse($details['visible']);
        $this->assertNull($details['email']);
        $this->assertNull($details['phone']);
        $this->assertNull($details['whatsapp_url']);
        $this->assertSame([], $details['fields']);
        // Kontak resmi kampus tetap terlihat (data publik instansi)
        $this->assertSame('(031) 99424930', $details['institution']['phone']);

        // Berlaku dua arah: mahasiswa juga tidak melihat nomor dosen yang bukan DPL-nya
        $this->dosenA2->update(['phone' => '0899 0000 1111']);
        $this->assertNull($this->contactDetails($this->studentA, $this->dosenA2)['phone']);
    }

    public function test_super_admin_sees_everyones_contact_data(): void
    {
        $details = $this->contactDetails($this->superAdmin, $this->studentA);

        $this->assertTrue($details['visible']);
        $this->assertSame('0812-3456-7890', $details['phone']);
    }

    public function test_group_members_details_follow_the_same_privacy_rule(): void
    {
        $groupId = $this->actingAs($this->univAdminA)
            ->postJson(route('chat.api.groups.store'), [
                'title' => 'Koordinasi DPL UNESA',
                'member_ids' => [$this->dosenA->id, $this->dosenA2->id, $this->studentA->id],
            ])->assertCreated()->json('conversation.id');

        $members = collect($this->actingAs($this->dosenA2)->getJson(route('chat.api.conversations.show', $groupId))
            ->assertOk()->json('conversation.members'))->keyBy('id');

        $this->assertFalse($members[$this->studentA->id]['details']['visible'], 'Dosen non-DPL tidak boleh melihat data mahasiswa di grup.');
        $this->assertTrue($members[$this->dosenA->id]['details']['visible'], 'Rekan dosen sekampus boleh saling melihat kontak.');
        $this->assertTrue($members[$this->univAdminA->id]['details']['visible']);
    }

    public function test_staff_can_save_phone_in_account_settings(): void
    {
        $this->actingAs($this->mentorX)->patch(route('profile.phone.update'), ['phone' => '+62 812 9999 8888'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasNoErrors();
        $this->assertSame('+62 812 9999 8888', $this->mentorX->fresh()->phone);

        $this->actingAs($this->mentorX)->patch(route('profile.phone.update'), ['phone' => 'bukan-nomor'])
            ->assertSessionHasErrors('phone');
        $this->assertSame('+62 812 9999 8888', $this->mentorX->fresh()->phone);

        // Mengosongkan nomor = tidak ditampilkan
        $this->actingAs($this->mentorX)->patch(route('profile.phone.update'), ['phone' => ''])->assertSessionHasNoErrors();
        $this->assertNull($this->mentorX->fresh()->phone);

        $this->actingAs($this->mentorX)->get(route('profile.edit'))->assertSee('Nomor Kontak untuk Chat');
        // Mahasiswa memakai nomor di Profil Saya
        $this->actingAs($this->studentA)->get(route('profile.edit'))->assertDontSee('Nomor Kontak untuk Chat');
        $this->actingAs($this->studentA)->patch(route('profile.phone.update'), ['phone' => '0811111111'])->assertRedirect(route('student.profile.edit'));
        $this->assertNull($this->studentA->fresh()->phone);
    }

    public function test_lecturer_phone_from_admin_form_is_no_longer_discarded(): void
    {
        $univ = University::where('code', 'UNESA')->firstOrFail();

        $this->actingAs($this->superAdmin)->post(route('admin.universities.dosens.store', $univ->id), [
            'name' => 'Dr. Baru Dosen',
            'email' => 'dosen.baru@unesa.ac.id',
            'phone' => '0877 1234 5678',
        ])->assertSessionHasNoErrors();

        $this->assertSame('0877 1234 5678', User::where('email', 'dosen.baru@unesa.ac.id')->value('phone'));
    }

    public function test_unread_chat_is_listed_first_in_navbar_bell(): void
    {
        // Banyak pemberitahuan otomatis lain (log audit) tidak boleh menenggelamkan pesan chat
        foreach (range(1, 6) as $i) {
            AuditLog::create(['user_name' => 'Sistem', 'user_role' => 'system', 'action' => "AKSI_{$i}"]);
        }
        $conversationId = $this->startConversation($this->studentA, $this->superAdmin);
        $this->sendMessage($this->studentA, $conversationId, ['body' => 'Halo Admin, mohon bantuan akun'])->assertCreated();

        $this->actingAs($this->superAdmin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee("Pesan baru dari {$this->studentA->name}");
    }
}
