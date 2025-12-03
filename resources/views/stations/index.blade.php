@extends("layouts.app")

@section("content")
    <div class="container mx-auto">
        <div class="grid grid-cols-2 gap-4">
            @foreach ($stations as $station)
                <div class="bg-badge rounded-md p-4">
                    <div class="divider">{{ $station->name }}</div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($station->rooms as $room)
                            <a href="{{ route("stations.room", $room) }}">
                                <div class="stats hover:bg-accent w-24 shadow">
                                    <div class="stat place-items-center">
                                        <div class="stat-title">{{ $station->name == "Register" ? "Counter" : "Room" }} </div>
                                        <div class="stat-value">{{ $room->name }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@push("scripts")
@endpush
