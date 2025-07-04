<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Poke Random</title>
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-300 via-yellow-200 to-blue-300 text-gray-800 font-sans">

<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
    @foreach($responses as $response)
<div x-data="{ showModal: false }" class="rounded-lg shadow-lg bg-slate-200 relative">

    <div class="flex flex-col md:flex-row p-4">
        <div class="md:w-1/2">
            <h2 class="text-xl font-bold text-center">{{ $response['id'] }}. {{ $response['name'] }}</h2>
            @foreach($response['types'] as $item)
                <p class="text-lg font-bold text-slate-400">{{ $item['type']['name'] }}</p>
            @endforeach
            <button onclick="document.getElementById('cry-{{ $response['id'] }}').play()" class="text-sm text-blue-600 underline">
                Escuchar
            </button>
            <audio id="cry-{{ $response['id'] }}" src="{{ $response['cries']['latest'] }}"></audio>

            @if(count($response['evolutions']) > 0)
            <button @click="showModal = true" class="mt-2 bg-indigo-500 text-white px-3 py-1 rounded">
                Ver Evoluciones
            </button>
            @endif
        </div>
        <div class="md:w-1/2">
            <img src="{{ $response['image'] }}" alt="{{ $response['name'] }}" class="w-32 h-32 mx-auto" />
        </div>
    </div>

    <!-- Modal -->
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 "
    >
        <div class="bg-white p-8 rounded-xl max-w-6xl w-full relative">
            <button @click="showModal = false" class="absolute top-2 right-3 text-gray-600 hover:text-black text-xl">&times;</button>
            <h3 class="text-xl font-semibold mb-4 text-center">Evoluciones de {{ $response['name'] }}</h3>

            <div class="grid grid-cols-2 gap-4">
                @foreach($response['evolutions'] as $evolution)
                <div class="text-center">
                    <img src="{{ $evolution['image'] }}" alt="{{ $evolution['name'] }}" class="w-24 h-24 mx-auto" />
                    <p class="text-sm font-bold">{{ $evolution['name'] }}</p>
                    <ul class="text-xs mt-1">
                        @foreach($evolution['stats'] as $stat)
                            <li>{{ $stat['stat']['name'] }}: {{ $stat['base_stat'] }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach

</body>
</html>
