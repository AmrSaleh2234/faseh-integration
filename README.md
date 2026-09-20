# Daftra → Fasah Pay Bridge (Laravel)

Laravel middleware that:

1. Reads an invoice from **Daftra**
2. Creates the same invoice on **Fasah Pay** (SADAD)
3. Receives Fasah **payment / settlement callbacks**
4. Marks the invoice **Paid** back in Daftra

```
Daftra invoice  →  POST /api/fasah/sync/{id}  →  Fasah Pay
Fasah Pay paid  →  callback  →  Daftra payment recorded
```

Project path: `daftra-fasah-bridge/`

---

## Quick start

```bash
cd daftra-fasah-bridge
cp .env.example .env   # if needed
php artisan key:generate
php artisan migrate
php artisan serve
php artisan queue:work   # if you use ?queue=1
```

---

## What to put in `.env`

### A) You already have (Daftra)

| Variable | Where to get it |
|----------|-----------------|
| `DAFTRA_BASE_URL` | `https://YOUR_SUBDOMAIN.daftra.com/api2` |
| `DAFTRA_API_KEY` | Daftra → Settings → API Keys |
| `DAFTRA_PAYMENT_METHOD` | Payment method key in Daftra used when marking paid (e.g. `bank`) |
| `APP_URL` | Your public HTTPS URL (required for callbacks) |

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

```bash
curl -X POST http://localhost:8000/api/fasah/sync/12345 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
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
