@php
    use Modules\Core\App\Enums\ActiveEnum;
    use Modules\Core\app\Helpers\FileUploadHelper;
    use Modules\Doctor\Models\MedicalExamination;
        $phone   = $patient->phone ?? '';            // رقم المريض
        $digits  = FileUploadHelper::digits_only($phone);              // للتيليغرام (app)
        $waUrl   = FileUploadHelper::wa_link($phone, '90');            // غيّر 90 إلى 964 مثلاً للعراق إذا لازم
        $telUrl  = 'tel:' . $phone;
        $tgApp   = 'tg://resolve?phone=' . $digits;  // يفتح تطبيق تيليغرام مباشرةً
       // $tgWeb   = $patient->telegram_username ?? null; // إذا عندك يوزرنيم بالموديل

@endphp
<div class="card mb-6">
    <div class="card-body pt-12">
        <div class="user-avatar-section">
            <div class=" d-flex align-items-center flex-column">
                <img class="img-fluid rounded mb-4"
                     src="{{$patient->getFirstMediaUrl('images') != null ? $patient->getFirstMediaUrl('images') : asset('assets/img/avatars/3.png')}}"
                     height="120" width="120" alt="User avatar">
                <div class="user-info text-center">
                    <h5>{{$patient->name}}</h5>
                    <span class="badge bg-{{$patient->is_active->class()}}">
                        {{$patient->is_active->label()}}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
            <div class="d-flex align-items-center me-5 gap-4">
                <div class="avatar">
                    <div class="avatar-initial bg-label-primary rounded">
                        <i class="icon-base ti tabler-checkbox icon-lg"></i>
                    </div>
                </div>
                <div>
                    <h5 class="mb-0">{{$patient->medicalExamination->count()}}</h5>
                </div>
            </div>
            <div class="d-flex align-items-center gap-4">
                <div class="avatar">
                    <a target="_blank" href="{{route('doctor.patients.show',['id'=> $patient->id])}}">
                        <div class="avatar-initial bg-label-primary rounded">
                            <i class="icon-base ti tabler-eye icon-lg"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <h5 class="pb-4 border-bottom mb-4">{{trans('doctor::doctor.medicalExaminations.patientDetails')}}</h5>
        <div class="info-container">
            <ul class="list-unstyled mb-6">
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.id')}}:</span>
                    <span>{{$patient->id}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.name')}}:</span>
                    <span>{{$patient->name}}</span>
                </li>
                <li class="mb-2">
                    <span
                        class="h6 text-{{$patient?->gender?->class()}}">{{trans('doctor::doctor.patients.gender')}}:</span>
                    <span class="text-{{$patient?->gender?->class()}}">{{$patient?->gender?->label()}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.age')}}:</span>
                    <span>{{$patient->age}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.nationality_id')}}:</span>
                    <span>{{$patient->nationality->name}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.email')}}:</span>
                    <a href="mailto:{{$patient->email}}">{{$patient->email}}</a>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.phone')}}:</span>
                    <span>
                                            <div class="btn-group">
                                                <button type="button"
                                                        class="btn btn-outline-primary dropdown-toggle waves-effect"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">{{$patient->phone}}</button>
                                                <ul class="dropdown-menu" style="">
                                                    <li>
                                                        <a class="dropdown-item waves-effect"
                                                           href="{{$waUrl}}">
                                                            <i class="ti icon-base tabler-brand-whatsapp"></i>
                                                            WhatsApp
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item waves-effect"
                                                           href="{{$tgApp}}">
                                                            <i class="ti icon-base tabler-brand-telegram"></i>
                                                            Telegram
                                                        </a>
                                                    </li>
                                                     <li>
                                                        <a class="dropdown-item waves-effect"
                                                           href="tel:{{$patient->phone}}">
                                                            <i class="ti icon-base tabler-phone"></i>
                                                            Phone
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            </span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.children')}}:</span>
                    <span>{{$patient->children}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.work')}}:</span>
                    <span>{{$patient->work}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6 text-{{$patient->blood_type->class()}}">{{trans('doctor::doctor.patients.blood_type')}}:</span>
                    <span class="text-{{$patient->blood_type->class()}}">{{$patient->blood_type->label()}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6 text-{{$patient->marital_status->class()}}">{{trans('doctor::doctor.patients.marital_status')}}:</span>
                    <span
                        class="text-{{$patient->marital_status->class()}}">{{$patient->marital_status->label()}}</span>
                </li>

                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.drug_allergies')}}:</span>
                    <span>{{$patient->drug_allergies}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.disabilities')}}:</span>
                    <span>{{$patient->disabilities}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.medical_history')}}:</span>
                    <span>{{$patient->medical_history}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.surgical_history')}}:</span>
                    <span>{{$patient->surgical_history}}</span>
                </li>
                <li class="mb-2">
                    <span class="h6">{{trans('doctor::doctor.patients.accident_history')}}:</span>
                    <span>{{$patient->accident_history}}</span>
                </li>
            </ul>
            <div class="d-flex justify-content-center">
                <a href="javascript:" class="btn btn-primary me-4 waves-effect waves-light"
                   data-bs-target="#editUser"
                   data-bs-toggle="modal">{{trans('doctor::doctor.edit')}}</a>
                <a href="javascript:"
                   class="btn btn-label-danger suspend-user waves-effect">{{trans('doctor::doctor.deactivate')}}</a>
            </div>
        </div>
    </div>
</div>
