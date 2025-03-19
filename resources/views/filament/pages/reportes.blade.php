<h2>Reporte de {{ ucfirst(request('tipo')) }}</h2>
<table>
    <thead>
        <tr>
            @foreach ($data->first()->toArray() as $key => $value)
            <th>{{ ucfirst($key) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
        <tr>
            @foreach ($item->toArray() as $value)
            <td>{{ $value }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>