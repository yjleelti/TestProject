@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h2 class="mt-5">Test List</h2>
            <table class="table table-hover mt-2">
                <thead>
                <tr>
                    <th>Communication Mode </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($results as $result)

                    <tr>
                        <td>{{ $result->TestDescription }}</td>
                        <td><a href="/test?testid={{$result->TestID}}&session_id={{$result->session_id}}&activity_id={{$result->FormID}}&testdescription={{$result->TestDescription}}&contacttestid={{$result->ContactTestID}}" class="btn btn-primary">Start Test</a></td>
                    </tr>
                @endforeach



                </tbody>
            </table>
        </div><!-- end col -->
    </div><!-- end row -->
    <div class="row">
        <div class="col">
                            <span><a href="{{ url('/logout') }}"
                                     onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();" class="btn btn-secondary float-right">Logout <i class="fa fa-sign-out" aria-hidden="true"></i></a></span>
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
        </div>
    </div>
@endsection
