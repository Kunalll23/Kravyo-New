with open(r'c:\xampp\htdocs\Kravyo\scripts\build_full_report.py', 'r', encoding='utf-8') as f:
    text = f.read()

modules = {
    '1. Tiffin Delivery & Subscriptions': ['tiffin', 'milk run', 'delivery_runs', 'subscription_deliveries', 'dabba'],
    '2. On-Demand Cascade Dispatch': ['cascade', 'dispatchservice', '25-second', 'handover otp'],
    '3. Home Chef & Kitchen Operations': ['kitchen', 'fssai', 'menu_items', 'hygiene'],
    '4. Zero-Waste Food Rescue': ['zero_waste', 'surplus', 'discounted deal', 'expiry_time'],
    '5. Customer Portal & Checkout': ['cart', 'razorpay', 'order_track', 'recommendation'],
    '6. Delivery Partner Portal': ['delivery_partners', 'is_online', 'is_busy', 'order_detail'],
    '7. Master Admin & Governance': ['super_admin', 'commission', 'fssai verification', 'dispute'],
    '8. 22-Table Database Architecture': ['22 normalized', 'kravyo_db', 'delivery_runs', 'delivery_run_stops'],
    '9. Testing & Quality Assurance': ['dc-07', 'selenium', 'phpunit', 'pass rate']
}

for mod, kws in modules.items():
    print(f'=== {mod} ===')
    for kw in kws:
        cnt = text.lower().count(kw)
        print(f'   "{kw}": {cnt} occurrences')
