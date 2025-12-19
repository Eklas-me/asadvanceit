@extends('layouts.dashboard')

@section('content')
    <div class="page-header fade-in">
        <h1 class="page-title">List Admins</h1>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="aero-card">
                <div class="aero-card-header">
                    <h2 class="aero-card-title">Admin List</h2>
                </div>
                <div class="table-responsive">
                    <table class="aero-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Profile Photo</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admins as $admin)
                                <tr>
                                    <td>{{ $admin->name }}</td>
                                    <td>
                                        <img src="{{ $admin->profile_photo ? asset('uploads/' . $admin->profile_photo) : asset('uploads/user.png') }}"
                                            alt="Profile"
                                            style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                    </td>
                                    <td>{{ $admin->phone }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>{{ $admin->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection