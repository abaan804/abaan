<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;

class FamilyTreeRelationshipService
{
    public function __construct(protected FtMemberRepository $memberRepo)
    {
    }

    // ─── Derived Relationship Methods ─────────────────────────────────────────

    public function grandparents(FtMember $member): array
    {
        $paternal = [];
        $maternal = [];

        if ($member->father) {
            $father = $member->father;
            if ($father->father) $paternal[] = ['member' => $father->father, 'label' => __('Paternal Grandfather')];
            if ($father->mother) $paternal[] = ['member' => $father->mother, 'label' => __('Paternal Grandmother')];
        }

        if ($member->mother) {
            $mother = $member->mother;
            if ($mother->father) $maternal[] = ['member' => $mother->father, 'label' => __('Maternal Grandfather')];
            if ($mother->mother) $maternal[] = ['member' => $mother->mother, 'label' => __('Maternal Grandmother')];
        }

        return array_merge($paternal, $maternal);
    }

    public function grandchildren(FtMember $member): Collection
    {
        $grandchildren = collect();

        foreach ($this->memberRepo->childrenOf($member) as $child) {
            $grandchildren = $grandchildren->merge(
                $this->memberRepo->childrenOf($child)
            );
        }

        return $grandchildren->unique('id')->values();
    }

    public function unclesAndAunts(FtMember $member): array
    {
        $result = [];

        if ($member->father) {
            foreach ($member->father->siblings() as $sibling) {
                $label = $sibling->gender === 'male' ? __('Paternal Uncle') : __('Paternal Aunt');
                $result[] = ['member' => $sibling, 'label' => $label];
            }
        }

        if ($member->mother) {
            foreach ($member->mother->siblings() as $sibling) {
                $label = $sibling->gender === 'male' ? __('Maternal Uncle') : __('Maternal Aunt');
                $result[] = ['member' => $sibling, 'label' => $label];
            }
        }

        return $result;
    }

    public function nephewsAndNieces(FtMember $member): Collection
    {
        $result = collect();

        foreach ($member->siblings() as $sibling) {
            $result = $result->merge(
                $this->memberRepo->childrenOf($sibling)
            );
        }

        return $result->unique('id')->values();
    }

    public function cousins(FtMember $member): Collection
    {
        $cousins = collect();

        foreach ($this->unclesAndAunts($member) as $entry) {
            $cousins = $cousins->merge(
                $this->memberRepo->childrenOf($entry['member'])
            );
        }

        return $cousins->unique('id')->values();
    }

    public function inLaws(FtMember $member): array
    {
        $result = [];

        foreach ($member->activeSpouses() as $spouse) {
            if ($spouse->father) {
                $result[] = [
                    'member' => $spouse->father,
                    'label' => $member->gender === 'male' ? __('Father-in-law') : __('Father-in-law'),
                ];
            }
            if ($spouse->mother) {
                $result[] = [
                    'member' => $spouse->mother,
                    'label' => $member->gender === 'male' ? __('Mother-in-law') : __('Mother-in-law'),
                ];
            }
            foreach ($spouse->brothers() as $b) {
                $result[] = ['member' => $b, 'label' => __('Brother-in-law')];
            }
            foreach ($spouse->sisters() as $s) {
                $result[] = ['member' => $s, 'label' => __('Sister-in-law')];
            }
        }

        // Siblings' spouses are also in-laws
        foreach ($member->siblings() as $sibling) {
            foreach ($sibling->activeSpouses() as $siblingSpouse) {
                $label = $sibling->gender === 'male' ? __('Sister-in-law') : __('Brother-in-law');
                if ($siblingSpouse->gender === 'male') $label = __('Brother-in-law');
                if ($siblingSpouse->gender === 'female') $label = __('Sister-in-law');
                $result[] = ['member' => $siblingSpouse, 'label' => $label];
            }
        }

        return $result;
    }

    /**
     * Build the full named relationship summary for a member's profile page.
     */
    public function fullRelationshipSummary(FtMember $member): array
    {
        return [
            'father' => $member->father,
            'mother' => $member->mother,
            'spouses' => $member->spouses(),
            'children' => $member->children(),
            'sons' => $member->children()->filter(fn ($c) => $c->gender === 'male')->values(),
            'daughters' => $member->children()->filter(fn ($c) => $c->gender === 'female')->values(),
            'brothers' => $member->brothers(),
            'sisters' => $member->sisters(),
            'grandparents' => $this->grandparents($member),
            'grandchildren' => $this->grandchildren($member),
            'uncles_aunts' => $this->unclesAndAunts($member),
            'nephews_nieces' => $this->nephewsAndNieces($member),
            'cousins' => $this->cousins($member),
            'in_laws' => $this->inLaws($member),
        ];
    }

    // ─── Relationship Path Finder (Bidirectional BFS) ─────────────────────────

    /**
     * Find the shortest relationship path between two members.
     * Returns an array of steps with [member, relation_to_previous] or empty if not connected.
     *
     * Algorithm: Bidirectional BFS starting from both memberA and memberB simultaneously.
     * Bidirectional BFS is significantly faster than one-directional BFS for large trees
     * since it halves the search depth at each step.
     *
     * Each node in the BFS carries a "parent" (the node it was discovered from)
     * and the "label" describing the relationship edge used to reach it.
     * When the two frontiers meet, the path is reconstructed by tracing back through parents.
     */
    public function findRelationshipPath(FtMember $memberA, FtMember $memberB): array
    {
        if ($memberA->id === $memberB->id) {
            return [['member' => $memberA, 'label' => __('Same Person')]];
        }

        // Each frontier entry: id => ['parent_id' => ?, 'label' => ?, 'member' => FtMember]
        $frontierA = [$memberA->id => ['parent_id' => null, 'label' => null, 'member' => $memberA]];
        $frontierB = [$memberB->id => ['parent_id' => null, 'label' => null, 'member' => $memberB]];
        $visitedA = $frontierA;
        $visitedB = $frontierB;

        $maxDepth = 12; // Prevents infinite loops in disconnected or very large trees
        $depth = 0;

        while ($depth < $maxDepth) {
            // Expand frontier A
            $nextFrontierA = [];
            foreach ($frontierA as $id => $data) {
                foreach ($this->getNeighbours($data['member']) as $neighbour) {
                    $nId = $neighbour['member']->id;
                    if (isset($visitedA[$nId])) continue;
                    $visitedA[$nId] = [
                        'parent_id' => $id,
                        'label' => $neighbour['label'],
                        'member' => $neighbour['member'],
                    ];
                    $nextFrontierA[$nId] = $visitedA[$nId];

                    // Check if frontier A has met frontier B
                    if (isset($visitedB[$nId])) {
                        return $this->reconstructPath($visitedA, $visitedB, $nId, $memberA, $memberB);
                    }
                }
            }
            $frontierA = $nextFrontierA;

            // Expand frontier B
            $nextFrontierB = [];
            foreach ($frontierB as $id => $data) {
                foreach ($this->getNeighbours($data['member']) as $neighbour) {
                    $nId = $neighbour['member']->id;
                    if (isset($visitedB[$nId])) continue;
                    $visitedB[$nId] = [
                        'parent_id' => $id,
                        'label' => $neighbour['label_reverse'],
                        'member' => $neighbour['member'],
                    ];
                    $nextFrontierB[$nId] = $visitedB[$nId];

                    if (isset($visitedA[$nId])) {
                        return $this->reconstructPath($visitedA, $visitedB, $nId, $memberA, $memberB);
                    }
                }
            }
            $frontierB = $nextFrontierB;

            if (empty($frontierA) && empty($frontierB)) break;
            $depth++;
        }

        return []; // No path found — disconnected members
    }

    /**
     * Returns all adjacent members (graph neighbours) for BFS traversal.
     * Each entry has: member, label (forward), label_reverse (from the other direction).
     */
    protected function getNeighbours(FtMember $member): array
    {
        $neighbours = [];

        // Parent edges
        if ($member->father_id && ($father = FtMember::find($member->father_id))) {
            $neighbours[] = [
                'member' => $father,
                'label' => $member->gender === 'male' ? __('Son of') : __('Daughter of'),
                'label_reverse' => __('Father of'),
            ];
        }
        if ($member->mother_id && ($mother = FtMember::find($member->mother_id))) {
            $neighbours[] = [
                'member' => $mother,
                'label' => $member->gender === 'male' ? __('Son of') : __('Daughter of'),
                'label_reverse' => __('Mother of'),
            ];
        }

        // Children edges
        foreach ($this->memberRepo->childrenOf($member) as $child) {
            $neighbours[] = [
                'member' => $child,
                'label' => $child->gender === 'male' ? __('Father of') : __('Mother of'),
                'label_reverse' => $child->gender === 'male' ? __('Son of') : __('Daughter of'),
            ];
        }

        // Spouse edges
        foreach ($member->spouses() as $spouse) {
            $neighbours[] = [
                'member' => $spouse,
                'label' => $member->gender === 'male' ? __('Husband of') : __('Wife of'),
                'label_reverse' => $spouse->gender === 'male' ? __('Husband of') : __('Wife of'),
            ];
        }

        return $neighbours;
    }

    /**
     * Reconstruct the full path from the meeting point,
     * tracing back through visitedA from meetingId to memberA,
     * then forward through visitedB from meetingId to memberB.
     */
    protected function reconstructPath(
        array $visitedA,
        array $visitedB,
        int $meetingId,
        FtMember $memberA,
        FtMember $memberB
    ): array {
        // Trace from meeting point back to A
        $pathA = [];
        $current = $meetingId;
        while ($current !== null) {
            $pathA[] = $visitedA[$current];
            $current = $visitedA[$current]['parent_id'];
        }
        $pathA = array_reverse($pathA);

        // Trace from meeting point forward to B
        $pathB = [];
        $current = $visitedB[$meetingId]['parent_id'] ?? null;
        while ($current !== null) {
            $pathB[] = $visitedB[$current];
            $current = $visitedB[$current]['parent_id'];
        }

        $fullPath = array_merge($pathA, $pathB);

        // Format into readable steps
        return collect($fullPath)->map(fn ($step) => [
            'member' => $step['member'],
            'label' => $step['label'],
        ])->toArray();
    }

    /**
     * Describe the relationship between two members in plain language.
     * Returns a string like "Ahmad is the Paternal Uncle of Ali".
     */
    public function describeRelationship(FtMember $memberA, FtMember $memberB): string
    {
        $path = $this->findRelationshipPath($memberA, $memberB);
        if (empty($path)) {
            return __(':name and :other are not connected in this family tree.', [
                'name' => $memberA->full_name,
                'other' => $memberB->full_name,
            ]);
        }

        // Build a simple description from the path length and final label
        $steps = collect($path)->filter(fn ($s) => $s['label'])->values();
        if ($steps->count() === 1) {
            return $memberA->full_name . ' is the ' . ($steps->last()['label'] ?? '') . ' ' . $memberB->full_name;
        }

        $description = $memberA->full_name;
        foreach ($steps as $step) {
            if ($step['label']) {
                $description .= ' → ' . $step['label'] . ' ' . $step['member']->full_name;
            }
        }
        return $description;
    }
}