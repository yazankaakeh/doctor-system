# Local Jitsi Meet for doctor-system

Runs a self-hosted Jitsi Meet on your Windows machine via Docker Desktop, so
the Video Consultation feature works without depending on public `meet.jit.si`
(which requires JWT auth and was producing `conference.connectionError.membersOnly`).

## One-time setup

1. Install **Docker Desktop** and make sure it's running.
2. Install **Git for Windows** if you don't have it.
3. Open PowerShell **in this folder** (`docker\jitsi`) and run:

   ```powershell
   .\setup.ps1
   ```

   The script:
   - Clones `docker-jitsi-meet` into `docker\jitsi\docker-jitsi-meet\`
   - Generates random passwords for Prosody, Jicofo, JVB
   - Configures `PUBLIC_URL=https://localhost:8443`
   - Creates the config volumes under `%USERPROFILE%\.jitsi-meet-cfg\`
   - Runs `docker compose up -d`

4. Open **https://localhost:8443** in your browser and accept the self-signed
   cert warning (click Advanced → Proceed).
5. Add these lines to `C:\laragon\www\laravel\doctor-system\.env`:

   ```env
   VIDEO_PROVIDER=jitsi
   JITSI_DOMAIN=localhost:8443
   JITSI_SELF_HOSTED=true
   JITSI_APP_ID=
   JITSI_SECRET=
   VIDEO_LOBBY_ENABLED=false
   ```

6. Clear config and restart:

   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

7. Reload your Laravel app and click **Join Consultation** — the room will
   now open against your local Jitsi, no `membersOnly` error.

## Starting / stopping Jitsi

```powershell
cd docker\jitsi\docker-jitsi-meet
docker compose up -d     # start
docker compose down      # stop
docker compose logs -f   # watch logs
```

## Updating

```powershell
cd docker\jitsi\docker-jitsi-meet
git pull
docker compose pull
docker compose up -d
```

## Resetting to a clean state

```powershell
cd docker\jitsi\docker-jitsi-meet
docker compose down -v
Remove-Item -Recurse -Force "$env:USERPROFILE\.jitsi-meet-cfg"
.\..\setup.ps1
```

## Troubleshooting

**"Ports 8000/8443/10000 are in use"**
Laragon's Apache may be on 80/443 but Jitsi uses 8000/8443 by default, so
they usually don't clash. If they do, change `HTTP_PORT` / `HTTPS_PORT` in
`docker-jitsi-meet\.env` and update `JITSI_DOMAIN` in the Laravel `.env`.

**Video doesn't connect / "ICE failed"**
This is almost always UDP 10000 being blocked. Check Windows Firewall:

```powershell
New-NetFirewallRule -DisplayName "Jitsi JVB UDP 10000" -Direction Inbound -Protocol UDP -LocalPort 10000 -Action Allow
```

**Browser blocks camera/mic**
Jitsi requires HTTPS. The self-signed cert warning must be accepted *once*
for localhost:8443 before the iframe can request camera permissions.

**Two browsers on same machine can't see each other**
That's a known WebRTC-on-localhost quirk. Test with two different browsers
(Chrome + Firefox) or use two different devices on your LAN pointing at
your machine's LAN IP instead of `localhost`.

## Later: enable JWT authentication (optional but recommended)

When you're ready to lock down moderator privileges to doctors only, edit
`docker-jitsi-meet\.env`:

```env
ENABLE_AUTH=1
AUTH_TYPE=jwt
JWT_APP_ID=medconsult
JWT_APP_SECRET=<generate a 64-char random string>
JWT_ACCEPTED_ISSUERS=medconsult
JWT_ACCEPTED_AUDIENCES=medconsult
```

And in the Laravel `.env`:

```env
JITSI_APP_ID=medconsult
JITSI_SECRET=<same 64-char random string>
```

Then `docker compose down && docker compose up -d`. The existing
`JitsiVideoService::generateToken()` already produces HS256 JWTs that
self-hosted Jitsi accepts — no PHP code changes needed.
