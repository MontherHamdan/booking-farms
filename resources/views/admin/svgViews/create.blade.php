@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('svgs.store') }}" method="POST">
                        @csrf
                        <h4 class="mb-4 text-primary">Create Svg</h4>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title (Optional)</label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ old('title') }}">
                        </div>
                        <div class="mb-3">
                            <label for="svg_code" class="form-label">SVG Code</label>
                            <textarea name="svg_code" id="svg_code" class="form-control" rows="5" required>{{ old('svg_code') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Add SVG</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
