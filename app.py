"""
Kravyo AI Recommendation Server Launcher
Allows running 'python app.py' directly from the Kravyo project root.
"""
import os
import sys

# Change working directory to python_ai folder
base_dir = os.path.dirname(os.path.abspath(__file__))
ai_dir = os.path.join(base_dir, "python_ai")
os.chdir(ai_dir)
sys.path.insert(0, ai_dir)

if __name__ == "__main__":
    import app
    print("=" * 55)
    print("  Kravyo AI Recommendation Server")
    print("  http://127.0.0.1:5000/")
    print("  Endpoints: /recommend?user_id=N  |  /health")
    print("=" * 55)
    app.app.run(host="127.0.0.1", port=5000, debug=False)

