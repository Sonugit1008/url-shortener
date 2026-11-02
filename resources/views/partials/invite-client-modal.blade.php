<!-- Invite New Client Modal -->
<div class="modal fade" id="inviteClientModal" tabindex="-1" aria-labelledby="inviteClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('superadmin.invite-client') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="inviteClientModalLabel">Invite New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="Client Name" required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="ex. sample@example.com" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Invitation</button>
                </div>

            </form>

        </div>
    </div>
</div>

@if ($errors->has('company_name') || $errors->has('email'))
    @push('scripts')
        <script>
            // Reopen the modal when validation fails
            $(document).ready(function () {
                new bootstrap.Modal('#inviteClientModal').show();
            });
        </script>
    @endpush
@endif
