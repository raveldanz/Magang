<?php

namespace Tests\Feature\Chat;

use App\Models\Placement;

class ChatContextButtonsTest extends ChatTestCase
{
    public function test_mentor_student_detail_offers_chat_and_guidance_group(): void
    {
        $placement = Placement::firstOrFail();

        $this->actingAs($this->mentorX)->get(route('mentor.students.show', $placement->id))
            ->assertOk()
            ->assertSee('Chat Mahasiswa')
            ->assertSee(route('chat.placement', $placement->id), false);
    }

    public function test_admin_application_detail_offers_chat_with_applicant(): void
    {
        $placement = Placement::firstOrFail();

        $this->actingAs($this->adminX)->get(route('admin.applications.show', $placement->application_id))
            ->assertOk()
            ->assertSee('Chat Pelamar');
    }

    public function test_chat_button_is_hidden_for_users_outside_contact_rules(): void
    {
        $this->assertStringNotContainsString('Chat Admin', $this->blade('<x-chat-button :user="$u" label="Chat Admin" />', ['u' => $this->adminY])->__toString(), 'Tanpa login tombol tidak boleh tampil.');

        $this->actingAs($this->studentA);
        $this->assertStringNotContainsString('Chat Admin', $this->blade('<x-chat-button :user="$u" label="Chat Admin" />', ['u' => $this->adminY])->__toString());
        $this->assertStringContainsString('Chat Mentor', $this->blade('<x-chat-button :user="$u" label="Chat Mentor" />', ['u' => $this->mentorX])->__toString());
    }
}
