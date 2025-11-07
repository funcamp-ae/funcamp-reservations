<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappMessageTemplate;

class PaymentReminderTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name' => 'إنشاء رابط دفع الكتروني',
                'type' => WhatsappMessageTemplate::TYPE_PAYMENT_LINK_CREATED,
                'message_ar' => '<p>🌟أهلاً [اسم العميل]،<br>تم إنشاء رابط الدفع الإلكتروني الخاص بحجزكم.<br>يمكنكم إتمام عملية السداد عبر الرابط التالي: [🔗 رابط الدفع]<br>شاكرين لكم تعاونكم ونتمنى لكم تجربة مميزة مع ✨️🏕️ Funcamp</p>',
                'message_en' => '<p>👋 Hello [Customer Name],<br>The online payment link for your reservation has been created.<br>You can complete the payment process via the following link: [🔗 Payment Link]<br>Thank you for your cooperation and we hope you have a great experience with Funcamp 🏕️✨</p>',
                'is_active' => true,
                'description' => 'إنشاء رابط دفع الكتروني'
            ],
            [
                'name' => 'إعادة إرسال رابط الدفع الإلكتروني',
                'type' => WhatsappMessageTemplate::TYPE_PAYMENT_LINK_RESEND,
                'message_ar' => '<p>🌟أهلاً [اسم العميل]،<br>نرسل لكم مجدداً رابط الدفع الإلكتروني الخاص بحجزكم للتسهيل عليكم.<br>يمكنكم إتمام عملية السداد عبر الرابط التالي: [🔗 رابط الدفع]<br>شاكرين لكم تعاونكم ونتمنى لكم تجربة مميزة مع ✨️🏕️ Funcamp</p>',
                'message_en' => '<p>👋Hello [Customer Name],<br>We are once again sending you the online payment link for your reservation for your convenience.<br>You can complete the payment process via the following link: [🔗 Payment Link]<br>Thank you for your cooperation and we hope you have a great experience with Funcamp✨🏕️</p>',
                'is_active' => true,
                'description' => 'إعادة إرسال رابط الدفع الإلكتروني'
            ],
            [
                'name' => 'تذكير بموعد الحجز',
                'type' => WhatsappMessageTemplate::TYPE_BOOKING_REMINDER,
                'message_ar' => '<p>🌟أهلاً [اسم العميل]،</p><p>تذكير لطيف نود 😊 إعلامكم بأن موعد حجزكم في المخيم سيكون يوم [التاريخ] من الساعة [وقت البداية] إلى الساعة [وقت النهاية] . وقد أرفقنا لكم ملف تأكيد الحجز &nbsp;متضمّنََا جميع التفاصيل.</p><p>&nbsp;ملاحظة هامة: يرجى التكّرم بسداد المبلغ المتبقي لدى مكتب االستقبال قبل استلام المخيم لضمان سهولة وسرعة إجراءات الدخول. نتطلع لاستقبالكم ونتمنى لكم تجربة ممتعة وأجواء لا تُنسى مع ✨️🏕️ Funcamp</p>',
                'message_en' => '<p>👋 Hello [Customer\'s Name],</p><p>This is a friendly reminder 😊 to inform you that your camping reservation will be will on [Date] , from [Start Time] to [End Time] .<br>We have also attached your booking confirmation file, which includes all the details.</p><p>Important Note : Please kindly settle the remaining payment at the reception desk before accessing the camp, to ensure a smooth and quick check-in process.</p><p>We look forward to welcoming you, and we hope you have an enjoyable and unforgettable experience with us at ✨🏕️ Funcamp.</p>',
                'is_active' => true,
                'description' => 'تذكير بموعد الحجز'
            ],
            [
                'name' => 'تذكير بقرب انتهاء الحجز',
                'type' => WhatsappMessageTemplate::TYPE_BOOKING_ENDING_REMINDER,
                'message_ar' => '<p>🌟أهلاً [اسم العميل]،<br>😔مع اقتراب انتهاء وقت حجزكم نتمنى لو استمرت تجربتكم معنا أكثر 🏕️✨<br><br>نود تذكيركم بأن موعد انتهاء حجزكم في المخيم (🔖 رقم الحجز: [رقم الحجز]) سيكون الساعة [وقت الخروج].<br>يرجى التكرم بتسجيل الخروج في الموعد المحدد.<br><br>في حال رغبتكم بتمديد فترة الإقامة، يمكنكم التواصل مع مكتب الاستقبال على الرقم: [رقم الهاتف] وذلك وفقاً للشروط والأحكام المعمول بها.<br><br>بانتظاركم دائماً 🏕️ Funcamp لتجارب أجمل مع</p>',
                'message_en' => '<p>👋 Hello [Customer Name],<br>😔 As your reservation time is coming to an end, we hope your experience with us will last longer 🏕️✨<br><br>We would like to remind you that your camp reservation (🔖 Reservation number: [Reservation Number]) will end at [check-out time].<br>Please check out on time.<br><br>If you would like to extend your stay, you may contact the reception at [phone number], in accordance with the applicable terms and conditions.<br><br>We look forward to welcoming you for more enjoyable experiences with Funcamp 🌟</p>',
                'is_active' => true,
                'description' => 'تذكير بقرب انتهاء الحجز'
            ],
            [
                'name' => 'تأكيد الحجز',
                'type' => WhatsappMessageTemplate::TYPE_RESERVATION_CONFIRMATION,
                'message_ar' => '<p>🌟 أهلاً [اسم العميل]،</p><p>نشكركم على ثقتكم باختيار Funcamp لقضاء أوقاتكم في أجواء صحراوية مميزة.</p><p>يسعدنا إبلاغكم بأنه تم تأكيد حجز المخيم الخاص بكم، وذلك وفق التفاصيل المبينة في ملف الـ PDF المرفق، والذي يتضمن جميع معلومات الحجز مثل موعد ومدة الحجز، رقم الحجز، المبلغ المدفوع والمتبقي، مبلغ التأمين، موقع المخيم، شروط وتعليمات الاستلام، وبيانات التحويل البنكي.</p><p>يرجى التكرّم بسداد المبلغ المتبقي في مكتب الاستقبال عند استلام المخيم، حيث سيتم التسليم بعد استكمال السداد بالكامل. كما نرجو ترتيب ذلك مسبقًا حرصًا على عدم انتظار ضيوفكم الكرام في مكتب الاستقبال لحين حضوركم.</p><p>كما يمكنكم — للتسهيل — سداد المبلغ المتبقي مسبقًا عبر التحويل البنكي الموضح في الملف المرفق.</p><p>نتمنى لكم إقامة ممتعة وتجربة صحراوية لا تُنسى مع ✨🏕️ Funcamp</p><p>مع خالص التحية،</p>',
                'message_en' => '<p>👋 Hello [Customer Name],</p><p>Thank you for choosing Funcamp to spend your time in a unique desert atmosphere.</p><p>We are pleased to inform you that your camp reservation has been confirmed, according to the details shown in the attached PDF file, which includes all reservation information such as reservation date and duration, reservation number, amount paid and remaining, insurance amount, camp location, delivery terms and instructions, and bank transfer details.</p><p>Please kindly pay the remaining amount at the reception desk when receiving the camp, as delivery will be made after full payment is completed. We also request that you arrange this in advance to avoid keeping your guests waiting at the reception until your arrival.</p><p>For your convenience, you can also pay the remaining amount in advance via bank transfer as detailed in the attached file.</p><p>We wish you an enjoyable stay and an unforgettable desert experience with Funcamp ✨🏕️</p><p>Best regards,</p>',
                'is_active' => true,
                'description' => 'رسالة تأكيد الحجز'
            ]
        ];

        foreach ($templates as $template) {
            WhatsappMessageTemplate::updateOrCreate(
                ['type' => $template['type']],
                $template
            );
        }
    }
}