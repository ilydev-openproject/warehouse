<table class="w-full text-sm text-left">
    <thead class="font-semibold text-gray-700">
        <tr>
            <th class="py-2">Toko</th>
            <th class="py-2">Platform</th>
            <th class="py-2">Rekening</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td class="py-1">{{ $item->toko->name }}</td>
                <td class="py-1">{{ $item->platform->name }}</td>
                <td class="py-1">{{ $item->rekening->name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>