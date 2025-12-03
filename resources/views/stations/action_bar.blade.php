<div class="breadcrumbs text-sm">
    <ul>
        <li><a href="{{ route("stations.index") }}">Station</a></li>
        <li>{{ $room->station->name }}</li>
        <li>{{ $room->name }}</li>
    </ul>
</div>
<div class="divider"></div>
<div class="container mx-auto">
    <div class="stats flex">
        <div class="stat flex-0">
            <button class="btn call-btn m-auto" type="button" onclick="callFunction('auto')">CALL</button>
        </div>
        <div class="stat flex-0">
            <div class="stat-title">Current</div>
            <div class="stat-value">{{ $now->number ?? "-" }}</div>
        </div>
        <div class="stat flex-0">
            <div class="stat-title">HN</div>
            <div class="stat-value">{{ $now->patient_master->hn ?? "-" }}</div>
        </div>
        @if ($now && $now->patient_master->vn != null)
            <div class="stat flex-0">
                <div class="stat-title">Name</div>
                <div class="stat-value">{{ $now->patient_master->vn ?? "-" }}</div>
            </div>
        @endif
        <div class="stat flex-1">
            <div class="stat-title">Name</div>
            <div class="stat-value">{{ $now->patient_master->name ?? "-" }}</div>
        </div>
        <div class="stat flex-0">
            <button class="btn m-auto whitespace-nowrap" id="call-again-btn" onclick="againFunction()" type="button">Call Again</button>
        </div>
        <div class="stat flex-0">
            <button class="btn m-auto whitespace-nowrap" id="hold-btn" onclick="holdFunction('auto')" type="button">Hold</button>
        </div>
        <div class="stat flex-0">
            <button class="btn m-auto" id="success-btn" onclick="successFunction('auto')" type="button">Success</button>
        </div>
    </div>
</div>
@push("scripts")
@endpush
