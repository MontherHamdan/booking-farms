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
                    <form action="{{ route('svgs.update', $svg->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h4 class="mb-4 text-primary">Edit Svg</h4>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title (Optional)</label>
                            <input type="text" name="title" id="title" class="form-control"
                                value="{{ $svg->title }}">
                        </div>
                        <div class="mb-3">
                            <label for="svg_code" class="form-label">SVG Code</label>
                            <textarea name="svg_code" id="svg_code" class="form-control" rows="5" required oninput="updateSvgPreview()">{{ $svg->svg_code }}</textarea>
                        </div>

                        <!-- SVG Preview -->
                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div id="svg-preview"
                                class="border rounded p-2 d-flex justify-content-center align-items-center"
                                style="min-height: 150px; max-width: 150px; overflow: hidden; margin: 0 auto;">
                                <div style="width: 100%; height: 100%; max-width: 100px; max-height: 100px;">
                                    {!! $svg->svg_code !!}
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">Update SVG</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function updateSvgPreview() {
            const svgCode = document.getElementById('svg_code').value;
            const previewContainer = document.getElementById('svg-preview').querySelector('div');
            previewContainer.innerHTML = svgCode;
        }
    </script>
@endsection
