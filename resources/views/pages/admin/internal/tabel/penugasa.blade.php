<table class="table table-striped" id="table-temp">
    <thead>
        <tr>
            <th class="text-center">
                #
            </th>
            <th>Nama </th>
            <th>Kota</th>
            <th>Transport Pulang</th>
            <th>Transport Pergi</th>
            <th>Hotel</th>
            <th>Hari 1</th>
            <th>Hari 2</th>
            <th>Hari 3q</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datas as $i => $data)
            <tr>
                <td>
                    {{ ++$i }}
                </td>
                <td>{{ $data->name }}</td>
                <td></td>
                <td>
                    <a href="{{ route('kepegawaian.edit', $data->id) }} "
                        class="btn btn-warning my-2"><i class="fas fa-edit"></i></a>

                    <button onclick="deleteData({{ $data->id }}, 'kepegawaian')"
                        class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        @endforeach

    </tbody>
</table>