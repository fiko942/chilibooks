# Customer Management Mobile Design

## Goal

Make customer management work cleanly on mobile by separating customer creation into its own page, adding search and pagination to the customer list, and exposing a WhatsApp contact action for valid Indonesian phone numbers.

## Current State

- The customer list page currently mixes a create form and a list in one screen.
- The list has no search or pagination.
- Customer cards only expose `Lihat Detail`.
- The transaction form currently only has a customer dropdown and no shortcut to add a new customer.

## Proposed UX

### 1) Transaction form shortcut

Add a small secondary action near the customer selector on the transaction entry page:

- label: `Tambah pelanggan`
- behavior: open the dedicated customer creation page
- purpose: let users add a customer before returning to transaction entry

This button is secondary to the customer selector and should not visually compete with the main transaction CTA.

### 2) Dedicated customer creation page

Move customer creation out of the list page and into its own page.

The form keeps the existing fields:

- name
- location
- phone

Add two submit actions:

- `Tambah`
  - saves the customer
  - redirects to the customer list
  - shows a success notification on the list page
- `Tambah dan input lagi`
  - saves the customer
  - stays on the create page
  - shows a success notification on the create page

Success feedback should use the same notification style already used elsewhere in the app so it reads as a normal app confirmation, not a modal or toast.

### 3) Customer list layout

Turn the customer list into compact mobile cards with server-side search and pagination.

Each card should show:

- customer name
- location
- phone number if present
- transaction count
- total spent

Each card should also expose two actions:

- `Lihat Detail`
- `Kontak melalui WhatsApp`

The WhatsApp button is only enabled when the customer has a valid Indonesian number:

- must start with `+62`
- must be a valid Indonesian WhatsApp-style phone number
- name must be at least one word with 3 characters minimum

The WhatsApp link should open `wa.me/<number>` with the normalized number.

### 4) Search and pagination

Add a search field above the customer list with server-side filtering.

Recommended search scope:

- customer name
- location
- phone number

Pagination should stay server-side and preserve the active search query in the URL.

## Data and Routing

No schema change is required for the first pass.

Expected code changes:

- customer controller
- customer list view
- customer create view
- transaction form customer area
- CSS for compact cards, search, buttons, and success notices
- routes for the dedicated customer create page if needed

## Error and Empty States

Handle these explicitly:

- empty customer list
- search returns no results
- invalid or missing phone number disables WhatsApp action
- database failure while loading list or create form

## Validation

Confirm all of the following:

- customer list paginates on mobile
- search filters by name, location, and phone
- create form supports both submit modes
- `Tambah pelanggan` exists on the transaction form
- WhatsApp button only appears as actionable when the phone is valid and normalized

