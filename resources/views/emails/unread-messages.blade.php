<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسائل غير مقروءة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            direction: rtl;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #855839 0%, #bb6d35 50%, #7d4a19 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .email-header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
            color: #333;
        }
        .alert-box {
            background-color: #e3f2fd;
            border-right: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-box p {
            margin: 0;
            color: #0d47a1;
            font-weight: 500;
        }
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            gap: 15px;
        }
        .stat-box {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #855839;
            margin: 0;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        .message-item {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-right: 3px solid #855839;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .message-sender {
            font-weight: bold;
            color: #855839;
            font-size: 15px;
        }
        .message-time {
            font-size: 12px;
            color: #6c757d;
        }
        .message-chat {
            font-size: 13px;
            color: #495057;
            margin-bottom: 8px;
        }
        .message-text {
            color: #212529;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #8B4513 0%, #b65d1dff 100%);
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
            transition: transform 0.3s ease;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
        }
        .email-footer {
            background-color: #f8f8f8;
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
        .divider {
            height: 1px;
            background: linear-gradient(to left, transparent, #ddd, transparent);
            margin: 20px 0;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        @media only screen and (max-width: 600px) {
            .stats-container {
                flex-direction: column;
            }
            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>💬 رسائل غير مقروءة</h1>
            <p>لديك رسائل جديدة في انتظارك</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="alert-box">
                <p>📩 لديك رسائل جديدة لم تقرأها بعد</p>
            </div>

            <p class="greeting">
                مرحباً {{ $user->name }}،
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6;">
                لديك رسائل غير مقروءة في محادثاتك. يرجى مراجعتها والرد عليها.
            </p>

            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-number">{{ $totalUnread }}</div>
                    <div class="stat-label">رسالة غير مقروءة<br>Unread Messages</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">{{ $chatsWithUnread }}</div>
                    <div class="stat-label">محادثة<br>Conversations</div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Recent Messages Preview (Show up to 5) -->
            <h3 style="color: #855839; margin-bottom: 15px;">📝 آخر الرسائل:</h3>
            
            @foreach($unreadMessages->take(5) as $message)
            <div class="message-item">
                <div class="message-header">
                    <span class="message-sender">👤 {{ $message->user->name }}</span>
                    <span class="message-time">⏰ {{ $message->created_at->diffForHumans() }}</span>
                </div>
                <div class="message-chat">
                    💬 {{ $message->chat->type === 'private' ? 'محادثة خاصة' : $message->chat->name }}
                </div>
                <div class="message-text">
                    {{ Str::limit($message->message, 150) }}
                </div>
            </div>
            @endforeach

            @if($totalUnread > 5)
            <p style="text-align: center; color: #6c757d; font-size: 14px; margin-top: 15px;">
                ... و {{ $totalUnread - 5 }} رسالة أخرى
            </p>
            @endif

            <div class="divider"></div>

            <div style="text-align: center;">
                <a href="{{ url('/dashboard/chat') }}" class="action-button">
                    افتح الرسائل الآن 💬
                </a>
            </div>

            <p style="font-size: 13px; color: #888; margin-top: 30px; line-height: 1.6; text-align: center;">
                💡 <strong>ملاحظة:</strong> للحصول على أفضل تجربة، يرجى الرد على الرسائل في أقرب وقت ممكن.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>© {{ date('Y') }} جميع الحقوق محفوظة</p>
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً من نظام إدارة الحجوزات</p>
            <p style="margin-top: 10px; font-size: 11px;">
                إذا كنت لا تريد تلقي إشعارات الرسائل، يمكنك تعطيلها من إعدادات حسابك
            </p>
        </div>
    </div>
</body>
</html>
