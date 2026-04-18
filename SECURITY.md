# Security Implementation - File Access

This document describes the security measures implemented for protecting sensitive medical files and documents.

## Overview

All sensitive files including medical records, payment proofs, test results, and meeting recordings are stored in a private secure storage location and require authentication to access.

## Secure Storage Configuration

### Storage Disk
- **Disk Name**: `secure`
- **Location**: `storage/app/secure/`
- **Visibility**: `private` (not publicly accessible)
- **Purpose**: Store all medical and sensitive documents

### Models Using Secure Storage

The following models automatically store their media files in secure storage:

1. **Payment** (`Modules\Payment\Models\Payment`)
   - Collection: `payment_proofs`
   - Access: Patient, Doctor (of booking), Admin with "manage payments" permission

2. **Patient** (`Modules\Doctor\Models\Patient`)
   - Collection: `attachments`
   - Access: Patient themselves, Doctors who have examined them

3. **MedicalExamination** (`Modules\Doctor\Models\MedicalExamination`)
   - Collection: `attachments`
   - Access: Doctor who created it, Patient who owns it

4. **MedicalExaminationMedicalTest** (`Modules\Doctor\Models\MedicalExaminationMedicalTest`)
   - Collection: `attachment`
   - Access: Doctor who ordered the test, Patient who owns it

5. **Booking** (for meeting recordings)
   - Access: Doctor, Patient, Admin

## Access Control

### Secure Download Route
- **URL**: `/secure-file/download/{mediaId}`
- **Route Name**: `secure-file.download`
- **Middleware**: `auth:doctor,patient,web`, `setLocale`
- **Controller**: `Modules\Core\App\Http\Controllers\SecureFileController`

### Authorization Logic

The `SecureFileController` implements role-based access control:

- **Payment Proofs**:
  - Patient who made the payment
  - Doctor who received the payment (via booking)
  - Admin with "manage payments" permission

- **Medical Records**:
  - Doctor who created the examination
  - Patient who owns the record

- **Patient Files**:
  - The patient themselves
  - Any doctor who has examined the patient

- **Meeting Recordings**:
  - Doctor who conducted the meeting
  - Patient who attended
  - Any admin user

## Usage in Code

### Storing Files Securely

When adding media to a model, the secure disk is automatically used:

```php
// Payment proofs
$payment->addMedia($file)->toMediaCollection('payment_proofs');

// Medical examination attachments
$examination->addMedia($file)->toMediaCollection('attachments');
```

### Generating Secure URLs

Instead of using `$media->getUrl()`, use the `getSecureMediaUrl()` method:

```php
// In Blade views
@foreach($payment->getMedia('payment_proofs') as $proof)
    <a href="{{ $payment->getSecureMediaUrl($proof) }}">
        {{ $proof->file_name }}
    </a>
@endforeach

// In controllers
$secureUrl = $model->getSecureMediaUrl($mediaItem);
```

## PDF Download Routes

Medical PDFs generated on-the-fly are already protected:

- `/doctor/pdf/downloadMedicines/{id}`
- `/doctor/pdf/downloadMedicalTest/{id}`
- `/doctor/pdf/downloadMedicinesPharmacy/{id}`

These routes require `auth:doctor` middleware and verify the doctor's relationship to the patient.

## Security Best Practices

1. **Never expose direct file paths** - Always use the secure download route
2. **Always verify ownership** - Check user relationship to the file before allowing access
3. **Log access attempts** - Monitor unauthorized access attempts
4. **Use HTTPS** - Ensure all file transfers are encrypted
5. **Set appropriate headers** - Use Content-Disposition inline for viewing, attachment for downloading

## Migrating Existing Files

If you have existing files in public storage that need to be secured:

1. Move files from `storage/app/public/` to `storage/app/secure/`
2. Update database records to use 'secure' disk
3. Update any direct URL references to use `getSecureMediaUrl()`

## Testing Access Control

Test scenarios to verify security:

1. Unauthenticated user accessing file → 403 Forbidden
2. Patient accessing another patient's files → 403 Forbidden
3. Doctor accessing files for their own patients → Success
4. Patient accessing their own files → Success
5. Admin accessing files with proper permissions → Success

## Future Enhancements

- Add file access audit logging
- Implement time-limited download tokens
- Add watermarking for sensitive PDFs
- Implement virus scanning before storage
