@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="block-login">
                <h2 class="text-center mt-5 mb-2">AAPPL Login</h2>
                <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                {{ csrf_field() }}
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" class="form-control" placeholder="Enter Username" type="text" name="username" value="">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" class="form-control" placeholder="Enter Password" type="password" name="password" value="">
                    </div>
                    <div class="form-group text-center">
                        <button type='submit' class="btn btn-primary" >Log In</button>
                    </div>
                </form>
            </div>
        </div><!-- end col -->
    </div><!-- end row -->
@endsection
