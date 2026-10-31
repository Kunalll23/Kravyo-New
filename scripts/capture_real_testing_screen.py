import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options

def capture_real_system_tests_screen():
    print("[*] Launching Selenium WebDriver to capture authentic Kravyo System Tests screen...")
    
    options = Options()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--window-size=1300,1080")
    
    driver = webdriver.Chrome(options=options)
    
    try:
        # Step 1: Admin Auto-Login
        print("[*] Authenticating as Admin...")
        driver.get("http://localhost/Kravyo/admin/auto-login")
        time.sleep(1.5)
        
        # Step 2: Navigate to System Tests Console
        target_url = "http://localhost/Kravyo/admin/system-tests"
        print(f"[*] Navigating to: {target_url}")
        driver.get(target_url)
        time.sleep(2)
        
        # Clean up transient floating prompts and scrollbars
        driver.execute_script("""
            // Remove audio prompt banner
            document.querySelectorAll('[id*="sound"], [class*="sound"], .sound-prompt').forEach(el => el.remove());
            const btns = document.querySelectorAll('button, div');
            btns.forEach(b => {
                if (b.textContent && b.textContent.includes('Click to enable order sounds')) {
                    b.remove();
                }
            });
            // Hide scrollbar for perfectly clean screenshot
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        """)
        time.sleep(1)
        
        # Ensure target directories exist
        diag_dir = r"c:\xampp\htdocs\Kravyo\diagrams"
        screens_dir = r"c:\xampp\htdocs\Kravyo\screenshots"
        os.makedirs(diag_dir, exist_ok=True)
        os.makedirs(screens_dir, exist_ok=True)
        
        diag_path = os.path.join(diag_dir, "automated_testing_execution.png")
        screen_path = os.path.join(screens_dir, "screen_6_1_automated_testing.png")
        
        # Save screenshot
        driver.save_screenshot(diag_path)
        driver.save_screenshot(screen_path)
        
        print(f"[SUCCESS] Real screenshot saved to: {diag_path} ({os.path.getsize(diag_path)} bytes)")
        print(f"[SUCCESS] Real screenshot saved to: {screen_path} ({os.path.getsize(screen_path)} bytes)")
        
    finally:
        driver.quit()

if __name__ == '__main__':
    capture_real_system_tests_screen()
