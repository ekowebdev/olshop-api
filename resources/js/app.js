import echo from './echo';

document.addEventListener('DOMContentLoaded', function() {
    echo.channel('PublicChannel')
        .listen('PublicNotificationEvent', (e) => {
            console.log(e);
            document.getElementById('text').innerText = e.data;
        });
});

// document.addEventListener('DOMContentLoaded', function() {
//     echo.private('App.Models.User.1')
//         .listen('RealTimeNotificationEvent', (e) => {
//             console.log(e);
//             document.getElementById('count').innerText = e.total_unread;
//         });
// });
