# PEMBUATAN WEB TOPUP GAME, PPOB, DAN STREAMING MENGGUNAKAN YII2 + MONGODB

Bertindaklah sebagai Senior Software Architect, Senior Yii2 Developer, MongoDB Expert, Payment Gateway Specialist, dan DevOps Engineer.

Saya ingin membuat platform topup game, PPOB, dan produk digital seperti Codashop, UniPin, Lapakgaming, dan Mitra PPOB.

## Teknologi

- PHP 8.3
- Yii2 Basic Template
- MongoDB
- Queue System (Yii Queue)
- REST API
- Bootstrap 5

Server:

- Nginx
- Docker
- Supervisor
- Cronjob

Payment Gateway:

- Flip (Primary)
- Tripay (Secondary/Fallback)

Supplier:

- VIP-Reseller (Primary)
- Supplier lain di masa depan

---

# ARSITEKTUR

Buat sistem menggunakan konsep Provider Layer agar mudah menambah supplier baru.

Contoh:

ProviderInterface

- getServices()
- order()
- checkStatus()
- getNickname()
- getStock()

Implementasi:

VipResellerProvider
DigiflazzProvider
MitraProvider
CustomProvider

Jangan hardcode VIP Reseller pada business logic.

Semua transaksi harus menggunakan abstraction layer.

---

# ROLE USER

Terdapat 3 level:

## USER

Fitur:

- Registrasi
- Login
- Topup game
- PPOB
- Streaming
- Deposit saldo
- Riwayat transaksi
- Invoice
- Voucher promo

## RESELLER

Semua fitur User +

- Harga reseller
- Deposit saldo
- API reseller
- White label API
- Margin khusus
- Generate API Key
- Webhook URL

## ADMIN

Full Access

- Kelola user
- Kelola reseller
- Kelola transaksi
- Kelola produk
- Kelola supplier
- Kelola payment gateway
- Kelola banner
- Kelola kategori
- Kelola promo
- Kelola komisi
- Kelola webhook
- Kelola laporan

---

# DATABASE MONGODB

Buat collection:

users

{
\_id,
username,
email,
password_hash,
role,
balance,
status,
created_at
}

products

{
\_id,
provider,
provider_code,
category,
brand,
product_name,
description,
base_price,
reseller_price,
user_price,
stock,
status,
config,
updated_at
}

categories

{
\_id,
name,
slug,
image,
status
}

transactions

{
\_id,
invoice_number,
trxid_provider,
user_id,
product_id,
target,
zone,
nickname,
provider,
payment_method,
payment_gateway,
buy_price,
sell_price,
profit,
status,
notes,
created_at
}

payments

{
\_id,
invoice_number,
amount,
gateway,
gateway_reference,
status,
paid_at
}

providers

{
\_id,
name,
type,
api_url,
api_key,
api_secret,
status
}

webhooks

{
\_id,
provider,
payload,
created_at
}

promos

{
\_id,
code,
type,
value,
start_date,
end_date
}

banners

{
\_id,
title,
image,
link
}

---

# PRODUK MANAGEMENT

Produk TIDAK diambil realtime saat user membuka website.

Buat mekanisme:

1. Ambil semua layanan dari VIP Reseller.
2. Simpan ke MongoDB.
3. Admin dapat:
    - Edit nama produk
    - Edit harga
    - Menonaktifkan produk
    - Menambah produk manual

4. Website membaca produk dari MongoDB.
5. Sinkronisasi supplier dilakukan setiap 6 jam melalui Cronjob.

Cron:

0 _/6 _ \* \*

Flow:

VIP API
↓
Sync Service
↓
MongoDB

---

# HARGA PRODUK

Setiap produk memiliki:

buy_price
margin_user
margin_reseller
sell_price_user
sell_price_reseller

Formula:

sell_price_user =
buy_price + margin_user

sell_price_reseller =
buy_price + margin_reseller

Admin dapat menentukan:

- Margin global
- Margin kategori
- Margin brand
- Margin produk

Prioritas:

Produk
→ Brand
→ Kategori
→ Global

---

# TOPUP GAME

Support:

- Mobile Legends
- Free Fire
- PUBG
- Honor Of Kings
- Valorant
- Steam Wallet
- Roblox
- Genshin Impact

Fitur:

- Validasi Nickname
- Validasi Zone
- Auto Detect Nickname
- Riwayat Topup

Gunakan endpoint:

get-nickname

dari VIP Reseller.

---

# PPOB

Support:

- Pulsa
- Paket Data
- Token PLN
- PLN Pascabayar
- BPJS
- PDAM
- E-Wallet
- Telkom

Gunakan endpoint prepaid.

---

# STREAMING

Support:

- Netflix
- Spotify
- YouTube Premium
- CapCut Pro
- Canva Pro
- Viu
- Vidio

Gunakan endpoint game-feature.

---

# PAYMENT GATEWAY

Integrasi:

## Flip

- Virtual Account
- QRIS
- Ewallet
- Retail Outlet

## Tripay

Sebagai alternatif.

Buat interface:

PaymentGatewayInterface

Implementasi:

XenditGateway
TripayGateway

Fitur:

createInvoice()

checkPayment()

cancelPayment()

handleWebhook()

---

# ORDER FLOW

User pilih produk

↓

Input target

↓

Validasi nickname

↓

Buat invoice

↓

Bayar Flip

↓

Webhook Payment

↓

Order ke Supplier

↓

Simpan trxid supplier

↓

Status waiting

↓

Webhook Supplier

↓

Update status

↓

Success/Error

↓

Notifikasi User

---

# WEBHOOK

Supplier:

VIP Reseller
IP Whitelist:
178.248.73.218

Validasi:

md5(API_ID + API_KEY)

Payment:

Flip

Tripay

Semua webhook wajib:

- Signature Verification
- Logging
- Retry Mechanism
- Queue Processing

---

# CRONJOB

1. Sync Product

setiap 6 jam

2. Check Pending Payment

setiap 5 menit

3. Check Pending Transaction

setiap 3 menit

4. Cleanup Expired Invoice

setiap 1 jam

5. Generate Report

setiap hari

---

# ADMIN DASHBOARD

Tampilkan:

- Total User
- Total Reseller
- Total Produk
- Total Transaksi
- Revenue Hari Ini
- Profit Hari Ini
- Produk Terlaris
- Game Terlaris
- Supplier Performance
- Payment Gateway Performance

---

# API RESELLER

Buat REST API:

POST /api/order

POST /api/status

GET /api/products

GET /api/balance

POST /api/deposit

Menggunakan:

API KEY
API SECRET
HMAC Signature

---

# KEAMANAN

- Rate Limiter
- Google reCAPTCHA
- CSRF Protection
- XSS Filter
- MongoDB Injection Protection
- API Signature Validation
- Audit Log

---

# PERFORMANCE

- Lazy Loading
- Pagination
- MongoDB Indexing
- Product Cache 15 Menit

---

# OUTPUT YANG SAYA INGINKAN

1. Struktur Folder Yii2 Basic.
2. ERD MongoDB Collections.
3. Service Layer Architecture.
4. Repository Pattern.
5. Provider Pattern.
6. Payment Gateway Pattern.
7. Detail Database Schema.
8. Detail API Design.
9. Cronjob Design.
10. Sequence Diagram.
11. Deployment Docker.
12. Docker Compose.
13. Contoh Source Code Yii2.
14. Contoh Integrasi VIP Reseller.
15. Contoh Integrasi Flip.
16. Contoh Integrasi Tripay.
17. Best Practice Production Ready.
18. High Availability Architecture.
19. Multi Supplier Design.
