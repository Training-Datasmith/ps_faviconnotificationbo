# Architecture: ps_faviconnotificationbo

## Purpose

A PrestaShop back-office module that updates the browser tab favicon to show a badge with
the count of pending orders and messages, providing a visual notification without requiring
the merchant to look at the back-office tabs.

## Directory Structure

```
ps_faviconnotificationbo.php   # Main module class; hooks into BO header
views/                          # JavaScript for favicon badge generation
translations/                   # Translation files
tests/                          # PHPStan and unit tests
```

## Key Design Decisions

Uses the `Favico.js` library (bundled) to dynamically update the favicon with a notification
badge via the Canvas API. Pending counts (orders, messages) are retrieved via AJAX polling
at a configurable interval. The module hooks into `displayBackOfficeHeader` to inject the
required JavaScript.

## Extension Points

Configure polling interval and notification thresholds in module settings.
