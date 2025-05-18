@extends('admin.layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add New User</h4>
            </div>
            <div class="card-body">
                <!-- Display Errors if any -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                class="form-control" 
                                placeholder="Enter user name" 
                                required 
                                value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                class="form-control" 
                                placeholder="Enter user email" 
                                required 
                                value="{{ old('email') }}">
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control" 
                                placeholder="Enter password" 
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                class="form-control" 
                                placeholder="Confirm password" 
                                required>
                        </div>
                    </div>
                
                    <div class="row">
                        <!-- New Title Field -->
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title" 
                                class="form-control" 
                                placeholder="Enter user title" 
                                value="{{ old('title') }}">
                        </div>
                    
                        <!-- New Image Upload Field -->
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">User Image</label>
                            <input 
                                type="file" 
                                name="image" 
                                id="image" 
                                class="form-control">
                        </div>
                    </div>
                
                    <div class="form-check mb-3">
                        <input 
                            type="checkbox" 
                            name="is_admin" 
                            class="form-check-input" 
                            id="is_admin" 
                            {{ old('is_admin') ? 'checked' : '' }}>
                        <label for="is_admin" class="form-check-label">Admin</label>
                    </div>
                
                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
