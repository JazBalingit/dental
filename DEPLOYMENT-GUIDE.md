# Deployment Guide — Pus-Pus Britanico Dental Clinic

Gabay para i-host ang app sa **Railway** (app + MySQL) at i-connect ang **.online domain galing Hostinger**, tapos buksan ang **Resend** para sa email.

Sunod-sunod ito. Huwag laktawan ang mga hakbang. Lahat ng nasa `code block` ay kailangang i-type nang eksakto.

---

## 0. Bago mag-umpisa — mga kailangan

- [ ] GitHub account + na-push mo na ang repo na ito sa GitHub (private ok lang)
- [ ] Railway account — mag-sign up sa https://railway.app gamit ang GitHub
- [ ] Hostinger account (dito mo bibilhin ang domain)
- [ ] Resend account — https://resend.com (libre, mag-sign up gamit Gmail mo)
- [ ] Naka-install locally: `git`, `php`, `composer`, `node` (meron ka na)

**Halaga (huwag mabigla):**
- Railway: may kasamang libreng trial credit; pagkatapos ~$5/buwan minimum (Hobby plan). Ang MySQL + app ay parehong kumakain ng resources.
- Hostinger `.online` domain: mura, madalas ₱150–₱400 first year, tumataas sa renewal.
- Resend: libre hanggang 3,000 email/buwan, 100/araw. Sapat na.

---

## 1. Ihanda ang code (gagawin sa local mo)

### 1.1 I-verify na tama ang branch at malinis

```bash
git status
```

Dapat naka-`main` ka at "working tree clean" (o commit mo muna ang pending changes).

### 1.2 Nagawa na para sa'yo (nasa repo na):

- `bootstrap/app.php` — nadagdagan ng `trustProxies(at: '*')`. **Kailangan ito** para sa Railway: kung wala, magiging `http://` (hindi `https://`) ang mga link sa email verification / password reset, at masisira ang ilang redirect.
- `.env` — may naka-comment na Resend-over-SMTP block, handa nang buksan.

### 1.3 Commit at push

```bash
git add -A
git commit -m "Prep for Railway deployment: trust proxies"
git push origin main
```

> **Note:** Ang `.env` ay naka-gitignore, kaya **hindi** ito napupunta sa GitHub. Lahat ng secrets (DB password, mail password, APP_KEY) ay ita-type mo mano-mano sa Railway sa Step 3.

### 1.4 Kunin ang bagong APP_KEY (para sa production)

```bash
php artisan key:generate --show
```

Kokopyahin mo ang buong output, halimbawa `base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=`. **I-save mo muna sa Notepad** — gagamitin sa Step 3. Huwag gamitin ang local APP_KEY mo sa production.

---

## 2. Railway — gumawa ng project at idagdag ang database

1. Pumunta sa https://railway.app → **New Project**
2. Piliin **Deploy from GitHub repo** → hanapin at piliin ang `DentalClinic` repo mo
3. Payagan ang Railway na ma-access ang repo kung hihingin
4. Gagawa si Railway ng isang **service** (ang Laravel app). Sandali lang, magbi-build ito — **mag-e-error muna ito dahil walang database at env vars pa. Normal lang. Ituloy.**

### 2.1 Idagdag ang MySQL

1. Sa loob ng project canvas, i-click **+ New** (o **Create** → **Database**) → **Add MySQL**
2. May lalabas na bagong **MySQL** service. Iiwan mo lang — auto-configured na ang credentials nito.

Dapat 2 na ang service mo ngayon: ang app (pangalan = repo name) at **MySQL**.

---

## 3. Railway — Environment Variables (pinakaimportanteng hakbang)

I-click ang **app service** (hindi ang MySQL) → tab na **Variables** → **+ New Variable** o **Raw Editor**.

Gamitin ang **Raw Editor** at i-paste itong lahat nang sabay, tapos palitan ang mga naka-`<...>`:

```
APP_NAME=Pus-Pus Britanico Dental Clinic
APP_ENV=production
APP_KEY=<i-paste ang base64:... galing Step 1.4>
APP_DEBUG=false
APP_URL=https://<domain-mo>.online
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

SUPERADMIN_EMAIL=<email-mo-para-sa-super-admin>
SUPERADMIN_PASSWORD=<matibay-na-password-palitan-ito>

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=puspusbritanicodentalclinic@gmail.com
MAIL_PASSWORD=oryasswruqnkgykm
MAIL_FROM_ADDRESS=puspusbritanicodentalclinic@gmail.com
MAIL_FROM_NAME=Pus-Pus Britanico Dental Clinic
```

**Mga paalala:**
- `${{MySQL.MYSQLHOST}}` — ito ay **variable reference** ni Railway. Kung ibang pangalan ang MySQL service mo (hindi "MySQL"), palitan ang `MySQL` sa loob ng `${{...}}`. I-type mo talaga nang literal ang `${{MySQL.MYSQLHOST}}`, huwag i-resolve mano-mano.
- **Alternatibo sa 5 DB var:** pwede mo ring gamitin ang isang `DB_URL=${{MySQL.MYSQL_URL}}` sa halip na `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD`. Sinusuportahan ito ng `config/database.php` (`'url' => env('DB_URL')`) at ginagamit nito ang internal networking (walang egress fee). Panatilihin pa rin ang `DB_CONNECTION=mysql`.
- `APP_URL` — kahit wala ka pang domain, ilagay mo muna ang temporary Railway URL (makikita sa Step 5), tapos babaguhin mo sa Step 8 papunta sa `.online` domain.
- `APP_DEBUG=false` — mahalaga sa production. Kung `true`, nakikita ng publiko ang error details at env vars.
- `SUPERADMIN_PASSWORD` — **palitan** `admin123`. Ito ang login sa buong system.
- `MAIL_*` — Gmail SMTP muna (gumagana kahit kanino padala). Lilipat sa Resend sa Step 9.

I-save. Awtomatikong magre-redeploy si Railway.

---

## 4. Railway — Build at Migration settings

I-click ang app service → **Settings**.

### 4.1 Pre-deploy step (para tumakbo ang migrations)

**Settings → Deploy → Add pre-deploy step** → ilagay:

```
php artisan migrate --force
```

Ito lang. Dito tumatakbo ang database migrations bawat deploy (na- save sa MySQL, permanente).

> **Huwag** idagdag dito ang `config:cache` / `route:cache` / `view:cache`. Sa Railway hindi napupunta sa runtime container ang mga cache file na ginawa sa pre-deploy step, at ang codebase na ito ay hindi ligtas i-`config:cache` (may `env()` calls na hindi lang sa config files). Hayaang walang config cache — gumagana pa rin nang tama, konti lang mas mabagal, hindi mapapansin sa clinic-scale traffic.

### 4.1b Custom Start Command — IWANANG BLANGKO

Ang "npm run start" na nakikita sa field ay placeholder lang ni Railway. Iwanang walang laman → gagamitin ang automatic na Nixpacks Laravel start (nginx + php-fpm, root sa `/public`).

### 4.2 Build — dapat auto-detect ni Nixpacks

Kusa nang naiintindihan ni Railway ang Laravel (Nixpacks):
- `composer install` — awtomatiko
- `npm install && npm run build` (Vite / Tailwind) — awtomatiko dahil may `package.json` na may `build` script
- nginx + php-fpm na naka-turo sa `/public` — awtomatiko
- PHP 8.2 — nakukuha sa `composer.json` (`"php": "^8.2"`)

**Kung mag-fail ang build** (halimbawa hindi ma-build ang assets), gumawa ng file na `nixpacks.toml` sa root ng repo:

```toml
providers = ["php", "node"]

[phases.build]
cmds = [
  "composer install --no-dev --optimize-autoloader --no-interaction",
  "npm ci",
  "npm run build",
]
```

Tapos `git add nixpacks.toml && git commit -m "Add nixpacks build config" && git push`.

### 4.3 Health check

Sa **Settings** → **Deploy** → **Healthcheck Path**, ilagay: `/up`
(May built-in health route na ang Laravel 12 dito.)

### 4.4 "Cron schedules are not available for serverless services"

Lalabas ito kung serverless mode ang service (natutulog kapag idle). **Ok lang muna** —
gagana pa rin ang site, mas mura. Epekto lang: hindi tatakbo ang automatic appointment
reminders (tignan 11.2). I-ayos mamaya kapag live na.

---

## 5. Unang matagumpay na deploy

1. Sa app service → tab na **Deployments** → tignan ang pinakabagong build. I-click para makita ang **logs**.
2. Hintayin ang "Build successful" tapos "Deploy successful".
3. Sa **Settings** → **Networking** → **Public Networking** → i-click **Generate Domain**. Bibigyan ka ng URL tulad ng `dentalclinic-production-xxxx.up.railway.app`.
4. **Ibalik sa Variables:** i-update ang `APP_URL` gamit itong Railway URL muna (may `https://`), habang wala pa ang custom domain. I-save (magre-redeploy).
5. Buksan ang URL sa browser.

### Ano ang dapat mong makita
- Bubukas ang landing page ng clinic.
- Pwede kang mag-`/login` at mag-sign in gamit ang `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD`.

### Kung may error (500 / white screen)
- Balik sa **Deployments → View Logs**. Hanapin ang pinakahuling error.
- Karaniwang dahilan:
  - `APP_KEY` mali o walang laman → i-check Step 1.4 / 3
  - DB connection refused → mali ang `${{MySQL.*}}` references, o hindi pa tapos mag-provision ang MySQL (hintayin 1 min, redeploy)
  - "Table not found" → hindi tumakbo ang Pre-Deploy migrate (Step 4.1). Pwede mo ring patakbuhin mano-mano: app service → **⋯ menu** → **Terminal / Shell** → `php artisan migrate --force`

**Huwag munang ituloy hangga't hindi bumubukas ang Railway URL nang maayos.**

---

## 6. Hostinger — bumili ng .online domain

1. Mag-login sa Hostinger → **Domains** → **Get a New Domain** (o gamitin ang hPanel search)
2. I-search ang gusto mong pangalan, hal. `puspusbritanico` → piliin ang `.online` na extension
3. Bilhin (tanggihan ang mga add-on na hindi kailangan — hindi mo kailangan ang paid "domain privacy" pero libre minsan, ok lang; hindi mo kailangan ng hosting/email add-on kasi Railway ang host).
4. Pagkatapos bumili: **Domains** → i-click ang domain → hanapin ang **DNS / Nameservers** section. Siguraduhin nasa **Hostinger nameservers** (default na `ns1.dns-parking.com` / `ns2.dns-parking.com`). Kung ganito, pwede mo nang i-edit ang DNS records sa Hostinger mismo.

---

## 7. I-connect ang domain sa Railway

### 7.1 Sa Railway
1. App service → **Settings** → **Networking** → **Custom Domain** → **+ Custom Domain**
2. I-type ang domain. Dalawang beses mo gagawin, isa-isa:
   - `puspusbritanico.online` (root/apex)
   - `www.puspusbritanico.online`
3. Bawat isa, bibigyan ka ni Railway ng **target value** (isang `CNAME` na parang `xxxx.up.railway.app`). Kopyahin.

> Para sa apex domain (`puspusbritanico.online` na walang `www`), minsan CNAME din ang bibigay ni Railway — ok lang sa Hostinger dahil sinusuportahan nito ang "CNAME flattening" para sa root. Kung ayaw, gamitin na lang ang `www` bilang pangunahin at i-redirect ang apex (Step 7.3).

### 7.2 Sa Hostinger — DNS records
**Domains** → domain mo → **DNS / Name Servers** → **Manage DNS records**.

Idagdag / palitan:

| Type | Name / Host | Value / Points to | TTL |
|------|-------------|-------------------|-----|
| CNAME | `www` | `<value galing Railway para sa www>` | 3600 |
| CNAME | `@` | `<value galing Railway para sa apex>` | 3600 |

- Burahin muna ang existing na `A` record na naka-turo sa Hostinger parking IP para sa `@`, at ang parking `CNAME` para sa `www`.
- Kung ayaw tanggapin ng Hostinger ang `CNAME` sa `@`: gawin ang Step 7.3 sa halip.

### 7.3 Alternatibo kung ayaw ng CNAME sa apex
- Sa Hostinger DNS, mag-set ng CNAME para sa `www` lang (turo sa Railway value).
- Sa Railway custom domain, gamitin `www.puspusbritanico.online` bilang pangunahin.
- Sa Hostinger, hanapin ang **"Domain forwarding" / "Redirect"** feature → i-redirect ang `puspusbritanico.online` → `https://www.puspusbritanico.online` (301).
- I-set ang `APP_URL=https://www.puspusbritanico.online`.

### 7.4 Hintayin ang DNS + SSL
- DNS propagation: 15 min – 2 oras kadalasan (minsan hanggang 24h).
- Sa Railway custom domain page, magiging **"Active"** ang status kapag na-detect na ang DNS at na-issue ang SSL (Let's Encrypt, awtomatiko).
- I-check: buksan `https://puspusbritanico.online` — dapat clinic landing page, may padlock (secure).

---

## 8. I-update ang APP_URL

Railway → app service → **Variables**:

```
APP_URL=https://puspusbritanico.online
```
(o `www.` na version kung yun ang pinili mo sa 7.3)

I-save → magre-redeploy. Ngayon tama na ang lahat ng generated links, kasama ang nasa email.

---

## 9. Resend — buksan ang production email

> Hanggang dito, Gmail SMTP pa rin ang gamit (gumagana naman). Optional ito pero mas propesyonal ang padala galing sa sarili mong domain.

### 9.1 Idagdag ang domain sa Resend
1. https://resend.com → **Domains** → **Add Domain**
2. I-type `puspusbritanico.online` (o subdomain tulad ng `mail.puspusbritanico.online` — mas ok ito para sa deliverability)
3. Bibigyan ka ni Resend ng ~3 DNS records: isang `MX`, at 2–3 `TXT` (SPF + DKIM), minsan may `CNAME` para sa tracking.

### 9.2 Sa Hostinger — idagdag lahat ng records na binigay ni Resend
**Manage DNS records** → idagdag isa-isa, **eksaktong** kopya ng Name at Value galing Resend. Ingat sa:
- Huwag idoble ang domain sa "Name" — kung sinabi ng Resend na `send`, `send` lang (hindi `send.puspusbritanico.online`) dahil kusa nang idinadagdag ng Hostinger ang domain.
- Para sa SPF `TXT`: kung may existing `TXT` na `v=spf1...` ka na (galing dati), pagsamahin, huwag gumawa ng pangalawa.

### 9.3 I-verify
Balik sa Resend → **Domains** → **Verify DNS Records**. Kapag lahat ✅ (15 min – 1 oras), ready na.

### 9.4 Kumuha ng API key
Resend → **API Keys** → **Create API Key** → **Sending access** → kopyahin ang `re_...` (isang beses lang makikita).

### 9.5 Palitan ang MAIL vars sa Railway
Railway → app service → **Variables** → palitan itong 6:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=<ang re_... API key>
MAIL_FROM_ADDRESS=noreply@puspusbritanico.online
MAIL_FROM_NAME=Pus-Pus Britanico Dental Clinic
```

- `MAIL_USERNAME` ay literal na salitang `resend`.
- `MAIL_FROM_ADDRESS` ay **kailangang nasa verified domain**. Kung ang na-verify mo ay `mail.puspusbritanico.online`, gamitin `noreply@mail.puspusbritanico.online`.

I-save → redeploy.

> Walang code change kailangan — pareho lang ang SMTP transport, iba lang ang host/credentials. Ang naka-comment sa local `.env` ay reference lang.

---

## 10. Final checklist bago ituring na "live"

- [ ] `https://puspusbritanico.online` bubukas, secure (padlock)
- [ ] `APP_DEBUG=false` sa Railway
- [ ] `APP_URL` = ang custom domain (may `https://`)
- [ ] Napalitan na ang `SUPERADMIN_PASSWORD` mula sa `admin123`
- [ ] Nakapag-login bilang super admin
- [ ] **Test email:** gumawa ng test na "forgot password" mula sa isang totoong Gmail na hindi mo account — dapat may dumating na OTP
- [ ] **Test booking:** mag-book ng appointment bilang guest / patient — dapat gumana at may email confirmation
- [ ] Nakikita ang mga larawan sa landing page (hero, clinic photos, logo)

---

## 11. Mga dapat malaman (gotchas)

### 11.1 Ephemeral filesystem — mawawala ang bagong upload tuwing redeploy
Ang mga na-upload na larawan (profile picture, at kung papalitan mo ang logo sa Settings) ay n-i-save sa `public/images/` sa loob ng container. **Tuwing magde-deploy ka ulit, babalik sila sa mga default na nasa repo.** Ang mga default na larawan (naka-commit sa Git) ay laging andiyan, kaya hindi masisira ang landing page — pero ang bagong pina-upload ay pansamantala lang.

- Para sa school demo / defense: **ok lang ito**, i-upload mo lang ulit kung kailangan bago mag-present.
- Permanenteng solusyon (mas advanced): mag-add ng Railway **Volume** na naka-mount sa `/app/public/images`, o ilipat ang uploads sa object storage (S3/R2) — kailangan nito ng code change. Sabihan mo ako kung gusto mo itong gawin.

### 11.2 Automatic appointment reminders
May scheduled command (`appointments:send-reminders`) na dapat tumakbo kada minuto. Sa Railway hindi ito kusang tumatakbo. Kung kailangan mo ng automatic reminders:
- Railway → **+ New** → **Cron** (o gumawa ng pangalawang service mula sa parehong repo) na may schedule na `* * * * *` at command na `php artisan schedule:run`
- O isang worker service na may start command na `php artisan schedule:work`
- **Kung hindi mo ito i-setup:** gagana pa rin ang lahat, pero walang awtomatikong "appointment reminder" email/notification. Ang manual na approve/status emails ay gumagana pa rin.

### 11.3 Queue
`QUEUE_CONNECTION=database` pero walang code na naka-queue ng email (sync lahat), kaya **hindi mo kailangan ng queue worker** para sa email. Iwan lang.

### 11.4 Gastos / pag-tulog ng app
Sa Railway Hobby plan, tuloy-tuloy tumatakbo ang app (walang cold start), pero kumakain ng credit habang naka-on. Kung matatapos ang credit, mag-o-offline ang site hanggang mag-top up ka.

### 11.5 Pag-update ng code sa hinaharap
`git push origin main` → awtomatikong magde-deploy si Railway. Tatakbo ang Pre-Deploy migrate. Yun lang.

### 11.6 Timezone
Naka-`Asia/Manila` na ang app (`config/app.php`). Walang kailangang baguhin.

---

## 12. Buod ng daloy

```
Local code ──push──> GitHub ──auto build──> Railway (app + MySQL)
                                                │
                          Railway URL ──test──> gumagana?
                                                │
Hostinger: bumili ng .online ──DNS CNAME──> Railway custom domain ──SSL──> https://puspusbritanico.online
                                                │
                                    Update APP_URL
                                                │
Resend: add domain ──DNS records sa Hostinger──> verify ──> palitan MAIL_* sa Railway
                                                │
                                          Final checks ──> LIVE
```
