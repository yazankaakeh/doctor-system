<?php

use App\Enum\Gender;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;
use Modules\Doctor\Enums\MedicalExaminationStatusEnum;
use Modules\Doctor\Enums\MedicalTestTypeEnum;

return [
    'enum' => [
        'Gender' => [
            Gender::MALE->value => 'Erkek',
            Gender::FEMALE->value => 'Kadın',
        ],
        'MedicalExaminationStatusEnum' => [
            MedicalExaminationStatusEnum::ACTIVE->value => 'Aktif',
            MedicalExaminationStatusEnum::DONE->value => 'Tamamlandı',
            MedicalExaminationStatusEnum::PENDING->value => 'Beklemede',
            MedicalExaminationStatusEnum::ARCHIVED->value => 'Arşivlendi',
        ],
        'MedicalTestTypeEnum' => [
            MedicalTestTypeEnum::LABORATORY_TESTS->value => 'Laboratuvar Testleri',
            MedicalTestTypeEnum::RADIOLOGY_TESTS->value => 'Radyoloji Testleri',
        ],
        'MaritalStatus' => [
            MaritalStatus::SINGLE->value => 'Bekar',
            MaritalStatus::MARRIED->value => 'Evli',
            MaritalStatus::DIVORCED->value => 'Boşanmış',
        ],
        'BloodType' => [
            BloodType::A_POSITIVE->value => 'A+',
            BloodType::A_NEGATIVE->value => 'A-',
            BloodType::B_POSITIVE->value => 'B+',
            BloodType::B_NEGATIVE->value => 'B-',
            BloodType::AB_POSITIVE->value => 'AB+',
            BloodType::AB_NEGATIVE->value => 'AB-',
            BloodType::O_POSITIVE->value => 'O+',
            BloodType::O_NEGATIVE->value => 'O-',
        ],
    ],
    'profile' => [
        'my_profile' => 'Profilim',
        'personal_info' => 'Kişisel Bilgiler',
        'name' => 'Ad Soyad',
        'email' => 'E-posta Adresi',
        'phone' => 'Telefon Numarası',
        'date_of_birth' => 'Doğum Tarihi',
        'gender' => 'Cinsiyet',
        'specialty' => 'Tıbbi Uzmanlık',
        'bio' => 'Hakkımda',
        'bio_placeholder' => 'Kendinizi, deneyimlerinizi ve uzmanlıklarınızı hastalara anlatın...',
        'avatar' => 'Profil Fotoğrafı',
        'change_avatar' => 'Fotoğrafı Değiştir',
        'avatar_hint' => 'JPG, PNG veya GIF. Maksimum 2MB.',
        'change_password' => 'Şifre Değiştir',
        'password' => 'Şifre',
        'new_password' => 'Yeni Şifre',
        'confirm_password' => 'Şifre Onayı',
        'leave_blank' => 'Mevcut şifreyi korumak için boş bırakın',
        'save_changes' => 'Değişiklikleri Kaydet',
        'profile_updated' => 'Profil başarıyla güncellendi.',
    ],
    'filter' => 'Filtre',
    'close' => 'Kapat',
    'submit' => 'Gönder',
    'save' => 'Kaydet',
    'create' => 'Oluştur',
    'pleaseSelectOne' => 'Lütfen Birini Seçin',
    'viewFile' => 'Dosyayı Görüntüle',
    'vitalSign' => [
        'name' => 'Vital İşaret Adı',
        'createVitalSign' => 'Vital İşaret Ekle',
        'updateVitalSign' => 'Vital İşareti Güncelle',
        'min_value' => 'Minimum Değer',
        'max_value' => 'Maksimum Değer',
        'unit' => 'Birim',
        'unit_placeholder' => 'örn. mmHg, bpm, °C',
        'normal_range' => 'Normal Aralık',
        'out_of_range' => 'Değer normal aralığın dışında',
    ],
    'medicalExaminations' => [
        'title' => 'Tıbbi Muayeneler',
        'history' => 'Önceki muayeneler',
        'print' => 'Yazdır',
        'createTitle' => 'Tıbbi Muayene Oluştur',
        'createAndContinue' => 'Oluştur ve Devam Et',
        'createHint' => 'Seçilen hasta için yeni bir tıbbi muayene oluşturulacaktır (veya mevcut bekleyen muayene tekrar açılacaktır) ve düzenleyiciye yönlendirileceksiniz.',
        'selectPatient' => 'Hasta Seçin',
        'filterTitle' => 'Tıbbi Muayeneleri Filtrele',
        'activeFilters' => 'Aktif filtreler',
        'clearFilters' => 'Filtreleri Temizle',
        'from' => 'Başlangıç',
        'to' => 'Bitiş',
        'empty' => 'Tıbbi muayene bulunamadı.',
        'reasonOfVisiting' => 'Ana Şikayet',
        'createdAt' => 'Oluşturulma Tarihi',
    ],
    'id' => 'ID',
    'edit' => 'Düzenle',
    'show' => 'Görüntüle',
    'patients' => [
        'name' => 'Hasta Adı',
    ],
];
