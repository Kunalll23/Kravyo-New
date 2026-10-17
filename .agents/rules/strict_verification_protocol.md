# Kravyo — Strict Verification & Anti-Assumption Protocol

## Purpose
This rule strictly forbids guessing, generalizing, or assuming how Kravyo features work based on common app conventions (e.g., Swiggy, Zomato, standard e-commerce patterns). A feature, notification, or behavior ONLY exists if there is an exact line of code executing it.

---

## 1. The Proof-First Protocol (Mandatory Before Answering)

Whenever the user asks a question such as:
- *"When does X appear?"*
- *"What triggers Y?"*
- *"Does the system do Z?"*
- *"What are the exact steps/statuses?"*

**The assistant MUST follow this 3-step sequence:**
1. **Search First:** Execute a `grep_search` or `view_file` to locate the exact controller method, model query, or JavaScript event listener in Kravyo.
2. **Inspect the Execution Branch:** Do NOT just check if a status exists in an `enum` or array; check if the code *actually executes an action* (e.g., does it actually call `createForUser()`, does it actually send an SMS, does it actually redirect?).
3. **Cite the File & Line Number:** Always provide the exact file path and line number as proof in the response (e.g., `[ChefController.php:961](file:///c:/xampp/htdocs/Kravyo/app/controllers/ChefController.php#L961)`).

---

## 2. Forbidden Behaviors
- **NO Generic Industry Assumptions:** Never assume that because standard food delivery apps notify on "Preparing", Kravyo must do so too.
- **NO Timeline Extrapolations:** Just because an order status exists in the tracking UI timeline (`pending`, `accepted`, `preparing`, `out_for_delivery`, `delivered`) does NOT mean every step triggers a notification or dialog box.
- **NO Unverified Guesses:** If you haven't verified the exact line of code, say: *"Let me verify the exact code in the controller first"* before making any factual statement.

---

## 3. Verification Tool
- Run `php scripts/system_audit.php` to instantly verify active database tables, controllers, services, models, 3rd-party integrations, and all 21 notification emission points.
