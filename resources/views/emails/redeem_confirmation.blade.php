<!DOCTYPE html>
<html>
<head>
    <title>Bakti Shop - Email</title>
</head>
<body>
    <h1 style="color: #333;">{{ trans('all.greeting_email_transaction_succes') }}</h1>
    <h3>#{{ $header_data['redeem_code'] }}</h3>

    <table style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Item</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Quantity</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail_data as $item)
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
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ $header_data['total_price'] }}</th>
            </tr>
            <tr>
                <th colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Shipping Fee</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ $header_data['shipping_fee'] }}</th>
            </tr>
            <tr>
                <th colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: right;">Grand Total</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ $header_data['total_amount'] }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
