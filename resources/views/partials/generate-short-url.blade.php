<!-- Generate Short URL Modal -->
<div class="modal fade" id="generateUrlModal" tabindex="-1" aria-labelledby="generateUrlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('short-urls.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="generateUrlModalLabel">Generate Short URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label fw-semibold">Long URL</label>
                    <input type="url" name="original_url" class="form-control @error('original_url') is-invalid @enderror" value="{{ old('original_url') }}" placeholder="e.g. https://sembark.com/travel-software/features/best-itinerary-builder" required>
                    @error('original_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>

            </form>

        </div>
    </div>
</div>

@if ($errors->has('original_url'))
    @push('scripts')
        <script>
            $(document).ready(function () {
                new bootstrap.Modal('#generateUrlModal').show();
            });
        </script>
    @endpush
@endif
