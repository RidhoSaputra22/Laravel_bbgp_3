<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Portal Assessment BBGTK</title>

    <style>
        button {
            cursor: pointer;
        }

        summary::marker {
            content: "+ ";
        }

        details[open] summary::marker {
            content: "- ";
        }
    </style>


</head>

<body class="relative min-h-screen bg-gray-100">
    @yield('content')

    @if (session('assessment_portal_success'))
        <x-assessment::ui.alert type="success" class="mb-4">
            {{ session('assessment_portal_success') }}
        </x-assessment::ui.alert>
    @endif

    @php
        $errorBag = $errors ?? null;
    @endphp

    @if ($errorBag && $errorBag->has('portal'))
        <x-assessment::ui.alert type="danger" class="mb-4">
            {{ $errorBag->first('portal') }}
        </x-assessment::ui.alert>
    @endif


    @stack('scripts')
</body>

</html>
