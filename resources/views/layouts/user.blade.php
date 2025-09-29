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
