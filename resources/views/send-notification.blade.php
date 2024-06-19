<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Bakti Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mx-auto pt-20 px-4">
        <div class="w-full text-gray-800 leading-normal">
            <div class="flex flex-wrap justify-center">
                <div class="w-full md:w-2/3 xl:w-1/2 p-3">
                    <div class="bg-white border rounded shadow p-4">
                        <div class="flex flex-col items-center">
                            <!-- Status Message -->
                            <div id="status-message" class="mb-4 p-2 bg-green-200 text-green-800 rounded w-full text-center" style="display: none;"></div>
                            <div id="status-message-error" class="mb-4 p-2 bg-red-200 text-red-800 rounded w-full text-center" style="display: none;"></div>
                            <!-- End of Status Message -->
                            <h5 class="font-bold uppercase text-gray-800 mb-4">Input Message</h5>
                            <!-- Add the form here -->
                            <form id="message-form" class="w-full flex" data-route="{{ route('send-notification') }}">
                                @csrf
                                <div class="flex w-full">
                                    <input type="text" id="message" name="message" class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter your message" autocomplete="off" required>
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r focus:outline-none focus:shadow-outline">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                    </button>
                                </div>
                            </form>
                            <!-- End of form -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        $('#message-form').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            var message = $('#message').val();
            var token = $('meta[name="csrf-token"]').attr('content');
            var routeUrl = $('#message-form').data('route');

            $.ajax({
                url: routeUrl,
                type: 'POST',
                data: {
                    _token: token,
                    message: message
                },
                success: function(response) {
                    $('#status-message').text(response.status).fadeIn().delay(3000).fadeOut();
                    $('#message').val(''); // Clear the input field
                },
                error: function(xhr) {
                    var errorMsg = 'Error sending message.';
                    if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.message) {
                        errorMsg = xhr.responseJSON.errors.message[0];
                    }
                    $('#status-message-error').text(errorMsg).fadeIn().delay(3000).fadeOut();
                }
            });
        });
    });
    </script>
</body>
</html>
