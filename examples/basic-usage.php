<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_faviconnotificationbo PrestaShop module.
 *
 * ps_faviconnotificationbo displays a notification badge on the Back Office
 * browser favicon tab showing the count of pending orders and other alerts.
 * The badge updates dynamically via polling without a full page refresh.
 *
 * This file documents common usage patterns.
 */

// --- The module operates exclusively in the Back Office ---
// It hooks into Back Office pages to inject the favicon notification JS.
// No storefront or configuration needed for standard use.

// --- Hook: displayBackOfficeTop ---
// The module injects JavaScript that:
//   1. Polls the Back Office API for pending order counts
//   2. Dynamically renders a canvas-based badge over the favicon
//   3. Updates the browser tab title with the pending count
//
// This happens automatically once the module is installed and enabled.

// --- What counts are displayed? ---
// By default, the badge shows:
//   - Number of orders in "Awaiting payment" or "Payment accepted" status
//   - Can be extended to show other pending items (support tickets, etc.)

// --- Polling interval ---
// The JavaScript polls every 120 seconds by default.
// This interval is configurable in the module's Back Office settings.

// --- Back Office configuration ---
// Modules > Favicon Notification:
//   - Enable/disable the badge
//   - Configure which order statuses count toward the notification
//   - Set polling interval (seconds)

// --- Module hook registration ---
// The module registers on: displayBackOfficeTop
// Available in all Back Office page layouts automatically after install.
