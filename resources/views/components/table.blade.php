@props([
    'headers' => [],
    'tbodyId' => null,
])

<div class="table-responsive">
    <table class="table table-premium align-middle mb-0">
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody @if($tbodyId) id="{{ $tbodyId }}" @endif>
            {{ $slot }}
        </tbody>
    </table>
</div>
