import echo from './echo';

document.addEventListener('DOMContentLoaded', function() {
    echo.channel('PublicChannel')
        .listen('.public-channel', (e) => {
            console.log(e);
            document.getElementById('text').innerText = e.data;
        });
});

// document.addEventListener('DOMContentLoaded', function() {
//     echo.private('Order.User.1')
//         .listen('.order-user', (e) => {
//             console.log(e);
//             document.getElementById('count').innerText = e.total_unread;
//         });
// });
