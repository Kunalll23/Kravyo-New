file_path = r"c:\xampp\htdocs\Kravyo\scripts\build_full_report.py"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = [
    # 1. LOF
    ('("Figure 3.14", "Activity Diagram: Multi-Tiffin Milk-Run Batch Routing & Circular Dabba Return Flow"),',
     '("Figure 3.14", "Activity Diagram: Multi-Tiffin Milk-Run Batch Routing & Doorstep Handover Flow"),'),

    # 2. Sustainability bullet
    ('add_bullet(doc, "Recurring weekly and monthly meal plan scheduling with circular zero-waste dabba returns and a surplus food rescue marketplace with countdown timers.", "5. Sustainability & Subscriptions: ")',
     'add_bullet(doc, "Recurring weekly and monthly meal plan scheduling with predictable batch delivery forecasting and a surplus food rescue marketplace with countdown timers.", "5. Sustainability & Subscriptions: ")'),

    # 3. Stage 6 heading
    ('add_sub_heading(doc, "1.2.6 Stage 6: Scheduled Multi-Tiffin Milk-Run Batch Logistics & Circular Dabba Return Engine")',
     'add_sub_heading(doc, "1.2.6 Stage 6: Scheduled Multi-Tiffin Milk-Run Batch Logistics & Doorstep Handover Engine")'),

    # 4. Stage 6 bullet
    ('add_bullet(doc, "Frictionless Doorstep Handover & Circular Dabba Return — Unlike impulse orders requiring interactive 4-digit OTP entry, daily lunch deliveries at corporate desks or residences are verified via contactless doorstep drops and physical container exchange. The mobile manifest (views/delivery/run_manifest.php) logs the collection of yesterday\'s clean empty stainless-steel dabbas (container_returned = 1), returning them to home kitchens on the return leg to maintain a sustainable, zero-waste circular container loop.", "Circular Dabba Return: ")',
     'add_bullet(doc, "Frictionless Doorstep Handover — Unlike impulse orders requiring interactive 4-digit OTP entry, daily lunch deliveries at corporate desks or residences are fulfilled via contactless doorstep drops. The mobile manifest (views/delivery/run_manifest.php) guides the courier through rapid waypoint completion without OTP friction, delivering hot, fresh home-cooked meals on schedule.", "Frictionless Handover: ")'),

    # 5. Table 1.1 Step 9
    ('("9", "Milk-Run Courier", "Multi-Tiffin Batch Manifest Fulfillment", "Batch manifest stops (Pickups -> Drops), Dabba return toggle", "delivery_runs: assigned -> in_progress -> completed; stops completed", "Hot tiffins delivered along corridor; clean dabbas returned to kitchens; bulk run payout credited")',
     '("9", "Milk-Run Courier", "Multi-Tiffin Batch Manifest Fulfillment", "Batch manifest stops (Pickups -> Drops), Contactless drop verification", "delivery_runs: assigned -> in_progress -> completed; stops completed", "Hot tiffins delivered along corridor; doorstep drops completed; bulk run payout credited")'),

    # 6. Competitor mention
    ('2) Traditional Unorganized Tiffin Providers (offline local Dabbawalas / phone ordering)',
     '2) Traditional Unorganized Tiffin Providers (offline local tiffin providers / phone ordering)'),

    # 7. FR-8.5
    ('("FR-8.5: ", "Delivery partners shall execute multi-stop milk-runs via an interactive mobile manifest (views/delivery/run_manifest.php), logging contactless doorstep drops and circular stainless-steel dabba exchanges without OTP friction."),',
     '("FR-8.5: ", "Delivery partners shall execute multi-stop milk-runs via an interactive mobile manifest (views/delivery/run_manifest.php), fulfilling contactless doorstep drops without OTP friction."),'),

    # 8. Table 22 summary
    ('("22", "delivery_run_stops", "id, run_id, stop_sequence, stop_type, subscription_delivery_id, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, status, delivery_otp, container_returned", "Sequential waypoints for multi-kitchen pickup and multi-customer drops with OTP and container return tracking.")',
     '("22", "delivery_run_stops", "id, run_id, stop_sequence, stop_type, subscription_delivery_id, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, status, delivery_otp", "Sequential waypoints for multi-kitchen pickup and multi-customer drops with turn-by-turn navigation.")'),

    # 9. Act diagram in Ch3
    ('("Activity Diagram: Multi-Tiffin Milk-Run Batch Routing & Circular Dabba Return Flow", "3.14", "diagrams/activity_3_3_10_milk_run.png"),',
     '("Activity Diagram: Multi-Tiffin Milk-Run Batch Routing & Doorstep Handover Flow", "3.14", "diagrams/activity_3_3_10_milk_run.png"),'),

    # 10. Screen summary in 3.4
    ('("Step-by-Step Waypoint Route Manifest", "Displays the ordered sequence of kitchen pickups and customer drop-offs with turn-by-turn navigation, customer call buttons, instant OTP verification modal, and dabba return toggle.")',
     '("Step-by-Step Waypoint Route Manifest", "Displays the ordered sequence of kitchen pickups and customer drop-offs with turn-by-turn navigation, customer call buttons, and one-tap doorstep completion.")'),

    # 11. Table 22 description
    ('("22. Delivery Run Stops Table (`delivery_run_stops`)", "Defines ordered waypoint navigation stops (kitchen pickups followed by consumer drops) along an active delivery run, enforcing OTP verification and empty dabba container exchange.", [',
     '("22. Delivery Run Stops Table (`delivery_run_stops`)", "Defines ordered waypoint navigation stops (kitchen pickups followed by consumer drops) along an active delivery run with turn-by-turn routing and contactless doorstep fulfillment.", ['),

    # 12. Remove container_returned row from Table 22
    ('''            ("delivery_otp", "VARCHAR", "6", "Confidential 4-to-6 digit delivery verification OTP, Nullable"),
            ("container_returned", "TINYINT", "1", "Flag verifying return of clean empty stainless-steel dabba (Default: 0, Not Null)"),
            ("completed_at", "DATETIME", "—", "Timestamp when waypoint was verified and marked done, Nullable"),''',
     '''            ("delivery_otp", "VARCHAR", "6", "Confidential 4-to-6 digit delivery verification OTP, Nullable"),
            ("completed_at", "DATETIME", "—", "Timestamp when waypoint was verified and marked done, Nullable"),'''),

    # 13. Screen 4.2.1.9 control
    ('("Circular Dabba Return Indicator: ", "Alerts the subscriber to keep yesterday\'s empty stainless-steel container ready for courier exchange.")',
     '("Contactless Handover Notice: ", "Informs the subscriber of seamless doorstep delivery without interactive OTP friction.")'),

    # 14. Screen 4.2.3.5
    ('("4.2.3.5 Multi-Stop Milk-Run Route Manifest, Turn-by-Turn Waypoints & Dabba Return Console", "4.21", "views/delivery/run_manifest.php",\n         "Interactive mobile operational console guiding riders through sequential kitchen pickups, corridor drop-offs, turn-by-turn navigation, customer calling, and circular stainless-steel dabba return exchange.",',
     '("4.2.3.5 Multi-Stop Milk-Run Route Manifest & Waypoint Navigation Console", "4.21", "views/delivery/run_manifest.php",\n         "Interactive mobile operational console guiding riders through sequential kitchen pickups, corridor drop-offs, turn-by-turn navigation, customer calling, and contactless doorstep delivery.",'),

    ('("Circular Dabba Return Toggle Switch: ", "Logs whether yesterday\'s clean empty stainless-steel container was collected (`container_returned = 1`)."),',
     '("One-Tap Drop-off Completion Button: ", "Verifies doorstep delivery fulfillment and advances to the next waypoint without OTP delays."),'),

    # 15. Test case DC-08
    ('("DC-08", "TSP Route Sequencing & Dabba Exchange Verification", "Courier executes batch run with TSP nearest-neighbor ordering; verifies container return toggle", "Waypoints sequenced by minimum Haversine distance; container_returned recorded as 1; inventory returned to kitchen", "Optimal corridor traversal verified with zero missing dabbas", "Pass")',
     '("DC-08", "TSP Route Sequencing & Multi-Stop Delivery Verification", "Courier executes batch run with TSP nearest-neighbor ordering; completes sequential drops", "Waypoints sequenced by minimum Haversine distance; all stops marked completed; bulk run payout credited", "Optimal corridor traversal verified with zero delayed drop-offs", "Pass")')
]

for old, new in replacements:
    if old not in content:
        print(f"WARNING: Not found: {old[:60]}...")
    else:
        content = content.replace(old, new)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("SUCCESS: build_full_report.py updated without dabba return flow!")
