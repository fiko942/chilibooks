# Transaction Entry Live Form Design

**Goal:** Replace the fixed two-product transaction form with a dynamic, live transaction entry flow that supports unlimited product lines, a custom date picker, payment methods with conditional proof uploads, and delivery options limited to `diantar` or `diambil di tempat`.

**Context:** The existing transaction controller already stores transactions, items, stock movement, and totals in the live database. The current form is too rigid for multi-item orders and relies on the browser’s default date input. This design keeps the current persistence model but expands the UI so one transaction can contain any number of products and payment metadata without fake defaults or optional hard-coded product slots.

---

## User-Facing Behavior

The transaction entry screen should support:
- a cleaner customer selector
- a custom date picker that opens on click and does not rely on the browser default date UI
- an unlimited list of product lines
- automatic line subtotal and transaction total calculation
- order status
- notes
- payment method
- payment proof upload when required
- delivery type with only `diantar` and `diambil di tempat`

The product entry UI must allow:
- one product line for Product A quantity 1
- another for Product B quantity 2
- another for Product C quantity 3
- any additional lines the user wants to add

No fixed second-product slot may remain. Product lines are additive and removable.

---

## Form Structure

The form should be organized into these sections:

1. **Customer and schedule**
   - customer selector
   - transaction date custom picker
   - delivery type

2. **Products**
   - repeating product lines
   - each line contains:
     - product selector
     - quantity input
     - calculated line subtotal preview
     - remove button
   - an `Tambah Produk` button adds more lines

3. **Payment and order metadata**
   - payment method
   - payment proof upload when required
   - status pesanan
   - notes

4. **Summary**
   - live subtotal
   - discount if present in the existing model
   - extra fee if present in the existing model
   - final total

The UI should remain mobile-first and compact, but the structure must clearly separate input groups so the form is not visually crowded.

---

## Data Rules

### 1) Product lines
Each line maps to one selected product and one quantity.
The controller should accept arrays of product IDs and quantities and ignore blank rows. A valid transaction may contain one or many product lines.

### 2) Totals
Totals must be computed from the selected products and quantities using live product pricing snapshots from the database at the moment of save. The UI may preview line subtotals and a final total before submit, but the server remains the source of truth.

### 3) Payment method
Supported payment methods are:
- `cash`
- `qris`
- `transfer`

If payment method is `qris` or `transfer`, proof upload becomes required.
If payment method is `cash`, proof upload is hidden and not required.

### 4) Delivery type
Supported delivery types are:
- `diantar`
- `diambil di tempat`

There is no `dine in` option.

### 5) Status
The form continues to support order status. The current transaction behavior should remain compatible with the existing `completed` and `canceled` workflow unless the controller logic is explicitly updated to support a finer-grained status set later.

---

## Architecture

Keep the existing controller as the transaction save point, but extend it to accept dynamic line arrays and payment metadata:
- `TransactionController::new()` loads customers and products.
- `TransactionController::create()` validates arbitrary product rows, payment fields, and delivery type.
- `TransactionController::show()` displays the stored metadata and item rows.
- `app/Views/transactions/form.php` becomes the main entry UI for dynamic rows and conditional fields.
- `public/assets/chilibooks.css` carries the layout rules for the grouped mobile form sections and visual states.
- Payment proof files are stored under `writable/uploads/payments/` with the path persisted on the transaction record or a related attachment field.

The form view should be refactored into small visual blocks:
- customer/date/delivery block
- dynamic product-line block
- payment block
- status/notes block
- summary block

The interaction layer should use lightweight client-side JavaScript for:
- adding/removing product rows
- updating line subtotals
- showing/hiding proof upload based on payment method
- opening a custom date-picker popover

The server-side controller must still reject incomplete or invalid rows and should not trust the browser UI for correctness.

---

## Date Picker

The date field should not use the default browser picker UI.

Instead:
- clicking the date field opens a custom mobile-friendly calendar popover
- the user can tap a day to fill the input
- the selected date should remain editable in the form state
- the field should still submit a standard `Y-m-d` value to the server

This keeps the UX consistent across browsers and avoids the default platform picker if it feels inconsistent with the rest of the mobile UI.

---

## Payment Proof

The proof upload should only appear when the payment method is `qris` or `transfer`.

Behavior:
- the upload input is hidden for `cash`
- the upload input becomes visible and required for `qris` and `transfer`
- the server should validate that a file exists when required
- the transaction detail screen should be able to show that proof later, or at minimum preserve the stored file reference for inspection

The upload does not need to be shown for cash because there is no proof requirement in that case.

---

## Error Handling

If the user submits:
- no valid products
- blank product rows only
- invalid quantity values
- an unsupported payment method
- a missing proof file when required
- an unsupported delivery type

the form should return with input preserved and a clear validation message.

The controller should continue to handle database failures gracefully and preserve the current transaction safety behavior with stock movement and rollback-aware persistence.

---

## Testing

Add coverage for:
- dynamic product arrays being accepted by the create flow
- blank product lines being ignored
- payment proof required for `qris` and `transfer`
- payment proof not required for `cash`
- delivery type limited to `diantar` and `diambil di tempat`
- custom date field still producing a valid `Y-m-d` value

If a browser-side interaction test is not practical in the current setup, the implementation should at minimum include controller-level or feature-level tests covering the submitted form payloads and validation outcomes.
