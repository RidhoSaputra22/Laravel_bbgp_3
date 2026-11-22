<div class="certificate-materials-page">
    <img class="certificate-materials-background" src="{{ $materialsBackgroundSrc }}" alt="">
    <div class="certificate-materials-title" data-certificate-materials-title>
        {{ $materialsTitle ?? '' }}
    </div>

    <table class="certificate-materials-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>MATERI</th>
                <th>JP</th>
            </tr>
        </thead>
        <tbody data-certificate-materials-preview>
            @forelse ($materials ?? [] as $index => $material)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{!! $material['materi'] ?? '' !!}</td>
                    <td>{{ $material['jp'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="certificate-materials-empty">Belum ada materi</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td>TOTAL</td>
                <td data-certificate-materials-total>
                    {{ collect($materials ?? [])->sum(fn ($material) => (float) ($material['jp'] ?? 0)) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
