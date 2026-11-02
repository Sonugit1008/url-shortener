<p>Hello {{ $invitation->name }},</p>

<p>
    You have been invited to join <strong>{{ $invitation->company->name }}</strong>
    as <strong>{{ $invitation->role->name }}</strong> on URL Shortener.
</p>

<p>
    <a href="{{ route('invitations.show', $invitation->token) }}">Click here to accept the invitation</a>
</p>

<p>This link will expire on {{ $invitation->expires_at->format('d M Y') }}.</p>
