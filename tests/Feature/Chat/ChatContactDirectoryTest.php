<?php

namespace Tests\Feature\Chat;

use App\Models\User;
use App\Services\Chat\ChatContactDirectory;

class ChatContactDirectoryTest extends ChatTestCase
{
    private function contactNames(User $user): array
    {
        return app(ChatContactDirectory::class)->contactsQuery($user)->orderBy('name')->pluck('name')->all();
    }

    private function assertContacts(User $user, array $expected): void
    {
        $names = array_map(fn (User $u) => $u->name, $expected);
        sort($names);
        $this->assertSame($names, $this->contactNames($user), "Kontak {$user->name} tidak sesuai aturan.");
    }

    public function test_student_sees_campus_staff_applied_agency_admin_and_own_supervisors(): void
    {
        $this->assertContacts($this->studentA, [
            $this->superAdmin, $this->univAdminA, $this->dosenA, $this->adminX, $this->mentorX,
        ]);
    }

    public function test_pending_applicant_sees_admin_of_agency_applied_to(): void
    {
        $this->assertContacts($this->studentB, [$this->superAdmin, $this->univAdminB, $this->dosenB, $this->adminY]);
    }

    public function test_agency_admin_sees_colleagues_applicants_their_dpl_and_campus_admin(): void
    {
        $this->assertContacts($this->adminX, [
            $this->superAdmin, $this->mentorX, $this->mentorX2, $this->studentA, $this->dosenA, $this->univAdminA,
        ]);
    }

    public function test_mentor_sees_colleagues_mentees_and_mentees_dpl(): void
    {
        $this->assertContacts($this->mentorX, [
            $this->superAdmin, $this->adminX, $this->mentorX2, $this->studentA, $this->dosenA,
        ]);
    }

    public function test_lecturer_sees_campus_colleagues_students_and_advisee_agency_contacts(): void
    {
        // Mahasiswa nonaktif sekampus tidak ikut ditampilkan
        $this->assertContacts($this->dosenA, [
            $this->superAdmin, $this->univAdminA, $this->studentA, $this->mentorX, $this->adminX,
        ]);
    }

    public function test_campus_admin_sees_campus_staff_students_and_agencies_of_applicants(): void
    {
        $this->assertContacts($this->univAdminA, [
            $this->superAdmin, $this->dosenA, $this->studentA, $this->adminX,
        ]);
    }

    public function test_super_admin_sees_everyone_except_inactive_accounts(): void
    {
        $names = $this->contactNames($this->superAdmin);

        $this->assertCount(count($this->allUsers()) - 1, $names);
        $this->assertNotContains($this->inactiveStudent->name, $names);
        $this->assertNotContains($this->superAdmin->name, $names);
    }

    public function test_contact_rules_are_symmetric(): void
    {
        $directory = app(ChatContactDirectory::class);

        foreach ($this->allUsers() as $a) {
            foreach ($this->allUsers() as $b) {
                if ($a->is($b)) {
                    continue;
                }
                $this->assertSame(
                    $directory->canContact($a, $b),
                    $directory->canContact($b, $a),
                    "Aturan kontak {$a->name} ↔ {$b->name} tidak dua arah."
                );
            }
        }
    }
}
