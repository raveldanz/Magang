<?php

namespace Tests\Feature\Chat;

use App\Models\Application;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Placement;
use App\Models\User;

class ChatPlacementGroupTest extends ChatTestCase
{
    private function mentorGroup(): ChatConversation
    {
        return ChatConversation::where('scope_type', ChatConversation::SCOPE_MENTOR_GUIDANCE)
            ->where('created_by', $this->mentorX->id)
            ->firstOrFail();
    }

    private function dplGroup(): ChatConversation
    {
        return ChatConversation::where('scope_type', ChatConversation::SCOPE_DPL_GUIDANCE)
            ->where('created_by', $this->dosenA->id)
            ->firstOrFail();
    }

    private function memberIds(ChatConversation $conversation): array
    {
        return ChatParticipant::where('conversation_id', $conversation->id)
            ->orderBy('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function test_mentor_and_dpl_guidance_groups_are_created_automatically(): void
    {
        // 1 Mentor menaungi mahasiswa bimbingannya (studentA)
        $mentorGroup = $this->mentorGroup();
        $expectedMentorMembers = [$this->mentorX->id, $this->studentA->id];
        sort($expectedMentorMembers);
        $this->assertSame($expectedMentorMembers, $this->memberIds($mentorGroup));
        $this->assertSame("Bimbingan Mentor {$this->mentorX->name}", $mentorGroup->title);

        // DPL tidak boleh berada di grup bimbingan mentor
        $this->assertNotContains($this->dosenA->id, $this->memberIds($mentorGroup));

        // 1 DPL menaungi mahasiswa bimbingan kampusnya (studentA)
        $dplGroup = $this->dplGroup();
        $expectedDplMembers = [$this->dosenA->id, $this->studentA->id];
        sort($expectedDplMembers);
        $this->assertSame($expectedDplMembers, $this->memberIds($dplGroup));
        $this->assertSame("Bimbingan Dosen {$this->dosenA->name}", $dplGroup->title);

        // Mentor tidak boleh berada di grup bimbingan DPL
        $this->assertNotContains($this->mentorX->id, $this->memberIds($dplGroup));
    }

    public function test_mentor_group_holds_multiple_plotted_students(): void
    {
        // Tambahkan mahasiswa kedua (studentB) yang diplot ke mentorX
        $studentC = User::factory()->create([
            'name' => 'Candra Mahasiswa',
            'role' => 'mahasiswa',
            'university_id' => $this->studentA->university_id,
        ]);
        $appC = Application::create([
            'user_id' => $studentC->id,
            'unit_id' => Application::first()->unit_id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);
        Placement::create([
            'application_id' => $appC->id,
            'mentor_id' => $this->mentorX->id,
            'academic_advisor_id' => $this->dosenA->id,
        ]);

        $mentorGroup = $this->mentorGroup();
        $expectedMentorMembers = [$this->mentorX->id, $this->studentA->id, $studentC->id];
        sort($expectedMentorMembers);
        $this->assertSame($expectedMentorMembers, $this->memberIds($mentorGroup));

        // Event join tercatat
        $events = ChatMessage::where('conversation_id', $mentorGroup->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString("{$studentC->name} bergabung ke grup bimbingan mentor", $events);
    }

    public function test_changing_mentor_moves_student_between_mentor_groups(): void
    {
        $placement = Placement::firstOrFail();
        $placement->update(['mentor_id' => $this->mentorX2->id]);

        // studentA harus keluar dari grup mentor lama (mentorX)
        $oldGroup = $this->mentorGroup();
        $this->assertNotContains($this->studentA->id, $this->memberIds($oldGroup));

        // studentA harus masuk ke grup mentor baru (mentorX2)
        $newGroup = ChatConversation::where('scope_type', ChatConversation::SCOPE_MENTOR_GUIDANCE)
            ->where('created_by', $this->mentorX2->id)
            ->firstOrFail();
        $this->assertContains($this->studentA->id, $this->memberIds($newGroup));

        $events = ChatMessage::where('conversation_id', $oldGroup->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString("{$this->studentA->name} tidak lagi berada dalam bimbingan mentor ini", $events);

        // mentorX sudah tidak bisa lagi mengakses obrolan dengan studentA di grup bimbingan mentor2
        $this->actingAs($this->mentorX)->getJson(route('chat.api.messages.index', $newGroup->id))->assertForbidden();
    }

    public function test_changing_dpl_moves_student_between_dpl_groups(): void
    {
        $placement = Placement::firstOrFail();
        $placement->update(['academic_advisor_id' => $this->dosenB->id]);

        // studentA harus keluar dari grup DPL lama (dosenA)
        $oldGroup = $this->dplGroup();
        $this->assertNotContains($this->studentA->id, $this->memberIds($oldGroup));

        // studentA harus masuk ke grup DPL baru (dosenB)
        $newGroup = ChatConversation::where('scope_type', ChatConversation::SCOPE_DPL_GUIDANCE)
            ->where('created_by', $this->dosenB->id)
            ->firstOrFail();
        $this->assertContains($this->studentA->id, $this->memberIds($newGroup));

        $events = ChatMessage::where('conversation_id', $oldGroup->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString("{$this->studentA->name} tidak lagi berada dalam bimbingan dosen ini", $events);
    }

    public function test_student_resigning_is_removed_from_both_mentor_and_dpl_groups(): void
    {
        $app = Application::where('user_id', $this->studentA->id)->firstOrFail();
        $app->update(['status' => 'resigned']);

        $mentorGroup = $this->mentorGroup();
        $dplGroup = $this->dplGroup();

        $this->assertNotContains($this->studentA->id, $this->memberIds($mentorGroup));
        $this->assertNotContains($this->studentA->id, $this->memberIds($dplGroup));

        $mentorEvents = ChatMessage::where('conversation_id', $mentorGroup->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString('dikeluarkan dari grup bimbingan karena telah mengundurkan diri (resigned)', $mentorEvents);

        $dplEvents = ChatMessage::where('conversation_id', $dplGroup->id)->pluck('body')->implode(' | ');
        $this->assertStringContainsString('dikeluarkan dari grup bimbingan karena telah mengundurkan diri (resigned)', $dplEvents);
    }

    public function test_guidance_group_is_managed_by_system(): void
    {
        $group = $this->mentorGroup();

        $this->actingAs($this->studentA)->postJson(route('chat.api.groups.leave', $group->id))->assertUnprocessable();
        $this->actingAs($this->mentorX)->patchJson(route('chat.api.groups.update', $group->id), ['title' => 'Ganti Nama'])->assertForbidden();

        $this->sendMessage($this->mentorX, $group->id, ['body' => 'Tolong unggah logbook minggu ini ya.'])->assertCreated();
        $this->actingAs($this->studentA)->getJson(route('chat.api.summary'))->assertJsonPath('unread_total', 1);
    }

    public function test_placement_link_redirects_to_appropriate_group(): void
    {
        $placement = Placement::firstOrFail();
        $mentorGroup = $this->mentorGroup();
        $dplGroup = $this->dplGroup();

        // Mahasiswa default diarahkan ke grup bimbingan mentornya
        $this->actingAs($this->studentA)->get(route('chat.placement', $placement->id))->assertRedirect(route('chat.show', $mentorGroup->id));
        // Mahasiswa dengan parameter type=dpl diarahkan ke grup DPL
        $this->actingAs($this->studentA)->get(route('chat.placement', ['placement' => $placement->id, 'type' => 'dpl']))->assertRedirect(route('chat.show', $dplGroup->id));

        // Mentor diarahkan ke grup bimbingan mentornya
        $this->actingAs($this->mentorX)->get(route('chat.placement', $placement->id))->assertRedirect(route('chat.show', $mentorGroup->id));

        // DPL diarahkan ke grup bimbingan DPL-nya
        $this->actingAs($this->dosenA)->get(route('chat.placement', $placement->id))->assertRedirect(route('chat.show', $dplGroup->id));

        // Pihak luar tidak punya akses
        $this->actingAs($this->adminY)->get(route('chat.placement', $placement->id))->assertForbidden();
    }

    public function test_sync_command_creates_mentor_and_dpl_groups(): void
    {
        ChatConversation::query()->delete();

        $this->artisan('chat:sync-placement-groups')->assertSuccessful();
        $this->assertSame(1, ChatConversation::where('scope_type', ChatConversation::SCOPE_MENTOR_GUIDANCE)->count());
        $this->assertSame(1, ChatConversation::where('scope_type', ChatConversation::SCOPE_DPL_GUIDANCE)->count());
    }
}
