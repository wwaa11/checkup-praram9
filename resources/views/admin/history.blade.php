@extends("layouts.app")

@section("content")
    <div>
        <form action="{{ route("admin.history") }}" method="get">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Search</span>
                </label>
                <input class="input input-bordered" name="search" type="text" value="{{ $request->search }}" placeholder="HN or VN" />
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
        <div class="divider">History</div>
        <div class="stat grid grid-cols-5">
            <div class="stat cols-1">
                <div class="stat-title">VN</div>
                <div class="stat-value">{{ $patient_master?->vn ?? "N/A" }}</div>
            </div>
            <div class="stat cols-1">
                <div class="stat-title">HN</div>
                <div class="stat-value">{{ $patient_master?->hn ?? "N/A" }}</div>
            </div>
            <div class="stat cols-3">
                <div class="stat-title">Patient Name</div>
                <div class="stat-value">{{ $patient_master?->name ?? "N/A" }}</div>
            </div>
        </div>
        <ul class="list bg-base-100 rounded-box shadow-md" id="history-list">
            @if ($patient_master?->logs?->count() > 0)
                @foreach ($patient_master?->logs as $log)
                    <li class="list-row hover:bg-base-200">
                        <div class="my-auto opacity-50">
                            {{ $log->created_at->format("H:i:s") }}
                        </div>
                        <div class="my-auto">
                            {{ $log->detail }}
                        </div>
                        <div class="my-auto opacity-50">
                            {{ $log->user }}
                        </div>
                    </li>
                @endforeach
            @else
                <li class="list-row">
                    <div class="my-auto">
                        No history found.
                    </div>
                </li>
            @endif
        </ul>
    </div>
@endsection
