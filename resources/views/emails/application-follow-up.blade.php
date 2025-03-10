<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Job Application Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4361ee;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #4361ee;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Metana</h1>
        </div>
        <div class="content">
            <h2>Application Under Review</h2>
            <p>Dear {{ $application->name }},</p>
            
            <p>Thank you for applying to Metana. We wanted to let you know that your application is currently under review by our team.</p>
            
            <p>We appreciate your interest in joining us and will carefully evaluate your qualifications and experience. Our team is committed to giving each application the attention it deserves.</p>
            
            <p>If your qualifications match our requirements, we will reach out to schedule an interview. Please note that due to the high volume of applications we receive, this process may take some time.</p>
            
            <p>Thank you for your patience and interest in Metana.</p>
            
            <p>Best regards,<br>
            The Metana Hiring Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Metana. All rights reserved.</p>
            <p>651 N Broad ST Suite 206, Middletown, DE 19709, United States</p>
        </div>
    </div>
</body>
</html>