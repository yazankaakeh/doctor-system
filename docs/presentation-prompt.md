# Prompt — OMCS Final Presentation (SWE6010)

Use the prompt below with any LLM (ChatGPT, Claude, Gemini) to generate a 13-slide
final-year presentation for the Online Medical Consultation System (OMCS) project.
It bakes in the facts from the dissertation so the output stays specific and accurate.

---

## PROMPT (copy everything between the lines)

You are helping me build a 13-slide final presentation for my university project. Produce a slide deck in Markdown where each slide is introduced with a level-2 heading like `## Slide N — Title`, followed by the bullet points or paragraph content for that slide, followed by one short line starting with `Speaker notes:` that I can read aloud. Keep slide text minimal (3–6 bullets per slide, each under 12 words). Put the detailed explanation in the speaker notes. Do not use emojis. Use British English.

Use only the project facts provided below — do not invent features, numbers, or citations.

### Project facts

- Project title: **Online Medical Consultation System (OMCS)**
- Student: **Yazan Kaakeh**
- Student ID: **2524815**
- Module: **SWE6010**
- Supervisor line: leave as `Supervisor: [your supervisor]` (I will fill it in)
- Target users: doctors, patients, clinic administrators; designed for small and medium-sized clinics in Turkey.
- Purpose: a single web platform that replaces paper records with a centralised Electronic Health Record, online appointment booking, video consultation, payment, and multilingual patient portal.

### Problem being solved
Paper-based records cause slow retrieval, data loss, illegibility, inconsistent copies across departments, and poor inter-departmental communication. Local-only storage is vulnerable to hardware failure and cyberattacks. Small and medium clinics cannot afford large enterprise EHR platforms like Teladoc or Doctolib.

### Aim
Build a professional, secure, and easy-to-use web platform for Turkish clinics that centralises patient registration, appointments, prescriptions, test results, and video consultation in one GDPR- and KVKK-compliant system.

### Objectives (use these five)
1. Analyse existing online medical consultation systems and identify gaps.
2. Collect real user requirements from doctors and patients through a survey.
3. Design and build OMCS using a modular Laravel architecture with focus on speed, reliability, and security.
4. Add secure file upload, PDF report generation, and a patient portal.
5. Document the system and propose future improvements (AI, mobile, analytics).

### Methodology
- Mixed-methods research: secondary literature synthesis + comparative system analysis + quantitative survey (21 respondents, Likert 1–5).
- SDLC model: **Agile Scrum**, single long sprint managed in **Jira** from November 2025 to April 2026.
- Reason for Agile: continuous delivery, ability to adapt scope, and suitability for a one-person team iterating alongside feedback.

### Literature review — key findings (5 themes)
1. EHR adoption — Weiskopf & Weng on data quality and completeness; Kruse et al. on adoption barriers (cost, training, resistance).
2. Telemedicine — Bashshur et al. show positive clinical outcomes for chronic disease management; WHO digital-health strategy confirms telemedicine as core infrastructure post-COVID.
3. Security and GDPR — Regulation 2016/679, role-based access, encryption in transit, audit logs (Weiskopf et al. 2017; Mourby et al. 2018).
4. Patient engagement — Barello et al. and Irizarry show portals improve communication when usability is prioritised.
5. Usability and architecture — Ahern et al. and Olakotan 2025 link poor EHR UI/UX to clinician burnout.

Technologies observed in similar systems: Practo (SaaS marketplace), Doctolib (SaaS, strong GDPR focus), Teladoc Solo (enterprise, FHIR/HL7), Babylon Health (AI triage). OMCS differentiates by being self-hosted, clinic-owned, modular, and Arabic-RTL ready.

### System design artefacts (one per slide, under System Design)
- System Architecture diagram (presentation / application / data / integrations layers).
- DFD Level 0 — OMCS as a single process with patient, doctor, admin, payment gateway actors.
- DFD Level 1 — decomposed into user management, appointment, medical preview, and payment processes.
- UML Use Case — 4 actors, main use cases for booking, consultation, prescription, payment.
- UML Class Diagram — Admin → Doctor/Patient; Appointment links Patient and Doctor; Medical Preview generates Medicines and Medical Tests; Payment linked to Appointment; Media polymorphic.
- ERD — full database schema.

Mention that each diagram appears on its own slide; embed the actual image if I paste one in, otherwise leave a placeholder: `[Insert Figure X here]`.

### Implementation stack
- Backend: PHP 8.4, Laravel 12
- Frontend: Livewire 3, Bootstrap 5, Alpine.js, Vite, SCSS
- Database: MySQL (with Redis for caching)
- Architecture: HMVC modular (nwidart/laravel-modules) across 15 modules — Core, Auth, Doctor, Patient, Booking, Payment, Messaging, Notification, AdminManagement, Blog, CMS, SEO, Theme, Website, MCP
- Video: self-hosted **Jitsi Meet** at `meet.yupcrm.com` (Docker Compose + nginx reverse proxy + Let's Encrypt + CSP `frame-ancestors`)
- Real time: Laravel Reverb WebSockets
- Auth: three guards (doctor, patient, web) + social login (Google, Facebook, X) via Laravel Socialite
- Permissions: Spatie Permission, Spatie Translatable, Spatie Media Library
- Payments: PayPal, offline bank transfer with proof upload, and a Luhn-validated credit-card prototype
- CI/CD: GitHub Actions running Laravel Pint, PHPStan (Larastan), `composer audit`, and a custom `translations:integrity-check` artisan command
- Dev: Laragon (local), PHP Storm, CloudPanel + Termius (VPS deploy)
- Project management: Jira (Agile Scrum)

### Testing and results (summary — keep this short on the slide)
- Automated tests split into **Unit** and **Feature** suites covering enums, vital-signs range checks, the Luhn validator rule, translations-integrity logic, composer CI script, and regression tests for fixed bugs (audit-log polymorphic accessor).
- Tests cover both functional requirements (booking, payment, consultation, medical records) and non-functional ones (security rules, input validation, multi-guard auth).
- Some tests initially failed (multi-guard binding, NotificationFake dispatcher, admins.phone NOT NULL, permissions.section NOT NULL, auth-guard `[admin]` undefined). These were all debugged and fixed, and the full suite now passes.
- User feedback: 21-respondent survey — 4.86 mean for data privacy importance, 4.81 for GDPR compliance expectation, 4.76 for role-based access, 4.67 for modular systems, 4.62 for EHR improving accuracy. Strong overall validation of the design.

### Challenges (pick the three most substantial)
1. **Scope of modular architecture** — 15 modules with many cross-references; solved by class_exists-guarded optional modules and shared traits (e.g. HasVideoConsultation in Core).
2. **Infrastructure** — self-hosting Jitsi (Docker Compose, firewall UDP 10000, nginx reverse proxy, CSP frame-ancestors replacing X-Frame-Options: SAMEORIGIN).
3. **Code quality at scale alone** — Laravel Pint + PHPStan in CI catch style and type issues; judgement calls on controller-vs-action boundaries had to be made without a senior developer.

### Reflection (use these points)
- Learned modular Laravel at production scale, Livewire 3, Reverb WebSockets, multi-guard auth, Spatie Permission + Translatable + Media Library.
- Learned DevOps skills: Docker Compose (Jitsi), nginx reverse proxy, Let's Encrypt, CloudPanel, GitHub Actions CI.
- Learned to own a project end-to-end — database schema to live VPS — and to debug across the full stack (including a 2 a.m. nginx fix for the iframe issue).
- Learned how to keep UI calm and usable in a complex clinical screen, and how to support Arabic RTL layout properly rather than as a string-translation afterthought.

### Conclusion and future work
- All main objectives achieved; system is deployed and end-to-end functional.
- Core flow works: patient finds doctor → books → pays → joins video → receives PDF prescription → keeps history.
- Not a finished product — credit-card prototype needs a real PSP (Stripe/Iyzico) integration; no mobile app yet; no AI transcription; audit-log view is basic.

Future enhancements:
1. Flutter mobile app for patients (push notifications, background call reminders, photo-upload of test results).
2. Real payment integration (Iyzico / Stripe) with tokenised returning-patient cards.
3. AI transcription of consultations (on-server Whisper) to auto-populate prescriptions.
4. Clinic analytics dashboard (consultation length, no-show rate, revenue per specialty).
5. Insurance-claim integration.
6. FHIR-based interoperability for connecting to national health systems.

### Slide structure — follow this exactly

1. **Title Page** — Project Title, Student Name, Student ID, Module Code (SWE6010), supervisor placeholder, and year (2026).
2. **Project Overview** — one short description, target users, purpose.
3. **Problem Statement** — the problem, why it matters (patient safety, efficiency, data sovereignty).
4. **Aim and Objectives** — aim sentence plus the five objectives above.
5. **Methodology** — research approach, SDLC (Agile Scrum via Jira), reason for choosing it.
6. **Literature Review** — the five themes and the comparator platforms (Practo, Doctolib, Teladoc, Babylon).
7. **System Design — Overview** — system architecture layered diagram.
8. **System Design — DFD 0 and DFD 1** — put both on one slide (or split into two if easier).
9. **System Design — UML Use Case** — actors and main use cases.
10. **System Design — UML Class + ERD** — on one slide each (so 2 slides if needed).

    > *Note: the student brief groups all design diagrams under "Slide 7" with each on its own slide. Expand slide 7 into one overview slide plus one slide per diagram (System Architecture, DFD 0, DFD 1, UML Use Case, UML Class, ERD). Renumber subsequent slides accordingly.*

11. **System Implementation** — languages, frameworks, database, module list, Jitsi self-hosting, CI pipeline.
12. **Testing and Results** — testing methods, short test-case summary (functional + non-functional, some fixed failures), user-feedback survey headlines.
13. **Challenges** — the three challenges above and how each was solved.
14. **Reflection** — skills learned, knowledge gained, experience.
15. **Conclusion and Future Enhancements** — outcome summary, objectives-achieved tick list, future features.
16. **System Demo** — single slide with the words "System Demo" centred; I will start the live demo after this slide.

### Formatting and tone
- Academic but clear; written as if spoken by the student.
- No marketing buzzwords. No emojis.
- Every technical claim must trace back to the facts above — do not add features that are not listed.
- For literature references, use the short author-year form (e.g. Weiskopf & Weng, 2013) — do not fabricate sources.
- Where a diagram is needed, output `[Insert Figure X here — System Architecture]` so I can drop the image in afterwards.

Produce the full Markdown deck now.

---

## How to use this prompt

1. Copy everything between the horizontal lines above.
2. Paste it into a new chat with your LLM of choice.
3. Take the Markdown output and either (a) paste it into a PowerPoint/Keynote outline view, or (b) ask the same LLM to convert it into `.pptx` content slide by slide.
4. Replace every `[Insert Figure X here]` placeholder with the actual figure from your dissertation document (Figures 1–13 for diagrams, 14–52 for screenshots).
5. Fill in the supervisor name on slide 1.
