# Achievement of Research Questions and Objectives — Evidence

This section continues *Discussion of Results* and maps each research question and each project objective to concrete evidence in OMCS, with citations to the literature review.

---

## 1. Research Questions — How they were answered

### RQ1. How can a modern digital system with smart features help healthcare workers keep track of patient records and give better care?

OMCS demonstrates this by replacing the paper record with a structured Electronic Health Record. The Doctor module captures every consultation as a `MedicalExamination` linked to `VitalSigns`, `Medicine`, `MedicalTest`, `FinalDiagnosis`, and arbitrary file attachments. The doctor can re-open any previous examination from the patient's history without re-keying data, and the prescription is rendered as a downloadable PDF rather than a handwritten note.

Weiskopf and Weng identified five core dimensions of EHR quality: data quality, completeness, correctness, concordance, and plausibility (Weiskopf & Weng, 2013). OMCS responds to each: vital signs validation enforces in-range numerical values (UT-11, UAT-15); medicines and tests are picked from controlled lists rather than free text (UAT-16, UAT-17); audit logging records every modification (UT-15 to UT-32, UAT-33); and the schema enforces referential integrity at the database layer.

### RQ2. What are the best tools and technology for making a safe and dependable system for storing medical data?

The chosen stack is Laravel 12 on PHP 8.4 with MySQL, Livewire 3, and a modular HMVC architecture via `nwidart/laravel-modules`. This decision is justified by Kruse et al., who report that cost, technical support, and resistance are the dominant barriers to EHR adoption in small clinics (Kruse, Kristof, Jones, Mitchell & Martinez, 2016). Open-source, well-documented technologies remove the cost barrier and lower the maintenance burden.

Reliability is enforced through automated testing (86 tests, 168 assertions), static analysis (PHPStan level 0), and a CI pipeline that blocks any merge with style, type, security, or translation regressions. Backup is automated through CloudPanel, and the Reverb WebSocket service handles real-time chat without polling. The full toolchain is documented in the *System Implementation* section of the dissertation.

### RQ3. How might introducing new services like online booking or file sharing help doctors perform less work every day and make patients' experiences better?

The Booking module turns the appointment workflow into a self-service wizard: select doctor → select slot → confirm → pay → join. The patient avoids a phone call and a clinic visit; the doctor receives a confirmed appointment with the patient already on the calendar. Bashshur et al. concluded that telemedicine reduces unnecessary in-person visits and improves chronic-disease outcomes (Bashshur et al., 2016), and the user survey in this project (n = 21) returned a mean of 4.43 on the statement *"Telemedicine can reduce unnecessary visits"* and 4.57 on *"Clinics benefit from integrated telemedicine features"*, validating the decision to embed video, chat, and records in one screen.

File sharing is implemented through Spatie Media Library, with role-based access enforcement (UAT-32). Patients upload pre-consultation lab results; doctors attach reports to the examination; everything stays inside the clinic-owned platform rather than scattering across email and messaging apps. Liu et al. emphasised that engagement in remote consultation depends on relational design and clear communication, not technology alone (Liu, Brandon-Jones & Vasilakis, 2024) — the booking summary, confirmation notification, and downloadable prescription PDF are the OMCS responses to that finding.

### RQ4. What kind of data protection and backup technique can best guarantee that patient and clinical information always stay safe and accessible?

OMCS aligns with the GDPR principles of lawfulness, purpose limitation, minimisation, and security (gdpr-info, 2025) and with Mourby et al.'s guidance for health data (Mourby et al., 2018). Concretely:

- **Access control:** three Laravel auth guards (doctor, patient, admin / web) plus Spatie Permission roles enforce least privilege (UAT-30, UAT-31, UAT-34).
- **Auditability:** every privileged action is recorded by `AuditLogMiddleware`, including method, route, user, target, and JSON payload (UT-15 to UT-32, UAT-33). Weiskopf et al. list audit logging as a minimum control for EHR systems (Weiskopf, Bakken, Hripcsak & Weng, 2017); OMCS satisfies it by default.
- **Transport security:** Let's Encrypt TLS on the application VHost and on the Jitsi VHost; CSP `frame-ancestors` scoped to the application domain (UAT-12).
- **Storage security:** files are stored outside the public root and served only to authorised sessions (UAT-32).
- **Backup:** CloudPanel runs scheduled MySQL dumps and file backups, addressing the resilience concern raised in *Project Background*.
- **Sovereignty:** the platform is self-hosted on a clinic-controlled VPS, removing the vendor data-control concern that the *Competitor Landscape* identifies for Practo, Doctolib, Teladoc Solo, and Babylon Health.

---

## 2. Objectives — Evidence of Achievement

| # | Objective | Evidence | Status |
|---|-----------|----------|:------:|
| 1 | Analyse existing online medical consultation systems and identify gaps. | *Competitor Landscape* and *Gap Analysis* sections of the dissertation compare Practo, Doctolib, Teladoc Solo, and Babylon Health, identifying clinic ownership, integrated EHR + telemedicine, and small-clinic affordability as the gaps OMCS targets. | Achieved |
| 2 | Collect real user requirements from doctors and patients via a survey. | 21-respondent quantitative survey, results in *Survey Result Summary*; means 4.43–4.86 across the five themes; findings traced into the functional and non-functional requirements lists. | Achieved |
| 3 | Design and build OMCS using a modular Laravel architecture with focus on speed, reliability, and security. | 15 modules under `Modules/`; HMVC structure documented in *SDLC*; performance under 300 ms for Livewire and under 1 s for Reverb chat (UAT-36, UAT-37); 86/86 automated tests passing; PHPStan / Pint / `composer audit` / translations check all green in CI. | Achieved |
| 4 | Add secure file upload, PDF report generation, and a patient portal. | Patient portal at `/patient/*`; secure file upload via Spatie Media Library with permission-checked download (UAT-18, UAT-32); PDF prescription via Spatie Laravel PDF (UAT-19); patient sees previous examinations, prescriptions, and uploads from a single dashboard. | Achieved |
| 5 | Document the system and propose future improvements (AI, mobile, analytics). | Technical documentation in `docs/`, dissertation chapters on *System Design* and *System Implementation*, this evidence document, and *Limitations and Future Scope* (mobile Flutter app, real PSP integration, on-server Whisper transcription, clinic analytics, FHIR interoperability). | Achieved |

---

## 3. Citation Summary

| Citation | Used to support |
|----------|-----------------|
| Weiskopf & Weng, 2013 | RQ1 — EHR data quality dimensions |
| Kruse, Kristof, Jones, Mitchell & Martinez, 2016 | RQ2 — Adoption barriers and stack choice |
| Bashshur et al., 2016 | RQ3 — Clinical effectiveness of telemedicine |
| Liu, Brandon-Jones & Vasilakis, 2024 | RQ3 — Engagement design for remote consultation |
| gdpr-info, 2025 | RQ4 — GDPR principles |
| Mourby et al., 2018 | RQ4 — GDPR for health data |
| Weiskopf, Bakken, Hripcsak & Weng, 2017 | RQ4 — Audit logging as a minimum EHR control |

Full reference entries are listed in the dissertation's *References* section.
