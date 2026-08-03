<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Facades\DB;
use Modules\FamilyTree\Models\FtActivityLog;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;

class FamilyTreeMemberService
{
    public function create(FtFamily $family, array $data): FtMember
    {
        return DB::transaction(function () use ($family, $data) {
            $data['company_id'] = $family->company_id;
            $data['family_id'] = $family->id;
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $member = FtMember::create($data);

            $this->log($family, 'member.created', $member, [
                'name' => $member->full_name,
                'gender' => $member->gender,
            ]);

            return $member;
        });
    }

    public function update(FtFamily $family, FtMember $member, array $data): FtMember
    {
        return DB::transaction(function () use ($member, $data) {
            $before = $member->only(['full_name', 'life_status', 'marital_status']);
            $data['updated_by'] = auth()->id();

            $member->update($data);

            $this->log($member->family, 'member.updated', $member, [
                'before' => $before,
                'after' => $member->only(['full_name', 'life_status', 'marital_status']),
            ]);

            return $member;
        });
    }

    public function delete(FtMember $member): void
    {
        DB::transaction(function () use ($member) {
            $this->log($member->family, 'member.deleted', $member, [
                'name' => $member->full_name,
            ]);

            // Unlink children so the tree doesn't orphan
            FtMember::where('father_id', $member->id)->update(['father_id' => null]);
            FtMember::where('mother_id', $member->id)->update(['mother_id' => null]);

            $member->delete();
        });
    }

    /**
     * Link a father to a member — updates both father_id
     * and optionally sets father_name_text to null since the link now exists.
     */
    public function linkFather(FtMember $member, FtMember $father): void
    {
        $member->update([
            'father_id' => $father->id,
            'father_name_text' => null,
        ]);

        $this->log($member->family, 'member.father_linked', $member, [
            'father_id' => $father->id,
            'father_name' => $father->full_name,
        ]);
    }

    /**
     * Link a mother to a member.
     */
    public function linkMother(FtMember $member, FtMember $mother): void
    {
        $member->update([
            'mother_id' => $mother->id,
            'mother_name_text' => null,
        ]);

        $this->log($member->family, 'member.mother_linked', $member, [
            'mother_id' => $mother->id,
            'mother_name' => $mother->full_name,
        ]);
    }

    protected function log(FtFamily $family, string $action, FtMember $subject, array $properties = []): void
    {
        FtActivityLog::create([
            'company_id' => $family->company_id,
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => 'FtMember',
            'subject_id' => $subject->id,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}