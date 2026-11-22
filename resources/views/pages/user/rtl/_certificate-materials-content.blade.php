@php
    $materialsCount = count($materials ?? []);
    $materialsTitleMarkup = (string) ($materialsTitle ?? '');
    $materialsTitleText = trim(preg_replace('/\s+/', ' ', strip_tags($materialsTitleMarkup)) ?? '');
    $materialsTitleLines = max(
        1,
        substr_count(strtolower($materialsTitleMarkup), '<br>')
            + (int) ceil(max(1, mb_strlen($materialsTitleText)) / 90)
    );
    $materialsDensityScore = $materialsCount + max(0, $materialsTitleLines - 1) * 8;
    $materialsDensityClass = match (true) {
        $materialsDensityScore > 55 => 'certificate-materials-page--extreme-dense',
        $materialsDensityScore > 38 => 'certificate-materials-page--ultra-dense',
        $materialsDensityScore > 28 => 'certificate-materials-page--dense',
        $materialsDensityScore > 16 => 'certificate-materials-page--compact',
        $materialsDensityScore > 12 => 'certificate-materials-page--tight',
        default => '',
    };
    $materialsTableStyle = '';
    $materialsCellStyle = '';

    if ($isPdf ?? false) {
        $availableTableHeight = max(20, 210 - 35 - ($materialsTitleLines * 5.8) - 5);
        $estimatedRowHeight = (12 * 0.3528 * 1.2) + (3.1 * 2) + 0.5;
        $estimatedTableHeight = max(1, ($materialsCount + 2) * $estimatedRowHeight);
        $heightRatio = $availableTableHeight / $estimatedTableHeight;
        $tableScale = min(1.12, max(.22, $heightRatio >= 1 ? $heightRatio * .98 : $heightRatio * .9));
        $tableFontSize = $materialsCount > 38
            ? max(4, round(12 * $tableScale, 2))
            : round(12 * $tableScale, 2);
        $tableCellPadding = $materialsCount > 38
            ? .2
            : round(max(.2, 3.1 * $tableScale), 2);
        $materialsTableStyle = 'font-size: '.$tableFontSize.'pt;';
        $materialsCellStyle = 'padding: '.$tableCellPadding.'mm 2.5mm;';
    }
@endphp

<div class="certificate-materials-page {{ $materialsDensityClass }}">
    <img class="certificate-materials-background" src="{{ $materialsBackgroundSrc }}" alt="">
    <div class="certificate-materials-title" data-certificate-materials-title>
        {{ $materialsTitle ?? '' }}
    </div>

    <table class="certificate-materials-table" style="{{ $materialsTableStyle }}">
        <colgroup>
            <col style="width: 18mm">
            <col style="width: 204mm">
            <col style="width: 23mm">
        </colgroup>
        <thead>
            <tr>
                <th style="{{ $materialsCellStyle }}">NO</th>
                <th style="{{ $materialsCellStyle }}">MATERI</th>
                <th style="{{ $materialsCellStyle }}">JP</th>
            </tr>
        </thead>
        <tbody data-certificate-materials-preview>
            @forelse ($materials ?? [] as $index => $material)
                <tr>
                    <td style="{{ $materialsCellStyle }}">{{ $index + 1 }}</td>
                    <td style="{{ $materialsCellStyle }}">{!! $material['materi'] ?? '' !!}</td>
                    <td style="{{ $materialsCellStyle }}">{{ isset($material['jp']) && $material['jp'] !== '' ? $material['jp'] : 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="certificate-materials-empty" style="{{ $materialsCellStyle }}">Belum ada materi</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td style="{{ $materialsCellStyle }}"></td>
                <td style="{{ $materialsCellStyle }}">TOTAL</td>
                <td data-certificate-materials-total style="{{ $materialsCellStyle }}">
                    {{ collect($materials ?? [])->sum(fn ($material) => (float) ($material['jp'] ?? 0)) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
