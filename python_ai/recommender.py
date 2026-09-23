"""
Kravyo — Python AI Recommendation Engine (recommender.py)
==========================================================

Machine Learning pipeline:
    1. Connect to MariaDB (kravyo_db via XAMPP)
    2. Load menu_items + categories + order history using Pandas
    3. Build a TF-IDF feature matrix over dish attributes (Scikit-learn)
    4. For a given customer, extract their order history
    5. Compute cosine similarity between ordered dishes and all available dishes
    6. Compute a popularity score from platform-wide order counts
    7. Final score = 0.80 × similarity_score + 0.20 × popularity_score
    8. Return top-N menu_item_ids ranked by final_score

Cold-start (zero orders):
    Return top-N dishes ranked by popularity only.

Libraries used:
    Pandas   — SQL → DataFrames, feature construction
    NumPy    — matrix operations, normalisation
    Scikit-learn — TfidfVectorizer, cosine_similarity
"""

import json
import os
import mysql.connector
import numpy as np
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity


# ─── Config Loader ─────────────────────────────────────────────────────────

_CONFIG_PATH = os.path.join(os.path.dirname(__file__), "ai_config.json")

# Immutable defaults — used when config file is missing or corrupt
_DEFAULTS = {
    "similarity_weight": 0.80,
    "popularity_weight": 0.20,
    "default_limit":     8,
}


def load_config() -> dict:
    """Read ai_config.json. Falls back to defaults if file is absent or invalid."""
    try:
        with open(_CONFIG_PATH, "r", encoding="utf-8") as f:
            cfg = json.load(f)
        # Validate numeric types; fall back to defaults per key if corrupt
        sw = float(cfg.get("similarity_weight", _DEFAULTS["similarity_weight"]))
        pw = float(cfg.get("popularity_weight",  _DEFAULTS["popularity_weight"]))
        lm = int(cfg.get("default_limit",        _DEFAULTS["default_limit"]))
        return {"similarity_weight": sw, "popularity_weight": pw, "default_limit": lm}
    except Exception:
        return dict(_DEFAULTS)


def save_config(cfg: dict) -> bool:
    """Persist validated config to disk. Returns True on success."""
    try:
        with open(_CONFIG_PATH, "w", encoding="utf-8") as f:
            json.dump(cfg, f, indent=4)
        return True
    except Exception:
        return False


# ─── Database Configuration ────────────────────────────────────────────────
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 3306,
    "database": "kravyo_db",
    "user": "root",
    "password": "",
    "charset": "utf8mb4",
}


def _get_connection():
    """Open and return a fresh MySQL/MariaDB connection."""
    return mysql.connector.connect(**DB_CONFIG)


# ─── Data Loading (Pandas) ─────────────────────────────────────────────────

def load_menu_items() -> pd.DataFrame:
    """
    Load all currently-available menu items with their category and kitchen info.
    Only items from approved, open kitchens are included.
    """
    sql = """
        SELECT
            m.id            AS menu_item_id,
            m.item_name,
            m.description,
            m.price,
            m.is_veg,
            m.is_jain_available,
            m.is_diabetic_friendly,
            m.is_available,
            m.kitchen_id,
            m.category_id,
            c.category_name,
            k.kitchen_name,
            k.city,
            k.hygiene_badge,
            k.approval_status,
            k.is_open,
            m.image
        FROM menu_items m
        JOIN categories c ON m.category_id = c.id
        JOIN kitchens   k ON m.kitchen_id  = k.id
        WHERE m.is_available    = 1
          AND k.approval_status = 'approved'
          AND k.is_open         = 1
    """
    conn = _get_connection()
    try:
        df = pd.read_sql(sql, conn)
    finally:
        conn.close()
    return df


def load_popularity_scores() -> pd.DataFrame:
    """
    Compute a platform-wide order frequency per menu_item_id from delivered orders.
    Returns DataFrame with columns [menu_item_id, order_count].
    """
    sql = """
        SELECT oi.menu_item_id, COUNT(*) AS order_count
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.order_status = 'delivered'
        GROUP BY oi.menu_item_id
    """
    conn = _get_connection()
    try:
        df = pd.read_sql(sql, conn)
    finally:
        conn.close()
    return df


def load_customer_history(customer_id: int) -> pd.DataFrame:
    """
    Load the menu_item_ids the customer has previously ordered (any status).
    Returns DataFrame with column [menu_item_id, order_count] representing
    how many times the customer ordered each dish.
    """
    sql = """
        SELECT oi.menu_item_id, COUNT(*) AS order_count
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.customer_id = %s
        GROUP BY oi.menu_item_id
    """
    conn = _get_connection()
    try:
        df = pd.read_sql(sql, conn, params=(customer_id,))
    finally:
        conn.close()
    return df


# ─── Feature Engineering (Pandas + NumPy) ─────────────────────────────────

def build_feature_strings(menu_df: pd.DataFrame) -> pd.Series:
    """
    Combine dish attributes into a single text string per dish for TF-IDF.

    Example output for one dish:
        "Paneer Tikka north_indian Veg Jain Diabetic"

    Fields used:
        item_name, category_name, is_veg, is_jain_available, is_diabetic_friendly
    """
    def _row_to_text(row) -> str:
        parts = []
        # Dish name (most important signal)
        parts.append(str(row["item_name"]).strip())
        # Category (repeat 2× to increase weight)
        cat = str(row["category_name"]).strip().replace(" ", "_")
        parts.extend([cat, cat])
        # Description words (optional, may be empty)
        if pd.notna(row["description"]) and str(row["description"]).strip():
            parts.append(str(row["description"]).strip())
        # Dietary flags as pseudo-tokens
        if int(row["is_veg"]) == 1:
            parts.append("Veg")
        else:
            parts.append("NonVeg")
        if int(row["is_jain_available"]) == 1:
            parts.append("Jain")
        if int(row["is_diabetic_friendly"]) == 1:
            parts.append("Diabetic")
        return " ".join(parts)

    return menu_df.apply(_row_to_text, axis=1)


# ─── Core Recommendation Logic ─────────────────────────────────────────────

def _normalise_scores(scores: np.ndarray) -> np.ndarray:
    """Min-max normalise a 1-D numpy array to [0, 1]. Handles all-zero edge case."""
    s_min, s_max = scores.min(), scores.max()
    if s_max == s_min:
        return np.zeros_like(scores, dtype=float)
    return (scores - s_min) / (s_max - s_min)


def get_recommendations(customer_id: int, limit: int = 8) -> list[dict]:
    """
    Main entry point called by Flask.

    Returns a list of dicts:
        [{"menu_item_id": int, "similarity_score": float,
          "popularity_score": float, "final_score": float}, ...]

    Algorithm:
        final_score = 0.80 × cosine_similarity + 0.20 × popularity_score
                                                        (both normalised 0-1)

    Cold-start: if customer has 0 orders, returns items ranked by popularity only.
    """
    # 1. Load data
    menu_df = load_menu_items()
    if menu_df.empty:
        return []

    popularity_df = load_popularity_scores()
    history_df    = load_customer_history(customer_id)

    # 2. Merge popularity into menu
    menu_df = menu_df.merge(popularity_df, on="menu_item_id", how="left")
    menu_df["order_count"] = menu_df["order_count"].fillna(0).astype(int)

    # 3. Build TF-IDF feature matrix for all available dishes (Scikit-learn)
    feature_strings = build_feature_strings(menu_df)
    vectorizer = TfidfVectorizer(
        ngram_range=(1, 2),   # unigrams + bigrams
        min_df=1,
        max_features=500,
        stop_words="english",
    )
    tfidf_matrix = vectorizer.fit_transform(feature_strings)  # shape: (N_dishes, vocab)

    # 4. Popularity score (NumPy normalisation)
    pop_raw = menu_df["order_count"].to_numpy(dtype=float)
    pop_norm = _normalise_scores(pop_raw)  # 0-1

    # ── COLD-START PATH ─────────────────────────────────────────────────────
    if history_df.empty:
        # No personal history → rank purely by popularity
        results = []
        for idx, row in menu_df.iterrows():
            results.append({
                "menu_item_id":   int(row["menu_item_id"]),
                "similarity_score": 0.0,
                "popularity_score": round(float(pop_norm[idx]), 4),
                "final_score":    round(float(pop_norm[idx]), 4),
            })
        results.sort(key=lambda x: x["final_score"], reverse=True)
        return results[:limit]

    # ── PERSONALISED PATH ────────────────────────────────────────────────────
    # 5. Identify which dishes the customer has ordered
    ordered_ids = set(history_df["menu_item_id"].tolist())
    ordered_mask = menu_df["menu_item_id"].isin(ordered_ids)
    ordered_indices = menu_df.index[ordered_mask].tolist()

    if not ordered_indices:
        # History exists in DB but none of those dishes are currently available
        # → fall back to popularity
        results = []
        for idx, row in menu_df.iterrows():
            results.append({
                "menu_item_id":   int(row["menu_item_id"]),
                "similarity_score": 0.0,
                "popularity_score": round(float(pop_norm[idx]), 4),
                "final_score":    round(float(pop_norm[idx]), 4),
            })
        results.sort(key=lambda x: x["final_score"], reverse=True)
        return results[:limit]

    # 6. Compute cosine similarity:
    #    For each available dish, similarity = mean cosine_similarity with
    #    all previously ordered dishes, weighted by how many times ordered.
    ordered_history = history_df.set_index("menu_item_id")["order_count"]

    sim_scores = np.zeros(len(menu_df), dtype=float)
    total_weight = 0.0

    for dish_id, freq in ordered_history.items():
        # Find this dish's row in menu_df (may or may not still be available)
        mask = menu_df["menu_item_id"] == dish_id
        if not mask.any():
            continue
        dish_idx = menu_df.index[mask][0]
        dish_vec = tfidf_matrix[dish_idx]                     # (1, vocab)
        # cosine similarity between this ordered dish and ALL available dishes
        sims = cosine_similarity(dish_vec, tfidf_matrix).flatten()  # (N_dishes,)
        weight = float(freq)
        sim_scores += sims * weight
        total_weight += weight

    if total_weight > 0:
        sim_scores /= total_weight   # weighted average

    sim_norm = _normalise_scores(sim_scores)   # 0-1 (NumPy)

    # 7. Final blended score — weights come from ai_config.json
    cfg = load_config()
    SIMILARITY_WEIGHT = cfg["similarity_weight"]
    POPULARITY_WEIGHT = cfg["popularity_weight"]
    final_scores = SIMILARITY_WEIGHT * sim_norm + POPULARITY_WEIGHT * pop_norm

    # 8. Build result list
    results = []
    for i, row in enumerate(menu_df.itertuples(index=False)):
        results.append({
            "menu_item_id":   int(row.menu_item_id),
            "similarity_score": round(float(sim_norm[i]), 4),
            "popularity_score": round(float(pop_norm[i]), 4),
            "final_score":    round(float(final_scores[i]), 4),
        })

    # 9. Sort descending by final_score
    results.sort(key=lambda x: x["final_score"], reverse=True)
    return results[:limit]
