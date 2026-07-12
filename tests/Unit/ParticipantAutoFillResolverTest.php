<?php

namespace Tests\Unit;

use App\Models\Guru;
use App\Support\Assessment\ParticipantAutoFillResolver;
use Tests\TestCase;

class ParticipantAutoFillResolverTest extends TestCase
{
    public function test_it_infers_common_sources_from_field_metadata(): void
    {
        $resolver = app(ParticipantAutoFillResolver::class);

        $this->assertSame('nama_lengkap', $resolver->inferSourceFromField('Nama Lengkap', 'nama_lengkap'));
        $this->assertSame('nip_nuptk', $resolver->inferSourceFromField('NIP / NUPTK', 'nip_nuptk'));
        $this->assertSame('golongan', $resolver->inferSourceFromField('Pangkat / Golongan', 'pangkat_golongan'));
        $this->assertSame('satuan_pendidikan', $resolver->inferSourceFromField('Nama Sekolah', 'nama_sekolah'));
        $this->assertSame('kabupaten', $resolver->inferSourceFromField('Kabupaten / Kota', 'kabupaten_kota'));
    }

    public function test_it_resolves_profile_values_for_supported_field_types(): void
    {
        $resolver = app(ParticipantAutoFillResolver::class);
        $guru = new Guru;
        $guru->forceFill([
            'nama_lengkap' => 'Siti Aminah',
            'kabupaten' => 'Makassar',
            'jabatan' => 'Guru',
            'tgl_lahir' => '1991-08-17',
        ]);

        $textField = [
            'tipe_field' => 'text',
            'autofill_source' => 'nama_lengkap',
        ];
        $selectField = [
            'tipe_field' => 'select',
            'autofill_source' => 'kabupaten',
            'opsi_field' => [
                ['label' => 'Makassar', 'value' => 'Makassar'],
                ['label' => 'Bone', 'value' => 'Bone'],
            ],
        ];
        $checkboxField = [
            'tipe_field' => 'checkbox',
            'autofill_source' => 'jabatan',
            'opsi_field' => [
                ['label' => 'Guru', 'value' => 'Guru'],
                ['label' => 'Kepala Sekolah', 'value' => 'Kepala Sekolah'],
            ],
        ];
        $dateField = [
            'tipe_field' => 'date',
            'autofill_source' => 'tgl_lahir',
        ];

        $resolvedText = $resolver->resolveForField($textField, $guru);
        $resolvedSelect = $resolver->resolveForField($selectField, $guru);
        $resolvedCheckbox = $resolver->resolveForField($checkboxField, $guru);
        $resolvedDate = $resolver->resolveForField($dateField, $guru);

        $this->assertSame('Siti Aminah', $resolvedText['value']);
        $this->assertSame('Makassar', $resolvedSelect['value']);
        $this->assertSame(['Guru'], $resolvedCheckbox['value']);
        $this->assertSame('1991-08-17', $resolvedDate['value']);
    }
}
