import os
import subprocess
from generate_activity_diagrams import (
    DIAGRAMS_DIR, CHROME_PATH, DEFS, draw_start_node, draw_end_node,
    draw_action, draw_decision, draw_merge, draw_arrow, draw_path,
    render_svg_to_png
)

def build_act_10_milk_run():
    w = 1000
    h = 1200
    cx = 500
    lx = 220
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 260, 46, "Daily Midnight Cron Triggers", "Generate subscription_deliveries")}
    {draw_arrow(cx, 123, cx, 155)}
    {draw_action(cx, 180, 270, 46, "Cluster Deliveries by Corridor", "& Meal Slot into delivery_runs")}
    {draw_arrow(cx, 203, cx, 235)}
    {draw_action(cx, 260, 270, 46, "Delivery Partner Inspects Runs", "on /delivery/runs & Claims Batch")}
    {draw_arrow(cx, 283, cx, 315)}
    {draw_action(cx, 340, 280, 46, "Execute Nearest-Neighbor TSP Heuristic", "optimizeDeliverySequence() (Haversine)")}
    {draw_arrow(cx, 363, cx, 395)}
    {draw_action(cx, 420, 280, 46, "Phase 1: Navigate to Kitchen Cluster", "Load Hot Tiffins into Thermal Carrier")}
    {draw_arrow(cx, 443, cx, 475)}
    {draw_action(cx, 500, 280, 46, "Phase 2: Begin Sequential Drop-Off Run", "Navigate to Current Customer Waypoint")}
    {draw_arrow(cx, 523, cx, 555)}
    {draw_action(cx, 580, 280, 46, "Fulfill Contactless Doorstep Drop", "Hand Over Fresh Hot Tiffin (No OTP)")}
    {draw_arrow(cx, 603, cx, 635)}
    {draw_action(cx, 660, 280, 46, "Mark Waypoint Stop 'completed'", "Send Real-Time Delivery Alert to Customer")}
    {draw_arrow(cx, 683, cx, 715)}
    {draw_decision(cx, 740, 160, 50, "More Stops Pending?")}
    
    <!-- Loop Back Branch (Yes) -->
    {draw_path(f"M {cx-80} 740 L {lx} 740 L {lx} 500 L {cx-140} 500", "Yes (Next Stop)", 290, 730, has_arrow=True)}
    
    <!-- No Branch (Completed) -->
    {draw_arrow(cx, 765, cx, 805, "No (All Drops Done)", "right")}
    {draw_action(cx, 830, 280, 46, "Complete Batch Delivery Run", "Update delivery_runs.status = 'completed'")}
    {draw_arrow(cx, 853, cx, 885)}
    {draw_action(cx, 910, 280, 46, "Credit Fixed Bulk Payout to Rider Wallet", "Update Customer Subscription History")}
    {draw_arrow(cx, 933, cx, 970)}
    {draw_end_node(cx, 985)}
    
    </svg>"""
    return svg

def build_act_11_zero_waste():
    w = 1000
    h = 1260
    cx = 500
    rx = 780
    lx = 220
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 250, 46, "Home Chef Accesses Deal Publisher", "on /chef/subscriptions Desk")}
    {draw_arrow(cx, 123, cx, 155)}
    {draw_action(cx, 180, 260, 46, "Select Fresh Surplus Dish from Catalog", "Set Discounted Price & Expiry Time")}
    {draw_arrow(cx, 203, cx, 235)}
    {draw_decision(cx, 260, 160, 50, "Valid Deal Parameters?")}
    
    <!-- Invalid Parameters Branch -->
    {draw_path(f"M {cx-80} 260 L {lx} 260 L {lx} 180 L {cx-130} 180", "No (Invalid)", 290, 250, has_arrow=True)}
    
    <!-- Valid Parameters Branch -->
    {draw_arrow(cx, 285, cx, 315, "Yes (Valid)", "right")}
    {draw_action(cx, 340, 260, 46, "Insert into zero_waste_items", "Status: active, Publish Deal")}
    {draw_arrow(cx, 363, cx, 395)}
    {draw_action(cx, 420, 270, 46, "Broadcast Deal to Customer Carousel", "Start Live Dynamic Countdown Timer")}
    {draw_arrow(cx, 443, cx, 475)}
    {draw_decision(cx, 500, 160, 50, "Claimed Before Cutoff?")}
    
    <!-- Claimed Branch (Yes) -->
    {draw_path(f"M {cx+80} 500 L {rx} 500 L {rx} 555", "Yes (Customer Buys)", 620, 490)}
    {draw_action(rx, 580, 220, 46, "Add Deal Dish to Cart", "Apply Slashed Discount Price")}
    {draw_arrow(rx, 603, rx, 635)}
    {draw_action(rx, 660, 220, 46, "Complete Checkout & Payment", "Decrement quantity_available")}
    {draw_path(f"M {rx} 683 L {rx} 720 L {cx+14} 720", has_arrow=True)}
    
    <!-- Expired Branch (No) -->
    {draw_arrow(cx, 525, cx, 555, "No (Timer Expired)", "left")}
    {draw_action(cx, 580, 220, 46, "Countdown Reaches 00:00", "Auto-Expire Zero-Waste Record")}
    {draw_arrow(cx, 603, cx, 635)}
    {draw_action(cx, 660, 220, 46, "Hide Deal from Discovery Feed", "Mark Status: 'expired'")}
    {draw_arrow(cx, 683, cx, 710)}
    
    <!-- Merge Node -->
    {draw_merge(cx, 720, 24)}
    {draw_arrow(cx, 732, cx, 765)}
    {draw_action(cx, 790, 260, 46, "Update Chef Food Waste Audit Metrics", "Record Total Portions Saved & Revenue")}
    {draw_arrow(cx, 813, cx, 850)}
    {draw_end_node(cx, 865)}
    
    </svg>"""
    return svg

def main():
    print("Re-generating Activity Diagram 10 (Multi-Tiffin Milk-Run without dabba return)...")
    render_svg_to_png(build_act_10_milk_run(), "activity_3_3_10_milk_run.html", "activity_3_3_10_milk_run.png", 1000, 1200)
    print("ALL ACTIVITY DIAGRAMS SUCCESSFULLY REFRESHED!")

if __name__ == "__main__":
    main()
