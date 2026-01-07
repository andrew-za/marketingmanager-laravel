{{ $textContent }}

---
{{ $campaign->organization->name ?? 'MarketPulse' }}
Unsubscribe: {{ route('email.unsubscribe', ['token' => $trackingToken]) }}

