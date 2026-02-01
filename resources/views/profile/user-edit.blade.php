@extends('layouts.user')

@section('title', 'My Profile')

@section('content_header')
    <h1>My Profile</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            Profile information updated successfully.
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            Password updated successfully.
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <div class="img-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <h3 class="profile-username text-center mt-3">{{ $user->name }}</h3>
                    <p class="text-muted text-center">{{ '@' . $user->username }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Country</b> <a class="float-right">{{ $user->country ?? 'Not set' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Referral Code</b> <a class="float-right"><code>{{ $user->referral_code }}</code></a>
                        </li>
                        <li class="list-group-item">
                            <b>Member Since</b> <a class="float-right">{{ $user->created_at->format('M d, Y') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Wallet Info -->
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-wallet mr-2"></i>Wallet Summary</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @if($user->wallet)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>USDT Balance</span>
                                <strong>${{ number_format($user->wallet->balance, 2) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>DOGE Balance</span>
                                <strong>{{ number_format($user->wallet->doge_balance, 8) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Invested</span>
                                <strong>${{ number_format($user->wallet->invested_amount, 2) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Earned</span>
                                <strong>${{ number_format($user->wallet->earned_amount + $user->wallet->referral_earnings, 2) }}</strong>
                            </li>
                        @else
                            <li class="list-group-item text-muted">No wallet yet</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Update Profile -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Update Profile</h3>
                </div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $user->country) }}" placeholder="e.g., United States">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" value="{{ $user->username }}" disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Profile</button>
                    </div>
                </form>
            </div>

            <!-- BEP-20 Wallet Address -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link mr-2"></i>BEP-20 Wallet Address (Withdrawal)</h3>
                </div>
                <form method="POST" action="{{ route('profile.update-wallet') }}">
                    @csrf
                    @method('patch')
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            This is the BSC (BEP-20) wallet address where your USDT withdrawals will be sent. Make sure this is correct before requesting a withdrawal.
                        </div>
                        <div class="form-group">
                            <label for="bep_wallet_address">BEP-20 Wallet Address</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-wallet"></i></span>
                                </div>
                                <input type="text" class="form-control @error('bep_wallet_address') is-invalid @enderror" id="bep_wallet_address" name="bep_wallet_address" value="{{ old('bep_wallet_address', $user->bep_wallet_address) }}" placeholder="0x..." maxlength="42">
                                @error('bep_wallet_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Must be a valid BSC address starting with 0x (42 characters)</small>
                        </div>
                        @if($user->bep_wallet_address)
                            <p class="mb-0">
                                <strong>Current:</strong>
                                <a href="https://bscscan.com/address/{{ $user->bep_wallet_address }}" target="_blank">
                                    <code>{{ $user->bep_wallet_address }}</code>
                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            </p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Update Wallet Address</button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Change Password</h3>
                </div>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" id="current_password" name="current_password" required>
                            @if($errors->updatePassword->has('current_password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" id="password" name="password" required>
                                    @if($errors->updatePassword->has('password'))
                                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info"><i class="fas fa-key mr-1"></i>Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.alert-dismissible').delay(5000).fadeOut(500);
});
</script>
@stop
