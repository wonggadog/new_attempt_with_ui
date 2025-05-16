<p>Dear {{ $document->to }},</p>

<p>This is a reminder that you have a pending document that requires your attention.</p>

<ul>
    <li><strong>Reference #:</strong> {{ $document->id }}</li>
    <li><strong>Subject:</strong> {{ $document->file_type }}</li>
    <li><strong>From:</strong> {{ $document->from }}</li>
    <li><strong>Due Date:</strong> {{ $document->due_date }}</li>
</ul>

<p>Please take the necessary action before the due date.</p>

<p>Thank you,<br>BUCS DocuManage</p> 