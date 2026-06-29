extends('layouts.user.app', ['title' => 'Bank Data BBGTK Sulawesi Selatan'])

@section('content')
    @push('styles')
    @endpush
    <style type="text/css">
		.pagination li{
			float: left;
			list-style-type: none;
			margin:5px;
		}
	</style>
 
	<h2><a href="https://www.malasngoding.com">www.malasngoding.com</a></h2>
	<h3>Data Pegawai</h3>
 
 
	<p>Cari Data Pegawai :</p>
	<form action="/pegawai/cari" method="GET">
		<input type="text" name="cari" placeholder="Cari Pegawai .." value="{{ old('cari') }}">
		<input type="submit" value="CARI">
	</form>
		
	<br/>
 
	<table border="1">
		<tr>
			<th>Nik</th>
			<th>Status Keikutpesertaan</th>
			<th>Instansi</th>
			<th>Golongan</th>
            <th>Jenis Kelamin</th>
            <th>Kelengkpaan peserta</th>
            <th>Nomor Handphone</th>
            <th>Nomor Whatsaap</th>
            <th>Kabupaten</th>
		</tr>
		@foreach($data as $p)
		<tr>
			<td>{{ $p->no_ktp }}</td>
			<td>{{ $p->status_keikutpesertaan }}</td>
			<td>{{ $p->instansi }}</td>
			<td>{{ $p->golongan }}</td>
            <td>{{ $p->jkl }}</td>
            <td>{{ $p->kelengkapan_peserta }}</td>
            <td>{{ $p->no_hp }}</td>
            <td>{{ $p->no_wa }}</td>
            <td>{{ $p->kabupaten }}</td>
		</tr>
		@endforeach
	</table>
 
	<br/>
	Halaman : {{ $data->currentPage() }} <br/>
	Jumlah Data : {{ $data->total() }} <br/>
	Data Per Halaman : {{ $data->perPage() }} <br/>
 
 
	{{ $data->links() }}

    @push('scripts')
    @endpush
@endsection