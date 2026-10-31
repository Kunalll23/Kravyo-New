import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options

SCREENSHOTS_DIR = r"c:\xampp\htdocs\Kravyo\screenshots"
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)

def capture_all_screens():
    options = Options()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--window-size=1280,840")
    
    driver = webdriver.Chrome(options=options)
    
    screens = [
        # Customer Public & Auth
        ("screen_4_1_customer_homepage.png", "http://localhost/Kravyo/"),
        ("screen_4_2_customer_register.png", "http://localhost/Kravyo/register"),
        ("screen_4_3_customer_login.png", "http://localhost/Kravyo/login"),
        
        # Customer Authenticated
        ("__AUTH_CUSTOMER__", "http://localhost/Kravyo/customer/auto-login"),
        ("screen_4_4_customer_feed.png", "http://localhost/Kravyo/home"),
        ("screen_4_5_kitchen_detail.png", "http://localhost/Kravyo/kitchen/2"),
        ("screen_4_6_cart_checkout.png", "http://localhost/Kravyo/checkout"),
        ("screen_4_7_order_track.png", "http://localhost/Kravyo/order/track/1"),
        ("screen_4_8_tiffin_subscriptions.png", "http://localhost/Kravyo/subscriptions"),
        ("screen_4_9_zero_waste.png", "http://localhost/Kravyo/zero-waste"),
        ("screen_4_10_tiffin_track.png", "http://localhost/Kravyo/subscription/track/95"),
        ("screen_4_11_my_subscriptions.png", "http://localhost/Kravyo/my-subscriptions"),
        ("screen_4_12_order_history.png", "http://localhost/Kravyo/orders/history"),
        
        # Chef Authenticated
        ("__AUTH_CHEF__", "http://localhost/Kravyo/chef/auto-login"),
        ("screen_4_13_chef_profile.png", "http://localhost/Kravyo/chef/profile"),
        ("screen_4_14_chef_dashboard.png", "http://localhost/Kravyo/chef/dashboard"),
        ("screen_4_15_chef_menu.png", "http://localhost/Kravyo/chef/menu"),
        ("screen_4_16_chef_subscriptions.png", "http://localhost/Kravyo/chef/subscriptions"),
        
        # Delivery Public & Authenticated
        ("screen_4_17_delivery_register.png", "http://localhost/Kravyo/delivery/register"),
        ("__AUTH_DELIVERY__", "http://localhost/Kravyo/delivery/auto-login"),
        ("screen_4_18_delivery_dashboard.png", "http://localhost/Kravyo/delivery/dashboard"),
        ("screen_4_19_delivery_order_detail.png", "http://localhost/Kravyo/delivery/order/115"),
        ("screen_4_20_delivery_runs.png", "http://localhost/Kravyo/delivery/runs"),
        ("screen_4_21_delivery_run_manifest.png", "http://localhost/Kravyo/delivery/run/2"),
        
        # Admin Authenticated
        ("__AUTH_ADMIN__", "http://localhost/Kravyo/admin/auto-login"),
        ("screen_4_22_admin_dashboard.png", "http://localhost/Kravyo/admin/dashboard"),
        ("screen_4_23_admin_chefs.png", "http://localhost/Kravyo/admin/chefs"),
        ("screen_4_24_admin_dispatch_board.png", "http://localhost/Kravyo/admin/dispatch-monitor"),
        ("screen_4_25_admin_reports_center.png", "http://localhost/Kravyo/admin/reports"),
    ]
    
    try:
        for fname, url in screens:
            if fname.startswith("__AUTH_"):
                print(f"Setting session: {fname} via {url}")
                driver.get(url)
                time.sleep(1)
                continue
                
            print(f"Capturing: {fname} from {url}...")
            driver.get(url)
            time.sleep(2.0)
            
            out_path = os.path.join(SCREENSHOTS_DIR, fname)
            driver.save_screenshot(out_path)
            size = os.path.getsize(out_path)
            print(f"  -> Saved {fname} ({size} bytes)")
            
    finally:
        driver.quit()
        print("All screen captures completed!")

if __name__ == "__main__":
    capture_all_screens()
