import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_html_to_png(html_content, png_filename, width=1280, height=820):
    html_path = os.path.join(DIAGRAMS_DIR, png_filename.replace('.png', '.html'))
    png_path = os.path.join(DIAGRAMS_DIR, png_filename)
    
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
    print(f"Generated: {png_filename} ({os.path.getsize(png_path)} bytes)")

def create_all_assets():
    # 1. Chef Dashboard
    chef_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Chef Kitchen Operations Desk</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px; }
  .header { display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 16px 24px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #e2e8f0; }
  .logo { font-size: 22px; font-weight: 800; color: #ea580c; display: flex; align-items: center; gap: 8px; }
  .duty-switch { display: flex; align-items: center; gap: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px 16px; border-radius: 30px; font-weight: 600; color: #065f46; font-size: 13px; }
  .duty-dot { width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; }
  
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
  .stat-card { background: #ffffff; padding: 18px; border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .stat-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }
  .stat-val { font-size: 24px; font-weight: 800; color: #0f172a; }
  .stat-sub { font-size: 11px; color: #10b981; font-weight: 600; margin-top: 4px; }
  
  .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
  .card { background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .card-head { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
  
  .order-item { border: 1px solid #f1f5f9; background: #f8fafc; border-radius: 8px; padding: 14px; margin-bottom: 12px; border-left: 4px solid #ea580c; }
  .order-top { display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; margin-bottom: 6px; }
  .badges { display: flex; gap: 6px; margin: 8px 0; }
  .badge { font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 700; }
  .badge-jain { background: #dcfce7; color: #166534; }
  .badge-spice { background: #ffedd5; color: #c2410c; }
  .badge-oil { background: #e0e7ff; color: #3730a3; }
  
  .btn-row { display: flex; gap: 8px; margin-top: 10px; }
  .btn { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; }
  .btn-accept { background: #ea580c; color: #ffffff; }
  .btn-ready { background: #10b981; color: #ffffff; }
</style>
</head>
<body>
  <div class="header">
    <div class="logo">🍳 Kravyo Chef Desk &bull; Annapurna Kitchen (Bardoli)</div>
    <div style="display:flex; align-items:center; gap:16px;">
      <span style="font-size:13px; color:#64748b;">FSSAI Lic: <strong>10723001000124</strong> &#10004; Verified</span>
      <div class="duty-switch"><div class="duty-dot"></div> Kitchen Open (Accepting Orders)</div>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Today's Revenue</div>
      <div class="stat-val">&#8377; 4,850.00</div>
      <div class="stat-sub">+18% vs yesterday</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Active Orders</div>
      <div class="stat-val">3 Preparing</div>
      <div class="stat-sub">Avg Prep: 18 mins</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Tiffin Subscriptions</div>
      <div class="stat-val">24 Active</div>
      <div class="stat-sub">14 Lunch / 10 Dinner</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Customer Rating</div>
      <div class="stat-val">4.9 &#9733;</div>
      <div class="stat-sub">Based on 142 reviews</div>
    </div>
  </div>

  <div class="main-grid">
    <div class="card">
      <div class="card-head">
        <span>Active Preparation Queue & Continuous Audio Chime Monitor</span>
        <span style="font-size:12px; color:#10b981; font-weight:600;">&#128266; Web Audio Chime: ACTIVE</span>
      </div>
      
      <div class="order-item">
        <div class="order-top">
          <span>Order #5412 &bull; 2x Kathiyawadi Thali + 1x Chaas</span>
          <span style="color:#ea580c;">Preparing (12m remaining)</span>
        </div>
        <p style="font-size:12.5px; color:#475569;">Customer: Sahil Patel &bull; Drop: Baben, Bardoli (2.4 km)</p>
        <div class="badges">
          <span class="badge badge-spice">Spice: Medium</span>
          <span class="badge badge-oil">Less Oil</span>
          <span class="badge badge-jain">Strict Jain Preparation</span>
        </div>
        <div class="btn-row">
          <button class="btn btn-ready">Mark Ready for Courier Pickup</button>
        </div>
      </div>

      <div class="order-item" style="border-left-color: #3b82f6;">
        <div class="order-top">
          <span>Order #5414 &bull; 1x Gujarati Dal Dhokli Special</span>
          <span style="color:#3b82f6;">Courier Assigned (Rider 102 Arriving in 4m)</span>
        </div>
        <p style="font-size:12.5px; color:#475569;">Customer: Neha Singh &bull; Drop: Royal Star Town (3.1 km)</p>
        <div class="badges">
          <span class="badge badge-spice">Spice: Low</span>
          <span class="badge badge-oil">Normal Ghee</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <span>Today's Tiffin Dispatch Forecast</span>
      </div>
      <div style="font-size:13px; line-height:1.7; color:#334155;">
        <p><strong>Lunch Slot (12:30 PM - 1:30 PM):</strong></p>
        <p>&bull; Standard Veg Thali: <strong>10 Portions</strong></p>
        <p>&bull; Jain Satvik Thali: <strong>4 Portions</strong></p>
        <div style="margin: 12px 0; border-top: 1px solid #f1f5f9;"></div>
        <p><strong>Dinner Slot (7:30 PM - 8:30 PM):</strong></p>
        <p>&bull; Kathiyawadi Khichdi & Kadhi: <strong>7 Portions</strong></p>
        <p>&bull; Diabetic Multigrain Roti Meal: <strong>3 Portions</strong></p>
      </div>
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(chef_html, "dashboard_chef.png")

    # 2. Delivery Partner Dashboard
    delivery_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Delivery Partner Terminal</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 20px; }
  .header { display: flex; justify-content: space-between; align-items: center; background: #1e293b; padding: 16px 24px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155; }
  .title { font-size: 20px; font-weight: 800; color: #38bdf8; display:flex; align-items:center; gap:8px; }
  .duty-toggle { background: #065f46; color: #34d399; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: 700; border: 1px solid #059669; }
  
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
  .stat-card { background: #1e293b; padding: 16px; border-radius: 10px; border: 1px solid #334155; }
  .stat-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
  .stat-val { font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 4px; }
  
  .main-card { background: #1e293b; border-radius: 10px; border: 1px solid #334155; padding: 20px; margin-bottom: 20px; }
  .active-order { border: 1px solid #38bdf8; background: #0f172a; border-radius: 8px; padding: 18px; }
  .map-box { background: #1e293b; height: 180px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; border: 1px dashed #475569; margin: 14px 0; font-family: monospace; }
  
  .otp-box { background: #334155; padding: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; margin-top: 14px; }
  .otp-input { background: #0f172a; border: 1px solid #64748b; color: #ffffff; font-size: 16px; font-weight: bold; padding: 6px 12px; border-radius: 4px; width: 140px; text-align: center; letter-spacing: 4px; }
  .btn-deliver { background: #38bdf8; color: #0f172a; font-weight: 800; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; }
</style>
</head>
<body>
  <div class="header">
    <div class="title">&#128757; Kravyo Courier Terminal &bull; Rider Nitin Singh (#172)</div>
    <div style="display:flex; align-items:center; gap:16px;">
      <span style="font-size:12.5px; color:#94a3b8;">Vehicle: <strong>GJ-19-BA-4820 (Hero Splendor)</strong></span>
      <div class="duty-toggle">&#9679; ON DUTY (Online & Idle)</div>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Today's Earnings</div><div class="stat-val">&#8377; 840.00</div></div>
    <div class="stat-card"><div class="stat-label">Trips Completed</div><div class="stat-val">12 Orders</div></div>
    <div class="stat-card"><div class="stat-label">Pusher GPS Stream</div><div class="stat-val" style="color:#34d399;">Active (4s Ping)</div></div>
    <div class="stat-card"><div class="stat-label">Rider Trust Rating</div><div class="stat-val">&#9733; 4.95 / 5.0</div></div>
  </div>

  <div class="main-card">
    <h3 style="margin-bottom:12px; color:#38bdf8;">Current Active Delivery Assignment &bull; Order #5412</h3>
    <div class="active-order">
      <div style="display:flex; justify-content:space-between; font-weight:700;">
        <span>Pickup: Annapurna Kitchen (Station Road)</span>
        <span>Drop: Baben, Bardoli (2.4 km)</span>
      </div>
      
      <div class="map-box">
        [ LIVE GOOGLE ROUTES API v2 NAVIGATION: Kitchen ➔ Turn Left at Gandhi Chowk ➔ 800m to Drop-off ]
      </div>

      <div class="otp-box">
        <div>
          <span style="font-size:13px; font-weight:bold;">Customer Handshake Authentication:</span>
          <p style="font-size:11.5px; color:#94a3b8;">Request confidential 4-digit OTP from customer upon arrival at doorstep.</p>
        </div>
        <div style="display:flex; gap:10px;">
          <input type="text" class="otp-input" value="8 4 9 2" placeholder="OTP">
          <button class="btn-deliver">Verify Handshake & Complete</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(delivery_html, "dashboard_delivery.png")

    # 3. Admin Command Dashboard
    admin_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Master Administrator Console</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0b0f19; color: #f1f5f9; padding: 20px; }
  .header { display: flex; justify-content: space-between; align-items: center; background: #131b2e; padding: 16px 24px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #1e293b; }
  .title { font-size: 20px; font-weight: 800; color: #f59e0b; display:flex; align-items:center; gap:8px; }
  
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
  .stat-card { background: #131b2e; padding: 18px; border-radius: 10px; border: 1px solid #1e293b; }
  .stat-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
  .stat-val { font-size: 24px; font-weight: 800; color: #ffffff; margin-top: 4px; }
  .stat-rate { font-size: 11px; color: #10b981; font-weight: 600; }

  .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
  .card { background: #131b2e; border-radius: 10px; border: 1px solid #1e293b; padding: 20px; }
  .card-head { font-size: 15px; font-weight: 700; color: #f59e0b; margin-bottom: 14px; }

  table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
  th { text-align: left; padding: 8px; color: #94a3b8; border-bottom: 1px solid #334155; font-size: 11px; }
  td { padding: 10px 8px; border-bottom: 1px solid #1e293b; color: #cbd5e1; }
  .badge-app { background: #065f46; color: #34d399; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
  .badge-pen { background: #78350f; color: #fcd34d; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
</style>
</head>
<body>
  <div class="header">
    <div class="title">&#128737; Kravyo Enterprise Command Console &bull; Super Admin</div>
    <div style="font-size:13px; color:#94a3b8;">Session: <strong>KRAVYO_ADMIN</strong> &bull; Host: localhost/Kravyo</div>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Platform GMV (Monthly)</div><div class="stat-val">&#8377; 4,82,450</div><div class="stat-rate">+22.4% vs last month</div></div>
    <div class="stat-card"><div class="stat-label">Net Commission (10%)</div><div class="stat-val">&#8377; 48,245</div><div class="stat-rate">Zero-loss model</div></div>
    <div class="stat-card"><div class="stat-label">Active Kitchens</div><div class="stat-val">45 Verified</div><div class="stat-rate">3 Pending FSSAI</div></div>
    <div class="stat-card"><div class="stat-label">Fleet Couriers</div><div class="stat-val">28 Online</div><div class="stat-rate">Cascade Latency: 1.2s</div></div>
  </div>

  <div class="content-grid">
    <div class="card">
      <div class="card-head">FSSAI Kitchen Hygiene Certification & Compliance Review Queue</div>
      <table>
        <tr><th>Kitchen Name</th><th>Chef</th><th>FSSAI Number</th><th>Inspection Doc</th><th>Action</th></tr>
        <tr><td>Annapurna Kitchen</td><td>Mehul Singh</td><td>10723001000124</td><td>fssai_cert_107.pdf</td><td><span class="badge-app">VERIFIED &#10004;</span></td></tr>
        <tr><td>Shreeji Tiffin Services</td><td>Priya Sharma</td><td>10723001000189</td><td>fssai_priya_2026.pdf</td><td><span class="badge-app">VERIFIED &#10004;</span></td></tr>
        <tr><td>Swad Kathiyawadi Home</td><td>Ramesh Patel</td><td>10723001000215</td><td>license_scan_0215.pdf</td><td><span class="badge-pen">PENDING AUDIT</span></td></tr>
        <tr><td>Radhe Dhokla Express</td><td>Kiran Desai</td><td>10723001000233</td><td>fssai_doc_desai.pdf</td><td><span class="badge-pen">PENDING AUDIT</span></td></tr>
      </table>
    </div>

    <div class="card">
      <div class="card-head">System Health & Cloud Infrastructure</div>
      <p style="font-size:12px; color:#94a3b8; line-height:1.7;">
        &bull; <strong>Database:</strong> MySQL InnoDB (20 Normalized Tables)<br>
        &bull; <strong>Google Maps Cache:</strong> 1,420 Cached Route Hits<br>
        &bull; <strong>Pusher WebSockets:</strong> 100% Channel Uptime<br>
        &bull; <strong>Razorpay Instant Refund:</strong> 0 Pending Disputes<br>
        &bull; <strong>Python AI Service:</strong> Online (TF-IDF Microservice)
      </p>
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(admin_html, "dashboard_admin.png")

    # 4. Customer Invoice Report
    invoice_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Official Tax Invoice</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #ffffff; color: #1e293b; padding: 24px; }
  .invoice-box { border: 2px solid #cbd5e1; border-radius: 8px; padding: 24px; max-width: 800px; margin: 0 auto; }
  .inv-header { display: flex; justify-content: space-between; border-bottom: 2px solid #ea580c; padding-bottom: 14px; margin-bottom: 16px; }
  .logo { font-size: 24px; font-weight: 800; color: #ea580c; }
  .meta { font-size: 12px; color: #64748b; line-height: 1.5; text-align: right; }
  
  .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 12px; margin-bottom: 18px; line-height: 1.5; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 16px; }
  th { background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
  td { border: 1px solid #cbd5e1; padding: 8px; }
  .total-row { font-weight: bold; font-size: 13px; background: #f1f5f9; }
  
  .footer { font-size: 11px; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 10px; display: flex; justify-content: space-between; }
</style>
</head>
<body>
  <div class="invoice-box">
    <div class="inv-header">
      <div>
        <div class="logo">🍽️ Kravyo Cloud Kitchen Platform</div>
        <p style="font-size:12px; color:#475569;">UKA Tarsadia University Tech Incubation &bull; Bardoli, Gujarat</p>
      </div>
      <div class="meta">
        <strong>TAX INVOICE / BILL RECEIPT</strong><br>
        Invoice No: <strong>INV-2026-5412</strong><br>
        Date: 26-Oct-2026 21:30 IST<br>
        Payment: <strong>Razorpay (UPI: pay_PQw78aB901Z)</strong>
      </div>
    </div>

    <div class="parties">
      <div>
        <strong>Billed From (Kitchen):</strong><br>
        Annapurna Cloud Kitchen<br>
        FSSAI Lic: 10723001000124<br>
        Station Road, Bardoli, Gujarat - 394601
      </div>
      <div>
        <strong>Billed To (Customer):</strong><br>
        Sahil Patel (Phone: 9601686114)<br>
        Flat 402, Royal Residency, Baben<br>
        Bardoli, Gujarat - 394601
      </div>
    </div>

    <table>
      <tr><th>Sr.</th><th>Dish Description</th><th>Dietary Specs</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
      <tr><td>1</td><td>Kathiyawadi Thali Special</td><td>Spice: Medium, Less Oil, Jain: True</td><td>2</td><td>&#8377; 180.00</td><td>&#8377; 360.00</td></tr>
      <tr><td>2</td><td>Masala Chaas (Spiced Buttermilk)</td><td>Traditional Cumin & Mint</td><td>2</td><td>&#8377; 25.00</td><td>&#8377; 50.00</td></tr>
      <tr><td>3</td><td>Zero-Waste Evening Poha</td><td>Surplus Deal (40% OFF)</td><td>1</td><td>&#8377; 30.00</td><td>&#8377; 30.00</td></tr>
      <tr><td colspan="5" style="text-align:right;">Subtotal:</td><td>&#8377; 440.00</td></tr>
      <tr><td colspan="5" style="text-align:right;">CGST (2.5%) + SGST (2.5%):</td><td>&#8377; 22.00</td></tr>
      <tr><td colspan="5" style="text-align:right;">Delivery Logistics Fee:</td><td>&#8377; 30.00</td></tr>
      <tr class="total-row"><td colspan="5" style="text-align:right;">GRAND TOTAL PAID:</td><td>&#8377; 492.00</td></tr>
    </table>

    <div class="footer">
      <span>Handover Verified via 4-Digit Customer OTP: <strong>8492</strong></span>
      <span>This is a computer-generated tax invoice. No signature required.</span>
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(invoice_html, "report_customer_invoice.png")

    # 5. Chef Daily Preparation Roster Report
    roster_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Chef Kitchen Batch Prep & Dispatch Roster</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #ffffff; color: #1e293b; padding: 24px; }
  .box { border: 2px solid #cbd5e1; border-radius: 8px; padding: 24px; max-width: 820px; margin: 0 auto; }
  .r-head { display: flex; justify-content: space-between; border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px; }
  .r-title { font-size: 20px; font-weight: 800; color: #0284c7; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 16px; }
  th { background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px; text-align: left; color:#0369a1; }
  td { border: 1px solid #e0f2fe; padding: 8px; }
  .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
  .b-jain { background: #dcfce7; color: #166534; }
  .b-reg { background: #f1f5f9; color: #475569; }
</style>
</head>
<body>
  <div class="box">
    <div class="r-head">
      <div>
        <div class="r-title">📋 Daily Kitchen Meal Preparation & Tiffin Dispatch Roster</div>
        <p style="font-size:12px; color:#64748b;">Annapurna Cloud Kitchen &bull; Generation Date: 26-Oct-2026</p>
      </div>
      <div style="font-size:12px; text-align:right; color:#64748b;">
        <strong>Report Type: MIS-CHEF-01</strong><br>
        Shift: <strong>Lunch (12:30 PM - 1:30 PM)</strong>
      </div>
    </div>

    <table>
      <tr><th>Slot ID</th><th>Subscriber Name</th><th>Delivery Address</th><th>Meal Package</th><th>Dietary Customization</th><th>Dispatch Status</th></tr>
      <tr><td>SUB-101</td><td>Kunal Patel</td><td>Hostel C, Room 204, UTU Campus</td><td>Kathiyawadi Thali</td><td><span class="badge b-reg">Medium Spice, Less Oil</span></td><td>Dispatched (#172)</td></tr>
      <tr><td>SUB-102</td><td>Aarav Mehta</td><td>A-302, Shivalik Western, Adajan</td><td>Kathiyawadi Thali</td><td><span class="badge b-jain">Strict Jain (No Root Veg)</span></td><td>Dispatched (#172)</td></tr>
      <tr><td>SUB-103</td><td>Priya Sharma</td><td>B-104, Royal Star Town, Bardoli</td><td>Gujarati Satvik Meal</td><td><span class="badge b-reg">Low Salt, Diabetic Friendly</span></td><td>Packed (Ready)</td></tr>
      <tr><td>SUB-104</td><td>Ritu Agarwal</td><td>Happy Hallmark Shoppers, Vesu</td><td>Deluxe Thali (Sweet)</td><td><span class="badge b-reg">Regular Ghee & Spice</span></td><td>Cooking in Kitchen</td></tr>
      <tr><td>SUB-105</td><td>Karan Joshi</td><td>C-12, Green City, Bardoli</td><td>Kathiyawadi Thali</td><td><span class="badge b-jain">Strict Jain (No Onion/Garlic)</span></td><td>Cooking in Kitchen</td></tr>
    </table>

    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px; font-size:12px; display:flex; justify-content:space-between;">
      <span>Total Lunch Deliveries Today: <strong>24 Tiffins</strong></span>
      <span>Jain Portions: <strong>8</strong> | Regular: <strong>16</strong></span>
      <span>Assigned Courier Fleet: <strong>4 Drivers</strong></span>
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(roster_html, "report_chef_roster.png")

    # 6. Admin GMV & Revenue Audit Report
    admin_rep_html = r"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Kravyo Monthly GMV & Revenue Audit Report</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #ffffff; color: #1e293b; padding: 24px; }
  .box { border: 2px solid #cbd5e1; border-radius: 8px; padding: 24px; max-width: 820px; margin: 0 auto; }
  .r-head { display: flex; justify-content: space-between; border-bottom: 2px solid #7c3aed; padding-bottom: 12px; margin-bottom: 16px; }
  .r-title { font-size: 20px; font-weight: 800; color: #7c3aed; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 16px; }
  th { background: #f5f3ff; border: 1px solid #ddd6fe; padding: 8px; text-align: left; color:#6d28d9; }
  td { border: 1px solid #ede9fe; padding: 8px; }
  .total-row { font-weight: bold; background: #faf5ff; }
</style>
</head>
<body>
  <div class="box">
    <div class="r-head">
      <div>
        <div class="r-title">📊 Executive Platform GMV, Commission & Financial Settlement Audit</div>
        <p style="font-size:12px; color:#64748b;">Period: October 2026 &bull; Scope: All Operational Cloud Kitchens</p>
      </div>
      <div style="font-size:12px; text-align:right; color:#64748b;">
        <strong>Report Type: DSS-ADMIN-01</strong><br>
        Classification: <strong>Executive Decision Report</strong>
      </div>
    </div>

    <table>
      <tr><th>Cloud Kitchen Name</th><th>Total Orders</th><th>Gross GMV</th><th>Platform Commission (10%)</th><th>Courier Delivery Fees</th><th>Net Vendor Disbursement</th></tr>
      <tr><td>Annapurna Cloud Kitchen</td><td>482</td><td>&#8377; 1,44,600.00</td><td>&#8377; 14,460.00</td><td>&#8377; 14,460.00</td><td>&#8377; 1,15,680.00</td></tr>
      <tr><td>Shreeji Tiffin Services</td><td>340</td><td>&#8377; 1,02,000.00</td><td>&#8377; 10,200.00</td><td>&#8377; 10,200.00</td><td>&#8377; 81,600.00</td></tr>
      <tr><td>Swad Kathiyawadi Home</td><td>295</td><td>&#8377; 88,500.00</td><td>&#8377; 8,850.00</td><td>&#8377; 8,850.00</td><td>&#8377; 70,800.00</td></tr>
      <tr><td>Radhe Dhokla Express</td><td>245</td><td>&#8377; 73,500.00</td><td>&#8377; 7,350.00</td><td>&#8377; 7,350.00</td><td>&#8377; 58,800.00</td></tr>
      <tr><td>Radha Krishna Satvik</td><td>246</td><td>&#8377; 73,850.00</td><td>&#8377; 7,385.00</td><td>&#8377; 7,385.00</td><td>&#8377; 59,080.00</td></tr>
      <tr class="total-row"><td colspan="2" style="text-align:right;">TOTAL PLATFORM METRICS:</td><td>&#8377; 4,82,450.00</td><td>&#8377; 48,245.00</td><td>&#8377; 48,245.00</td><td>&#8377; 3,85,960.00</td></tr>
    </table>

    <div style="font-size:12px; color:#475569; line-height:1.6; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
      <strong>Executive Audit Summary:</strong> The platform achieved an operational GMV of &#8377; 4,82,450 across 1,608 successfully fulfilled meal transactions. 100% of vendor disbursements were settled without manual cash disputes. Zero-waste food rescue initiatives eliminated an estimated 420 kg of commercial kitchen wastage during this billing cycle.
    </div>
  </div>
</body>
</html>"""
    render_html_to_png(admin_rep_html, "report_admin_gmv.png")

if __name__ == '__main__':
    create_all_assets()
