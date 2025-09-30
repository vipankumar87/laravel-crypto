@extends('layouts.user')

@section('title', 'Referral Tree')

@section('content_header')
    <h1>Referral Tree</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Referral Network</h3>
                </div>
                <div class="card-body">
                    @if(count($referralTree) > 0)
                        <div class="referral-tree">
                            @foreach($referralTree as $node)
                                @include('referrals.partials.tree-node', ['node' => $node])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h4>No referrals yet</h4>
                            <p>Share your referral link to start building your network!</p>
                            <a href="{{ route('referrals.index') }}" class="btn btn-primary">
                                <i class="fas fa-share-alt"></i> Get Referral Link
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.referral-tree {
    font-family: Arial, sans-serif;
}

.tree-node {
    margin-left: 20px;
    position: relative;
    padding: 10px 0;
}

.tree-node:before {
    content: '';
    position: absolute;
    left: -15px;
    top: 25px;
    width: 10px;
    height: 1px;
    background-color: #ddd;
}

.tree-node:after {
    content: '';
    position: absolute;
    left: -15px;
    top: 0;
    width: 1px;
    height: 100%;
    background-color: #ddd;
}

.tree-node:last-child:after {
    height: 25px;
}

.user-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 5px;
    display: inline-block;
    min-width: 200px;
}

.level-1 .user-card { background: #e3f2fd; border-color: #2196f3; }
.level-2 .user-card { background: #f3e5f5; border-color: #9c27b0; }
.level-3 .user-card { background: #e8f5e8; border-color: #4caf50; }
</style>
@stop