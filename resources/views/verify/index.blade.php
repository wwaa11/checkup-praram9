@extends("layouts.app")
@section("content")
    <input class="radio" id="hn" type="radio" name="type" value="hn">
    <label for="hn"> HN</label>
    <input class="radio" id="passport" type="radio" name="type" value="passport">
    <label for="passport"> Passport</label>
    <input class="radio" id="phoneno" type="radio" name="type" value="phoneno">
    <label for="phoneno"> Phone Number</label>
    <input class="radio" id="nationalid" type="radio" name="type" value="nationalid">
    <label for="nationalid"> ID card</label>
    <input id="input" type="text" name="generate" placeholder="Enter text">
    <button class="btn" id="generate-btn" onclick="search()">Search</button>
    <table class="w-full border border-gray-300">
        <thead>
            <tr>
                <th>hn</th>
                <th>firstname</th>
                <th>lastname</th>
                <th>nationality</th>
                <th>number</th>
            </tr>
        </thead>
        <tbody id="result">

        </tbody>
    </table>
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
                        html += `
                        <tr>
                            <td id="hn">${item.hn}</td>
                            <td id="firstname">${item.firstname}</td>
                            <td id="lastname">${item.lastname}</td>
                            <td id="nationality">${item.nationality}</td>
                        `;
                        if (item.isExist) {
                            html += `<td id="number_${item.hn}" >${item.number}</td>`;
                        } else {
                            html += `<td id="number_${item.hn}" onclick="getNumber('${item.hn}')">Get Number</td>`;
                        }
                        html += `</tr>`;
                    });
                    $("#result").html(html);
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
