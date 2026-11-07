# Model data

```mermaid
erDiagram
  USERS ||--o{ ADDRESSES : has
  USERS ||--|| CARTS : owns
  CARTS ||--o{ CART_ITEMS : contains
  CATEGORIES ||--o{ PRODUCTS : groups
  PRODUCTS ||--o{ PRODUCT_IMAGES : has
  USERS ||--o{ ORDERS : places
  ORDERS ||--o{ ORDER_ITEMS : snapshots
  ORDERS ||--o{ PAYMENTS : attempts
  PAYMENTS ||--o{ PAYMENT_NOTIFICATIONS : receives
  PAYMENTS ||--o{ PAYMENT_ADJUSTMENTS : adjusts
  ORDERS ||--o{ STOCK_RESERVATIONS : reserves
  PRODUCTS ||--o{ STOCK_MOVEMENTS : ledger
  ORDERS ||--o{ ORDER_STATUS_HISTORIES : traces
```

SKU, order number, provider order ID, dan event key unik. Unique gabungan melindungi cart item, idempotency customer, reservation, attempt, dan efek ledger. MySQL CHECK menjaga reserved ≤ on hand dan quantity positif.
