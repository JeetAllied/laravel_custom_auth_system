@extends('layout.main_layout')

@section('body')
<div class="row">
    <div class="col-md-4">
        <ul class="list-group profile-nav">
            <li class="list-group-item {{request()->route()->getName() == 'dashboard' ? 'active' : ''}}"><a href="{{route('dashboard')}}">Dashboard</a></li>
            <li class="list-group-item {{request()->route()->getName() == 'editProfile' ? 'active' : ''}}"><a href="{{route('editProfile')}}">Edit Profile</a></li>
            <li class="list-group-item" {{request()->route()->getName() == 'changePassword' ? 'active' : ''}}><a href="{{route('changePassword')}}">Change Password</a></li>
        </ul>
    </div>
    <div class="col-md-8">
        @yield('profile')
    </div>
</div>


@endsection
