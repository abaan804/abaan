<?php

namespace Modules\FamilyTree\Services;

use Illuminate\Support\Collection;
use Modules\FamilyTree\Models\FtFamily;
use Modules\FamilyTree\Models\FtMember;
use Modules\FamilyTree\Repositories\FtMemberRepository;

class FamilyTreeVisualizationService
{
    public function __construct(protected FtMemberRepository $memberRepo)
    {
    }

    // ─── Descendant Tree (Top-Down from a root member) ────────────────────────

    /**
     * Build a Treant.js-compatible JSON config for a descendant tree.
     * Starts from the given root member and expands downward.
     */
    public function descendantTree(FtMember $root, int $maxDepth = 6): array
    {
        return [
            'chart' => $this->chartConfig('Descendant Tree — ' . $root->full_name),
            'nodeStructure' => $this->buildDescendantNode($root, 0, $maxDepth),
        ];
    }

    protected function buildDescendantNode(FtMember $member, int $depth, int $maxDepth): array
    {
        $node = $this->buildNodeTemplate($member);

        if ($depth >= $maxDepth) {
            $node['collapsed'] = true;
            return $node;
        }

        $children = $this->memberRepo->childrenOf($member);

        if ($children->isEmpty()) {
            return $node;
        }

        // Group children by mother so each family unit is visually distinct
        $byMother = $children->groupBy('mother_id');

        $childNodes = [];
        foreach ($byMother as $motherId => $group) {
            // If there's a mother record, show her spouse node inline
            if ($motherId && $mother = FtMember::find($motherId)) {
                // Add spouse connector — Treant.js doesn't natively support
                // horizontal spouse lines, so we use a pseudo-parent group node
                $groupNode = [
                    'text' => ['name' => ''],
                    'pseudo' => true, // signals the view to render this as a couple connector
                    'HTMLclass' => 'ft-couple-node',
                    'data-husband' => $member->id,
                    'data-wife' => $motherId,
                    'children' => [],
                ];

                foreach ($group as $child) {
                    $groupNode['children'][] = $this->buildDescendantNode($child, $depth + 1, $maxDepth);
                }
                $childNodes[] = $groupNode;
            } else {
                foreach ($group as $child) {
                    $childNodes[] = $this->buildDescendantNode($child, $depth + 1, $maxDepth);
                }
            }
        }

        if (! empty($childNodes)) {
            $node['children'] = $childNodes;
        }

        return $node;
    }

    // ─── Ancestor Tree (Bottom-Up from a member) ──────────────────────────────

    /**
     * Build an ancestor tree — shows parents, grandparents, great-grandparents.
     * Treant.js renders top-down by default, so ancestors are shown above
     * by inverting the node structure: the subject is the deepest child.
     */
    public function ancestorTree(FtMember $leaf, int $maxDepth = 4): array
    {
        // Build from the top (oldest ancestor found) down to the leaf
        $ancestors = $this->memberRepo->ancestors($leaf, $maxDepth);

        // Find the oldest generation root(s) — members with no parents in the set
        $ancestorIds = $ancestors->pluck('id')->push($leaf->id)->toArray();
        $roots = $ancestors->filter(fn ($a) => ! in_array($a->father_id, $ancestorIds) && ! in_array($a->mother_id, $ancestorIds));

        if ($roots->isEmpty()) {
            $roots = collect([$leaf]);
        }

        $rootNodes = $roots->map(fn ($r) => $this->buildAncestorNode($r, $leaf, $ancestors, 0, $maxDepth))->values()->toArray();

        return [
            'chart' => $this->chartConfig('Ancestor Tree — ' . $leaf->full_name),
            'nodeStructure' => count($rootNodes) === 1
                ? $rootNodes[0]
                : ['text' => ['name' => ''], 'pseudo' => true, 'children' => $rootNodes],
        ];
    }

    protected function buildAncestorNode(FtMember $member, FtMember $target, Collection $setMembers, int $depth, int $maxDepth): array
    {
        $node = $this->buildNodeTemplate($member, $member->id === $target->id);

        $children = $setMembers->filter(fn ($m) =>
            $m->father_id === $member->id || $m->mother_id === $member->id
        );

        if ($children->isNotEmpty() && $depth < $maxDepth) {
            $node['children'] = $children->map(fn ($c) =>
                $this->buildAncestorNode($c, $target, $setMembers, $depth + 1, $maxDepth)
            )->values()->toArray();
        }

        return $node;
    }

    // ─── Full Family Tree ─────────────────────────────────────────────────────

    /**
     * Build a full family tree for the given family, starting from all root members.
     */
    public function fullTree(FtFamily $family, int $maxDepth = 5): array
    {
        $roots = $this->memberRepo->roots($family->id);

        if ($roots->isEmpty()) {
            return ['chart' => $this->chartConfig($family->name), 'nodeStructure' => ['text' => ['name' => __('No members')]]];
        }

        $rootNodes = $roots->map(fn ($r) => $this->buildDescendantNode($r, 0, $maxDepth))->values()->toArray();

        return [
            'chart' => $this->chartConfig($family->name),
            'nodeStructure' => count($rootNodes) === 1
                ? $rootNodes[0]
                : [
                    'text' => ['name' => $family->name],
                    'HTMLclass' => 'ft-family-root',
                    'children' => $rootNodes,
                ],
        ];
    }

    // ─── Node Template ────────────────────────────────────────────────────────

    /**
     * Build a single Treant.js node data structure for a member.
     * The HTML is rendered as a custom Bootstrap card in the view layer.
     */
    protected function buildNodeTemplate(FtMember $member, bool $highlight = false): array
    {
        $photo = $member->profile_photo
            ? asset('storage/' . $member->profile_photo)
            : asset('images/familytree/default-' . $member->gender . '.png');

        $age = $member->age ? " ({$member->age})" : '';
        $lifeStatus = $member->life_status === 'deceased' ? ' D' : '';

        return [
            'text' => [
                'name' => $member->full_name . $lifeStatus,
                'title' => $member->father_display_name,
                'contact' => $age,
                'desc' => $member->occupation ?? '',
            ],
            'image' => $photo,
            'HTMLclass' => 'ft-member-node'
                . ($highlight ? ' ft-node-highlight' : '')
                . ($member->life_status === 'deceased' ? ' ft-node-deceased' : '')
                . ($member->gender === 'female' ? ' ft-node-female' : ' ft-node-male'),
            'data-member-id' => $member->id,
            'data-gender' => $member->gender,
            'data-age' => $member->age ?? '',
            'data-dob' => $member->date_of_birth?->format('d M Y') ?? '',
            'data-life-status' => $member->life_status,
            'data-marital-status' => $member->marital_status,
            'data-occupation' => $member->occupation ?? '',
            'data-contact' => $member->contact_number ?? '',
            'data-address' => $member->current_address ?? '',
        ];
    }

    // ─── Chart Config ─────────────────────────────────────────────────────────

    protected function chartConfig(string $title): array
    {
        return [
            'container' => '#ft-tree-container',
            'animateOnInit' => true,
            'node' => [
                'HTMLclass' => 'ft-tree-node',
                'collapsable' => true,
            ],
            'connectors' => [
                'type' => 'bCurve',
                'style' => [
                    'stroke' => '#1a5276',
                    'stroke-width' => 2,
                    'arrow-end' => 'block-wide-long',
                ],
            ],
            'levelSeparation' => 60,
            'siblingSeparation' => 30,
            'subTeeSeparation' => 30,
            'scrollbar' => 'fancy',
            'padding' => 15,
            'title' => $title,
        ];
    }
}