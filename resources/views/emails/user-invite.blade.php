<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>🎉 You're invited!</h2>

    <p>
        You were invited to join
        <strong>{{ $invite->tenant->name }}</strong>
        as <strong>{{ ucfirst($invite->role) }}</strong>.
    </p>

    <p>
        Click the button below to accept the invitation:
    </p>

    <p>
        <a
            href="{{ route('invites.accept', $invite->token) }}"
            style="
                display: inline-block;
                padding: 10px 16px;
                background: #000;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
            "
        >
            Accept invitation
        </a>
    </p>

    <p style="margin-top: 20px; font-size: 12px; color: #666;">
        This invitation expires on
        {{ $invite->expires_at->format('d/m/Y H:i') }}.
    </p>
</body>
</html>
