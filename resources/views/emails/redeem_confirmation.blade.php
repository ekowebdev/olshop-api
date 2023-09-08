<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <h1 style="color: #333;">Thank you for your order!</h1>
    <h2>#{{ $transaction_details['redeem_code'] }}</h2>

    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Item</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Quantity</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($item_details as $item)
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['name'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['quantity'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['price'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total Price</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ $transaction_details['total_price'] }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
