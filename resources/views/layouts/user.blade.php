@extends('adminlte::page')

@section('title', 'User Dashboard')

@section('adminlte_css')
    @parent
    <style>
        /* Custom User Theme - Blue Theme */
        :root {
            --user-primary: #3490dc;
            --user-secondary: #6cb2eb;
            --user-success: #38c172;
            --user-info: #6574cd;
            --user-warning: #ffed4a;
            --user-danger: #e3342f;
        }
        
        /* Navbar customization */
        .navbar-white {
            background-color: var(--user-primary) !important;
        }
        
        .navbar-light .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        /* Sidebar customization */
        .sidebar-dark-primary {
            background-color: #2779bd !important;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--user-secondary) !important;
        }
        
        /* Card customization */
        .card-primary:not(.card-outline) > .card-header {
            background-color: var(--user-primary) !important;
        }
        
        /* Button customization */
        .btn-primary {
            background-color: var(--user-primary) !important;
            border-color: var(--user-primary) !important;
        }
        
        /* Brand logo */
        .brand-link {
            background-color: #1c5d99 !important;
        }
        
        /* Footer customization */
        .main-footer {
            background-color: #f8f9fa;
            color: #6c757d;
        }
    </style>
@stop

@section('body_class', 'layout-fixed layout-navbar-fixed')

@section('content_top_nav_right')
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <span class="dropdown-item dropdown-header">3 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-coins mr-2"></i> New investment return
                <span class="float-right text-muted text-sm">3 mins</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-chart-line mr-2"></i> Investment completed
                <span class="float-right text-muted text-sm">12 hours</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-wallet mr-2"></i> Withdrawal processed
                <span class="float-right text-muted text-sm">2 days</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
    </li>
@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} <a href="#">CryptoInvest</a>.</strong> All rights reserved.
@stop

@section('js')
    <script>
        // Global JavaScript for user panel
        $(document).ready(function() {
            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Enable popovers
            $('[data-toggle="popover"]').popover();
            
            // Auto-hide alerts after 5 seconds
            $('.alert:not(.alert-important)').delay(5000).fadeOut(500);
        });
    </script>
@stop
