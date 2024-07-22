import echo from './echo';

document.addEventListener('DOMContentLoaded', function() {
    echo.channel('PublicChannel')
        .listen('.public-event', (e) => {
            console.log(e);
            document.getElementById('text').innerText = e.data;
        });
});

// document.addEventListener('DOMContentLoaded', function() {
//     echo.private('Order.User.1')
//         .listen('.order-user', (e) => {
//             console.log(e);
//             document.getElementById('text').innerText = e.data;
//         });
// });
