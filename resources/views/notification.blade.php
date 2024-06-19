<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Bakti Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
</head>
<body>
    <div class="container mx-auto pt-20 px-4">
        <div class="w-full text-gray-800 leading-normal">
            <div class="flex flex-wrap justify-center">
                <div class="w-full md:w-2/3 xl:w-1/2 p-3">
                    <div class="bg-white border rounded shadow p-4">
                        <div class="flex flex-col items-center">
                            <div class="flex-1 text-center">
                                <h5 class="font-bold uppercase text-gray-800">Realtime Notification</h5>
                                <h3 class="font-bold text-2xl">
                                    <p>
                                        <span id="text">-</span>
                                    </p>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/app.js')
</body>
</html>
