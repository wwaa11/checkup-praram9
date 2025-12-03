@extends("layouts.app")

@section("content")
    @include("stations.action_bar")
    <div class="mt-3 grid grid-cols-2 gap-4">
        <div>
            <div class="divider">Appointment</div>
            <ul class="list bg-base-100 rounded-box shadow-md" id="wait-list"></ul>
            <div class="divider">walk-in</div>
            <ul class="list bg-base-100 rounded-box shadow-md" id="walkin-list"></ul>
        </div>
        <div>
            <div class="divider">FollowUp</div>
            <ul class="list bg-base-100 rounded-box shadow-md" id="followup-list"></ul>
            <div class="divider">Hold</div>
            <ul class="list bg-base-100 rounded-box shadow-md" id="hold-list"></ul>
        </div>
    </div>
@endsection
@push("scripts")
    <script type="module">
        $(document).ready(function() {
            list();
            if ('{{ $room->now !== "-" }}') {
                $(".call-btn").prop("disabled", true);
            } else {
                $("#call-again-btn").prop("disabled", true);
                $("#hold-btn").prop("disabled", true);
                $("#success-btn").prop("disabled", true);
            }
        });

        const createListItemHtml = (item, key, roomNowStatus) => {
            let actionButtonHtml = '';

            if (key === 'hold') {
                actionButtonHtml = `
                <button class="btn btn-ghost my-auto" type="button" onclick="deleteFunction('${item.id}')">
                    DELETE
                </button>
                `;
            } else {
                actionButtonHtml = `
                <button class="btn btn-ghost my-auto" type="button" onclick="holdFunction('${item.id}')">
                    HOLD
                </button>
                `;
            }

            const vnBadgeHtml = item.vn != null ?
                `<div class="text-lg ">VN : <span class="badge badge-primary">${item.vn}</span></div>` :
                '';

            const noteHtml = item.note != null ?
                `<span class="text-error">${item.note}</span>` :
                '';

            return `
                <li class="list-row">
                    <div class="my-auto opacity-50">
                        <div class="text-4xl font-thin tabular-nums">${item.number}</div>
                        ${vnBadgeHtml}
                    </div>
                    <div class="list-col-grow">
                        <div class="list-row flex mx-auto">
                            <div class="flex-1">
                                <div>${item.hn} | ${item.name}</div>
                                <div>${noteHtml}</div>
                            </div>
                            <div>${item.waiting_time} minutes </div>
                        </div>
                    </div>
                    <button class="btn btn-ghost my-auto" type="button" ${roomNowStatus} onclick="callFunction('${item.id}')">
                        CALL
                    </button>
                    ${actionButtonHtml}
                </li>
            `;
        };

        function list() {
            axios.get("{{ route("stations.register.list") }}")
                .then(function(response) {
                    const roomNowStatus = `{{ $room->now !== "-" ? "disabled" : "" }}`;
                    for (let key in response.data) {
                        const listContainer = $("#" + key + "-list");
                        listContainer.html("");

                        const listItemsHtml = response.data[key].map(item => {
                            return createListItemHtml(item, key, roomNowStatus);
                        });

                        listContainer.append(listItemsHtml.join(''));
                    }
                });
            setTimeout(() => {
                list();
            }, 5000);
        }
    </script>
    <script>
        function callFunction(id) {
            axios.post("{{ route("stations.register.call") }}", {
                    id: id,
                    roomID: "{{ $room->id }}"
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        refreshPage();
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: response.data.message,
                            icon: "error",
                            showConfirmButton: false,
                            buttonsStyling: false,
                            timer: 1500,
                        });
                    }
                });
        }

        function holdFunction(id) {
            Swal.fire({
                title: "Are you sure you want to hold this patient?",
                icon: "warning",
                showCancelButton: true,
                input: "text",
                inputPlaceholder: "Provide reason for hold",
                confirmButtonText: "Confirm",
                cancelButtonText: "Cancel",
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-accent mx-3', // DaisyUI Primary Color
                    cancelButton: 'btn btn-ghost mx-3' // DaisyUI Ghost/subtle style
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route("stations.register.hold") }}", {
                            id: id,
                            roomID: "{{ $room->id }}",
                            reason: result.value
                        })
                        .then(function(response) {
                            console.log(response.data);
                            if (response.data.status == "success") {
                                refreshPage();
                            }
                        });
                }
            });
        }
    </script>
@endpush
