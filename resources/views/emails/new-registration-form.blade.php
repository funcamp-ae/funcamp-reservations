<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج تسجيل جديد</title>
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
            background-color: #fff3cd;
            border-right: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-box p {
            margin: 0;
            color: #856404;
            font-weight: 500;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table tr:last-child {
            border-bottom: none;
        }
        .info-table td {
            padding: 12px 0;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #855839;
            width: 40%;
        }
        .info-table td:last-child {
            color: #555;
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
        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-left: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container" dir="rtl">
        <!-- Header -->
        <div class="email-header">
            <h1>🔔 نموذج تسجيل جديد</h1>
            <p>تم استلام طلب تسجيل جديد</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="alert-box">
                <p>⚠️ تم استلام نموذج تسجيل جديد يتطلب المراجعة</p>
            </div>

            <p style="font-size: 16px; color: #333; line-height: 1.6;">
                مرحباً،
            </p>
            <p style="font-size: 14px; color: #555; line-height: 1.6;">
                تم تقديم نموذج تسجيل جديد في النظام. يرجى مراجعة التفاصيل التالية:
            </p>

            <div class="divider"></div>

            <table class="info-table">
                <tr>
                    <td><span class="icon"><i class="fas fa-hashtag"></i></span> رقم الطلب:</td>
                    <td><strong>{{ $registrationForm->request_code }}</strong></td>
                </tr>
                <tr>
                    <td><span class="icon"><i class="fas fa-user"></i></span> الاسم:</td>
                    <td>{{ $registrationForm->first_name }} {{ $registrationForm->last_name }}</td>
                </tr>
                <tr>
                    <td><span class="icon"><i class="fas fa-envelope"></i></span> البريد الإلكتروني:</td>
                    <td>{{ $registrationForm->email }}</td>
                </tr>
                <tr>
                    <td><span class="icon"><i class="fas fa-phone"></i></span> رقم الهاتف:</td>
                    <td>{{ $registrationForm->mobile_phone }}</td>
                </tr>
                @if($registrationForm->service)
                <tr>
                    <td><span class="icon"><i class="fas fa-concierge-bell"></i></span> المخيم:</td>
                    <td>{{ $registrationForm->service->name }}</td>
                </tr>
                @endif
                <tr>
                    <td><span class="icon"><i class="fas fa-calendar-alt"></i></span> تاريخ التقديم:</td>
                    <td>{{ $registrationForm->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            </table>

            <div class="divider"></div>

            <div style="text-align: center;">
                <a href="{{ route('registrationforms.index') }}" class="action-button">
                    عرض التفاصيل الكاملة →
                </a>
            </div>

            <p style="font-size: 13px; color: #888; margin-top: 30px; line-height: 1.6;">
                💡 <strong>ملاحظة:</strong> يرجى المراجعة والرد على هذا الطلب في أقرب وقت ممكن لتحسين تجربة العميل.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>© {{ date('Y') }} جميع الحقوق محفوظة</p>
            <p>هذا البريد الإلكتروني تم إرساله تلقائياً من نظام إدارة الحجوزات</p>
        </div>
    </div>
</body>
</html>
