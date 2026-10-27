import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1320, height=840):
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
    print(f"Generated: {png_filename}")

DEFS = """
<defs>
  <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
    <path d="M 0 1.5 L 9 5 L 0 8.5 z" fill="#000000" />
  </marker>
</defs>
"""

def draw_box(cx, cy, w, h, text1, text2=None, is_bold=False, rx=6):
    x = cx - w / 2
    y = cy - h / 2
    weight = "font-weight=\"bold\"" if is_bold else ""
    if text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text1}</text>
        """
    return f"""
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{rx}" ry="{rx}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_arrow(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)" />'

def draw_line(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" />'

def build_traversal_diagram():
    w, h = 1300, 830
    cx_root = 650
    cy_root = 40
    
    # Column centers
    c1 = 170   # ADMIN
    c2 = 490   # HOME CHEF
    c3 = 810   # DELIVERY PARTNER
    c4 = 1130  # CUSTOMER
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    
    <!-- Root Node: Kravyo -->
    {draw_box(cx_root, cy_root, 150, 42, "Kravyo", is_bold=True, rx=8)}
    {draw_line(cx_root, 61, cx_root, 88)}
    
    <!-- Top Bus Line spanning all 4 columns -->
    {draw_line(c1, 88, c4, 88)}
    
    <!-- Vertical drops to role nodes -->
    {draw_arrow(c1, 88, c1, 109)}
    {draw_arrow(c2, 88, c2, 109)}
    {draw_arrow(c3, 88, c3, 109)}
    {draw_arrow(c4, 88, c4, 109)}
    
    <!-- ============================================================== -->
    <!-- COLUMN 1: ADMIN -->
    <!-- ============================================================== -->
    {draw_box(c1, 130, 160, 42, "ADMIN", is_bold=True)}
    {draw_arrow(c1, 151, c1, 195)}
    
    {draw_box(c1, 215, 160, 40, "Login")}
    {draw_arrow(c1, 235, c1, 275)}
    
    {draw_box(c1, 295, 170, 40, "Executive Dashboard")}
    {draw_line(c1, 315, c1, 345)}
    
    <!-- Admin Split into 2 sub-branches (left: Governance, right: Operations) -->
    {draw_line(95, 345, 245, 345)}
    {draw_arrow(95, 345, 95, 375)}
    {draw_arrow(245, 345, 245, 375)}
    
    <!-- Admin Sub-branch Left -->
    {draw_box(95, 395, 135, 42, "Verify Chef Docs", "& FSSAI Licenses")}
    {draw_arrow(95, 416, 95, 455)}
    {draw_box(95, 475, 135, 42, "Resolve Disputes", "& Order Refunds")}
    {draw_arrow(95, 496, 95, 535)}
    {draw_box(95, 555, 135, 42, "Manage Users", "& Kitchen Approvals")}
    
    <!-- Admin Sub-branch Right -->
    {draw_box(245, 395, 135, 42, "Live Order Monitor", "& Dispatch Board")}
    {draw_arrow(245, 416, 245, 455)}
    {draw_box(245, 475, 135, 42, "Platform Analytics", "& Revenue Growth")}
    {draw_arrow(245, 496, 245, 535)}
    {draw_box(245, 555, 135, 42, "Commission Rates", "& Vendor Payouts")}
    
    <!-- ============================================================== -->
    <!-- COLUMN 2: HOME CHEF -->
    <!-- ============================================================== -->
    {draw_box(c2, 130, 170, 42, "HOME CHEF", is_bold=True)}
    {draw_arrow(c2, 151, c2, 195)}
    
    {draw_box(c2, 215, 180, 40, "Register / Login")}
    {draw_arrow(c2, 235, c2, 275)}
    
    {draw_box(c2, 295, 190, 40, "Upload FSSAI & Kitchen Docs")}
    {draw_arrow(c2, 315, c2, 355)}
    
    {draw_box(c2, 375, 190, 40, "Seller Dashboard (Shift Toggle)")}
    {draw_arrow(c2, 395, c2, 435)}
    
    {draw_box(c2, 455, 190, 40, "Manage Menu & Daily Portions")}
    {draw_arrow(c2, 475, c2, 515)}
    
    {draw_box(c2, 535, 190, 40, "Process Orders & Tiffin Prep")}
    {draw_line(c2, 555, c2, 585)}
    
    <!-- Chef Split into 2 leaves -->
    {draw_line(415, 585, 565, 585)}
    {draw_arrow(415, 585, 415, 620)}
    {draw_arrow(565, 585, 565, 620)}
    
    {draw_box(415, 642, 135, 44, "View Earnings &", "Settlement Balances")}
    {draw_box(565, 642, 135, 44, "Customer Reviews", "& Rating Feedback")}
    
    <!-- ============================================================== -->
    <!-- COLUMN 3: DELIVERY PARTNER -->
    <!-- ============================================================== -->
    {draw_box(c3, 130, 180, 42, "DELIVERY PARTNER", is_bold=True)}
    {draw_arrow(c3, 151, c3, 195)}
    
    {draw_box(c3, 215, 180, 40, "Register / Login")}
    {draw_arrow(c3, 235, c3, 275)}
    
    {draw_box(c3, 295, 190, 40, "Upload License & Vehicle Info")}
    {draw_arrow(c3, 315, c3, 355)}
    
    {draw_box(c3, 375, 190, 40, "Duty Dashboard (Online/Offline)")}
    {draw_arrow(c3, 395, c3, 435)}
    
    {draw_box(c3, 455, 190, 40, "Accept 25s Dispatch Offer")}
    {draw_arrow(c3, 475, c3, 515)}
    
    {draw_box(c3, 535, 190, 40, "Pickup Route & OTP Delivery")}
    {draw_line(c3, 555, c3, 585)}
    
    <!-- Delivery Partner Split into 2 leaves -->
    {draw_line(735, 585, 885, 585)}
    {draw_arrow(735, 585, 735, 620)}
    {draw_arrow(885, 585, 885, 620)}
    
    {draw_box(735, 642, 135, 44, "View Trip Earnings", "& Rider Wallet")}
    {draw_box(885, 642, 135, 44, "Delivery History", "& Mission Logs")}
    
    <!-- ============================================================== -->
    <!-- COLUMN 4: CUSTOMER -->
    <!-- ============================================================== -->
    {draw_box(c4, 130, 170, 42, "CUSTOMER", is_bold=True)}
    {draw_arrow(c4, 151, c4, 195)}
    
    {draw_box(c4, 215, 180, 40, "Register / Login (OTP Verified)")}
    {draw_arrow(c4, 235, c4, 275)}
    
    {draw_box(c4, 295, 190, 40, "Browse Dishes, Chefs & Plans")}
    {draw_arrow(c4, 315, c4, 355)}
    
    {draw_box(c4, 375, 190, 40, "Subscribe / Place Order")}
    {draw_arrow(c4, 395, c4, 435)}
    
    {draw_box(c4, 455, 190, 40, "Checkout (COD / UPI / Online)")}
    {draw_arrow(c4, 475, c4, 515)}
    
    {draw_box(c4, 535, 190, 40, "Track Live GPS & Provide OTP")}
    {draw_line(c4, 555, c4, 585)}
    
    <!-- Customer Split into 2 leaves -->
    {draw_line(1055, 585, 1205, 585)}
    {draw_arrow(1055, 585, 1055, 620)}
    {draw_arrow(1205, 585, 1205, 620)}
    
    {draw_box(1055, 642, 135, 44, "Submit Rating", "& Food Review")}
    {draw_box(1205, 642, 135, 44, "Download Invoice", "/ Report Dispute")}
    
    <!-- Bottom Caption -->
    <text x="{cx_root}" y="{h - 50}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="bold" fill="#000000">Figure 13 : Kravyo Traversal Diagram</text>
    
    </svg>"""
    return svg

def main():
    print("Generating Kravyo Traversal Hierarchical Diagram...")
    render_svg_to_png(build_traversal_diagram(), "traversal_hierarchical_diagram.html", "traversal_hierarchical_diagram.png", 1320, 840)
    print("Traversal Diagram successfully created!")

if __name__ == "__main__":
    main()
