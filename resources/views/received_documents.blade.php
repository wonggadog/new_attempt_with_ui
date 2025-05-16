<div class="document-details">
    <h3>{{ $document->title }}</h3>
    <p>{{ $document->description }}</p>
    <p><strong>Due Date:</strong> {{ $document->due_date }}</p>
    <a href="{{ route('documents.download', $document->id) }}" class="btn btn-primary">Download</a>
</div> 