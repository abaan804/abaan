@php
ob_start();
$total = $members->count();
$males = $members->where('gender','male')->count();
$females = $members->where('gender','female')->count();
$living = $members->where('life_status','living')->count();
$deceased = $members->where('life_status','deceased')->count();
@endphp
<div class="stat-row">
    <table>
        <tr>
            <td style="width:20%; padding-right:5px;">
                <div class="stat-box">
                    <div class="stat-label">{{ __('Total') }}</div>
                    <div class="stat-value"><?= $total ?></div>
                </div>
            </td>
            <td style="width:20%; padding-right:5px;">
                <div class="stat-box">
                    <div class="stat-label">{{ __('Male') }}</div>
                    <div class="stat-value male-color"><?= $males ?></div>
                </div>
            </td>
            <td style="width:20%; padding-right:5px;">
                <div class="stat-box">
                    <div class="stat-label">{{ __('Female') }}</div>
                    <div class="stat-value female-color"><?= $females ?></div>
                </div>
            </td>
            <td style="width:20%; padding-right:5px;">
                <div class="stat-box">
                    <div class="stat-label">{{ __('Living') }}</div>
                    <div class="stat-value" style="color:#1e8449;"><?= $living ?></div>
                </div>
            </td>
            <td style="width:20%;">
                <div class="stat-box">
                    <div class="stat-label">{{ __('Deceased') }}</div>
                    <div class="stat-value" style="color:#566573;"><?= $deceased ?></div>
                </div>
            </td>
        </tr>
    </table>
</div>

<table class="dt">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ __('Full Name') }}</th>
            <th>{{ __('Father') }}</th>
            <th>{{ __('Gender') }}</th>
            <th>{{ __('Date of Birth') }}</th>
            <th>{{ __('Age') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Marital') }}</th>
            <th>{{ __('Contact') }}</th>
            <th>{{ __('Occupation') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $i => $m): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td class="<?= $m->life_status === 'deceased' ? 'deceased' : '' ?>">
                <?= e($m->full_name) ?>
                <?= $m->life_status === 'deceased' ? ' †' : '' ?>
            </td>
            <td><?= e($m->father_display_name) ?></td>
            <td><span class="badge-<?= $m->gender ?>"><?= ucfirst($m->gender) ?></span></td>
            <td><?= $m->date_of_birth ? formatDate($m->date_of_birth) : '—' ?></td>
            <td><?= $m->age !== null ? $m->age . ' yrs' : '—' ?></td>
            <td><span class="badge-<?= $m->life_status ?>"><?= ucfirst($m->life_status) ?></span></td>
            <td><?= ucfirst($m->marital_status) ?></td>
            <td><?= e($m->contact_number ?? '—') ?></td>
            <td><?= e($m->occupation ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10">
                {{ __('Total') }}: <?= $total ?> {{ __('members') }}
                (<?= $males ?> {{ __('male') }}, <?= $females ?> {{ __('female') }})
            </td>
        </tr>
    </tfoot>
</table>
<?php $content = ob_get_clean(); ?>

@include('familytree::reports.pdf.layout', [
    'letterhead'  => $letterhead,
    'reportTitle' => __('Members Report') . ' — ' . $family->name,
    'reportMeta'  => __('As of') . ' ' . formatDate(now()),
    'content'     => $content,
])