# Kravyo — Python AI Recommendation Module

## What is this?

This folder contains the **Python Machine Learning Recommendation Engine** for the Kravyo project.
It runs as a standalone **Flask microservice** alongside XAMPP and exposes a REST API that the PHP application queries for personalized dish recommendations.

---

## Technology Stack

| Technology | Role |
|---|---|
| **Python** | Core language for the AI module |
| **Flask** | Lightweight REST API web server |
| **Pandas** | SQL data loading and feature engineering |
| **NumPy** | Matrix normalisation and score blending |
| **Scikit-learn** | TF-IDF vectorisation and cosine similarity |
| **mysql-connector-python** | MariaDB connection from Python |

---

## How the Algorithm Works (For Viva Explanation)

```
Customer Order History
        ↓
Pandas loads menu_items, categories, order_items from MariaDB
        ↓
Feature Engineering: item_name + category_name + dietary flags → text string
        ↓
TF-IDF Vectorization (Scikit-learn): text → numerical vectors
        ↓
Cosine Similarity: compare user's ordered dishes vs all available dishes
        ↓
Popularity Score: platform-wide order counts (NumPy normalised 0–1)
        ↓
Final Score = 0.80 × similarity_score + 0.20 × popularity_score
        ↓
Top-N dishes sorted by final_score DESC
        ↓
Flask REST API → JSON response
        ↓
PHP (Recommendation.php) fetches JSON via cURL
        ↓
Kravyo UI displays personalised "Recommended For You"
```

### Cold-Start (New Customer)
If the customer has **zero previous orders**, the ML engine has no history to learn from.
In this case, it returns dishes ranked **purely by platform popularity**.
This is also the fallback used when the Python server is offline.

---

## Files

| File | Purpose |
|---|---|
| `app.py` | Flask server — exposes `/recommend` and `/health` endpoints |
| `recommender.py` | Core ML engine — Pandas + Scikit-learn + NumPy logic |
| `requirements.txt` | Python dependencies |
| `README.md` | This file |

---

## Setup & Run (Windows / XAMPP)

### Step 1: Ensure Python is installed
```
python --version
```
Requires Python 3.10 or higher.

### Step 2: Create a virtual environment (recommended)
```
cd c:\xampp\htdocs\Kravyo
python -m venv python_ai\.venv
python_ai\.venv\Scripts\activate
```

### Step 3: Install dependencies
```
pip install -r python_ai\requirements.txt
```

### Step 4: Start XAMPP (Apache + MySQL must be running)

### Step 5: Start the Python AI server
```
python python_ai\app.py
```
You should see:
```
=======================================================
  Kravyo AI Recommendation Server
  http://127.0.0.1:5000/
  Endpoints: /recommend?user_id=N  |  /health
=======================================================
```

### Step 6: Open Kravyo in Chrome
```
http://localhost/Kravyo/
```

---

## API Usage

### Health Check
```
GET http://127.0.0.1:5000/health
```
Response:
```json
{"status": "ok", "service": "Kravyo AI Recommendation Server"}
```

### Get Recommendations
```
GET http://127.0.0.1:5000/recommend?user_id=5
```
Response:
```json
{
  "success": true,
  "user_id": 5,
  "algorithm": "content_based_filtering_tfidf_cosine",
  "recommendations": [
    {"menu_item_id": 15, "similarity_score": 0.92, "popularity_score": 0.65, "final_score": 0.87},
    {"menu_item_id": 21, "similarity_score": 0.81, "popularity_score": 0.50, "final_score": 0.75}
  ]
}
```

---

## PHP Fallback
If the Python server is **not running**, the Kravyo PHP application automatically
falls back to the built-in PHP popular-dish engine. The website never crashes.
