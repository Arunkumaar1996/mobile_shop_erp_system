<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers Registry - Print View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-family: sans-serif; color: #000; padding: 20px; }
        .print-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-between align-items-center print-header">
        <div>
            <h2 class="fw-bold mb-0">Mobile Shop ERP</h2>
            <p class="text-muted mb-0">Customer Listing Directory</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm no-print">Print Document</button>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Wallet Balance</th>
                <th>Loyalty Points</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->name }}</td>
                <td>{{ $c->phone }}</td>
                <td>{{ $c->email ?? 'N/A' }}</td>
                <td>${{ number_format($c->wallet_balance, 2) }}</td>
                <td>{{ $c->loyalty_points }}</td>
                <td>{{ $c->status ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
