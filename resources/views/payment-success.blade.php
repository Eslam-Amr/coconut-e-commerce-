<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
        }
        .detail-value {
            color: #666;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Payment Successful!</h1>
        <p class="message">{{ $message ?? 'Your payment has been processed successfully.' }}</p>
        
        @if(isset($order_number) || isset($transaction_id) || isset($amount) || isset($new_balance))
        <div class="details">
            @if(isset($order_number))
            <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span class="detail-value">{{ $order_number }}</span>
            </div>
            @endif
            
            @if(isset($transaction_id))
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value">{{ $transaction_id }}</span>
            </div>
            @endif
            
            @if(isset($amount))
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value">${{ number_format($amount, 2) }}</span>
            </div>
            @endif
            
            @if(isset($new_balance))
            <div class="detail-row">
                <span class="detail-label">New Balance:</span>
                <span class="detail-value">${{ number_format($new_balance, 2) }}</span>
            </div>
            @endif
        </div>
        @endif
        
        <a href="/" class="btn">Return to Home</a>
    </div>
</body>
</html>