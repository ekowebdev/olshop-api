<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel Websockets</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
    </head>
    <body>
        <div class="container w-full mx-auto pt-20">
            <div class="w-full px-4 md:px-0 md:mt-8 mb-16 text-gray-800 leading-normal">
                <div class="flex flex-wrap">
                    <div class="w-full md:w-2/2 xl:w-3/3 p-3">
                        <div class="bg-white border rounded shadow p-2">
                            <div class="flex flex-row items-center">
                                {{-- <div class="flex-shrink pr-4">
                                    <div class="rounded p-3 bg-yellow-600"><i class="fas fa-user-plus fa-2x fa-fw fa-inverse"></i></div>
                                </div> --}}
                                <div class="flex-1 text-right md:text-center">
                                    <h5 class="font-bold uppercase text-gray-800">Bio</h5>
                                    <h3 class="font-bold text-2xl">
                                        <p>
                                            Name: <span id="user_name"></span>
                                        </p>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    @vite('resources/js/app.js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Echo.channel('events')
                .listen('NotificationEvent', (e) => {
                    document.getElementById('user_name').innerText = e.data;
                })
        });
    </script>
</html>