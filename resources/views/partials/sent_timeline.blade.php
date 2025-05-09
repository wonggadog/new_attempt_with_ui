@php
    $steps = ['sent' => 'bi-pencil-square', 'delivered' => 'bi-house', 'read' => 'bi-eye', 'acknowledged' => 'bi-check-circle-fill'];
    $labels = ['sent' => 'Sent', 'delivered' => 'Delivered', 'read' => 'Read', 'acknowledged' => 'Acknowledged'];
    $statusHistory = collect($doc->statuses)->pluck('status')->toArray();
@endphp
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $doc->attention }}</strong> <span class="badge bg-secondary">{{ $doc->file_type }}</span>
        </div>
        <div>
            <span class="text-muted">To: {{ $doc->to }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <span class="fw-bold">Sent:</span> {{ $doc->created_at->format('M d, Y H:i') }}
        </div>
        <div class="tracking-progress mb-3">
            <div class="stepper-wrapper">
                @foreach($steps as $status => $icon)
                    <div class="stepper-item {{ in_array($status, $statusHistory) ? 'completed' : '' }}">
                        <div class="step-counter">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="step-name">{{ $labels[$status] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="tracking-timeline">
            @foreach($doc->statuses as $status)
                <div class="timeline-item">
                    <div class="timeline-date">{{ $status->created_at ? \Carbon\Carbon::parse($status->created_at)->format('M d H:i') : '' }}</div>
                    <div class="timeline-badge completed">
                        <i class="bi bi-check"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">{{ ucfirst($status->status) }}</div>
                        <div class="timeline-description">
                            @if($status->status === 'sent')
                                Document sent to recipient.
                            @elseif($status->status === 'delivered')
                                Document delivered to recipient's inbox.
                            @elseif($status->status === 'read')
                                Document has been opened/read by recipient.
                            @elseif($status->status === 'acknowledged')
                                Recipient has acknowledged the document.
                            @else
                                Status updated.
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">
            <span class="fw-bold">Notes:</span> {{ $doc->additional_notes ?? 'None' }}
        </div>
    </div>
</div> 