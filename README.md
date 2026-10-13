# Titanic Ways local app and Daftra → Fasah Pay bridge

## Local application setup

The dashboard, invoices, clients, staff, roles, and activity log use a local MySQL database. New issued local invoices are queued for direct delivery to Fasah Pay; they do not go through Daftra. The older Daftra bridge API described below remains separate.

1. Create an empty MySQL database named `faseh_local` on localhost (or change `DB_DATABASE` in `.env`). Configure `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` for your local MySQL installation.
2. Run `php artisan migrate --force` and `php artisan db:seed --force`. The seeder creates only the built-in roles and permissions; it does not import preview clients or invoices.
3. Run `php artisan app:create-owner` in a terminal and answer the name, email, and password prompts. No default password is installed.
4. Set `QUEUE_CONNECTION=database` and `FASAHPAY_ENV=sandbox` in `.env`, then run `php artisan queue:work --queue=fasah-local --tries=1` in a separate terminal. Keep this worker running and restart it after changing Fasah settings.
5. Run `php artisan serve --port=8018` and sign in at `/login`.

The Owner can create staff accounts and set their initial passwords. Staff members must change that password at first sign-in. Invoices are SAR-only. A draft stays local. Issuing a local invoice creates a pending Fasah sync record and dispatches a queue job after the database transaction commits. The page displays **synced** only after Fasah accepts the request and returns its invoice number. Definite rejections can be corrected and retried from the invoice page; timeouts, server failures, and accepted responses without a number require manual investigation before any resend. The local invoice remains issued if delivery fails. Payments may be recorded manually on the invoice page. Uploaded files are stored privately and require authorization to download.

Configure the sandbox credentials and `FASAHPAY_BILLER_VAT_NUMBER` below before expecting successful delivery. Set a strong `FASAHPAY_CALLBACK_SECRET` and arrange for Fasah to send it in the `X-Callback-Secret` header so payment and settlement callbacks can update local invoices. A Fasah paid notification records the remaining balance once; repeated notifications do not create another payment. With missing sandbox credentials, the sync fails visibly and no invoice is marked synced. Do not switch to production until a sandbox invoice has been accepted and its callbacks verified.

Run `php artisan test` for the feature suite. The default suite uses an in-memory SQLite database; it can also run against a separate MySQL test database using `DB_CONNECTION=mysql DB_DATABASE=your_test_database php artisan test` after that test database has been created. Never point tests at the application database because the test runner refreshes its schema.

## Daftra → Fasah Pay bridge

Laravel middleware that:

1. Reads an invoice from **Daftra**
2. Creates the same invoice on **Fasah Pay** (SADAD)
3. Receives Fasah **payment / settlement callbacks**
4. Marks the invoice **Paid** back in Daftra

```
Daftra invoice  →  POST /api/fasah/sync/{id}  →  Fasah Pay
Fasah Pay paid  →  callback  →  Daftra payment recorded
```

---

## Quick start

```bash
cd faseh
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
php artisan serve
php artisan queue:work --queue=fasah-local --tries=1   # handles local invoice sync only
```

---

## What to put in `.env`

### A) You already have (Daftra)

| Variable | Where to get it |
|----------|-----------------|
| `DAFTRA_BASE_URL` | `https://YOUR_SUBDOMAIN.daftra.com/api2` |
| `DAFTRA_API_KEY` | Daftra → Settings → API Keys (a named key row, e.g. "Fasah") |
| `DAFTRA_CLIENT_ID` / `DAFTRA_CLIENT_SECRET` | Daftra → Settings → API Keys → "OAuth Credentials" block (account-wide, resettable) |
| `DAFTRA_USERNAME` / `DAFTRA_PASSWORD` | Your Daftra account login — required for the OAuth2 password grant used to get the Bearer token |
| `DAFTRA_PAYMENT_METHOD` | Payment method key in Daftra used when marking paid (e.g. `bank`) |
| `APP_URL` | Your public HTTPS URL (required for callbacks) |

> Daftra's `/api2` endpoints now require **both** an `apikey` header **and** a real OAuth2 `Authorization: Bearer <token>` (obtained via `POST {subdomain}/v2/oauth/token`, password grant). `DaftraAuthService` fetches and caches this token automatically using the OAuth credentials above.

### B) You must get from **Fasah Pay / ELM**

| Variable | What it is | How you get it |
|----------|------------|----------------|
| `FASAHPAY_SANDBOX_CLIENT_ID` | App Client ID | Developer portal → create App |
| `FASAHPAY_SANDBOX_CLIENT_SECRET` | App Client Secret | Same place (**save once**, cannot recover) |
| `FASAHPAY_SANDBOX_USERNAME` | Fasah test user | From Fasah account manager / email |
| `FASAHPAY_SANDBOX_PASSWORD` | Fasah test password | From Fasah account manager / email |
| `FASAHPAY_BILLER_VAT_NUMBER` | Your 15-digit VAT | Your finance team / Fasah onboarding |
| Public IP(s) of your server | For whitelist | You give these **to Fasah** |
| Callback URLs | Your endpoints | You give these **to Fasah**, then run `fasah:subscribe-callbacks` |
| ELM outbound IPs | To whitelist on your firewall | Ask Fasah ops |
| Invoice type + port lookups | Business config | Fasah integration guide / account manager |

After UAT, repeat for production:

- `FASAHPAY_PRODUCTION_CLIENT_ID`
- `FASAHPAY_PRODUCTION_CLIENT_SECRET`
- `FASAHPAY_PRODUCTION_USERNAME`
- `FASAHPAY_PRODUCTION_PASSWORD`
- set `FASAHPAY_ENV=production`

### C) URLs already set in this project

```env
FASAHPAY_INVOICE_CALLBACK_URL="${APP_URL}/api/fasah/callbacks/invoice-notification"
FASAHPAY_SETTLEMENT_CALLBACK_URL="${APP_URL}/api/fasah/callbacks/settlement-notification"
```

Replace `APP_URL` with a real public HTTPS domain before asking Fasah to whitelist.

---

## What to ask Fasah for (checklist email)

Send this to your Fasah Pay account manager:

1. Invite developer email(s) to sandbox portal  
2. Confirm sandbox Client ID / Secret after App + Enterprise Plan approval  
3. Sandbox Fasah user username/password for JWT  
4. Whitelist our public IP(s): `___`  
5. Whitelist our callback URLs:  
   - `https://YOUR_DOMAIN/api/fasah/callbacks/invoice-notification`  
   - `https://YOUR_DOMAIN/api/fasah/callbacks/settlement-notification`  
6. Send ELM public IPs to allow on our firewall  
7. Confirm our invoice category (Bill of Lading / Declaration / Broker / …)  
8. Confirm our biller VAT number onboarding  
9. Port codes / shipment type lookups if not already provided  

Contacts from their docs: `FasahPayOnboarding@elm.sa`, `fasahBO@elm.sa`

---

## Daftra custom fields (required for logistics data)

Create custom fields on Daftra invoices with these **keys** (or change keys in `.env`):

| Key | Used for |
|-----|----------|
| `fasah_invoice_type` | `billoflading` / `declaration` / `custombroker` / `importer` / `shippingagent` |
| `bill_of_lading` | BL number |
| `doc_ref_no` | Option A manifest doc ref |
| `carrier_manifest` + `carrier_manifest_date` | Option B |
| `shipment_type` | Lookup (1=Import, …) |
| `port` | Port code (e.g. `30`) |

Or pass the same fields in the sync API body / artisan options.

---

## API endpoints

| Method | Path | Purpose |
|--------|------|---------|
| `POST` | `/api/fasah/sync/{daftraInvoiceId}` | Push Daftra invoice → Fasah |
| `GET` | `/api/fasah/synced-invoices` | List sync records |
| `GET` | `/api/fasah/synced-invoices/{id}` | One sync record |
| `POST` | `/api/fasah/callbacks/invoice-notification` | Fasah invoice status webhook |
| `POST` | `/api/fasah/callbacks/settlement-notification` | Fasah settlement webhook |

### Sync example

Set a long random `BRIDGE_SYNC_API_TOKEN` in `.env`. The sync and synced-invoice API endpoints require it in the `X-Bridge-Token` header. Without a configured token, these endpoints return 401. Artisan commands do not require this header.

```bash
curl -X POST http://localhost:8000/api/fasah/sync/12345 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Bridge-Token: YOUR_BRIDGE_SYNC_API_TOKEN" \
  -d '{
    "invoice_type": "billoflading",
    "bill_of_lading": "202107071",
    "doc_ref_no": "20210707990965",
    "port": "30",
    "shipment_type": 1
  }'
```

Add `"queue": true` to process via queue worker.

---

## Artisan commands

```bash
# 1) Test Fasah JWT
php artisan fasah:test-auth

# 2) Sync one invoice
php artisan fasah:sync-invoice 12345 \
  --type=billoflading \
  --bl=202107071 \
  --doc-ref=20210707990965 \
  --port=30 \
  --shipment-type=1

# 3) Register callbacks with Fasah (after IP whitelist)
php artisan fasah:subscribe-callbacks
```

---

## Recommended go-live order

1. Fill Daftra `.env` values and test `getInvoice` via sync  
2. Get Fasah invite → create App → subscribe Enterprise Plan  
3. Fill Fasah sandbox credentials + VAT in `.env`  
4. `php artisan fasah:test-auth`  
5. Deploy publicly, give Fasah your IPs + callback URLs  
6. `php artisan fasah:subscribe-callbacks`  
7. Sync a test invoice with `paymentMethod=View` first if needed, then `Sadad`  
8. Pay test bill → confirm callback → confirm Daftra shows Paid  
9. Repeat for production portal  

---

## Notes

- Lost Client Secret cannot be recovered; regenerate in the portal if needed.  
- `FASAHPAY_PAYMENT_METHOD=View` is demo only (no real SADAD payment).  
- Invoice base URL path follows Fasah docs (`.../api/v1.1/FasahPay/invoices`). If portal shows a different path after subscription, update `FASAHPAY_*_INVOICE_BASE_URL`.  
- Protect sync endpoints in production (API token / Sanctum / IP allowlist). Callbacks should stay reachable by Fasah.
