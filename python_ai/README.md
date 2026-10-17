# Kravyo — Python AI Recommendation Microservice

## What is this?

This directory contains the **Python Machine Learning Recommendation Engine** for the Kravyo cloud kitchen platform.
It runs as a standalone **Flask microservice** alongside XAMPP and exposes a high-performance REST API that the PHP backend queries to deliver personalized dish recommendations to customers.

---

## Technology Stack

| Technology | Role |
|---|---|
| **Python 3.10+** | Core runtime for the AI/ML service |
| **Flask** | Lightweight REST API web server |
| **Pandas** | In-memory relational data loading & feature engineering |
| **NumPy** | Matrix normalisation and score blending |
| **Scikit-learn** | TF-IDF vectorisation and Cosine Similarity calculation |
| **mysql-connector-python** | Direct high-speed connection to MariaDB (`kravyo_db`) |

---

## How the Algorithm Works (Viva & Architecture Explanation)

```
Customer Order History (kravyo_db)
        ↓
Pandas loads menu_items, categories, order_items from MariaDB
        ↓
Feature Construction: item_name + category_name + dietary flags → composite text token
        ↓
TF-IDF Vectorization (Scikit-learn): transforms text features into high-dimensional vectors
        ↓
Cosine Similarity: computes angle between customer's previously ordered dishes and all active menu items
        ↓
Popularity Score: platform-wide order frequency (NumPy normalized 0.0 to 1.0)
        ↓
Final Blended Score = (similarity_weight × similarity_score) + (popularity_weight × popularity_score)
        ↓
Top-N dishes sorted by final_score DESC
        ↓
Flask REST API → Structured JSON response
        ↓
PHP (app/models/Recommendation.php) consumes JSON via cURL
        ↓
Kravyo Customer UI renders personalized "Recommended For You" carousel
```

### Cold-Start Strategy (New Customer)
If a customer has **zero previous orders**, the ML engine has no user-specific history. In this scenario, it returns dishes ranked **purely by platform popularity**. This same robust fallback is used if the customer is an anonymous guest.

---

## Directory & File Structure

| File | Purpose |
|---|---|
| `app.py` | Flask REST API server — exposes `/recommend`, `/config`, and `/health` endpoints |
| `recommender.py` | Core ML engine — Pandas data loading + Scikit-learn TF-IDF + Cosine Similarity |
| `ai_config.json` | Persistent JSON configuration for ML model weights (`similarity_weight`, `popularity_weight`, `default_limit`) |
| `requirements.txt` | Python library dependencies |
| `README.md` | This technical documentation file |

---

## Setup & Run Guide (Windows / XAMPP)

### Step 1: Verify Python Installation
```powershell
python --version
```
*Requires Python 3.10 or higher.*

### Step 2: Create a Dedicated Virtual Environment
```powershell
cd c:\xampp\htdocs\Kravyo\python_ai
python -m venv .venv
.\.venv\Scripts\activate
```

### Step 3: Install Required Dependencies
```powershell
pip install -r requirements.txt
```

### Step 4: Ensure XAMPP MySQL is Running
Apache and MySQL must be running in your XAMPP Control Panel with `kravyo_db` imported.

### Step 5: Start the Python AI Server
```powershell
python app.py
```
You should see:
```
=======================================================
  Kravyo AI Recommendation Server
  http://127.0.0.1:5000/
  Endpoints: /recommend?user_id=N  |  /config  |  /health
=======================================================
```

### Step 6: Test in Browser / Postman
Open your browser and navigate to:
```
http://127.0.0.1:5000/health
```

---

## REST API Reference

### 1. Health Probe
- **URL:** `GET http://127.0.0.1:5000/health`
- **Response:**
  ```json
  {
    "status": "ok",
    "service": "Kravyo AI Recommendation Server"
  }
  ```

### 2. Personalized Dish Recommendations
- **URL:** `GET http://127.0.0.1:5000/recommend?user_id={id}&limit={n}`
- **Parameters:**
  - `user_id` *(integer, required)*: The customer's ID from `customers` table.
  - `limit` *(integer, optional)*: Maximum number of dishes to return (defaults to 8).
- **Response:**
  ```json
  {
    "success": true,
    "user_id": 5,
    "algorithm": "content_based_filtering_tfidf_cosine",
    "weights": {
      "similarity_weight": 0.70,
      "popularity_weight": 0.30
    },
    "recommendations": [
      {
        "menu_item_id": 15,
        "similarity_score": 0.92,
        "popularity_score": 0.65,
        "final_score": 0.839
      },
      {
        "menu_item_id": 21,
        "similarity_score": 0.81,
        "popularity_score": 0.50,
        "final_score": 0.717
      }
    ]
  }
  ```

### 3. Read AI Hyperparameter Config
- **URL:** `GET http://127.0.0.1:5000/config`
- **Response:**
  ```json
  {
    "success": true,
    "config": {
      "similarity_weight": 0.7,
      "popularity_weight": 0.3,
      "default_limit": 6
    }
  }
  ```

### 4. Update AI Hyperparameters (Admin Live Tuning)
- **URL:** `POST http://127.0.0.1:5000/config`
- **Payload:**
  ```json
  {
    "similarity_weight": 0.8,
    "popularity_weight": 0.2,
    "default_limit": 8
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "message": "AI configuration updated successfully",
    "config": {
      "similarity_weight": 0.8,
      "popularity_weight": 0.2,
      "default_limit": 8
    }
  }
  ```

---

## 🛡️ Zero-Downtime PHP Fallback Engine

The Kravyo web platform (`app/models/Recommendation.php`) communicates with this microservice via an asynchronous cURL request with a strict 1.5-second timeout.
- If the Python service is active, customers receive real-time ML-powered suggestions.
- If the Python service is offline or unreachable, the PHP backend **instantly and transparently falls back** to its built-in heuristic scoring engine (category affinity + dietary preference match + platform order counts). The platform never crashes or displays error dialogs to the end user.
