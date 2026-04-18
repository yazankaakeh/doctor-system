<?php

return [
    'module' => 'Rezervasyon',
    'availability' => 'Uygunluk',
    'bookings' => 'Rezervasyonlar',
    'my_bookings' => 'Rezervasyonlarım',
    'book_appointment' => 'Randevu Al',
    'book_appointment_subtitle' => 'Uzman doktorlarımızla randevunuzu planlayın',

    // Availability
    'add_availability' => 'Uygunluk Ekle',
    'edit_availability' => 'Uygunluğu Düzenle',
    'date' => 'Tarih',
    'start_time' => 'Başlangıç Saati',
    'end_time' => 'Bitiş Saati',
    'slot_duration' => 'Slot Süresi (dakika)',
    'consultation_fee' => 'Konsültasyon Ücreti',
    'is_active' => 'Aktif',

    // Booking wizard
    'step_1' => 'Uzmanlık',
    'step_2' => 'Doktor',
    'step_3' => 'Tarih ve Saat',
    'step_4' => 'Onay',
    'step_5' => 'Hesap',
    'select_specialty' => 'Bir Uzmanlık Seçin',
    'choose_medical_specialty' => 'İhtiyacınız olan tıbbi uzmanlığı seçin',
    'select_doctor' => 'Bir Doktor Seçin',
    'choose_preferred_doctor' => 'Tercih ettiğiniz sağlık hizmet sağlayıcısını seçin',
    'select_date' => 'Tarih ve Saat Seçin',
    'choose_date_time' => 'Tercih ettiğiniz randevu saatini seçin',
    'select_time' => 'Bir Saat Seçin',
    'available_dates' => 'Uygun Tarihler',
    'available_slots' => 'Uygun Saatler',
    'available' => 'Uygun',
    'unavailable' => 'Uygun Değil',
    'no_available_slots' => 'Bu doktor için uygun saat yok',
    'no_slots_for_date' => 'Bu tarih için uygun saat yok',
    'select_date_first' => 'Uygun saatleri görmek için bir tarih seçin',
    'no_specialties' => 'Şu anda uygun uzmanlık yok',
    'no_doctors' => 'Bu uzmanlık için doktor yok',
    'review_appointment' => 'Onaylamadan önce randevu detaylarınızı inceleyin',
    'sign_in_to_complete' => 'Rezervasyonunuzu tamamlamak için giriş yapın veya hesap oluşturun',
    'notes' => 'Notlar',
    'notes_placeholder' => 'Doktor için notlar ekleyin (belirtiler, sorular vb.)',
    'optional' => 'isteğe bağlı',
    'booking_summary' => 'Rezervasyon Özeti',
    'continue' => 'Devam',
    'back' => 'Geri',
    'confirm_booking' => 'Rezervasyonu Onayla',
    'processing' => 'İşleniyor...',
    'minutes' => 'dakika',

    // Calendar view
    'table_view' => 'Tablo Görünümü',
    'calendar_view' => 'Takvim Görünümü',
    'today' => 'Bugün',
    'month' => 'Ay',
    'week' => 'Hafta',
    'day' => 'Gün',
    'list' => 'Liste',
    'complete' => 'Tamamla',
    'no_show' => 'Gelmedi',

    // Status labels
    'pending' => 'Beklemede',
    'confirmed' => 'Onaylandı',
    'cancelled' => 'İptal Edildi',
    'completed' => 'Tamamlandı',

    // Auth step
    'login_or_register' => 'Giriş Yap veya Hesap Oluştur',
    'login' => 'Giriş Yap',
    'register' => 'Kayıt Ol',
    'auth_email' => 'E-posta Adresi',
    'auth_email_placeholder' => 'E-postanızı girin',
    'auth_password' => 'Şifre',
    'auth_password_placeholder' => 'Şifrenizi girin',
    'remember_me' => 'Beni hatırla',
    'auth_full_name' => 'Ad Soyad',
    'auth_full_name_placeholder' => 'Adınızı ve soyadınızı girin',
    'auth_phone' => 'Telefon Numarası',
    'auth_phone_placeholder' => 'Telefon numaranızı girin',
    'auth_confirm_password' => 'Şifre Onayı',
    'auth_confirm_password_placeholder' => 'Şifrenizi onaylayın',
    'login_and_book' => 'Giriş Yap ve Rezerve Et',
    'register_and_book' => 'Kayıt Ol ve Rezerve Et',

    // Booking details
    'booking_details' => 'Rezervasyon Detayları',
    'doctor' => 'Doktor',
    'patient' => 'Hasta',
    'booking_date' => 'Randevu Tarihi',
    'time' => 'Saat',
    'duration' => 'Süre',
    'fee' => 'Ücret',
    'status' => 'Durum',
    'meeting_link' => 'Toplantı Linki',
    'join_consultation' => 'Konsültasyona Katıl',
    'no_meeting_scheduled' => 'Henüz toplantı odası planlanmadı',
    'cancel_booking' => 'Rezervasyonu İptal Et',
    'cancellation_reason' => 'İptal Nedeni (isteğe bağlı)',

    // Chat
    'chat_with_doctor' => 'Doktor ile Sohbet',
    'chat_with_patient' => 'Hasta ile Sohbet',
    'conversation_welcome' => 'Dr. :doctor ile :date tarihinde :time saatinde randevu için sohbet başladı. Artık birbirinizle iletişim kurabilirsiniz.',
    'no_messages_yet' => 'Henüz mesaj yok. Sohbeti başlatın!',
    'type_message' => 'Bir mesaj yazın...',
    'chat_not_available' => 'Bu rezervasyon için sohbet mevcut değil.',
    'system' => 'Sistem',

    // Messages
    'availability_created' => 'Uygunluk başarıyla oluşturuldu.',
    'availability_updated' => 'Uygunluk başarıyla güncellendi.',
    'availability_deleted' => 'Uygunluk başarıyla silindi.',
    'booking_created' => 'Rezervasyon başarıyla oluşturuldu. Lütfen ödemeye geçin.',
    'booking_confirmed' => 'Rezervasyonunuz onaylandı.',
    'booking_cancelled' => 'Rezervasyon başarıyla iptal edildi.',
    'booking_completed' => 'Rezervasyon tamamlandı olarak işaretlendi.',
    'marked_no_show' => 'Rezervasyon gelmedi olarak işaretlendi.',
    'slot_not_available' => 'Bu saat artık uygun değil.',
    'cannot_cancel' => 'Bu rezervasyon iptal edilemez.',
    'cannot_complete' => 'Bu rezervasyon tamamlandı olarak işaretlenemez.',
    'cannot_mark_no_show' => 'Bu rezervasyon gelmedi olarak işaretlenemez.',
    'already_processed' => 'Bu rezervasyon zaten işlendi.',

    // Enum
    'enum' => [
        'BookingStatusEnum' => [
            1 => 'Beklemede',
            2 => 'Onaylandı',
            3 => 'İptal Edildi',
            4 => 'Tamamlandı',
            5 => 'Gelmedi',
        ],
    ],

    // Email
    'email' => [
        'greeting' => 'Merhaba :name,',
        'thank_you' => 'Hizmetimizi kullandığınız için teşekkürler!',

        // Booking Created (Patient)
        'booking_created_subject' => 'Randevunuz Planlandı',
        'booking_created_line1' => 'Bizimle randevu aldığınız için teşekkürler. İşte randevu detaylarınız:',
        'booking_created_footer' => 'Randevunuz onaylandığında bir onay e-postası alacaksınız.',

        // Booking Confirmed (Patient)
        'booking_confirmed_subject' => 'Rezervasyonunuz Onaylandı',
        'booking_confirmed_line1' => 'Harika haber! Randevunuz onaylandı.',
        'booking_details' => 'Doktor: :doctor | Tarih: :date | Saat: :time',
        'confirmed_footer' => 'Lütfen planlanan saatte video konsültasyona katılın. Birkaç dakika erken katılmanızı öneririz.',
        'video_consultation' => 'Video Konsültasyon',
        'video_consultation_ready' => 'Video konsültasyon odanız hazır. Planlanan saatte katılmak için aşağıdaki butona tıklayın.',
        'join_consultation' => 'Video Konsültasyona Katıl',

        // Booking Cancelled
        'booking_cancelled_subject' => 'Rezervasyon İptal Edildi',
        'booking_cancelled_line1' => 'Bir rezervasyon iptal edildi.',
        'cancelled_details' => 'Hasta: :patient | Tarih: :date | Saat: :time',
        'cancellation_reason' => 'Neden: :reason',
        'slot_available_again' => 'Bu saat artık diğer hastalar için uygun.',

        // Patient Reminders
        'reminder_24h_subject' => 'Randevu Hatırlatması - Yarın',
        'reminder_24h_line1' => 'Bu, randevunuzun yarın olduğunu hatırlatan dostça bir mesajdır.',
        'reminder_1h_subject' => 'Randevunuz Yakında Başlıyor',
        'reminder_1h_line1' => 'Randevunuz yaklaşık 1 saat içinde başlıyor. Lütfen konsültasyona katılmaya hazır olun.',
        'reminder_join_instruction' => 'Planlanan saatte video konsültasyonunuza katılmak için aşağıdaki butona tıklayın.',
        'reminder_footer' => 'Lütfen stabil bir internet bağlantınız olduğundan ve kamera/mikrofonunuzun düzgün çalıştığından emin olun.',

        // Doctor Reminders
        'doctor_reminder_24h_subject' => 'Randevu Hatırlatması - Yarın',
        'doctor_reminder_24h_line1' => 'Bu, yarın için planlanmış randevunuz hakkında bir hatırlatmadır.',
        'doctor_reminder_1h_subject' => 'Randevu Yakında Başlıyor',
        'doctor_reminder_1h_line1' => 'Randevunuz yaklaşık 1 saat içinde başlıyor. Lütfen konsültasyona hazırlanın.',
        'doctor_reminder_footer' => 'Lütfen hasta detaylarını inceleyin ve konsültasyona hazırlanın.',

        // New Booking (Doctor)
        'new_booking_subject' => 'Yeni Randevu Rezervasyonu Alındı',
        'new_booking_line1' => 'Yeni bir randevu rezervasyonu aldınız. Lütfen aşağıdaki detayları inceleyin:',
        'new_booking_details' => 'Hasta: :patient | Tarih: :date | Saat: :time | Süre: :duration dk',
        'new_booking_footer' => 'Lütfen rezervasyonu inceleyin ve konsültasyona hazırlanın.',

        // Common Labels
        'appointment_details' => 'Randevu Detayları',
        'patient_details' => 'Hasta Bilgileri',
        'doctor_label' => 'Doktor',
        'specialty_label' => 'Uzmanlık',
        'date_label' => 'Tarih',
        'time_label' => 'Saat',
        'duration_label' => 'Süre',
        'fee_label' => 'Konsültasyon Ücreti',
        'status_label' => 'Durum',
        'patient_name_label' => 'Hasta Adı',
        'phone_label' => 'Telefon',
        'email_label' => 'E-posta',
        'your_notes' => 'Notlarınız',
        'patient_notes' => 'Hasta Notları',
        'view_booking' => 'Rezervasyon Detaylarını Görüntüle',
    ],

    // Notifications (Database)
    'notification' => [
        'booking_created' => 'Randevunuz başarıyla planlandı.',
        'booking_confirmed' => 'Rezervasyonunuz onaylandı. Video konsültasyon linki hazır.',
        'booking_cancelled' => 'Rezervasyonunuz iptal edildi.',
        'new_booking' => 'Yeni bir randevu rezervasyonu aldınız.',
        'reminder_24h' => 'Hatırlatma: Randevunuz yarın. Zamanında katılmayı unutmayın!',
        'reminder_1h' => 'Randevunuz 1 saat içinde başlıyor. Lütfen konsültasyona katılmaya hazır olun.',
        'doctor_reminder_24h' => 'Hatırlatma: Yarın için planlanmış bir randevunuz var.',
        'doctor_reminder_1h' => 'Randevunuz 1 saat içinde başlıyor. Lütfen konsültasyona hazırlanın.',
    ],

    // Push notifications (Firebase)
    'push' => [
        // Patient notifications
        'booking_created_title' => 'Randevu Planlandı',
        'booking_created_body' => 'Dr. :doctor ile :date tarihinde :time saatinde randevunuz planlandı.',
        'booking_confirmed_title' => 'Rezervasyon Onaylandı',
        'booking_confirmed_body' => 'Dr. :doctor ile :date tarihinde :time saatindeki randevunuz onaylandı.',

        // Doctor notifications
        'new_booking_title' => 'Yeni Randevu',
        'new_booking_body' => ':patient :date tarihinde :time saatinde randevu aldı.',

        // Patient reminders
        'reminder_24h_title' => 'Yarın Randevunuz Var',
        'reminder_24h_body' => 'Hatırlatma: Dr. :doctor ile randevunuz yarın saat :time.',
        'reminder_1h_title' => '1 Saat İçinde Randevu',
        'reminder_1h_body' => 'Dr. :doctor ile randevunuz 1 saat içinde başlıyor. Hazır olun!',

        // Doctor reminders
        'doctor_reminder_24h_title' => 'Yarın Randevu',
        'doctor_reminder_24h_body' => 'Hatırlatma: :patient ile randevunuz yarın saat :time.',
        'doctor_reminder_1h_title' => '1 Saat İçinde Randevu',
        'doctor_reminder_1h_body' => ':patient ile randevunuz 1 saat içinde başlıyor.',
    ],
];
