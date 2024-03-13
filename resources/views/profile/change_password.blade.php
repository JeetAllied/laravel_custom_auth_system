@extends('profile.profile_layout')

@section('profile')
    <div class="card">
        <div class="card-header">
            Change Password
        </div>
        <div class="card-body">
            <form action="{{route('updatePassword')}}" id="change_password_form" method="post">
                @csrf
                <div class="form-group">
                    <label for="old_password">Old Password</label>
                    <input type="password" name="old_password" id="old_password" class="form-control">
                    @if($errors->any('old_password'))
                        <span class="text-danger">{{$errors->first('old_password')}}</span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control">
                    @if($errors->any('new_password'))
                        <span class="text-danger">{{$errors->first('new_password')}}</span>
                    @endif
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                    @if($errors->any('confirm_password'))
                        <span class="text-danger">{{$errors->first('confirm_password')}}</span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>

@endsection
