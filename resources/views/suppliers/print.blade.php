<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suppliers Registry - Print View</title>
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
            <p class="text-muted mb-0">Supplier Listing Directory</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm no-print">Print Document</button>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>GSTIN</th>
                <th>Outstanding Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $s)
            <tr>
                <td>{{ $s->id }}</td>
                <td>{{ $s->name }}</td>
                <td>{{ $s->contact_person ?? 'N/A' }}</td>
                <td>{{ $s->phone ?? 'N/A' }}</td>
                <td>{{ $s->email ?? 'N/A' }}</td>
                <td>{{ $s->gstin ?? 'N/A' }}</td>
                <td>${{ number_format($s->outstanding_balance, 2) }}</td>
                <td>{{ $s->status ? 'Active' : 'Inactive' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
