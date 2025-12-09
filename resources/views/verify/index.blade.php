@extends("layouts.app")
@section("content")
    <div class="card bg-base-100 mx-auto w-96 p-6 shadow-xl">
        <div class="card-body p-0">
            <h2 class="card-title mb-4 text-2xl">Search Type Selection</h2>
            <div class="mb-6 grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label cursor-pointer gap-2">
                        <input class="radio radio-primary" id="hn" type="radio" name="type" value="hn" checked />
                        <span class="label-text">HN</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer gap-2">
                        <input class="radio radio-primary" id="passport" type="radio" name="type" value="passport" />
                        <span class="label-text">Passport</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer gap-2">
                        <input class="radio radio-primary" id="phoneno" type="radio" name="type" value="phoneno" />
                        <span class="label-text">Phone Number</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer gap-2">
                        <input class="radio radio-primary" id="nationalid" type="radio" name="type" value="nationalid" />
                        <span class="label-text">ID card</span>
                    </label>
                </div>
            </div>

            <div class="join w-full">
                <input class="input input-bordered join-item w-full" id="input" type="text" name="generate" placeholder="Enter search text..." />
                <button class="btn btn-primary join-item" id="generate-btn" onclick="search()">
                    Search
                </button>
            </div>

        </div>
    </div>
    <div class="divider">Search Result</div>
    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500" scope="col">
                        HN
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500" scope="col">
                        Patient Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500" scope="col">
                        Nationality
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500" scope="col">
                        Queue No.
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white" id="result">
            </tbody>
        </table>
    </div>
@endsection
@push("scripts")
    <script>
        function search() {
            Swal.fire({
                title: "Loading...",
                icon: "info",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 1500,
            });

            axios.post("{{ route("verify.search") }}", {
                type: $("input[name='type']:checked").val(),
                input: $("#input").val(),
            }).then((response) => {
                if (response.data.status == "success") {
                    Swal.close();
                    let html = "";
                    response.data.patient.forEach((item) => {
                        // Use map/join for slightly cleaner template literal generation
                        html = response.data.patient.map((item) => {

                            // Determine the content for the Queue No. cell
                            let queueCellContent;
                            if (item.isExist) {
                                // Display the existing number with emphasis
                                queueCellContent = `<span class="px-4 py-1 inline-flex text-lg leading-5 font-semibold rounded-full bg-green-100 text-green-800">${item.number}</span>`;
                            } else {
                                // Use a styled button for the action
                                queueCellContent = `
                                        <button 
                                            type="button" 
                                            onclick="getNumber('${item.hn}')" 
                                            class="px-3 py-1 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out"
                                        >
                                            Get Queue
                                        </button>
                                    `;
                            }

                            return `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.hn}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">${item.firstname} ${item.lastname}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.nationality}</td>
                                        <td id="number_${item.hn}" class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            ${queueCellContent}
                                        </td>
                                    </tr>
                                `;
                        }).join(''); // Join the array of HTML strings into a single string

                        $("#result").html(html);
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: response.data.message,
                        icon: "error",
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 1000,
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: "Error!",
                    text: error.response.data.message,
                    icon: "error",
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 1000,
                });
            });
        }

        function getNumber(hn) {
            Swal.fire({
                title: "Loading...",
                icon: "info",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 1500,
            });

            axios.post("{{ route("verify.getnumber") }}", {
                hn: hn,
            }).then((response) => {
                if (response.data.status == "success") {
                    Swal.fire({
                        title: "Success!",
                        text: response.data.message,
                        icon: "success",
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 1000,
                    });
                    $("#number_" + hn).text(response.data.number);

                    setTimeout(() => {
                        search();
                    }, 2000);
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: response.data.message,
                        icon: "error",
                        showCancelButton: false,
                        showConfirmButton: false,
                        timer: 1000,
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: "Error!",
                    text: error.response.data.message,
                    icon: "error",
                });
            });
        }
    </script>
@endpush
