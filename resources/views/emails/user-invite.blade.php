<h1>You're invited 🎉</h1>

<p>You were invited to join {{ $invite->tenant->name }}</p>

<a href="{{ url('/invites/accept/'.$invite->token) }}">
    Accept invitation
</a>

<p>This link expires in 7 days.</p>
