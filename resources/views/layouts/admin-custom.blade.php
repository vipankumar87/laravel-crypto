@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('adminlte_css')
    @parent
    <style>
        /* Custom Admin Theme - Dark Theme */
        :root {
            --admin-primary: #343a40;
            --admin-secondary: #6c757d;
            --admin-success: #28a745;
            --admin-info: #17a2b8;
            --admin-warning: #ffc107;
            --admin-danger: #dc3545;
            --admin-accent: #7952b3;
        }
        
        /* Navbar customization */
        .navbar-white {
            background-color: var(--admin-primary) !important;
        }
        
        .navbar-light .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        
        /* Sidebar customization */
        .sidebar-dark-primary {
            background-color: #212529 !important;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--admin-accent) !important;
        }
        
        /* Card customization */
        .card-primary:not(.card-outline) > .card-header {
            background-color: var(--admin-accent) !important;
        }
        
        /* Button customization */
        .btn-primary {
            background-color: var(--admin-accent) !important;
            border-color: var(--admin-accent) !important;
        }
        
        /* Brand logo */
        .brand-link {
            background-color: #111 !important;
            color: white !important;
        }
        
        /* Footer customization */
        .main-footer {
            background-color: #343a40;
            color: #f8f9fa;
            border-top: 1px solid #454d55;
        }
        
        /* Content wrapper */
        .content-wrapper {
            background-color: #f4f6f9;
        }
    </style>
@stop

@section('body_class', 'layout-fixed layout-navbar-fixed sidebar-mini')
