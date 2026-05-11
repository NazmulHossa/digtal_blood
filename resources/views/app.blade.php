<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    {{-- Inertia Head: Vue-এর <Head> component এখানে inject হয় --}}
    @inertiaHead

    {{-- Vite: CSS + JS build files --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    {{-- Inertia Root: Vue app এই div-এ mount হয় --}}
    @inertia

</body>
</html>