@extends('layouts.app')

@section('title', 'Dashboard')
@section('module-title', 'Overview')

@section('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Today's Sales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Sales</div>
                        <div class="h5 mb-0 font-weight-bold">$1,250.00</div>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Sales</div>
                        <div class="h5 mb-0 font-weight-bold">$42,380.00</div>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="bi bi-graph-up fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stock Value</div>
                        <div class="h5 mb-0 font-weight-bold">$85,450.00</div>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-danger border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Low Stock Alerts</div>
                        <div class="h5 mb-0 font-weight-bold text-danger">4 Items</div>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sales -->
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>Recent Sales Orders</span>
                <a href="#" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>INV-2026-0001</td>
                                <td>John Doe</td>
                                <td>27 Jun 2026</td>
                                <td>$799.00</td>
                                <td><span class="badge bg-success bg-opacity-15 text-success badge-custom">Completed</span></td>
                            </tr>
                            <tr>
                                <td>INV-2026-0002</td>
                                <td>Jane Smith</td>
                                <td>27 Jun 2026</td>
                                <td>$1,200.00</td>
                                <td><span class="badge bg-success bg-opacity-15 text-success badge-custom">Completed</span></td>
                            </tr>
                            <tr>
                                <td>INV-2026-0003</td>
                                <td>Bob Johnson</td>
                                <td>26 Jun 2026</td>
                                <td>$350.00</td>
                                <td><span class="badge bg-warning bg-opacity-15 text-warning badge-custom">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Activities -->
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-activity me-2"></i>Recent Activities
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                            <i class="bi bi-cart-check-fill fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-7">Purchase Invoice Created</div>
                            <small class="text-muted d-block">PO-2026-0014 has been accepted by Warehouse.</small>
                            <span class="text-xs text-muted">10 mins ago</span>
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle text-success">
                            <i class="bi bi-cash-stack fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-7">Payment Received</div>
                            <small class="text-muted d-block">Received $799.00 from John Doe.</small>
                            <span class="text-xs text-muted">45 mins ago</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning">
                            <i class="bi bi-shield-lock-fill fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-7">User Role Assigned</div>
                            <small class="text-muted d-block">Cashier User was assigned role "Cashier".</small>
                            <span class="text-xs text-muted">2 hours ago</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
