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
                            <!-- Status Message -->
                            @if (session('status'))
                                <div class="mb-4 p-2 bg-green-200 text-green-800 rounded w-full text-center">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <!-- End of Status Message -->
                            <h5 class="font-bold uppercase text-gray-800 mb-4">Input Notification</h5>
                            <!-- Add the form here -->
                            <form id="message-form" action="{{ route('send-notification') }}" method="POST" class="w-full">
                                @csrf
                                <div class="mb-4">
                                    <label for="message" class="block text-gray-700 text-sm font-bold mb-2"></label>
                                    <input type="text" id="message" name="message" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                </div>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto">
                                    Send
                                </button>
                            </form>
                            <!-- End of form -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @vite('resources/js/app.js')
</body>
</html>
