# State machine

Order: `awaiting_payment|awaiting_confirmation → confirmed → processing → completed`; `cancelled|exception` adalah cabang operasional.

Payment: `unpaid → initializing → pending|challenge → paid`; failure/expiry/cancel tidak boleh menurunkan paid. Refund memakai `partially_refunded|refunded` dan adjustment.

Fulfillment pickup: `unfulfilled → preparing → ready_for_pickup → picked_up`. Delivery: `unfulfilled → preparing → out_for_delivery → delivered`.

Online hanya diproses setelah paid. COD dikomit saat konfirmasi dan completed mensyaratkan uang diterima. Commit/release idempotent berdasarkan reservation dan ledger effect unik.
