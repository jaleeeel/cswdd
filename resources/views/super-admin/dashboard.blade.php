@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Super Admin Dashboard
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3>{{ $stats['total_users'] }}</h3>
                                            <p class="mb-0">Total Users</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3>{{ $stats['active_users'] }}</h3>
                                            <p class="mb-0">Active Users</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-check fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3>{{ $stats['total_departments'] }}</h3>
                                            <p class="mb-0">Departments</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-building fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3>{{ $stats['total_reports'] }}</h3>
                                            <p class="mb-0">Reports Generated</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-bar fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('super-admin.users') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-users me-2"></i>Manage Users
                                        </a>
                                        <a href="{{ route('super-admin.departments') }}" class="btn btn-outline-info">
                                            <i class="fas fa-building me-2"></i>Manage Departments
                                        </a>
                                        <a href="{{ route('super-admin.reports') }}" class="btn btn-outline-warning">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>System Overview</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>System Status:</strong> <span class="badge bg-success">Online</span></p>
                                    <p><strong>Last Updated:</strong> {{ now()->format('M d, Y H:i:s') }}</p>
                                    <p><strong>Version:</strong> v1.0.0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
