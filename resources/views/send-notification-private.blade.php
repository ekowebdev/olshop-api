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
                            <!-- Status Notification -->
                            <div id="status-notification" class="mb-4 p-2 bg-green-200 text-green-800 rounded w-full text-center" style="display: none;"></div>
                            <div id="status-notification-error" class="mb-4 p-2 bg-red-200 text-red-800 rounded w-full text-center" style="display: none;"></div>
                            <!-- End of Status Notification -->
                            <h5 class="font-bold uppercase text-gray-800 mb-4">Send Notification</h5>
                            <!-- Add the form here -->
                            <form id="notification-form" class="w-full flex flex-col items-center" data-route="{{ route('send-notification-private') }}">
                                @csrf
                                <div class="w-full mb-4">
                                    <label for="user_id" class="block text-gray-700 text-sm font-bold mb-2">Select User Email</label>
                                    <select id="user_id" name="user_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="">-</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    <i class="fas fa-paper-plane mr-2"></i>Send
                                </button>
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
            $('#notification-form').on('submit', function(e) {
                e.preventDefault();
                var userId = $('#user_id').val();
                var token = $('meta[name="csrf-token"]').attr('content');
                var routeUrl = $('#notification-form').data('route');

                $.ajax({
                    url: routeUrl,
                    type: 'POST',
                    data: {
                        _token: token,
                        user_id: userId
                    },
                    success: function(response) {
                        $('#status-notification').text(response.status).fadeIn().delay(3000).fadeOut();
                        $('#user_id').val('');
                    },
                    error: function(xhr) {
                        var errorMsg = 'Error sending notification.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            if (xhr.responseJSON.errors.user_id) {
                                errorMsg = xhr.responseJSON.errors.user_id[0];
                            }
                        }
                        $('#status-notification-error').text(errorMsg).fadeIn().delay(3000).fadeOut();
                    }
                });
            });
        });
    </script>
</body>
</html>
