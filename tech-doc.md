# AI ENGINEERING SPECIFICATION

## Project

Digital Commerce Platform

Fitur:

- Topup Game
- PPOB
- Streaming Subscription
- Reseller API

Framework:

- PHP 8.3
- Yii2 Basic Template
- MongoDB
- Docker
- Nginx

---

# IMPORTANT RULES

AI MUST FOLLOW THESE RULES.

1. Use Domain Driven Design (DDD).
2. Use Repository Pattern.
3. Use Service Layer.
4. Use Dependency Injection.
5. Never call external provider directly from Controller.
6. All business logic must be inside Service Layer.
7. All database access must be through Repository Layer.
8. All supplier integrations must use Provider Pattern.
9. All payment gateways must use Gateway Pattern.
10. Use Queue for external API communication.
11. Use MongoDB ActiveRecord.
12. Use framework cache abstraction.
13. Use SOLID principles.
14. Avoid duplicated code.
15. Code must be production-ready.

---

# PAYMENT GATEWAY

Primary Gateway:

Flip

Secondary Gateway:

Mayar

Never hardcode payment gateway names.

Use abstraction layer.

Interface:

PaymentGatewayInterface

Methods:

- createInvoice()
- getPaymentStatus()
- cancelInvoice()
- handleWebhook()

Implementations:

FlipGateway
MayarGateway

Structure:

gateways/

- PaymentGatewayInterface.php
- FlipGateway.php
- MayarGateway.php

---

# SUPPLIER ARCHITECTURE

Primary Supplier:

VIP Reseller

Future Suppliers:

- Digiflazz
- Mitra PPOB
- Internal Provider

Use Provider Pattern.

Interface:

ProviderInterface

Methods:

- getServices()
- order()
- checkStatus()
- getNickname()
- getStock()

Implementations:

- VipResellerProvider
- DigiflazzProvider
- MitraProvider

Never place supplier-specific logic inside Service Layer.

---

# DOMAIN MODULES

identity
catalog
order
payment
supplier
promotion
reporting
reseller

Each module contains:

- Controllers
- Services
- Repositories
- Models
- DTO
- Validators

---

# ORDER FLOW

User Select Product

↓

Validate Product

↓

Validate Target

↓

Create Order

↓

Create Invoice

↓

Generate Payment (Flip)

↓

Wait Payment

↓

Payment Webhook

↓

Push Queue

↓

Send Order To Supplier

↓

Wait Supplier Callback

↓

Update Status

↓

Send Notification

↓

Complete

---

# PRODUCT SOURCE

Product catalog must not be loaded from supplier in realtime.

Flow:

Supplier API

↓

Sync Service

↓

MongoDB

↓

Frontend

Sync Interval:

Every 6 hours

Admin can:

- Change Product Name
- Change Margin
- Change Category
- Disable Product
- Create Manual Product

---

# PRICING ENGINE

Priority:

Product Margin

>

Brand Margin

>

Category Margin

>

Global Margin

Formula:

sell_price_user =
buy_price + margin_user

sell_price_reseller =
buy_price + margin_reseller

---

# MONGODB COLLECTIONS

users
products
categories
transactions
payments
providers
webhooks
promos
banners
audit_logs

---

# QUEUE JOBS

CreateSupplierOrderJob

CheckPaymentJob

CheckTransactionJob

SyncProductJob

SendWebhookJob

GenerateReportJob

---

# API SECURITY

Use:

- HMAC SHA256
- API Key
- API Secret
- Timestamp
- Nonce

Validate all signatures.

---

# CACHE STRATEGY

Yii cache component

TTL:

Product List = 15 minutes

Category List = 1 hour

Config = 1 hour

---

# WEBHOOK REQUIREMENTS

Every webhook must:

- Verify Signature
- Verify Source IP
- Save Raw Payload
- Save Headers
- Log Result
- Retry Failed Processing
- Use Queue

Never process webhook directly in Controller.

Controller only pushes Queue.

---

# DOCKER SERVICES

nginx
php-fpm
queue-worker
scheduler
mongodb
mongo-express

---

# NON FUNCTIONAL REQUIREMENTS

Availability:
99.9%

Target:
100,000 transactions/day

Response Time:
<300ms

Concurrent Users:
10,000+

---

# CODE GENERATION RULE

Whenever generating code:

1. Generate complete files.
2. Include namespace.
3. Include use statements.
4. Follow PSR-12.
5. Add PHPDoc.
6. Use typed properties.
7. Use constructor injection.
8. Add exception handling.
9. Add logging.
10. Add unit-test-ready structure.
