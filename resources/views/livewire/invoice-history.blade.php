<div>
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Subscription History</flux:heading>
        <div>
            <b>
                Rp {{number_format($UserSaldo, 0, ',', '.')}}
            </b>
        </div>
    </div>
    <hr class="mt-2 mb-2">
    @if($getAllSubscriptions->isEmpty())
        <center>
            <flux:text class="text-xs text-gray-500">No subscription records found.</flux:text>
        </center>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
            @foreach ($getAllSubscriptions as $data)
                <flux:callout class="shadow-lg rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="flex justify-between items-center">
                                <flux:heading size="lg" class="mb-0">T{{date('symdhis', strtotime($data->created_at))}}</flux:heading>
                                <b class="text-red-600">Rp {{ number_format($data->nominal,0,0,'.') }}</b>
                            </div>
                            <flux:text class="text-xs mt-0" style="text-transform: capitalize">
                                <flux:badge variant="pill" size="sm" class="mt-2">{{$data->tipe}}: {{ $data->tipe == 'class' ? $this->getNameClassOrCourse('class', $data->class_uuid) : $this->getNameClassOrCourse('course', $data->course_uuid) }}</flux:badge>
                                <flux:badge variant="pill" size="sm" class="mt-2" icon="calendar">{{ $data->expired_at == '9999-12-31 00:00:00' ? 'Lifetime' : date('d F Y', strtotime($data->expired_at)) }}</flux:badge>
                            </flux:text>
                        </div>
                    </div>
                </flux:callout>
            @endforeach
        </div>
    @endif
</div>
