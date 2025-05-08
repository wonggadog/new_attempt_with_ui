<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 24px; font-weight: bold; }
        .content { padding: 20px; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        .section-content { background-color: #f1f1f1; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">New Document Uploaded</div>
        <div class="content">
            <p>Dear {{ $recipientName }},</p>
            <p>{{ $senderName }} has uploaded a new document for your attention.</p>
            <div class="section">
                <div class="section-title">Action Items:</div>
                <div class="section-content">{{ $actionItems }}</div>
            </div>
            <div class="section">
                <div class="section-title">Additional Actions:</div>
                <div class="section-content">{{ $additionalActions }}</div>
            </div>
            <div class="section">
                <div class="section-title">Additional Notes:</div>
                <div class="section-content">{{ $notes }}</div>
            </div>
            <div class="section">
                <div class="section-title">Document Details:</div>
                <ul>
                    <li><strong>Subject:</strong> {{ $subject }}</li>
                    <li><strong>File Name:</strong> {{ $fileName }}</li>
                </ul>
            </div>
            <p>Please log in to your account to view and manage the document.</p>
        </div>
        <div class="footer">
            This is an automated message. Please do not reply to this email.<br>
            BUCS DocuManage System
        </div>
    </div>
</body>
</html> 