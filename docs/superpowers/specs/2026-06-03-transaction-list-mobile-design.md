# Transaction List Mobile Design

## Goal

Make the transaction list usable on mobile without turning it into a wide table.
The page should show one transaction per compact card, support server-side search plus pagination, and include a visible **Kirim Invoice** button that is intentionally inert for now.

## Current State

- The list page renders all transactions in a simple vertical stack of cards.
- There is no search field.
- There is no pagination.
- Each card currently exposes only the `Detail` action.

## Proposed UX

### 1) Mobile-first card layout

Use one card per transaction with the smallest useful set of fields:

- invoice number
- customer name
- transaction date
- total
- status

The card should keep the visual hierarchy simple:

- invoice as the primary label
- status as a small badge
- monetary values emphasized
- secondary metadata kept compact

This replaces any table-like presentation on mobile. The goal is to fit more than 1-2 items per screen without losing readability.

### 2) Search and pagination

Add a search input above the list. Search should be server-side and should survive pagination.

Planned behavior:

- default sort remains newest first
- search filters the current result set
- pagination is server-side, not infinite scroll
- the active search query stays in the URL when changing pages
- empty search result shows a clean “no data found” state

Recommended search scope:

- invoice number
- customer name

This keeps the query predictable and fast enough for the current transaction list size.

### 3) Actions per transaction

Each card should expose two actions:

- `Detail` as the primary action
- `Kirim Invoice` as a secondary action

`Kirim Invoice` must be present in the UI but have no functional backend behavior yet.
It should not navigate anywhere, should not submit anything, and should wait for the next instruction.

## Data and Routing

No database schema changes are required for this task.

Expected code changes are limited to:

- the transaction list controller/query
- the transaction list view
- pagination-aware URL handling
- styling for compact mobile cards and actions

## Error and Empty States

Handle these states explicitly:

- no transactions at all
- search returns no results
- database lookup fails while loading the list

The page should degrade gracefully with a readable message instead of a broken layout.

## Validation

Confirm all of the following after implementation:

- search filtering works
- pagination keeps the search query
- the card layout stays compact on mobile
- `Kirim Invoice` appears but does nothing yet
- the page still renders correctly when there are no rows

