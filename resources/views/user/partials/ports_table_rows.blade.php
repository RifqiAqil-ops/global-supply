@forelse($ports as $port)
@php
    $latestScore = $port->country && $port->country->latestRiskScore 
        ? (float) $port->country->latestRiskScore->composite_score 
        : null;

    $riskColor = 'bg-secondary';
    if ($latestScore !== null) {
        if ($latestScore < 40) {
            $riskColor = 'bg-success';
        } elseif ($latestScore < 70) {
            $riskColor = 'bg-warning';
        } else {
            $riskColor = 'bg-danger';
        }
    }
@endphp
<tr>
    <td class="align-middle">
        <div class="d-flex align-items-center gap-2">
            <span class="text-primary"><i class="bi bi-ship-front-fill fs-5"></i></span>
            <div>
                <strong class="text-dark d-block" style="font-size: 0.88rem;">{{ $port->name }}</strong>
                @if($port->un_locode)
                    <span class="badge bg-light text-muted border border-light-subtle rounded-pill py-0 px-2" style="font-size: 0.65rem;">LOCODE: {{ $port->un_locode }}</span>
                @endif
            </div>
        </div>
    </td>
    <td class="align-middle small">
        <code class="text-dark bg-light px-2 py-1 rounded" style="font-size: 0.76rem;">{{ $port->port_code ?? 'N/A' }}</code>
    </td>
    <td class="align-middle">
        <div class="d-flex align-items-center gap-2">
            @if($port->country)
                <img src="{{ $port->country->flag_url }}" alt="{{ $port->country->name }} Flag" class="rounded border border-secondary border-opacity-10 shadow-sm" style="width: 24px; height: 16px; object-fit: cover; flex-shrink: 0;">
                <div>
                    <span class="text-dark fw-semibold small d-block" style="line-height: 1.25;">{{ $port->country->name }}</span>
                    @if($port->region || $port->country->region)
                        <span class="text-muted" style="font-size: 0.68rem;">{{ $port->region ?? $port->country->region }}</span>
                    @endif
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </div>
    </td>
    <td class="align-middle small text-muted">
        {{ number_format($port->latitude, 4) }}, {{ number_format($port->longitude, 4) }}
    </td>
    <td class="align-middle small">
        <span class="badge bg-light text-dark border border-light-subtle rounded-pill py-1 px-2.5 text-capitalize" style="font-size: 0.72rem;">{{ $port->port_type }}</span>
    </td>
    <td class="align-middle small">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill py-1 px-2.5" style="font-size: 0.72rem;">{{ $port->port_size ?? 'N/A' }}</span>
    </td>
    <td class="align-middle">
        @if($latestScore !== null)
            <div class="d-flex align-items-center gap-2" style="min-width: 110px;">
                <div class="risk-progress-bar flex-grow-1">
                    <div class="risk-progress-fill {{ $riskColor }}" style="width: {{ $latestScore }}%;"></div>
                </div>
                <strong class="text-dark small" style="font-size: 0.76rem;">{{ number_format($latestScore, 1) }}</strong>
            </div>
        @else
            <span class="text-muted small">N/A</span>
        @endif
    </td>
    <td class="align-middle">
        <button type="button" onclick="focusMap({{ $port->latitude }}, {{ $port->longitude }}, '{{ addslashes($port->name) }}')" class="btn btn-sm btn-light border-light-subtle rounded-pill text-primary fw-bold px-3 py-1 shadow-sm" style="font-size: 0.75rem;">
            <i class="bi bi-geo-alt-fill me-1"></i>Focus
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted py-5">
        <div class="py-4">
            <span class="d-block mb-2 fs-2">⚓</span>
            <h6 class="fw-bold text-dark mb-1">No matching ports found</h6>
            <p class="text-muted small mb-0">Try another keyword or filter configurations.</p>
        </div>
    </td>
</tr>
@endforelse
