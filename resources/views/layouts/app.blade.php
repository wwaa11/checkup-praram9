<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset("images/logo.ico") }}" type="image/x-icon">
    <title>{{ config("app.name", "Laravel") }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <!-- Styles / Scripts -->
    @if (file_exists(public_path("build/manifest.json")) || file_exists(public_path("hot")))
        @vite(["resources/css/app.css", "resources/js/app.js"])
    @endif
</head>

<body>
    @guest
        <div class="">
            @yield("content")
        </div>
    @endguest
    @auth
        <div class="navbar bg-base-100 shadow-sm">
            <div class="flex-1">
                <a href="{{ route("stations.index") }}">
                    <div class="btn btn-ghost text-xl">
                        CHECK UP
                    </div>
                </a>
                <a href="{{ route("service.index") }}">
                    <div class="btn btn-ghost">Service</div>
                </a>
                <a href="{{ route("verify.index") }}">
                    <div class="btn btn-ghost">Pre VN</div>
                </a>
                <a href="{{ route("admin.history") }}">
                    <div class="btn btn-ghost">History</div>
                </a>
            </div>
            <div>
                <div class="stat flex-0">
                    <div class="stat-title">{{ Auth::user()->userid }} {{ Auth::user()->name }}</div>
                    <div class="stat-title">{{ Auth::user()->department }}</div>
                </div>
            </div>
            <button class="btn btn-error" onclick="logoutRequest()">logout</button>
        </div>
        <div class="container mx-auto p-3">
            @yield("content")
        </div>
    @endauth
</body>
<script>
    function logoutRequest() {
        Swal.fire({
            title: "Are you sure you want to logout?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel",
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-error mx-3', // DaisyUI Primary Color
                cancelButton: 'btn btn-ghost mx-3' // DaisyUI Ghost/subtle style
            },
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.setItem('activeRoute', 'logout');
                axios.post("{{ route("logout") }}")
                    .then(function(response) {
                        if (response.data.status === "success") {
                            window.location.href = "{{ route("login") }}";
                        }
                    });
            }
        });
    }

    function refreshPage() {
        window.location.reload();
    }
</script>
@stack("scripts")

</html>
