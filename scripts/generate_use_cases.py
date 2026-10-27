import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1150, height=1200):
    html_path = os.path.join(DIAGRAMS_DIR, html_filename)
    png_path = os.path.join(DIAGRAMS_DIR, png_filename)
    
    html_content = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {{
    margin: 0;
    padding: 10px;
    background: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, Helvetica, sans-serif;
  }}
  svg {{
    background: #ffffff;
  }}
</style>
</head>
<body>
{svg_content}
</body>
</html>"""
    
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_content)
        
    cmd = [
        CHROME_PATH,
        "--headless",
        "--disable-gpu",
        "--hide-scrollbars",
        f"--window-size={width},{height}",
        f"--screenshot={png_path}",
        html_path
    ]
    subprocess.run(cmd, check=True)
    print(f"Generated with correct UML arrow directions: {png_filename}")

# Standard Draw.io UML Markers
UML_DEFS = """
<defs>
  <!-- Standard Open Arrowhead for UML relationships -->
  <marker id="uml_arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse">
    <path d="M 0 1.5 L 9 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.3" />
  </marker>
</defs>
"""

def draw_drawio_actor(x, y, name):
    return f"""
    <!-- Actor: {name} -->
    <g id="actor_{name.replace(' ', '_')}">
      <circle cx="{x}" cy="{y - 40}" r="15" stroke="#000000" stroke-width="1.5" fill="#ffffff" />
      <line x1="{x}" y1="{y - 25}" x2="{x}" y2="{y + 15}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x - 25}" y1="{y - 12}" x2="{x + 25}" y2="{y - 12}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x}" y1="{y + 15}" x2="{x - 20}" y2="{y + 50}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x}" y1="{y + 15}" x2="{x + 20}" y2="{y + 50}" stroke="#000000" stroke-width="1.5" />
      <text x="{x}" y="{y + 70}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">{name}</text>
    </g>
    """

def draw_drawio_ellipse(cx, cy, rx, ry, text1, text2=None):
    if text2:
        text_content = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 13}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text2}</text>
        """
    else:
        text_content = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#000000">{text1}</text>
        """
    return f"""
    <ellipse cx="{cx}" cy="{cy}" rx="{rx}" ry="{ry}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {text_content}
    """

def draw_drawio_assoc(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" />'

def draw_drawio_rel(base_x, base_y, target_x, target_y, stereotype="<<extend>>"):
    """
    Standard UML Direction Rules:
    - <<include>>: Base Use Case -> Included Use Case (Arrow points RIGHT towards target)
    - <<extend>>:  Extension Use Case -> Base Use Case (Arrow points LEFT towards base)
    """
    mid_x = (base_x + target_x) / 2
    mid_y = (base_y + target_y) / 2
    box_w = 68 if "extend" in stereotype else 72
    
    if "extend" in stereotype:
        # Arrowhead points to BASE (left: base_x, base_y)
        line_svg = f'<line x1="{target_x}" y1="{target_y}" x2="{base_x}" y2="{base_y}" stroke="#000000" stroke-width="1.2" stroke-dasharray="4,3" marker-end="url(#uml_arrow)" />'
    else:
        # <<include>>: Arrowhead points to TARGET (right: target_x, target_y)
        line_svg = f'<line x1="{base_x}" y1="{base_y}" x2="{target_x}" y2="{target_y}" stroke="#000000" stroke-width="1.2" stroke-dasharray="4,3" marker-end="url(#uml_arrow)" />'
        
    return f"""
    {line_svg}
    <rect x="{mid_x - box_w/2}" y="{mid_y - 8}" width="{box_w}" height="16" fill="#ffffff" />
    <text x="{mid_x}" y="{mid_y + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" font-style="italic" fill="#000000">&lt;&lt;{stereotype.replace('<<','').replace('>>','')}&gt;&gt;</text>
    """

# =========================================================================
# 1. CUSTOMER USE CASE DIAGRAM
# =========================================================================
def build_customer_diagram():
    w, h = 1100, 1150
    box_x, box_y, box_w, box_h = 240, 40, 800, 1030
    actor_x, actor_y = 110, 520
    
    col1_x = 420
    col2_x = 830
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Customer")}
    """
    
    cases = [
        (105, "Register / Login", None),
        (205, "Browse & Search Food", None),
        (305, "Customize Meal", None),
        (400, "Manage Cart", None),
        (495, "Place Online Order", None),
        (610, "Track Order Live", None),
        (720, "Manage Subscriptions", None),
        (820, "Claim Zero Waste Deals", None),
        (910, "Submit Review", None),
        (995, "File Dispute / Report", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - 105, cy)
        svg += draw_drawio_ellipse(col1_x, cy, 105, 25, l1, l2)
        
    # Relationships with strict UML arrow directions:
    # extend: points LEFT to base (col1)
    # include: points RIGHT to target (col2)
    svg += draw_drawio_ellipse(col2_x, 85, 95, 23, "Verify Email OTP", None)
    svg += draw_drawio_rel(col1_x + 105, 105, col2_x - 95, 85, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 140, 95, 23, "Forgot Password", None)
    svg += draw_drawio_rel(col1_x + 105, 105, col2_x - 95, 140, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 195, 105, 23, "View AI Recommendations", None)
    svg += draw_drawio_rel(col1_x + 105, 205, col2_x - 105, 195, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 250, 105, 23, "Filter Veg/Jain/Diabetic", None)
    svg += draw_drawio_rel(col1_x + 105, 205, col2_x - 105, 250, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 305, 105, 23, "Select Spice/Oil/Jain", None)
    svg += draw_drawio_rel(col1_x + 105, 305, col2_x - 105, 305, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 400, 95, 23, "Apply Coupon", None)
    svg += draw_drawio_rel(col1_x + 105, 400, col2_x - 95, 400, "<<extend>>")
    
    # Place Online Order INCLUDES Make Razorpay Payment and Receive Delivery OTP
    svg += draw_drawio_ellipse(col2_x, 465, 100, 23, "Make Razorpay Payment", None)
    svg += draw_drawio_rel(col1_x + 105, 495, col2_x - 100, 465, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 520, 100, 23, "Receive Delivery OTP", None)
    svg += draw_drawio_rel(col1_x + 105, 495, col2_x - 100, 520, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 575, 95, 23, "Cancel Order", None)
    svg += draw_drawio_rel(col1_x + 105, 495, col2_x - 95, 575, "<<extend>>")
    
    # Track Order Live INCLUDES Live WebSockets Map
    svg += draw_drawio_ellipse(col2_x, 635, 105, 23, "Live WebSockets Map", None)
    svg += draw_drawio_rel(col1_x + 105, 610, col2_x - 105, 635, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 720, 95, 23, "Pause/Resume Delivery", None)
    svg += draw_drawio_rel(col1_x + 105, 720, col2_x - 95, 720, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 820, 95, 23, "View Expiry Timer", None)
    svg += draw_drawio_rel(col1_x + 105, 820, col2_x - 95, 820, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 995, 95, 23, "Attach Defect Photo", None)
    svg += draw_drawio_rel(col1_x + 105, 995, col2_x - 95, 995, "<<extend>>")
    
    svg += f'<text x="{box_x + box_w/2}" y="{box_y + box_h + 35}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.2.1 Customer - Use Case Diagram</text>'
    
    svg += "</svg>"
    return svg

# =========================================================================
# 2. HOME CHEF USE CASE DIAGRAM
# =========================================================================
def build_chef_diagram():
    w, h = 1100, 1050
    box_x, box_y, box_w, box_h = 240, 40, 800, 930
    actor_x, actor_y = 110, 480
    
    col1_x = 420
    col2_x = 830
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Home Chef")}
    """
    
    cases = [
        (105, "Register / Login", None),
        (210, "Manage Kitchen Profile", None),
        (320, "Manage Menu", None),
        (435, "Manage Orders", None),
        (560, "Update Preparation Status", None),
        (665, "Manage Subscriptions", None),
        (765, "List Discounted Meals", None),
        (855, "View Dashboard", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - 105, cy)
        svg += draw_drawio_ellipse(col1_x, cy, 105, 25, l1, l2)
        
    svg += draw_drawio_ellipse(col2_x, 105, 95, 23, "Forgot Password", None)
    svg += draw_drawio_rel(col1_x + 105, 105, col2_x - 95, 105, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 185, 105, 23, "Apply Hygiene Verification", None)
    svg += draw_drawio_rel(col1_x + 105, 210, col2_x - 105, 185, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 240, 105, 23, "Toggle Kitchen Open/Close", None)
    svg += draw_drawio_rel(col1_x + 105, 210, col2_x - 105, 240, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 320, 105, 23, "Assign Dietary Tags", None)
    svg += draw_drawio_rel(col1_x + 105, 320, col2_x - 105, 320, "<<extend>>")
    
    # Manage Orders INCLUDES Audio Alert; EXTENDS Reject Orders
    svg += draw_drawio_ellipse(col2_x, 395, 105, 23, "Audio Alert (beep.wav)", None)
    svg += draw_drawio_rel(col1_x + 105, 435, col2_x - 105, 395, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 455, 95, 23, "Reject Orders", None)
    svg += draw_drawio_rel(col1_x + 105, 435, col2_x - 95, 455, "<<extend>>")
    
    # Update Prep Status INCLUDES Mark Ready for Pickup
    svg += draw_drawio_ellipse(col2_x, 560, 105, 23, "Mark Ready for Pickup", None)
    svg += draw_drawio_rel(col1_x + 105, 560, col2_x - 105, 560, "<<include>>")
    
    # Manage Subscriptions INCLUDES Daily Production Forecast
    svg += draw_drawio_ellipse(col2_x, 665, 105, 23, "Daily Production Forecast", None)
    svg += draw_drawio_rel(col1_x + 105, 665, col2_x - 105, 665, "<<include>>")
    
    # List Discounted Meals INCLUDES Set Expiry Timer
    svg += draw_drawio_ellipse(col2_x, 765, 100, 23, "Set Expiry Timer", None)
    svg += draw_drawio_rel(col1_x + 105, 765, col2_x - 100, 765, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 855, 100, 23, "View Reviews & Ratings", None)
    svg += draw_drawio_rel(col1_x + 105, 855, col2_x - 100, 855, "<<extend>>")
    
    svg += f'<text x="{box_x + box_w/2}" y="{box_y + box_h + 35}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.2.2 Home Chef - Use Case Diagram</text>'
    
    svg += "</svg>"
    return svg

# =========================================================================
# 3. DELIVERY PARTNER USE CASE DIAGRAM
# =========================================================================
def build_delivery_diagram():
    w, h = 1100, 1050
    box_x, box_y, box_w, box_h = 240, 40, 800, 930
    actor_x, actor_y = 110, 480
    
    col1_x = 420
    col2_x = 830
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Delivery Partner")}
    """
    
    cases = [
        (105, "Register / Login", None),
        (215, "Manage Shift Duty", None),
        (335, "Receive Dispatch Offer", None),
        (455, "Navigate Route", None),
        (565, "Confirm Kitchen Pickup", None),
        (665, "Stream Live GPS Location", None),
        (765, "Deliver Order", None),
        (855, "View Earnings History", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - 105, cy)
        svg += draw_drawio_ellipse(col1_x, cy, 105, 25, l1, l2)
        
    # Register INCLUDES Upload Driving License
    svg += draw_drawio_ellipse(col2_x, 105, 100, 23, "Upload Driving License", None)
    svg += draw_drawio_rel(col1_x + 105, 105, col2_x - 100, 105, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 215, 100, 23, "Toggle Online / Offline", None)
    svg += draw_drawio_rel(col1_x + 105, 215, col2_x - 100, 215, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 305, 100, 23, "Accept 25s Ping Offer", None)
    svg += draw_drawio_rel(col1_x + 105, 335, col2_x - 100, 305, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 365, 100, 23, "Decline / Timeout Offer", None)
    svg += draw_drawio_rel(col1_x + 105, 335, col2_x - 100, 365, "<<extend>>")
    
    # Navigate Route INCLUDES Google Routes Navigation
    svg += draw_drawio_ellipse(col2_x, 455, 105, 23, "Google Routes Navigation", None)
    svg += draw_drawio_rel(col1_x + 105, 455, col2_x - 105, 455, "<<include>>")
    
    # Kitchen Pickup INCLUDES Update Out for Delivery
    svg += draw_drawio_ellipse(col2_x, 565, 105, 23, "Update Out for Delivery", None)
    svg += draw_drawio_rel(col1_x + 105, 565, col2_x - 105, 565, "<<include>>")
    
    # Stream GPS INCLUDES Broadcast Pusher GPS
    svg += draw_drawio_ellipse(col2_x, 665, 105, 23, "Broadcast Pusher GPS", None)
    svg += draw_drawio_rel(col1_x + 105, 665, col2_x - 105, 665, "<<include>>")
    
    # Deliver Order INCLUDES Verify Customer OTP
    svg += draw_drawio_ellipse(col2_x, 765, 105, 23, "Verify Customer OTP", None)
    svg += draw_drawio_rel(col1_x + 105, 765, col2_x - 105, 765, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 855, 95, 23, "View Trip Incentives", None)
    svg += draw_drawio_rel(col1_x + 105, 855, col2_x - 95, 855, "<<extend>>")
    
    svg += f'<text x="{box_x + box_w/2}" y="{box_y + box_h + 35}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.2.3 Delivery Partner - Use Case Diagram</text>'
    
    svg += "</svg>"
    return svg

# =========================================================================
# 4. ADMINISTRATOR USE CASE DIAGRAM
# =========================================================================
def build_admin_diagram():
    w, h = 1100, 1050
    box_x, box_y, box_w, box_h = 240, 40, 800, 930
    actor_x, actor_y = 110, 480
    
    col1_x = 420
    col2_x = 830
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Administrator")}
    """
    
    cases = [
        (110, "Admin Login", None),
        (225, "Manage Users & Kitchens", None),
        (345, "Manage Delivery Partners", None),
        (465, "Manage Food & Categories", None),
        (580, "Handle Disputes & Complaints", None),
        (700, "Monitor Orders & Fleet", None),
        (825, "Generate Analytics", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - 105, cy)
        svg += draw_drawio_ellipse(col1_x, cy, 105, 25, l1, l2)
        
    svg += draw_drawio_ellipse(col2_x, 110, 95, 23, "Change Password", None)
    svg += draw_drawio_rel(col1_x + 105, 110, col2_x - 95, 110, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 195, 105, 23, "Approve Hygiene Badges", None)
    svg += draw_drawio_rel(col1_x + 105, 225, col2_x - 105, 195, "<<extend>>")
    
    # Manage Kitchens INCLUDES Review FSSAI License
    svg += draw_drawio_ellipse(col2_x, 255, 105, 23, "Review FSSAI License", None)
    svg += draw_drawio_rel(col1_x + 105, 225, col2_x - 105, 255, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 320, 100, 23, "Approve Driver License", None)
    svg += draw_drawio_rel(col1_x + 105, 345, col2_x - 100, 320, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 375, 100, 23, "Suspend Rider Account", None)
    svg += draw_drawio_rel(col1_x + 105, 345, col2_x - 100, 375, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 465, 95, 23, "Moderate Reviews", None)
    svg += draw_drawio_rel(col1_x + 105, 465, col2_x - 95, 465, "<<extend>>")
    
    # Handle Disputes INCLUDES Audit Order Timestamps; EXTENDS Process Razorpay Refund
    svg += draw_drawio_ellipse(col2_x, 550, 105, 23, "Audit Order Timestamps", None)
    svg += draw_drawio_rel(col1_x + 105, 580, col2_x - 105, 550, "<<include>>")
    
    svg += draw_drawio_ellipse(col2_x, 615, 105, 23, "Process Razorpay Refund", None)
    svg += draw_drawio_rel(col1_x + 105, 580, col2_x - 105, 615, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 700, 95, 23, "Live Fleet Map", None)
    svg += draw_drawio_rel(col1_x + 105, 700, col2_x - 95, 700, "<<extend>>")
    
    svg += draw_drawio_ellipse(col2_x, 825, 100, 23, "Export Revenue CSV", None)
    svg += draw_drawio_rel(col1_x + 105, 825, col2_x - 100, 825, "<<extend>>")
    
    svg += f'<text x="{box_x + box_w/2}" y="{box_y + box_h + 35}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.2.4 Administrator - Use Case Diagram</text>'
    
    svg += "</svg>"
    return svg

def main():
    print("Re-rendering all 4 diagrams with strict UML arrow directions...")
    render_svg_to_png(build_customer_diagram(), "use_case_customer.html", "use_case_customer.png", 1120, 1180)
    render_svg_to_png(build_chef_diagram(), "use_case_chef.html", "use_case_chef.png", 1120, 1080)
    render_svg_to_png(build_delivery_diagram(), "use_case_delivery.html", "use_case_delivery.png", 1120, 1080)
    render_svg_to_png(build_admin_diagram(), "use_case_admin.html", "use_case_admin.png", 1120, 1080)
    print("All 4 diagrams updated with correct UML arrow directions!")

if __name__ == "__main__":
    main()
